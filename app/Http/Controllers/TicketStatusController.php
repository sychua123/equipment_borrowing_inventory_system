<?php
// Author: Lai Chee Kit
// Module 4: changes the status of a ticket (start, resolve, close, reopen, withdraw).
// This controller is the "client" of the State Pattern: it has no if/else about statuses.
// It only asks the ticket's current state object to do the action.

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\NotificationService;
use App\States\InvalidTicketTransition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TicketStatusController extends Controller
{
    // The only method names that may be called on a state object
    private const ACTIONS = ['start', 'resolve', 'close', 'reopen', 'withdraw'];

    public function update(Request $request, Ticket $ticket, NotificationService $notifications): RedirectResponse
    {
        $action = $request->validate([
            'action' => ['required', Rule::in(self::ACTIONS)],
        ])['action'];

        // 1) Is this USER allowed? (TicketPolicy has one method per action)
        Gate::authorize($action, $ticket);

        // 2) Does the current STATE allow it? (the state throws when it does not)
        try {
            $ticket->state()->{$action}($ticket);
        } catch (InvalidTicketTransition $e) {
            return redirect()->route('tickets.show', $ticket)->with('error', $e->getMessage());
        }

        $notifications->statusChanged($ticket, $request->user());

        return redirect()->route('tickets.show', $ticket)->with('success', "The ticket is now {$ticket->status}.");
    }
}
