<?php
// Author: Lai Chee Kit
// Module 4: Ticket report, built from Module 4's own tickets table.
//
// SQL Injection defence, part 2: every filter value reaches the database as a bound
// parameter ("?"), never glued into the SQL text. The Query Builder does the binding
// in where()/whereBetween(), and the raw parts below contain no user input at all.

namespace App\Reports;

use App\Models\Ticket;
use App\Services\EquipmentService;
use Illuminate\Database\Eloquent\Builder;

class TicketReport implements Report
{
    public function __construct(private EquipmentService $equipment)
    {
    }

    public function build(ReportFilter $filter): ReportData
    {
        $report = new ReportData('Ticket Report');
        $report->categoryOptions = Ticket::CATEGORIES;

        $byStatus = $this->countBy('status', array_keys(Ticket::STATES), $filter);
        $byCategory = $this->countBy('category', Ticket::CATEGORIES, $filter);
        $byPriority = $this->countBy('priority', Ticket::PRIORITIES, $filter);
        $averageHours = $this->averageHandlingHours($filter);

        $report->summary = [
            'Tickets submitted' => array_sum($byStatus),
            'Still open or in progress' => $byStatus['open'] + $byStatus['in_progress'],
            'Average handling time' => $averageHours === null ? 'n/a' : $this->formatHours($averageHours),
        ];

        $report->addSection('Tickets by status', ['Status', 'Tickets'], $this->pairs($byStatus));
        $report->addSection('Tickets by category', ['Category', 'Tickets'], $this->pairs($byCategory));
        $report->addSection('Tickets by priority', ['Priority', 'Tickets'], $this->pairs($byPriority));
        // (pairs() shows each stored value with Ticket::label, e.g. "in_progress" -> "In Progress")
        $report->addSection('Equipment with the most faults (top 10)', ['Equipment', 'Fault tickets'], $this->faultyEquipment($filter));

        // Titles a user typed are listed too, so the CSV export must make them safe (see CsvExporter)
        $report->addSection(
            'Tickets in this period',
            ['#', 'Title', 'Category', 'Priority', 'Status', 'Submitted by', 'Submitted'],
            $this->filtered($filter)->with('user')->latest()->limit(200)->get()
                ->map(fn (Ticket $t) => [
                    $t->id,
                    $t->subject,
                    $t->category,
                    Ticket::label($t->priority),
                    Ticket::label($t->status),
                    $t->user->name,
                    $t->created_at->format('Y-m-d H:i'),
                ])
                ->all(),
        );

        $report->addChart('doughnut', 'Tickets by status', array_map([Ticket::class, 'label'], array_keys($byStatus)), $byStatus);
        $report->addChart('bar', 'Tickets by category', array_keys($byCategory), $byCategory);

        return $report;
    }

    // The tickets that match the filter. All values are bound parameters.
    private function filtered(ReportFilter $filter): Builder
    {
        return Ticket::query()
            ->whereBetween('created_at', [$filter->from, $filter->to])
            ->when($filter->category, fn (Builder $q, string $value) => $q->where('category', $value))
            ->when($filter->status, fn (Builder $q, string $value) => $q->where('status', $value))
            ->when($filter->priority, fn (Builder $q, string $value) => $q->where('priority', $value));
    }

    /**
     * COUNT(*) ... GROUP BY one column. $column always comes from this class (never from
     * the browser), and it is checked against a fixed list anyway before it is used.
     */
    private function countBy(string $column, array $allValues, ReportFilter $filter): array
    {
        abort_unless(in_array($column, ['status', 'category', 'priority'], true), 500);

        $counts = $this->filtered($filter)
            ->selectRaw("{$column} AS label, COUNT(*) AS total")
            ->groupBy($column)
            ->pluck('total', 'label');

        // Keep a fixed order and show 0 for values with no tickets
        $result = [];
        foreach ($allValues as $value) {
            $result[$value] = (int) ($counts[$value] ?? 0);
        }

        return $result;
    }

    // Average time from "submitted" to "resolved", in hours (null when nothing was resolved)
    private function averageHandlingHours(ReportFilter $filter): ?float
    {
        $resolved = $this->filtered($filter)->whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);

        if ($resolved->isEmpty()) {
            return null;
        }

        return $resolved->avg(fn (Ticket $t) => $t->created_at->diffInMinutes($t->resolved_at) / 60);
    }

    private function faultyEquipment(ReportFilter $filter): array
    {
        $rows = $this->filtered($filter)
            ->where('category', 'Fault')
            ->whereNotNull('equipment_id')
            ->selectRaw('equipment_id, COUNT(*) AS total')
            ->groupBy('equipment_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // The equipment name comes from Module 1; only the ID is stored in our table
        return $rows->map(fn ($row) => [$this->equipment->label((int) $row->equipment_id), (int) $row->total])->all();
    }

    private function pairs(array $counts): array
    {
        $labels = array_map([Ticket::class, 'label'], array_keys($counts));

        return array_map(null, $labels, array_values($counts));
    }

    private function formatHours(float $hours): string
    {
        return $hours >= 48 ? round($hours / 24, 1).' days' : round($hours, 1).' hours';
    }
}
