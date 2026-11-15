<?php
// Author: Lai Chee Kit
// Module 4: FAKE answers that stand in for Module 1 and Module 2's web services.
//
// TEMPORARY. Used only while MODULE1_USE_MOCK / MODULE2_USE_MOCK are true in .env.
// The answers use the same IFA shape the real services should return, so switching
// to the real services needs no change in the reports.

namespace App\Services\Api;

use App\Support\Ifa;
use Illuminate\Support\Carbon;

class MockModuleData
{
    public function respond(string $path, array $query): array
    {
        $data = match ($path) {
            config('module4.module1.paths.equipment') => $this->equipment(),
            config('module4.module1.paths.stock_summary') => $this->stockSummary(),
            config('module4.module2.paths.borrowing_records') => $this->borrowingRecords($query['from'] ?? null, $query['to'] ?? null),
            config('module4.module2.paths.overdue_records') => $this->overdueRecords(),
            default => null,
        };

        if ($data === null) {
            return ['status' => 'F', 'requestID' => $query['requestID'] ?? null, 'timeStamp' => now()->format(Ifa::TIME_FORMAT), 'message' => 'Unknown mock path'];
        }

        return [
            'status' => 'S',
            'requestID' => $query['requestID'] ?? null,
            'timeStamp' => now()->format(Ifa::TIME_FORMAT),
            'data' => $data,
        ];
    }

    // ---------- Module 1 ----------

    private function equipment(): array
    {
        $rows = [
            ['LAP-0001', 'Dell Latitude 5440 Laptop', 'Laptop', 'Lab 1', 'Available'],
            ['LAP-0002', 'Dell Latitude 5440 Laptop', 'Laptop', 'Lab 1', 'Borrowed'],
            ['LAP-0003', 'Lenovo ThinkPad E14 Laptop', 'Laptop', 'Lab 2', 'Borrowed'],
            ['LAP-0004', 'Lenovo ThinkPad E14 Laptop', 'Laptop', 'Lab 2', 'Under Maintenance'],
            ['LAP-0005', 'HP ProBook 440 Laptop', 'Laptop', 'Lab 3', 'Available'],
            ['PRJ-0001', 'Epson EB-X51 Projector', 'Projector', 'Lab 1', 'Available'],
            ['PRJ-0002', 'Epson EB-X51 Projector', 'Projector', 'Lab 2', 'Borrowed'],
            ['PRJ-0003', 'BenQ MW560 Projector', 'Projector', 'Lab 3', 'Under Maintenance'],
            ['PRJ-0004', 'BenQ MW560 Projector', 'Projector', 'Store Room', 'Retired'],
            ['CAM-0001', 'Canon EOS 90D Camera', 'Camera', 'Media Room', 'Available'],
            ['CAM-0002', 'Canon EOS 90D Camera', 'Camera', 'Media Room', 'Borrowed'],
            ['CAM-0003', 'Sony ZV-E10 Camera', 'Camera', 'Media Room', 'Lost'],
            ['LAB-0001', 'Digital Oscilloscope', 'Lab Instrument', 'Electronics Lab', 'Available'],
            ['LAB-0002', 'Digital Multimeter', 'Lab Instrument', 'Electronics Lab', 'Available'],
            ['LAB-0003', 'Function Generator', 'Lab Instrument', 'Electronics Lab', 'Available'],
            ['AUD-0001', 'Rode Wireless Microphone', 'Audio', 'Media Room', 'Borrowed'],
            ['AUD-0002', 'JBL Portable Speaker', 'Audio', 'Media Room', 'Available'],
        ];

        $equipment = [];
        foreach ($rows as $index => [$assetTag, $name, $category, $location, $status]) {
            $equipment[] = [
                'equipmentID' => $index + 1,
                'assetTag' => $assetTag,
                'name' => $name,
                'category' => $category,
                'location' => $location,
                'status' => $status,
            ];
        }

        return $equipment;
    }

    private function stockSummary(): array
    {
        $summary = [];

        foreach ($this->equipment() as $item) {
            $category = $item['category'];
            $summary[$category] ??= ['category' => $category, 'total' => 0, 'available' => 0, 'borrowed' => 0, 'underMaintenance' => 0, 'lost' => 0, 'retired' => 0];
            $summary[$category]['total']++;

            $field = match ($item['status']) {
                'Available' => 'available',
                'Borrowed' => 'borrowed',
                'Under Maintenance' => 'underMaintenance',
                'Lost' => 'lost',
                default => 'retired',
            };
            $summary[$category][$field]++;
        }

        return array_values($summary);
    }

    // ---------- Module 2 ----------

    // One row per borrowed item, spread over the last six months
    private function allBorrowingRecords(): array
    {
        $equipment = $this->equipment();
        $count = count($equipment);
        $today = Carbon::today();
        $records = [];

        for ($i = 0; $i < 90; $i++) {
            $item = $equipment[($i * $i + 3) % $count];
            $isStaff = $i % 4 === 0;
            $borrowDate = $today->copy()->subDays($i * 2);
            $expected = $borrowDate->copy()->addDays($isStaff ? 14 : 7);

            if ($expected->isFuture() || $expected->isToday()) {
                $status = 'Issued';
                $returnDate = null;
            } elseif ($i % 11 === 0) {
                $status = 'Overdue';
                $returnDate = null;
            } else {
                $status = 'Returned';
                $returnDate = $expected->copy()->subDays($i % 3)->toDateString();
            }

            $records[] = [
                'borrowingItemID' => 1000 + $i,
                'borrowingRequestID' => 500 + intdiv($i, 2),
                'equipmentID' => $item['equipmentID'],
                'equipmentName' => $item['name'],
                'assetTag' => $item['assetTag'],
                'category' => $item['category'],
                'borrowerName' => $isStaff ? 'Stella Staff' : 'Sam Student',
                'borrowerRole' => $isStaff ? 'Staff' : 'Student',
                'borrowDate' => $borrowDate->toDateString(),
                'expectedReturnDate' => $expected->toDateString(),
                'returnDate' => $returnDate,
                'status' => $status,
            ];
        }

        return $records;
    }

    private function borrowingRecords(?string $from, ?string $to): array
    {
        return array_values(array_filter(
            $this->allBorrowingRecords(),
            fn (array $r) => ($from === null || $r['borrowDate'] >= $from) && ($to === null || $r['borrowDate'] <= $to),
        ));
    }

    // Overdue is judged per item (team decision R2-2)
    private function overdueRecords(): array
    {
        $today = Carbon::today();
        $overdue = [];

        foreach ($this->allBorrowingRecords() as $r) {
            if ($r['status'] === 'Overdue') {
                $r['daysOverdue'] = (int) Carbon::parse($r['expectedReturnDate'])->diffInDays($today);
                $overdue[] = $r;
            }
        }

        return $overdue;
    }
}
