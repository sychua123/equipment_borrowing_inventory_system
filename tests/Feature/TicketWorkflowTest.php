<?php
// Author: Lai Chee Kit
// Module 4: tests for the ticket status flow (State Pattern), replies, notifications and auto-close

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\States\InvalidTicketTransition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $admin;
    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->ticket = $this->owner->tickets()->create([
            'title' => 'Broken projector',
            'description' => 'No image on screen.',
            'category' => 'Fault',
            'priority' => 'High',
            'equipment_id' => 6,
        ]);
    }

    private function act(User $user, string $action)
    {
        return $this->actingAs($user)->post("/tickets/{$this->ticket->id}/status", ['action' => $action]);
    }

    public function test_full_flow_open_to_closed(): void
    {
        $this->act($this->admin, 'start')->assertSessionHas('success');
        $this->assertSame('In Progress', $this->ticket->fresh()->status);

        $this->act($this->admin, 'resolve');
        $this->assertSame('Resolved', $this->ticket->fresh()->status);
        $this->assertNotNull($this->ticket->fresh()->resolved_at);

        $this->act($this->owner, 'close');
        $this->assertSame('Closed', $this->ticket->fresh()->status);
        $this->assertNotNull($this->ticket->fresh()->closed_at);
    }

    public function test_owner_can_reopen_a_resolved_ticket(): void
    {
        $this->act($this->admin, 'start');
        $this->act($this->admin, 'resolve');
        $this->act($this->owner, 'reopen');

        $ticket = $this->ticket->fresh();
        $this->assertSame('In Progress', $ticket->status);
        $this->assertNull($ticket->resolved_at);
    }

    public function test_owner_can_withdraw_only_while_open(): void
    {
        $this->act($this->admin, 'start');
        $this->act($this->owner, 'withdraw')->assertSessionHas('error');
        $this->assertSame('In Progress', $this->ticket->fresh()->status);

        $second = $this->owner->tickets()->create($this->ticket->only(['title', 'description', 'category', 'priority', 'equipment_id']));
        $this->actingAs($this->owner)->post("/tickets/{$second->id}/status", ['action' => 'withdraw']);
        $this->assertSame('Closed', $second->fresh()->status);
    }

    public function test_policy_stops_the_wrong_person(): void
    {
        $stranger = User::factory()->create();

        // Only the Admin may start or resolve
        $this->act($this->owner, 'start')->assertForbidden();
        // Only the submitter may withdraw, close or reopen
        $this->act($this->admin, 'withdraw')->assertForbidden();
        $this->act($stranger, 'withdraw')->assertForbidden();

        $this->assertSame('Open', $this->ticket->fresh()->status);
    }

    public function test_state_stops_a_wrong_step_and_unknown_actions_are_rejected(): void
    {
        // Open -> Resolved skips "In Progress": the state refuses
        $this->act($this->admin, 'resolve')->assertSessionHas('error');
        $this->assertSame('Open', $this->ticket->fresh()->status);

        // Only the five action names are accepted
        $this->act($this->admin, 'delete')->assertSessionHasErrors('action');
        $this->act($this->admin, 'moveTo')->assertSessionHasErrors('action');
    }

    public function test_state_objects_throw_on_invalid_transitions(): void
    {
        $this->assertSame(['start', 'withdraw'], $this->ticket->state()->availableActions());
        $this->assertTrue($this->ticket->state()->canEdit());

        $this->ticket->state()->withdraw($this->ticket);
        $this->assertSame('Closed', $this->ticket->status);
        $this->assertFalse($this->ticket->state()->canReply());
        $this->assertSame([], $this->ticket->state()->availableActions());

        $this->expectException(InvalidTicketTransition::class);
        $this->ticket->state()->reopen($this->ticket);
    }

    public function test_status_cannot_be_set_through_the_edit_form(): void
    {
        $this->actingAs($this->owner)->put("/tickets/{$this->ticket->id}", [
            'title' => 'Broken projector', 'description' => 'x', 'category' => 'Fault', 'priority' => 'Low',
            'equipment_id' => 6, 'status' => 'Closed',
        ]);

        $this->assertSame('Open', $this->ticket->fresh()->status);
    }

    // ---------- Replies ----------

    public function test_owner_and_admin_can_reply_and_the_other_side_is_notified(): void
    {
        $this->actingAs($this->admin)->post("/tickets/{$this->ticket->id}/replies", ['body' => 'We are checking it.'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ticket_notifications', ['user_id' => $this->owner->id, 'type' => 'new_reply']);

        $this->actingAs($this->owner)->post("/tickets/{$this->ticket->id}/replies", ['body' => 'Thank you.']);
        $this->assertDatabaseHas('ticket_notifications', ['user_id' => $this->admin->id, 'type' => 'new_reply']);

        $this->actingAs($this->owner)->get("/tickets/{$this->ticket->id}")
            ->assertSee('We are checking it.')->assertSee('Thank you.');
    }

    public function test_stranger_cannot_reply_and_closed_ticket_takes_no_replies(): void
    {
        $stranger = User::factory()->create();
        $this->actingAs($stranger)->post("/tickets/{$this->ticket->id}/replies", ['body' => 'Hello'])->assertForbidden();

        $this->act($this->owner, 'withdraw');
        $this->actingAs($this->owner)->post("/tickets/{$this->ticket->id}/replies", ['body' => 'One more thing'])->assertForbidden();

        $this->assertDatabaseCount('ticket_replies', 0);
    }

    public function test_replies_cannot_be_edited_or_deleted(): void
    {
        $this->actingAs($this->owner)->post("/tickets/{$this->ticket->id}/replies", ['body' => 'First']);
        $replyId = $this->ticket->replies()->first()->id;

        // No such routes exist
        $this->actingAs($this->owner)->put("/tickets/{$this->ticket->id}/replies/{$replyId}", ['body' => 'Changed'])->assertStatus(404);
        $this->actingAs($this->owner)->delete("/tickets/{$this->ticket->id}/replies/{$replyId}")->assertStatus(404);
        $this->actingAs($this->owner)->delete("/tickets/{$this->ticket->id}")->assertStatus(405);
    }

    // ---------- Notifications ----------

    public function test_status_change_notifies_the_owner_and_opening_marks_it_read(): void
    {
        $this->act($this->admin, 'start');

        $notification = $this->owner->ticketNotifications()->first();
        $this->assertSame('status_changed', $notification->type);
        $this->assertTrue($notification->isUnread());

        $this->actingAs($this->owner)->get('/notifications')->assertSee('In Progress');

        // Another user cannot open it
        $this->actingAs($this->admin)->get("/notifications/{$notification->id}")->assertForbidden();

        $this->actingAs($this->owner)->get("/notifications/{$notification->id}")
            ->assertRedirect("/tickets/{$this->ticket->id}");
        $this->assertFalse($notification->fresh()->isUnread());
    }

    public function test_mark_all_as_read(): void
    {
        $this->act($this->admin, 'start');
        $this->act($this->admin, 'resolve');
        $this->assertSame(2, $this->owner->ticketNotifications()->whereNull('read_at')->count());

        $this->actingAs($this->owner)->post('/notifications/read-all');
        $this->assertSame(0, $this->owner->ticketNotifications()->whereNull('read_at')->count());
    }

    // ---------- Auto-close ----------

    public function test_resolved_ticket_closes_itself_after_seven_days(): void
    {
        $this->act($this->admin, 'start');
        $this->act($this->admin, 'resolve');

        // 6 days later: still Resolved
        $this->travel(6)->days();
        $this->actingAs($this->owner)->get('/tickets');
        $this->assertSame('Resolved', $this->ticket->fresh()->status);

        // After the 7th day: closed when someone opens the ticket list
        $this->travel(2)->days();
        $this->actingAs($this->owner)->get('/tickets');
        $this->assertSame('Closed', $this->ticket->fresh()->status);
        $this->assertDatabaseHas('ticket_notifications', [
            'user_id' => $this->owner->id,
            'message' => "Your ticket #{$this->ticket->id} was changed to Closed automatically",
        ]);
    }
}
