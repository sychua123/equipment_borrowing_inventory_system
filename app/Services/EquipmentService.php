<?php
// Author: Lai Chee Kit
// Module 4: reads equipment data from Module 1's web service.
// Equipment belongs to Module 1, so Module 4 never reads the equipment table directly.

namespace App\Services;

use App\Services\Api\ApiResult;
use App\Services\Api\ModuleApiClient;

class EquipmentService
{
    // Remember the list for the rest of this page load
    private ?ApiResult $listResult = null;

    public function __construct(private ModuleApiClient $client)
    {
    }

    // Every equipment item, including Retired ones (old tickets still need their names)
    public function all(): ApiResult
    {
        return $this->listResult ??= $this->client->get('module1', 'equipment');
    }

    /** Equipment as simple rows: id, asset_tag, name, category, location, status */
    public function list(): array
    {
        return array_map([$this, 'mapEquipment'], $this->all()->data);
    }

    // For the "submit ticket" dropdown: Retired equipment is hidden
    public function selectable(): array
    {
        return array_values(array_filter($this->list(), fn (array $e) => $e['status'] !== 'Retired'));
    }

    /** Find one equipment item by ID, or null when it is unknown or Module 1 is unavailable. */
    public function find(?int $id): ?array
    {
        foreach ($this->list() as $equipment) {
            if ($equipment['id'] === $id) {
                return $equipment;
            }
        }

        return null;
    }

    // Text to show for an equipment ID, for example "PRJ-0001 · Epson Projector (retired)"
    public function label(?int $id): string
    {
        if ($id === null) {
            return 'None';
        }

        $equipment = $this->find($id);

        if ($equipment === null) {
            return "Equipment #{$id} (details unavailable)";
        }

        return $equipment['asset_tag'].' · '.$equipment['name'].($equipment['status'] === 'Retired' ? ' (retired)' : '');
    }

    // Stock per category: category, total, available, borrowed, under_maintenance, lost, retired
    public function stockSummary(): ApiResult
    {
        return $this->client->get('module1', 'stock_summary');
    }

    public function stockRows(ApiResult $result): array
    {
        return array_map([$this, 'mapStock'], $result->data);
    }

    // ----- PLACEHOLDER: field names of Module 1's JSON. Change the right-hand side only. -----

    private function mapEquipment(array $row): array
    {
        return [
            'id' => (int) ($row['equipmentID'] ?? 0),
            'asset_tag' => (string) ($row['assetTag'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'category' => (string) ($row['category'] ?? ''),
            'location' => (string) ($row['location'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
        ];
    }

    private function mapStock(array $row): array
    {
        return [
            'category' => (string) ($row['category'] ?? ''),
            'total' => (int) ($row['total'] ?? 0),
            'available' => (int) ($row['available'] ?? 0),
            'borrowed' => (int) ($row['borrowed'] ?? 0),
            'under_maintenance' => (int) ($row['underMaintenance'] ?? 0),
            'lost' => (int) ($row['lost'] ?? 0),
            'retired' => (int) ($row['retired'] ?? 0),
        ];
    }
}
