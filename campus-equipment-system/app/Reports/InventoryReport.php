<?php
// Author: Lai Chee Kit
// Module 4: Inventory report. The data comes from Module 1's stock summary web service.

namespace App\Reports;

use App\Services\EquipmentService;

class InventoryReport implements Report
{
    public function __construct(private EquipmentService $equipment)
    {
    }

    public function build(ReportFilter $filter): ReportData
    {
        $report = new ReportData('Inventory Report');
        $report->note = 'Inventory figures show the stock right now, so the date range does not change this report.';

        $result = $this->equipment->stockSummary();
        $report->addNotice($result->notice());

        if (! $result->ok) {
            return $report;
        }

        $rows = $this->equipment->stockRows($result);
        $report->categoryOptions = array_column($rows, 'category');

        if ($filter->category !== null) {
            $rows = array_values(array_filter($rows, fn (array $r) => $r['category'] === $filter->category));
        }

        $threshold = config('module4.low_stock_threshold');
        $lowStock = array_values(array_filter($rows, fn (array $r) => $r['available'] <= $threshold));

        $report->summary = [
            'Total equipment' => array_sum(array_column($rows, 'total')),
            'Available now' => array_sum(array_column($rows, 'available')),
            'Borrowed now' => array_sum(array_column($rows, 'borrowed')),
            'Low-stock categories' => count($lowStock),
        ];

        $report->addSection(
            'Stock by category',
            ['Category', 'Total', 'Available', 'Borrowed', 'Under maintenance', 'Lost', 'Retired'],
            array_map(fn (array $r) => [$r['category'], $r['total'], $r['available'], $r['borrowed'], $r['under_maintenance'], $r['lost'], $r['retired']], $rows),
        );

        $report->addSection(
            "Low-stock alerts ({$threshold} or fewer available)",
            ['Category', 'Available', 'Total'],
            array_map(fn (array $r) => [$r['category'], $r['available'], $r['total']], $lowStock),
        );

        $report->addChart('bar', 'Available units by category', array_column($rows, 'category'), array_column($rows, 'available'));

        return $report;
    }
}
