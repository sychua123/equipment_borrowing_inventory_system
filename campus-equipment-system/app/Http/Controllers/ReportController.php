<?php
// Author: Lai Chee Kit
// Module 4: shows a report on screen and exports it as CSV or PDF (Admin only)

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Reports\BorrowingReport;
use App\Reports\InventoryReport;
use App\Reports\Report;
use App\Reports\TicketReport;
use App\Services\CsvExporter;
use App\Services\PdfExporter;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    // URL word => [menu label, report class]. An unknown word never reaches this controller (see routes/web.php).
    public const REPORTS = [
        'tickets' => ['Tickets', TicketReport::class],
        'inventory' => ['Inventory', InventoryReport::class],
        'borrowing' => ['Borrowing usage', BorrowingReport::class],
    ];

    public function show(ReportFilterRequest $request, string $type): View
    {
        $filter = $request->toFilter();

        return view('reports.show', [
            'type' => $type,
            'filter' => $filter,
            'report' => $this->report($type)->build($filter),
        ]);
    }

    public function export(ReportFilterRequest $request, string $type, string $format, CsvExporter $csv, PdfExporter $pdf): Response
    {
        $filter = $request->toFilter();
        $report = $this->report($type)->build($filter);
        $fileName = "{$type}-report-".now()->format('Ymd-His').".{$format}";

        // The export contains exactly what the screen shows, because both read the same ReportData
        [$content, $contentType] = $format === 'pdf'
            ? [$pdf->toPdf($report, $filter), 'application/pdf']
            : [$csv->toCsv($report, $filter), 'text/csv; charset=UTF-8'];

        return response($content, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    private function report(string $type): Report
    {
        return app(self::REPORTS[$type][1]);
    }
}
