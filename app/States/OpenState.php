<?php
// Author: Lai Chee Kit
// Module 4: State Pattern. A new ticket that nobody has started working on.

namespace App\States;

use App\Models\Ticket;

class OpenState extends BaseTicketState
{
    public function name(): string
    {
        return 'Open';
    }

    // Open -> In Progress
    public function start(Ticket $ticket): void
    {
        $ticket->moveTo(new InProgressState());
    }

    // Open -> Closed (the submitter withdraws the ticket)
    public function withdraw(Ticket $ticket): void
    {
        $ticket->moveTo(new ClosedState());
    }

    // The content can be changed only while the ticket is still Open
    public function canEdit(): bool
    {
        return true;
    }

    public function availableActions(): array
    {
        return ['start', 'withdraw'];
    }
}
