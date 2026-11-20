<?php
// Author: Lai Chee Kit
// Module 4: tests for the dashboard, the three reports, CSV/PDF export and web service failure handling

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlaceholderEquipmentSeeder::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // Creates a ticket. The "title" key is stored in the template's "subject" column.
    private function ticket(array $overrides = []): void
    {
        $attributes = array_merge([
            'subject' => 'Broken projector', 'description' => 'x', 'category' => 'Fault', 'priority' => 'high', 'equipment_id' => 6,
        ], $overrides);

        if (isset($attributes['title'])) {
            $attributes['subject'] = $attributes['title'];
            unset($attributes['title']);
        }

        User::factory()->create()->tickets()->create($attributes);
    }

    // Use the real HTTP calls to the other modules (which the test then fakes), not the built-in fake data
    private function useRealApis(): void
    {
        config(['module4.module1.mock' => false, 'module4.module2.mock' => false]);
    }

    private function stockSummaryAnswer(): array
    {
        return ['status' => 'S', 'timeStamp' => '2026-11-15 10:00:00', 'data' => [
            ['category' => 'Drone', 'total' => 4, 'available' => 1, 'borrowed' => 3],
        ]];
    }

    public function test_only_admin_can_open_dashboard_and_reports(): void
    {
        $student = User::factory()->create();
        $staff = User::factory()->create(['role' => 'staff']);

        foreach (['/dashboard', '/reports/tickets', '/reports/inventory', '/reports/borrowing', '/reports/tickets/export/csv'] as $url) {
            $this->actingAs($student)->get($url)->assertForbidden();
            $this->actingAs($staff)->get($url)->assertForbidden();
            $this->actingAs($this->admin)->get($url)->assertOk();
        }

        $this->actingAs($this->admin)->get('/reports/secret')->assertNotFound();
        $this->actingAs($this->admin)->get('/reports/tickets/export/exe')->assertNotFound();
    }

    public function test_home_sends_admin_to_dashboard_and_others_to_tickets(): void
    {
        $this->actingAs($this->admin)->get('/')->assertRedirect('/dashboard');
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect('/tickets');
    }

    public function test_ticket_report_counts_and_filters(): void
    {
        $this->ticket(['title' => 'First fault']);
        $this->ticket(['title' => 'Second fault', 'priority' => 'low']);
        $this->ticket(['title' => 'Some feedback', 'category' => 'General Feedback', 'equipment_id' => null]);

        $this->actingAs($this->admin)->get('/reports/tickets')
            ->assertSee('First fault')->assertSee('Some feedback')
            ->assertSee('PRJ-0001');   // equipment with the most faults, name from Module 1

        $this->actingAs($this->admin)->get('/reports/tickets?category=Fault&priority=low')
            ->assertSee('Second fault')->assertDontSee('First fault')->assertDontSee('Some feedback');

        // Last month has no tickets
        $this->actingAs($this->admin)->get('/reports/tickets?preset=last_month')->assertDontSee('First fault');
    }

    public function test_report_filter_values_are_validated(): void
    {
        $this->actingAs($this->admin)->get('/reports/tickets?preset=forever')->assertSessionHasErrors('preset');
        $this->actingAs($this->admin)->get('/reports/tickets?preset=custom')->assertSessionHasErrors(['from', 'to']);
        $this->actingAs($this->admin)->get('/reports/tickets?preset=custom&from=2026-11-10&to=2026-11-01')->assertSessionHasErrors('to');
        $this->actingAs($this->admin)->get('/reports/tickets?status='.urlencode("open' OR '1'='1"))->assertSessionHasErrors('status');
        $this->actingAs($this->admin)->get('/reports/tickets?preset=custom&from=2026-11-01&to=2026-11-30')->assertOk();
    }

    public function test_inventory_and_borrowing_reports_show_data_from_the_other_modules(): void
    {
        $this->actingAs($this->admin)->get('/reports/inventory')
            ->assertOk()->assertSee('Stock by category')->assertSee('Laptop')->assertSee('Low-stock alerts');

        $this->actingAs($this->admin)->get('/reports/borrowing?preset=this_semester')
            ->assertOk()->assertSee('Most-borrowed equipment')->assertSee('Overdue items right now');
    }

    // ---------- Export ----------

    public function test_csv_export_makes_formula_cells_safe(): void
    {
        // CSV Injection attempt: a title that Excel would run as a formula
        $this->ticket(['title' => '=HYPERLINK("http://bad.example","Click me")']);
        $this->ticket(['title' => '@SUM(1+1)']);
        $this->ticket(['title' => 'Normal title']);

        $response = $this->actingAs($this->admin)->get('/reports/tickets/export/csv');
        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="tickets-report-', $response->headers->get('Content-Disposition'));

        $csv = $response->getContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        // The dangerous cells now start with a single quote, so Excel shows them as text
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'@SUM(1+1)", $csv);
        $this->assertStringContainsString('Normal title', $csv);
        // No cell starts with a formula character
        $this->assertDoesNotMatchRegularExpression('/(^|,)"?[=+\-@]/m', $csv);
    }

    public function test_pdf_export_returns_a_pdf_file(): void
    {
        $this->ticket();

        foreach (['tickets', 'inventory', 'borrowing'] as $type) {
            $response = $this->actingAs($this->admin)->get("/reports/{$type}/export/pdf");
            $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
    }

    // ---------- Web service consumption and failure handling ----------

    public function test_real_api_is_called_with_key_and_ifa_fields(): void
    {
        $this->useRealApis();
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/api/m1/stock-summary')) {
                return Http::response($this->stockSummaryAnswer());
            }

            return Http::response(['status' => 'F', 'message' => 'Not used in this test'], 404);
        });

        $this->actingAs($this->admin)->get('/reports/inventory')->assertOk()->assertSee('Drone');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/m1/stock-summary')
            && $request->hasHeader('X-API-KEY', 'local-dev-key')
            && isset($request['requestID'], $request['timeStamp']));
    }

    public function test_when_a_module_is_down_only_its_panel_shows_an_error(): void
    {
        $this->useRealApis();
        Http::fake(['*' => Http::response('Server error', 500)]);
        $this->ticket(['title' => 'Still visible']);

        // The dashboard still opens and the ticket panel (own data) still works
        $this->actingAs($this->admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Still visible')
            ->assertSee('Module 1 (Inventory) is not responding right now.')
            ->assertSee('Module 2 (Borrowing) is not responding right now.');
    }

    public function test_saved_snapshot_is_shown_when_the_module_stops_answering(): void
    {
        $this->useRealApis();

        // The first stock-summary call succeeds; every later one fails
        $stockCalls = 0;
        Http::fake(function ($request) use (&$stockCalls) {
            if (! str_contains($request->url(), '/api/m1/stock-summary')) {
                return Http::response(['status' => 'F', 'message' => 'Not used in this test'], 404);
            }

            $stockCalls++;

            return $stockCalls === 1
                ? Http::response($this->stockSummaryAnswer())
                : Http::response('Server error', 500);
        });

        // First visit: the answer is fetched and saved
        $this->actingAs($this->admin)->get('/reports/inventory')->assertSee('Drone')->assertDontSee('Showing saved data');

        // Within 5 minutes the saved answer is reused without calling the module again
        $this->travel(2)->minutes();
        $this->actingAs($this->admin)->get('/reports/inventory')->assertSee('Drone');
        $this->assertSame(1, $stockCalls);

        // After 12 minutes the module is down: the old data is shown with a warning
        $this->travel(10)->minutes();
        $this->actingAs($this->admin)->get('/reports/inventory')
            ->assertSee('Drone')
            ->assertSee('Showing saved data');
        $this->assertSame(2, $stockCalls);
    }

    public function test_an_answer_with_status_f_counts_as_a_failure(): void
    {
        $this->useRealApis();
        Http::fake(['*' => Http::response(['status' => 'F', 'message' => 'Bad key'])]);

        $this->actingAs($this->admin)->get('/reports/borrowing')
            ->assertOk()->assertSee('Module 2 (Borrowing) is not responding right now.');
    }
}
