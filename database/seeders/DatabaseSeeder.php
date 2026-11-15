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
        // Module 4 (Lai Chee Kit): TEMPORARY test accounts until Module 1's users are ready
        $student = User::factory()->create(['name' => 'Sam Student', 'email' => 'student@example.com', 'role' => 'student']);
        $staff = User::factory()->create(['name' => 'Stella Staff', 'email' => 'staff@example.com', 'role' => 'staff']);
        $admin = User::factory()->create(['name' => 'Adam Admin', 'email' => 'admin@example.com', 'role' => 'admin']);

        // Module 4 (Lai Chee Kit): sample tickets in every status.
        // Columns: owner, category, priority, equipment ID, title, description, status, submitted N days ago, resolved N days ago
        $samples = [
            [$student, 'Fault', 'High', 7, 'Projector shows no image', 'The projector powers on but the screen stays blank, even after changing the HDMI cable.', 'Open', 1, null],
            [$student, 'Usage Problem', 'Low', null, 'How do I extend my borrowing period?', 'I need the camera for two more days for my assignment shooting.', 'In Progress', 3, null],
            [$staff, 'General Feedback', 'Medium', null, 'Please add more HDMI adapters', 'Lab 3 often runs out of USB-C to HDMI adapters during presentation weeks.', 'Open', 2, null],
            [$staff, 'Fault', 'High', 4, 'Laptop does not charge', 'The charging light stays off with two different chargers.', 'In Progress', 5, null],
            [$student, 'Fault', 'Medium', 7, 'Projector remote is missing', 'The remote control was not in the bag when I collected the projector.', 'Resolved', 6, 2],
            [$student, 'Fault', 'Medium', 11, 'Camera battery drains quickly', 'A full battery lasts only about 20 minutes.', 'Resolved', 12, 9],
            [$staff, 'Usage Problem', 'Low', null, 'Cannot find the oscilloscope manual', 'Is there a PDF manual for the digital oscilloscope?', 'Closed', 10, 8],
            [$student, 'Fault', 'Low', 9, 'Old projector has a dim lamp', 'The picture is hard to see even in a dark room.', 'Closed', 13, 11],
            [$staff, 'General Feedback', 'Low', null, 'Borrowing counter opening hours', 'Could the counter open until 6 pm on Fridays?', 'Open', 0, null],
        ];

        foreach ($samples as [$owner, $category, $priority, $equipmentId, $title, $description, $status, $submittedDaysAgo, $resolvedDaysAgo]) {
            $ticket = new Ticket([
                'category' => $category,
                'priority' => $priority,
                'equipment_id' => $equipmentId,
                'title' => $title,
                'description' => $description,
            ]);
            $ticket->user_id = $owner->id;
            $ticket->status = $status;
            $ticket->created_at = Carbon::now()->subDays($submittedDaysAgo);
            $ticket->resolved_at = $resolvedDaysAgo === null ? null : Carbon::now()->subDays($resolvedDaysAgo);
            $ticket->closed_at = $status === 'Closed' ? Carbon::now()->subDays($resolvedDaysAgo - 1) : null;
            $ticket->save();

            // A short conversation on the tickets the Admin already worked on
            if ($status !== 'Open') {
                $this->reply($ticket, $admin, 'Thank you for the report. We are looking into it.', $submittedDaysAgo);
            }
            if ($resolvedDaysAgo !== null) {
                $this->reply($ticket, $admin, 'This has been handled. Please confirm that the problem is solved.', $resolvedDaysAgo);
                Notification::create([
                    'user_id' => $owner->id,
                    'ticket_id' => $ticket->id,
                    'type' => 'status_changed',
                    'message' => "Your ticket #{$ticket->id} was changed to Resolved",
                ]);
            }
            if ($status === 'Open') {
                Notification::create([
                    'user_id' => $admin->id,
                    'ticket_id' => $ticket->id,
                    'type' => 'new_ticket',
                    'message' => "New ticket #{$ticket->id}: {$ticket->title}",
                ]);
            }
        }
    }

    private function reply(Ticket $ticket, User $writer, string $body, int $daysAgo): void
    {
        $reply = $ticket->replies()->make(['body' => $body]);
        $reply->user_id = $writer->id;
        $reply->created_at = Carbon::now()->subDays($daysAgo);
        $reply->save();
    }
}
