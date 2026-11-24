<?php
// Author: Lai Chee Kit
// Module 4: State Pattern. The Admin says it is solved; waiting for the submitter to confirm.

namespace App\States;

use App\Models\Ticket;

class ResolvedState extends BaseTicketState
{
    public function name(): string
    {
        return 'resolved';
    }

    // Resolved -> Closed (the submitter confirms, or 7 days pass)
    public function close(Ticket $ticket): void
    {
        $ticket->moveTo(new ClosedState());
    }

    // Resolved -> In Progress (the submitter says it is not solved)
    public function reopen(Ticket $ticket): void
    {
        $ticket->moveTo(new InProgressState());
    }

    public function availableActions(): array
    {
        return ['close', 'reopen'];
    }

    // Record when it was resolved: used by auto-close and the handling-time report
    public function onEnter(Ticket $ticket): void
    {
        $ticket->resolved_at = now();
    }
}
