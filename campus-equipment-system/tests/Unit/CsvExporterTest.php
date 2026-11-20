<?php
// Author: Lai Chee Kit
// Module 4: unit test for the CSV Injection defence

namespace Tests\Unit;

use App\Services\CsvExporter;
use PHPUnit\Framework\TestCase;

class CsvExporterTest extends TestCase
{
    public function test_cells_that_start_like_a_formula_get_a_quote_in_front(): void
    {
        $exporter = new CsvExporter();

        $this->assertSame("'=1+1", $exporter->escapeCell('=1+1'));
        $this->assertSame("'+cmd|' /C calc'!A0", $exporter->escapeCell("+cmd|' /C calc'!A0"));
        $this->assertSame("'-2+3", $exporter->escapeCell('-2+3'));
        $this->assertSame("'@SUM(A1)", $exporter->escapeCell('@SUM(A1)'));
        $this->assertSame("'\t=1+1", $exporter->escapeCell("\t=1+1"));
    }

    public function test_normal_text_and_numbers_are_not_changed(): void
    {
        $exporter = new CsvExporter();

        $this->assertSame('Broken projector', $exporter->escapeCell('Broken projector'));
        $this->assertSame('a = b', $exporter->escapeCell('a = b'));
        $this->assertSame(12, $exporter->escapeCell(12));
        $this->assertSame(-5, $exporter->escapeCell(-5));
        $this->assertSame('', $exporter->escapeCell(''));
    }
}
