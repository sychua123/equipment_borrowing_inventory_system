<?php
// Author: Lai Chee Kit
// Module 4: reads borrowing data from Module 2's web service (for the borrowing usage report).

namespace App\Services;

use App\Services\Api\ApiResult;
use App\Services\Api\ModuleApiClient;
use Illuminate\Support\Carbon;

class BorrowingService
{
    public function __construct(private ModuleApiClient $client)
    {
    }

    // One row per borrowed item, borrowed between the two dates
    public function borrowingRecords(Carbon $from, Carbon $to): ApiResult
    {
        return $this->client->get('module2', 'borrowing_records', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    // Items that are overdue right now (judged per item)
    public function overdueRecords(): ApiResult
    {
        return $this->client->get('module2', 'overdue_records');
    }

    public function rows(ApiResult $result): array
    {
        return array_map([$this, 'mapRecord'], $result->data);
    }

    // ----- PLACEHOLDER: field names of Module 2's JSON. Change the right-hand side only. -----

    private function mapRecord(array $row): array
    {
        return [
            'equipment_id' => (int) ($row['equipmentID'] ?? 0),
            'equipment_name' => (string) ($row['equipmentName'] ?? ''),
            'asset_tag' => (string) ($row['assetTag'] ?? ''),
            'category' => (string) ($row['category'] ?? ''),
            'borrower_name' => (string) ($row['borrowerName'] ?? ''),
            'borrow_date' => (string) ($row['borrowDate'] ?? ''),
            'expected_return_date' => (string) ($row['expectedReturnDate'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'days_overdue' => (int) ($row['daysOverdue'] ?? 0),
        ];
    }
}
