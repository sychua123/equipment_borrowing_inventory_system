<?php
// Author: Lai Chee Kit
// Module 4: creates the in-app (bell) notifications.
// A notification is created for a new ticket, a new reply, and a status change.

namespace App\Services;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    // The short title shown in the notification list, for each notification type
    private const TITLES = [
        'new_ticket' => 'New ticket',
        'new_reply' => 'New reply',
        'status_changed' => 'Status changed',
    ];

    // New ticket -> tell every Admin
    public function ticketSubmitted(Ticket $ticket): void
    {
        $this->send($this->admins(), $ticket, 'new_ticket', "New ticket #{$ticket->id}: {$ticket->subject}");
    }

    // New reply -> tell the other side
    public function replyAdded(Ticket $ticket, TicketReply $reply): void
    {
        $writer = $reply->user;

        if ($writer->id === $ticket->user_id) {
            $this->send($this->admins(), $ticket, 'new_reply', "{$writer->name} replied on ticket #{$ticket->id}");
        } else {
            $this->send([$ticket->user], $ticket, 'new_reply', "The Admin replied on your ticket #{$ticket->id}");
        }
    }

    // Status change -> tell the other side. $actor is null when the system auto-closed the ticket.
    public function statusChanged(Ticket $ticket, ?User $actor): void
    {
        $status = Ticket::label($ticket->status);

        if ($actor !== null && $actor->id === $ticket->user_id) {
            $this->send($this->admins(), $ticket, 'status_changed', "Ticket #{$ticket->id} was changed to {$status} by {$actor->name}");
        } else {
            $how = $actor === null ? ' automatically' : '';
            $this->send([$ticket->user], $ticket, 'status_changed', "Your ticket #{$ticket->id} was changed to {$status}{$how}");
        }
    }

    // PLACEHOLDER: how to find the Admins depends on Module 1's users table
    private function admins(): Collection
    {
        return User::query()->where('role', 'admin')->get();
    }

    private function send(iterable $users, Ticket $ticket, string $type, string $message): void
    {
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'title' => self::TITLES[$type],
                'message' => $message,
                'notification_type' => $type,
                'is_read' => false,
            ]);
        }
    }
}
