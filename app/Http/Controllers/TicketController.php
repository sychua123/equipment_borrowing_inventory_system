<?php
// Author: Lai Chee Kit
// Module 4: Support Ticket (submit, list, detail)

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\EquipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    // Laravel creates the EquipmentService for us (dependency injection)
    public function __construct(private EquipmentService $equipmentService)
    {
    }

    // Ticket list: Admin sees every ticket, Student/Staff see only their own
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Ticket::query()->with('user')->latest();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return view('tickets.index', ['tickets' => $query->simplePaginate(10)]);
    }

    // Show the "submit ticket" form
    public function create(): View
    {
        return view('tickets.create', [
            'categories' => Ticket::CATEGORIES,
            'priorities' => Ticket::PRIORITIES,
            'equipmentList' => $this->equipmentService->all(),
        ]);
    }

    // Save a new ticket
    public function store(Request $request): RedirectResponse
    {
        $equipmentIds = array_column($this->equipmentService->all(), 'id');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', Rule::in(Ticket::CATEGORIES)],
            'priority' => ['required', Rule::in(Ticket::PRIORITIES)],
            // A Fault ticket must be linked to an equipment item
            'equipment_id' => ['nullable', 'required_if:category,Fault', Rule::in($equipmentIds)],
        ], [
            'equipment_id.required_if' => 'Please choose the equipment that has the fault.',
        ]);

        // Create through the relationship so user_id is always the logged-in user,
        // never a value sent from the browser
        $ticket = $request->user()->tickets()->create($validated);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket submitted!');
    }

    // Ticket detail
    public function show(Request $request, Ticket $ticket): View
    {
        $user = $request->user();

        // Only the submitter or an Admin may open this ticket
        abort_unless($user->isAdmin() || $ticket->user_id === $user->id, 403);

        return view('tickets.show', [
            'ticket' => $ticket,
            'equipment' => $this->equipmentService->find($ticket->equipment_id),
        ]);
    }
}
