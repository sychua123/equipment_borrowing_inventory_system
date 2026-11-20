<?php
// Author: Lai Chee Kit
// Module 4: Support Ticket (list, submit, view, edit)

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TicketFilterRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Services\AttachmentService;
use App\Services\EquipmentService;
use App\Services\NotificationService;
use App\Services\TicketAutoCloser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        private EquipmentService $equipment,
        private AttachmentService $attachments,
        private NotificationService $notifications,
    ) {
    }

    // Ticket list. Admin sees every ticket; Student/Staff see only their own.
    public function index(TicketFilterRequest $request, TicketAutoCloser $autoCloser): View
    {
        // Close tickets that stayed Resolved for more than 7 days
        $autoCloser->closeExpired();

        $filters = $request->validated();

        // The sort word was validated; here it becomes a column name from our own fixed list
        [$sortColumn, $sortDirection] = TicketFilterRequest::SORTS[$filters['sort'] ?? 'newest'];

        $tickets = Ticket::query()
            ->visibleTo($request->user())
            ->with('user')
            // Each value below is sent to the database as a bound parameter
            ->when($filters['status'] ?? null, fn (Builder $q, string $value) => $q->where('status', $value))
            ->when($filters['category'] ?? null, fn (Builder $q, string $value) => $q->where('category', $value))
            ->when($filters['priority'] ?? null, fn (Builder $q, string $value) => $q->where('priority', $value))
            ->when($filters['q'] ?? null, fn (Builder $q, string $value) => $this->search($q, $value))
            ->orderBy($sortColumn, $sortDirection)
            ->simplePaginate(10)
            ->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'equipment' => $this->equipment,
        ]);
    }

    /**
     * Keyword search in subject (the title) and description.
     * SQL Injection defence: the keyword is a bound parameter ("?"), and the LIKE wildcards
     * % and _ inside it are escaped with "!", so a user cannot change the meaning of the query.
     */
    private function search(Builder $query, string $keyword): void
    {
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $keyword).'%';

        $query->where(function (Builder $q) use ($pattern) {
            $q->whereRaw("subject LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("description LIKE ? ESCAPE '!'", [$pattern]);
        });
    }

    public function create(): View
    {
        return view('tickets.create', [
            'ticket' => new Ticket(),
            'equipmentList' => $this->equipment->selectable(),
            'equipmentNotice' => $this->equipment->all()->notice(),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        // user_id comes from the logged-in user, never from the form
        $ticket = $request->user()->tickets()->create($request->ticketData());

        $this->attachments->store($request->file('photos', []), $ticket);
        $this->notifications->ticketSubmitted($ticket);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Your ticket was submitted.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['user', 'photos', 'replies.user', 'replies.photos']);

        // Buttons to show = what the STATE allows AND what this USER may do
        $actions = array_values(array_filter(
            $ticket->state()->availableActions(),
            fn (string $action) => $request->user()->can($action, $ticket),
        ));

        return view('tickets.show', [
            'ticket' => $ticket,
            'actions' => $actions,
            'equipmentLabel' => $this->equipment->label($ticket->equipment_id),
        ]);
    }

    public function edit(Ticket $ticket): View
    {
        Gate::authorize('update', $ticket);

        $equipmentList = $this->equipment->selectable();

        // Keep the equipment already chosen in the list even if it was retired afterwards
        $current = $this->equipment->find($ticket->equipment_id);
        if ($current !== null && ! in_array($current['id'], array_column($equipmentList, 'id'), true)) {
            $equipmentList[] = $current;
        }

        return view('tickets.edit', [
            'ticket' => $ticket->load('photos'),
            'equipmentList' => $equipmentList,
            'equipmentNotice' => $this->equipment->all()->notice(),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update($request->ticketData());

        $ticket->photos()
            ->whereIn('id', $request->validated('remove_photos', []))
            ->get()
            ->each(fn (TicketAttachment $photo) => $this->attachments->delete($photo));

        $this->attachments->store($request->file('photos', []), $ticket);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Your ticket was updated.');
    }
}
