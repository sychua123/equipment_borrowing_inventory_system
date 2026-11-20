<?php
// Author: Lai Chee Kit
// Module 4: State Pattern (shared default behaviour)
//
// By default every action is rejected. Each concrete state overrides only the
// actions it allows, so a forbidden change (for example Closed -> Open) can never happen.

namespace App\States;

use App\Models\Ticket;

abstract class BaseTicketState implements TicketState
{
    public function start(Ticket $ticket): void
    {
        throw new InvalidTicketTransition($this->name(), 'start');
    }

    public function resolve(Ticket $ticket): void
    {
        throw new InvalidTicketTransition($this->name(), 'resolve');
    }

    public function close(Ticket $ticket): void
    {
        throw new InvalidTicketTransition($this->name(), 'close');
    }

    public function reopen(Ticket $ticket): void
    {
        throw new InvalidTicketTransition($this->name(), 'reopen');
    }

    public function withdraw(Ticket $ticket): void
    {
        throw new InvalidTicketTransition($this->name(), 'withdraw');
    }

    public function canEdit(): bool
    {
        return false;
    }

    public function canReply(): bool
    {
        return true;
    }

    public function availableActions(): array
    {
        return [];
    }

    public function onEnter(Ticket $ticket): void
    {
        // Nothing to record by default
    }
}
