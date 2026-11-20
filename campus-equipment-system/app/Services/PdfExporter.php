<?php
// Author: Lai Chee Kit
// Module 4: turns a report into a PDF file with the Dompdf library.
// Dompdf converts an HTML page (our Blade view reports/pdf.blade.php) into a PDF.

namespace App\Services;

use App\Reports\ReportData;
use App\Reports\ReportFilter;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfExporter
{
    public function toPdf(ReportData $report, ReportFilter $filter): string
    {
        $options = new Options();
        // The PDF is built only from our own HTML: no remote files, no PHP inside the HTML
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('reports.pdf', ['report' => $report, 'filter' => $filter])->render());
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->output();
    }
}
