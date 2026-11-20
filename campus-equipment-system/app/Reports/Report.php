<?php
// Author: Lai Chee Kit
// Module 4: what every report class must provide

namespace App\Reports;

interface Report
{
    public function build(ReportFilter $filter): ReportData;
}
