<?php
// Author: Lai Chee Kit
// Module 4: the finished content of one report.
// The web page, the CSV export and the PDF export all read this same object,
// so the three always show the same numbers.

namespace App\Reports;

class ReportData
{
    /** Key figures shown as cards: label => value */
    public array $summary = [];

    /** Tables: each has title, columns, rows */
    public array $sections = [];

    /** Charts: each has type, title, labels, values */
    public array $charts = [];

    /** Warnings, for example "Module 2 is not responding" */
    public array $notices = [];

    /** A plain remark about the report (not a warning) */
    public ?string $note = null;

    /** Choices for the category filter */
    public array $categoryOptions = [];

    public function __construct(public readonly string $title)
    {
    }

    public function addSection(string $title, array $columns, array $rows): void
    {
        $this->sections[] = ['title' => $title, 'columns' => $columns, 'rows' => $rows];
    }

    public function addChart(string $type, string $title, array $labels, array $values): void
    {
        $this->charts[] = ['type' => $type, 'title' => $title, 'labels' => array_values($labels), 'values' => array_values($values)];
    }

    public function addNotice(?string $notice): void
    {
        if ($notice !== null) {
            $this->notices[] = $notice;
        }
    }
}
