<?php
// Author: Lai Chee Kit
// Module 4: State Pattern. The Admin is working on the ticket.

namespace App\States;

use App\Models\Ticket;

class InProgressState extends BaseTicketState
{
    public function name(): string
    {
        return 'in_progress';
    }

    // In Progress -> Resolved
    public function resolve(Ticket $ticket): void
    {
        $ticket->moveTo(new ResolvedState());
    }

    public function availableActions(): array
    {
        return ['resolve'];
    }

    // A reopened ticket is not resolved any more, so clear the resolved time
    public function onEnter(Ticket $ticket): void
    {
        $ticket->resolved_at = null;
    }
}
