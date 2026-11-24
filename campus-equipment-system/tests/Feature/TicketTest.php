<?php
// Author: Lai Chee Kit
// Module 4: tests for submit / list / detail / edit of tickets and their photos

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlaceholderEquipmentSeeder::class);
    }

    // In the built-in fake data: 6 = PRJ-0001 (Available), 9 = PRJ-0004 (Retired)
    // The form field is "title"; the tickets table stores it in the "subject" column.
    private function validTicket(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Broken projector',
            'description' => 'No image on screen.',
            'category' => 'Fault',
            'priority' => 'high',
            'equipment_id' => 6,
        ], $overrides);
    }

    // Saves a ticket straight into the database (no HTTP form), mapping "title" to "subject"
    private function createTicket(User $owner, array $overrides = []): Ticket
    {
        $data = $this->validTicket($overrides);
        $data['subject'] = $data['title'];
        unset($data['title']);

        return $owner->tickets()->create($data);
    }

    private function photo(string $name = 'photo.jpg'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, 'image/jpeg');
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/tickets')->assertRedirect('/login');
    }

    public function test_user_can_submit_a_ticket_and_it_starts_open(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post('/tickets', $this->validTicket())->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'subject' => 'Broken projector',
            'equipment_id' => 6,
            'status' => 'open',
        ]);
        // The Admin gets a bell notification
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'notification_type' => 'new_ticket']);
    }

    public function test_fault_ticket_needs_equipment(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['equipment_id' => '']))
            ->assertSessionHasErrors('equipment_id');

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_feedback_ticket_does_not_need_equipment(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['category' => 'General Feedback', 'equipment_id' => '']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_bad_values_from_the_browser_are_rejected_or_ignored(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['equipment_id' => 999]))
            ->assertSessionHasErrors('equipment_id');

        // Retired equipment cannot be chosen for a new ticket
        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['equipment_id' => 9]))
            ->assertSessionHasErrors('equipment_id');

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['category' => 'Hacking']))
            ->assertSessionHasErrors('category');

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['priority' => 'urgent']))
            ->assertSessionHasErrors('priority');

        // A "status" or "user_id" sent by the browser must not be saved
        $other = User::factory()->create();
        $this->actingAs($user)->post('/tickets', $this->validTicket(['status' => 'closed', 'user_id' => $other->id]));
        $this->assertDatabaseHas('tickets', ['user_id' => $user->id, 'status' => 'open']);
        $this->assertDatabaseMissing('tickets', ['user_id' => $other->id]);
    }

    public function test_user_sees_only_own_tickets_but_admin_sees_all(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createTicket($alice, ['title' => 'Alice ticket']);
        $this->createTicket($bob, ['title' => 'Bob ticket']);

        $this->actingAs($alice)->get('/tickets')->assertSee('Alice ticket')->assertDontSee('Bob ticket');
        $this->actingAs($admin)->get('/tickets')->assertSee('Alice ticket')->assertSee('Bob ticket');
    }

    public function test_user_cannot_open_another_users_ticket(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = $this->createTicket($bob);

        $this->actingAs($alice)->get("/tickets/{$ticket->id}")->assertForbidden();
        $this->actingAs($bob)->get("/tickets/{$ticket->id}")->assertOk()->assertSee('PRJ-0001');
        $this->actingAs($admin)->get("/tickets/{$ticket->id}")->assertOk();
    }

    public function test_retired_equipment_still_shows_on_an_old_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = $this->createTicket($user, ['equipment_id' => 9]);

        $this->actingAs($user)->get("/tickets/{$ticket->id}")->assertSee('PRJ-0004')->assertSee('(retired)');
    }

    public function test_description_is_escaped_in_the_detail_page(): void
    {
        $user = User::factory()->create();
        $ticket = $this->createTicket($user, ['description' => '<script>alert(1)</script>']);

        $this->actingAs($user)->get("/tickets/{$ticket->id}")
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    // ---------- Filters and search (SQL Injection defence) ----------

    public function test_list_can_be_filtered_and_searched(): void
    {
        $user = User::factory()->create();
        $this->createTicket($user, ['title' => 'Projector lamp']);
        $this->createTicket($user, ['title' => 'Counter hours', 'category' => 'General Feedback', 'equipment_id' => null]);

        $this->actingAs($user)->get('/tickets?category=Fault')->assertSee('Projector lamp')->assertDontSee('Counter hours');
        $this->actingAs($user)->get('/tickets?q=counter')->assertSee('Counter hours')->assertDontSee('Projector lamp');
    }

    public function test_sql_injection_text_in_search_is_treated_as_plain_text(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $this->createTicket($alice, ['title' => 'Alice ticket']);
        $this->createTicket($bob, ['title' => 'Bob ticket']);

        // If the keyword were glued into the SQL, "OR 1=1" would show every ticket
        $this->actingAs($alice)->get('/tickets?q='.urlencode("' OR 1=1 --"))
            ->assertOk()->assertDontSee('Alice ticket')->assertDontSee('Bob ticket');

        // % is a LIKE wildcard; escaped, it only matches a real "%" character
        $this->actingAs($alice)->get('/tickets?q='.urlencode('%'))->assertOk()->assertDontSee('Alice ticket');

        $this->assertDatabaseCount('tickets', 2);
    }

    public function test_unknown_sort_or_status_value_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/tickets?sort='.urlencode('id; DROP TABLE tickets'))->assertSessionHasErrors('sort');
        $this->actingAs($user)->get('/tickets?status=Hacked')->assertSessionHasErrors('status');
        $this->actingAs($user)->get('/tickets?sort=oldest')->assertOk();
    }

    // ---------- Photos ----------

    public function test_photos_are_saved_privately_and_only_viewers_of_the_ticket_can_open_them(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($owner)
            ->post('/tickets', $this->validTicket(['photos' => [$this->photo(), $this->photo('second.png')]]))
            ->assertSessionHasNoErrors();

        $ticket = Ticket::first();
        $this->assertCount(2, $ticket->photos);
        Storage::disk('local')->assertExists($ticket->photos[0]->file_path);

        $url = '/attachments/'.$ticket->photos[0]->id;
        $this->actingAs($owner)->get($url)->assertOk();
        $this->actingAs($stranger)->get($url)->assertForbidden();
    }

    public function test_wrong_file_type_and_too_many_photos_are_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['photos' => [UploadedFile::fake()->create('virus.php', 10, 'text/x-php')]]))
            ->assertSessionHasErrors('photos.0');

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['photos' => [UploadedFile::fake()->create('big.jpg', 3000, 'image/jpeg')]]))
            ->assertSessionHasErrors('photos.0');

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['photos' => [$this->photo(), $this->photo(), $this->photo(), $this->photo()]]))
            ->assertSessionHasErrors('photos');

        $this->assertDatabaseCount('tickets', 0);
    }

    // ---------- Edit ----------

    public function test_owner_can_edit_an_open_ticket_and_swap_photos(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/tickets', $this->validTicket(['photos' => [$this->photo(), $this->photo(), $this->photo()]]));
        $ticket = Ticket::first();
        $oldPhoto = $ticket->photos[0];

        // Already 3 photos: adding one more without removing one is refused
        $this->actingAs($user)
            ->put("/tickets/{$ticket->id}", $this->validTicket(['photos' => [$this->photo()]]))
            ->assertSessionHasErrors('photos');

        $this->actingAs($user)
            ->put("/tickets/{$ticket->id}", $this->validTicket([
                'title' => 'Changed title',
                'remove_photos' => [$oldPhoto->id],
                'photos' => [$this->photo('new.jpg')],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'subject' => 'Changed title']);
        $this->assertCount(3, $ticket->fresh()->photos);
        Storage::disk('local')->assertMissing($oldPhoto->file_path);
    }

    public function test_ticket_cannot_be_edited_by_others_or_after_work_started(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = $this->createTicket($owner);

        // Not the owner (even the Admin cannot rewrite a user's ticket)
        $this->actingAs($admin)->put("/tickets/{$ticket->id}", $this->validTicket(['title' => 'Hacked']))->assertForbidden();

        // Once the Admin starts, the owner cannot edit any more
        $this->actingAs($admin)->post("/tickets/{$ticket->id}/status", ['action' => 'start']);
        $this->actingAs($owner)->get("/tickets/{$ticket->id}/edit")->assertForbidden();
        $this->actingAs($owner)->put("/tickets/{$ticket->id}", $this->validTicket(['title' => 'Too late']))->assertForbidden();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'subject' => 'Broken projector']);
    }
}
