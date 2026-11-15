<?php
// Author: Lai Chee Kit
// Module 4: gets equipment data, which is owned by Module 1.
// TEMPORARY: returns a fixed list. In Week 5 this class will call Module 1's REST API instead.

namespace App\Services;

class EquipmentService
{
    /** @return array<int, array{id:int, asset_tag:string, name:string}> */
    public function all(): array
    {
        return [
            ['id' => 1, 'asset_tag' => 'LAP-0001', 'name' => 'Dell Latitude 5440 Laptop'],
            ['id' => 2, 'asset_tag' => 'PRJ-0001', 'name' => 'Epson EB-X51 Projector'],
            ['id' => 3, 'asset_tag' => 'CAM-0001', 'name' => 'Canon EOS 90D Camera'],
            ['id' => 4, 'asset_tag' => 'LAB-0001', 'name' => 'Digital Oscilloscope'],
        ];
    }

    /** Find one equipment item by ID, or null when it does not exist. */
    public function find(?int $id): ?array
    {
        foreach ($this->all() as $equipment) {
            if ($equipment['id'] === $id) {
                return $equipment;
            }
        }

        return null;
    }
}
