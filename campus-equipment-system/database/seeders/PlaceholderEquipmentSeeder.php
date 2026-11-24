<?php
// Author: Lai Chee Kit
// Module 4: placeholder equipment rows so tickets can point at equipment before Module 1's data is loaded.
// The template's tickets.equipment_id is a foreign key to the "equipment" table, so the row must exist.
// Module 1 owns this data. This seeder only adds rows that are missing and never changes existing ones.

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaceholderEquipmentSeeder extends Seeder
{
    // The IDs used by the built-in fake data (MockModuleData) and by the sample tickets
    private const IDS = [3, 4, 6, 7, 9, 11];

    public function run(): void
    {
        DB::table('categories')->insertOrIgnore([
            ['id' => 1, 'category_name' => 'Placeholder', 'description' => 'Temporary until Module 1 data is loaded'],
        ]);

        DB::table('locations')->insertOrIgnore([
            ['id' => 1, 'location_name' => 'Placeholder', 'building' => null, 'room_number' => null, 'description' => 'Temporary until Module 1 data is loaded'],
        ]);

        foreach (self::IDS as $id) {
            DB::table('equipment')->insertOrIgnore([
                'id' => $id,
                'category_id' => 1,
                'location_id' => 1,
                'asset_tag' => sprintf('PLACEHOLDER-%04d', $id),
                'equipment_name' => 'Placeholder item '.$id,
                'status' => 'available',
            ]);
        }
    }
}
