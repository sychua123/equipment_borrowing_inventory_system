<?php
// Author: Lai Chee Kit
// Module 4: the one place that calls other modules' REST web services (the "consume" side).
//
// Rules agreed by the team:
//   1. Every call gives up after 5 seconds (timeout).
//   2. After a successful call the answer is saved as a snapshot and reused for 5 minutes.
//   3. When a call fails, the old snapshot is shown with a warning. No snapshot = an error
//      for that panel only; the rest of the page still works.

namespace App\Services\Api;

use App\Support\Ifa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ModuleApiClient
{
    private const MODULE_NAMES = ['module1' => 'Module 1 (Inventory)', 'module2' => 'Module 2 (Borrowing)'];

    public function __construct(private MockModuleData $mock)
    {
    }

    /**
     * @param  string  $module  "module1" or "module2" (see config/module4.php)
     * @param  string  $pathKey  which endpoint, for example "equipment"
     * @param  array  $query  extra query values, for example a date range
     */
    public function get(string $module, string $pathKey, array $query = []): ApiResult
    {
        $cacheKey = 'm4.api.'.md5($module.'|'.$pathKey.'|'.json_encode($query));
        $snapshot = Cache::get($cacheKey);

        // Rule 2: a snapshot younger than 5 minutes is reused without calling again
        if ($snapshot !== null) {
            $fetchedAt = Carbon::createFromTimestamp($snapshot['fetched_at']);

            if ($fetchedAt->diffInMinutes(now()) < config('module4.snapshot_minutes')) {
                return ApiResult::fresh($snapshot['data'], $fetchedAt);
            }
        }

        try {
            $body = $this->fetch($module, $pathKey, $query);

            // The other module must answer in the IFA format with status "S"
            if (! is_array($body) || ($body['status'] ?? null) !== 'S' || ! is_array($body['data'] ?? null)) {
                throw new RuntimeException('Unexpected answer: '.Str::limit(json_encode($body), 200));
            }

            Cache::forever($cacheKey, ['data' => $body['data'], 'fetched_at' => now()->timestamp]);

            return ApiResult::fresh($body['data'], now());
        } catch (Throwable $e) {
            // Timeout, connection error, HTTP error, or status F/E
            Log::warning("Module 4 could not read {$module}/{$pathKey}: ".$e->getMessage());

            $message = self::MODULE_NAMES[$module].' is not responding right now.';

            // Rule 3
            return $snapshot !== null
                ? ApiResult::snapshot($snapshot['data'], Carbon::createFromTimestamp($snapshot['fetched_at']), $message)
                : ApiResult::failed($message.' Please try again later.');
        }
    }

    private function fetch(string $module, string $pathKey, array $query): mixed
    {
        $settings = config("module4.{$module}");
        $path = $settings['paths'][$pathKey];

        // Every request carries requestID + timeStamp (IFA requirement)
        $query['requestID'] = (string) Str::uuid();
        $query['timeStamp'] = now()->format(Ifa::TIME_FORMAT);

        // Until the teammate's API exists, answer from the built-in fake data
        if ($settings['mock']) {
            return $this->mock->respond($path, $query);
        }

        // Rule 1: timeout
        return Http::timeout(config('module4.api_timeout'))
            ->connectTimeout(config('module4.api_timeout'))
            ->acceptJson()
            ->withHeaders(['X-API-KEY' => config('module4.api_key')])
            ->get(rtrim($settings['base_url'], '/').$path, $query)
            ->throw()
            ->json();
    }
}
