<?php
// Author: Lai Chee Kit
// Module 4: State Pattern (the "State" interface)
//
// Every ticket status (Open, In Progress, Resolved, Closed) is one class that
// implements this interface. The Ticket model is the "Context": it asks its
// current state object what is allowed, instead of using long if/else checks.

namespace App\States;

use App\Models\Ticket;

interface TicketState
{
    // The status text stored in the database, for example "In Progress"
    public function name(): string;

    // ----- The five actions. A state that does not allow an action throws InvalidTicketTransition. -----

    // Admin starts working on the ticket
    public function start(Ticket $ticket): void;

    // Admin marks the problem as solved
    public function resolve(Ticket $ticket): void;

    // The submitter confirms the solution (or 7 days pass) and the ticket is finished
    public function close(Ticket $ticket): void;

    // The submitter says the problem is not solved yet
    public function reopen(Ticket $ticket): void;

    // The submitter cancels the ticket before anyone works on it
    public function withdraw(Ticket $ticket): void;

    // ----- Questions the rest of the system asks the state -----

    // May the submitter still change the content and photos?
    public function canEdit(): bool;

    // May the submitter and the Admin still add replies?
    public function canReply(): bool;

    // The action names this state allows, used to decide which buttons to show
    public function availableActions(): array;

    // Runs once when a ticket enters this state (for example to record the time)
    public function onEnter(Ticket $ticket): void;
}
