<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // The template's test account (kept as in the team template)
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // The tickets below point at equipment rows, so those rows must exist first
        $this->call(PlaceholderEquipmentSeeder::class);

        // Module 4 (Lai Chee Kit): TEMPORARY test accounts until Module 1's users are ready
        $student = User::factory()->create(['name' => 'Sam Student', 'email' => 'student@example.com', 'role' => 'student']);
        $staff = User::factory()->create(['name' => 'Stella Staff', 'email' => 'staff@example.com', 'role' => 'staff']);
        $admin = User::factory()->create(['name' => 'Adam Admin', 'email' => 'admin@example.com', 'role' => 'admin']);

        // Module 4 (Lai Chee Kit): sample tickets in every status.
        // Columns: owner, category, priority, equipment ID, title (stored as subject), description, status, submitted N days ago, resolved N days ago
        // Priority and status use the template's lowercase enum values.
        $samples = [
            [$student, 'Fault', 'high', 7, 'Projector shows no image', 'The projector powers on but the screen stays blank, even after changing the HDMI cable.', 'open', 1, null],
            [$student, 'Usage Problem', 'low', null, 'How do I extend my borrowing period?', 'I need the camera for two more days for my assignment shooting.', 'in_progress', 3, null],
            [$staff, 'General Feedback', 'medium', null, 'Please add more HDMI adapters', 'Lab 3 often runs out of USB-C to HDMI adapters during presentation weeks.', 'open', 2, null],
            [$staff, 'Fault', 'high', 4, 'Laptop does not charge', 'The charging light stays off with two different chargers.', 'in_progress', 5, null],
            [$student, 'Fault', 'medium', 7, 'Projector remote is missing', 'The remote control was not in the bag when I collected the projector.', 'resolved', 6, 2],
            [$student, 'Fault', 'medium', 11, 'Camera battery drains quickly', 'A full battery lasts only about 20 minutes.', 'resolved', 12, 9],
            [$staff, 'Usage Problem', 'low', null, 'Cannot find the oscilloscope manual', 'Is there a PDF manual for the digital oscilloscope?', 'closed', 10, 8],
            [$student, 'Fault', 'low', 9, 'Old projector has a dim lamp', 'The picture is hard to see even in a dark room.', 'closed', 13, 11],
            [$staff, 'General Feedback', 'low', null, 'Borrowing counter opening hours', 'Could the counter open until 6 pm on Fridays?', 'open', 0, null],
        ];

        foreach ($samples as [$owner, $category, $priority, $equipmentId, $title, $description, $status, $submittedDaysAgo, $resolvedDaysAgo]) {
            $ticket = new Ticket([
                'category' => $category,
                'priority' => $priority,
                'equipment_id' => $equipmentId,
                'subject' => $title,
                'description' => $description,
            ]);
            $ticket->user_id = $owner->id;
            $ticket->status = $status;
            $ticket->created_at = Carbon::now()->subDays($submittedDaysAgo);
            $ticket->resolved_at = $resolvedDaysAgo === null ? null : Carbon::now()->subDays($resolvedDaysAgo);
            $ticket->closed_at = $status === 'closed' ? Carbon::now()->subDays($resolvedDaysAgo - 1) : null;
            $ticket->save();

            // A short conversation on the tickets the Admin already worked on
            if ($status !== 'open') {
                $this->reply($ticket, $admin, 'Thank you for the report. We are looking into it.', $submittedDaysAgo);
            }
            if ($resolvedDaysAgo !== null) {
                $this->reply($ticket, $admin, 'This has been handled. Please confirm that the problem is solved.', $resolvedDaysAgo);
                Notification::create([
                    'user_id' => $owner->id,
                    'ticket_id' => $ticket->id,
                    'title' => 'Status changed',
                    'message' => "Your ticket #{$ticket->id} was changed to Resolved",
                    'notification_type' => 'status_changed',
                    'is_read' => false,
                ]);
            }
            if ($status === 'open') {
                Notification::create([
                    'user_id' => $admin->id,
                    'ticket_id' => $ticket->id,
                    'title' => 'New ticket',
                    'message' => "New ticket #{$ticket->id}: {$ticket->subject}",
                    'notification_type' => 'new_ticket',
                    'is_read' => false,
                ]);
            }
        }
    }

    private function reply(Ticket $ticket, User $writer, string $message, int $daysAgo): void
    {
        $reply = $ticket->replies()->make(['message' => $message]);
        $reply->user_id = $writer->id;
        $reply->created_at = Carbon::now()->subDays($daysAgo);
        $reply->save();
    }
}
