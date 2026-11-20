<?php
// Author: Lai Chee Kit
// Module 4: closes tickets that have been Resolved for more than 7 days with no answer.
// It runs whenever the ticket list or the dashboard is opened, so no scheduled task is needed.

namespace App\Services;

use App\Models\Ticket;

class TicketAutoCloser
{
    public function __construct(private NotificationService $notifications)
    {
    }

    // Returns how many tickets were closed
    public function closeExpired(): int
    {
        $deadline = now()->subDays(config('module4.auto_close_days'));

        $expired = Ticket::query()
            ->where('status', 'resolved')
            ->where('resolved_at', '<=', $deadline)
            ->get();

        foreach ($expired as $ticket) {
            // Go through the State Pattern like every other status change
            $ticket->state()->close($ticket);
            $this->notifications->statusChanged($ticket, null);
        }

        return $expired->count();
    }
}
