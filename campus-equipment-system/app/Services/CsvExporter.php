<?php
// Author: Lai Chee Kit
// Module 4: turns a report into a CSV file.
//
// CSV Injection (Formula Injection) defence:
// Excel treats a cell that starts with = + - @ (or a tab / line break) as a formula.
// A user could type a ticket title such as  =HYPERLINK("http://bad.site","Click")  and it
// would run when the Admin opens the exported file. Before writing, every text cell that
// starts with one of those characters gets a single quote in front, so Excel shows it as text.

namespace App\Services;

use App\Reports\ReportData;
use App\Reports\ReportFilter;

class CsvExporter
{
    private const DANGEROUS_FIRST_CHARACTERS = ['=', '+', '-', '@', "\t", "\r", "\n"];

    public function toCsv(ReportData $report, ReportFilter $filter): string
    {
        $file = fopen('php://temp', 'r+');

        // "\xEF\xBB\xBF" (BOM) tells Excel the file is UTF-8
        fwrite($file, "\xEF\xBB\xBF");

        $this->writeRow($file, [$report->title]);
        $this->writeRow($file, ['Period', $filter->rangeLabel()]);
        $this->writeRow($file, ['Generated', now()->format('Y-m-d H:i')]);
        $this->writeRow($file, []);

        foreach ($report->summary as $label => $value) {
            $this->writeRow($file, [$label, $value]);
        }

        foreach ($report->sections as $section) {
            $this->writeRow($file, []);
            $this->writeRow($file, [$section['title']]);
            $this->writeRow($file, $section['columns']);

            foreach ($section['rows'] as $row) {
                $this->writeRow($file, $row);
            }
        }

        rewind($file);
        $csv = stream_get_contents($file);
        fclose($file);

        return $csv;
    }

    private function writeRow($file, array $cells): void
    {
        // fputcsv adds the quotes and doubles any " inside a cell
        fputcsv($file, array_map([$this, 'escapeCell'], $cells), ',', '"', '');
    }

    // Make one cell safe to open in a spreadsheet program
    public function escapeCell(mixed $value): mixed
    {
        // Real numbers (counts, IDs) are safe and stay numbers
        if (is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        $text = (string) $value;

        if ($text !== '' && in_array($text[0], self::DANGEROUS_FIRST_CHARACTERS, true)) {
            return "'".$text;
        }

        return $text;
    }
}
