<?php
// Author: Lai Chee Kit
// Module 4: tests for the web service Module 4 exposes (open fault tickets of an equipment item)

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenFaultTicketApiTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = ['X-API-KEY' => 'local-dev-key'];

    private function url(int|string $equipmentId, array $query = []): string
    {
        $query += ['requestID' => 'REQ-001', 'timeStamp' => '2026-11-15 10:00:00'];

        return "/api/m4/equipment/{$equipmentId}/open-fault-tickets?".http_build_query($query);
    }

    private function fault(int $equipmentId, string $status = 'Open', string $category = 'Fault'): void
    {
        $ticket = User::factory()->create()->tickets()->make([
            'title' => 'Fault on '.$equipmentId, 'description' => 'x', 'category' => $category, 'priority' => 'High', 'equipment_id' => $equipmentId,
        ]);
        $ticket->status = $status;
        $ticket->save();
    }

    public function test_request_without_the_api_key_is_refused(): void
    {
        $this->getJson($this->url(6))->assertStatus(401)->assertJsonPath('status', 'F');
        $this->getJson($this->url(6), ['X-API-KEY' => 'wrong'])->assertStatus(401)->assertJsonPath('status', 'F');
    }

    public function test_it_counts_only_open_fault_tickets_of_that_equipment(): void
    {
        $this->fault(6, 'Open');
        $this->fault(6, 'In Progress');
        $this->fault(6, 'Resolved');                    // not counted: already solved
        $this->fault(6, 'Open', 'Usage Problem');       // not counted: not a fault
        $this->fault(7, 'Open');                        // not counted: other equipment

        $this->getJson($this->url(6), self::KEY)
            ->assertOk()
            ->assertJsonPath('status', 'S')
            ->assertJsonPath('requestID', 'REQ-001')
            ->assertJsonPath('data.equipmentID', 6)
            ->assertJsonPath('data.openFaultCount', 2)
            ->assertJsonCount(2, 'data.tickets')
            ->assertJsonStructure(['status', 'requestID', 'timeStamp', 'data' => ['tickets' => [['ticketID', 'title', 'priority', 'status', 'submittedAt']]]])
            // No personal data or description leaves Module 4
            ->assertJsonMissingPath('data.tickets.0.description')
            ->assertJsonMissingPath('data.tickets.0.user_id');
    }

    public function test_equipment_without_faults_gives_zero(): void
    {
        $this->getJson($this->url(3), self::KEY)->assertOk()->assertJsonPath('data.openFaultCount', 0);
    }

    public function test_missing_ifa_fields_or_bad_values_give_status_f(): void
    {
        $base = '/api/m4/equipment/6/open-fault-tickets';

        $this->getJson($base, self::KEY)->assertStatus(422)->assertJsonPath('status', 'F');
        $this->getJson($base.'?requestID=R1&timeStamp=yesterday', self::KEY)->assertStatus(422)->assertJsonPath('status', 'F')->assertJsonPath('requestID', 'R1');
        $this->getJson($this->url('abc'), self::KEY)->assertStatus(422)->assertJsonPath('status', 'F');
        $this->getJson($this->url('6 OR 1=1'), self::KEY)->assertStatus(422)->assertJsonPath('status', 'F');
    }
}
