<?php
// Author: Lai Chee Kit
// Module 4: adds a reply to a ticket. A reply cannot be edited or deleted afterwards.

namespace App\Http\Controllers;

use App\Http\Requests\StoreReplyRequest;
use App\Models\Ticket;
use App\Services\AttachmentService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;

class TicketReplyController extends Controller
{
    public function store(
        StoreReplyRequest $request,
        Ticket $ticket,
        AttachmentService $attachments,
        NotificationService $notifications,
    ): RedirectResponse {
        // The form field is "body"; the template's column is "message"
        $reply = $ticket->replies()->make(['message' => $request->validated('body')]);
        $reply->user()->associate($request->user());
        $reply->save();

        $attachments->store($request->file('photos', []), $ticket, $reply);
        $notifications->replyAdded($ticket, $reply);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Your reply was added.');
    }
}
