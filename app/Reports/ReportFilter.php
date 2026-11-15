<?php
// Author: Lai Chee Kit
// Module 4: the filter choices of a report, already validated and turned into real dates

namespace App\Reports;

use Illuminate\Support\Carbon;

class ReportFilter
{
    public const PRESETS = [
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_semester' => 'This semester',
        'custom' => 'Custom range',
    ];

    public function __construct(
        public readonly string $preset,
        public readonly Carbon $from,   // start of the first day
        public readonly Carbon $to,     // end of the last day
        public readonly ?string $category = null,
        public readonly ?string $status = null,
        public readonly ?string $priority = null,
    ) {
    }

    public static function fromInput(array $input): self
    {
        $preset = $input['preset'] ?? 'this_month';
        $today = Carbon::today();

        [$from, $to] = match ($preset) {
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_semester' => [Carbon::parse(config('module4.semester_start')), $today->copy()],
            'custom' => [Carbon::parse($input['from']), Carbon::parse($input['to'])],
            default => [$today->copy()->startOfMonth(), $today->copy()],
        };

        return new self(
            $preset,
            $from->startOfDay(),
            $to->endOfDay(),
            $input['category'] ?? null,
            $input['status'] ?? null,
            $input['priority'] ?? null,
        );
    }

    // For the report heading, for example "1 Nov 2026 to 15 Nov 2026"
    public function rangeLabel(): string
    {
        return $this->from->format('j M Y').' to '.$this->to->format('j M Y');
    }

    // The same choices as query values, so export links keep the filter
    public function toQuery(): array
    {
        return array_filter([
            'preset' => $this->preset,
            'from' => $this->preset === 'custom' ? $this->from->toDateString() : null,
            'to' => $this->preset === 'custom' ? $this->to->toDateString() : null,
            'category' => $this->category,
            'status' => $this->status,
            'priority' => $this->priority,
        ]);
    }
}
