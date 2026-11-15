<?php
// Author: Lai Chee Kit
// Module 4: the in-app notification list (the bell in the menu)

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->ticketNotifications()->simplePaginate(15),
        ]);
    }

    // Open a notification: mark it as read, then go to its ticket
    public function open(Request $request, Notification $notification): RedirectResponse
    {
        // A user may open only their own notifications
        abort_unless($notification->user_id === $request->user()->id, 403);

        if ($notification->isUnread()) {
            $notification->read_at = now();
            $notification->save();
        }

        return redirect()->route('tickets.show', $notification->ticket_id);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->ticketNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'All notifications were marked as read.');
    }
}
