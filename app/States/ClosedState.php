<?php
// Author: Lai Chee Kit
// Module 4: State Pattern. The ticket is finished. Nothing can change it any more,
// so this class overrides no action: every action stays rejected by BaseTicketState.

namespace App\States;

use App\Models\Ticket;

class ClosedState extends BaseTicketState
{
    public function name(): string
    {
        return 'Closed';
    }

    public function canReply(): bool
    {
        return false;
    }

    public function onEnter(Ticket $ticket): void
    {
        $ticket->closed_at = now();
    }
}
