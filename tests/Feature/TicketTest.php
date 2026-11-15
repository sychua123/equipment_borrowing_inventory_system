<?php
// Author: Lai Chee Kit
// Module 4: tests for submit / list / detail of tickets

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    private function validTicket(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Broken projector',
            'description' => 'No image on screen.',
            'category' => 'Fault',
            'priority' => 'High',
            'equipment_id' => 2,
        ], $overrides);
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/tickets')->assertRedirect('/login');
    }

    public function test_user_can_submit_a_ticket_and_it_starts_open(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tickets', $this->validTicket())->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'title' => 'Broken projector',
            'equipment_id' => 2,
            'status' => 'Open',
        ]);
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

        $this->actingAs($user)
            ->post('/tickets', $this->validTicket(['category' => 'Hacking']))
            ->assertSessionHasErrors('category');

        // A "status" or "user_id" sent by the browser must not be saved
        $other = User::factory()->create();
        $this->actingAs($user)->post('/tickets', $this->validTicket(['status' => 'Closed', 'user_id' => $other->id]));
        $this->assertDatabaseHas('tickets', ['user_id' => $user->id, 'status' => 'Open']);
        $this->assertDatabaseMissing('tickets', ['user_id' => $other->id]);
    }

    public function test_user_sees_only_own_tickets_but_admin_sees_all(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $alice->tickets()->create($this->validTicket(['title' => 'Alice ticket']));
        $bob->tickets()->create($this->validTicket(['title' => 'Bob ticket']));

        $this->actingAs($alice)->get('/tickets')->assertSee('Alice ticket')->assertDontSee('Bob ticket');
        $this->actingAs($admin)->get('/tickets')->assertSee('Alice ticket')->assertSee('Bob ticket');
    }

    public function test_user_cannot_open_another_users_ticket(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = $bob->tickets()->create($this->validTicket());

        $this->actingAs($alice)->get("/tickets/{$ticket->id}")->assertForbidden();
        $this->actingAs($bob)->get("/tickets/{$ticket->id}")->assertOk()->assertSee('PRJ-0001');
        $this->actingAs($admin)->get("/tickets/{$ticket->id}")->assertOk();
    }

    public function test_description_is_escaped_in_the_detail_page(): void
    {
        $user = User::factory()->create();
        $ticket = $user->tickets()->create($this->validTicket(['description' => '<script>alert(1)</script>']));

        $this->actingAs($user)->get("/tickets/{$ticket->id}")
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
