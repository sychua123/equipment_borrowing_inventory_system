<?php
// Author: Lai Chee Kit
// Module 4: Admin dashboard. One panel per report, each with its key figures and one chart.
// The panels are built separately, so when Module 1 or Module 2 is down only that panel shows an error.

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Reports\BorrowingReport;
use App\Reports\InventoryReport;
use App\Reports\ReportFilter;
use App\Reports\TicketReport;
use App\Services\TicketAutoCloser;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        TicketAutoCloser $autoCloser,
        TicketReport $ticketReport,
        InventoryReport $inventoryReport,
        BorrowingReport $borrowingReport,
    ): View {
        $autoCloser->closeExpired();

        // The dashboard always shows this month
        $filter = ReportFilter::fromInput(['preset' => 'this_month']);

        return view('dashboard', [
            'filter' => $filter,
            'panels' => [
                'tickets' => $ticketReport->build($filter),
                'inventory' => $inventoryReport->build($filter),
                'borrowing' => $borrowingReport->build($filter),
            ],
            // Tickets the Admin still has to handle, oldest first
            'waiting' => Ticket::query()
                ->whereIn('status', ['Open', 'In Progress'])
                ->with('user')
                ->oldest()
                ->limit(5)
                ->get(),
            'waitingTotal' => Ticket::query()->whereIn('status', ['Open', 'In Progress'])->count(),
        ]);
    }
}
