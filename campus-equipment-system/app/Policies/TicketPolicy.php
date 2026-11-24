<?php
// Author: Lai Chee Kit
// Module 4: who may do what with a ticket.
// The Policy answers "is this USER allowed?"; the State classes answer "does the STATUS allow it?".

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    // Only the submitter or an Admin may open a ticket (and its photos)
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $ticket->user_id === $user->id;
    }

    // Only the submitter may edit, and only while the state allows it (Open)
    public function update(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id && $ticket->state()->canEdit();
    }

    // The submitter and the Admin may reply until the ticket is Closed
    public function reply(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket) && $ticket->state()->canReply();
    }

    // ----- Status actions (same names as the methods in TicketState) -----

    public function start(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    public function resolve(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }

    public function withdraw(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }
}
