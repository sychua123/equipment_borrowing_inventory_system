<?php
// Author: Lai Chee Kit
// Module 4: Borrowing usage report. The data comes from Module 2's web services.

namespace App\Reports;

use App\Services\BorrowingService;
use Illuminate\Support\Carbon;

class BorrowingReport implements Report
{
    public function __construct(private BorrowingService $borrowing)
    {
    }

    public function build(ReportFilter $filter): ReportData
    {
        $report = new ReportData('Borrowing Usage Report');

        $recordsResult = $this->borrowing->borrowingRecords($filter->from, $filter->to);
        $overdueResult = $this->borrowing->overdueRecords();
        $report->addNotice($recordsResult->notice());

        // Do not show the same warning twice
        if ($overdueResult->notice() !== $recordsResult->notice()) {
            $report->addNotice($overdueResult->notice());
        }

        $records = $this->borrowing->rows($recordsResult);
        $overdue = $this->borrowing->rows($overdueResult);

        $categories = array_unique(array_merge(array_column($records, 'category'), array_column($overdue, 'category')));
        sort($categories);
        $report->categoryOptions = $categories;

        if ($filter->category !== null) {
            $sameCategory = fn (array $r) => $r['category'] === $filter->category;
            $records = array_values(array_filter($records, $sameCategory));
            $overdue = array_values(array_filter($overdue, $sameCategory));
        }

        $report->summary = [
            'Items borrowed' => $recordsResult->ok ? count($records) : 'n/a',
            'Overdue items now' => $overdueResult->ok ? count($overdue) : 'n/a',
        ];

        if ($recordsResult->ok) {
            // Most-borrowed equipment: count the rows per equipment item
            $perEquipment = [];
            foreach ($records as $r) {
                $key = $r['equipment_id'];
                $perEquipment[$key] ??= ['label' => trim($r['asset_tag'].' · '.$r['equipment_name'], ' ·'), 'category' => $r['category'], 'count' => 0];
                $perEquipment[$key]['count']++;
            }
            usort($perEquipment, fn (array $a, array $b) => $b['count'] <=> $a['count']);
            $top = array_slice($perEquipment, 0, 10);

            // Borrows per month
            $perMonth = [];
            foreach ($records as $r) {
                $month = substr($r['borrow_date'], 0, 7);   // "2026-11"
                $perMonth[$month] = ($perMonth[$month] ?? 0) + 1;
            }
            ksort($perMonth);
            $monthLabels = array_map(fn (string $m) => Carbon::parse($m.'-01')->format('M Y'), array_keys($perMonth));

            $report->addSection(
                'Most-borrowed equipment (top 10)',
                ['Equipment', 'Category', 'Times borrowed'],
                array_map(fn (array $e) => [$e['label'], $e['category'], $e['count']], $top),
            );

            $report->addSection(
                'Items borrowed per month',
                ['Month', 'Items borrowed'],
                array_map(null, $monthLabels, array_values($perMonth)),
            );

            $report->addChart('bar', 'Items borrowed per month', $monthLabels, $perMonth);
            $report->addChart('bar', 'Most-borrowed equipment', array_column($top, 'label'), array_column($top, 'count'));
        }

        if ($overdueResult->ok) {
            usort($overdue, fn (array $a, array $b) => $b['days_overdue'] <=> $a['days_overdue']);

            $report->addSection(
                'Overdue items right now',
                ['Equipment', 'Borrower', 'Expected return', 'Days overdue'],
                array_map(fn (array $r) => [trim($r['asset_tag'].' · '.$r['equipment_name'], ' ·'), $r['borrower_name'], $r['expected_return_date'], $r['days_overdue']], $overdue),
            );
        }

        return $report;
    }
}
