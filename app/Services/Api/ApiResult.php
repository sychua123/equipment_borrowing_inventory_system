<?php
// Author: Lai Chee Kit
// Module 4: the answer of one call to another module's web service.
// A page checks this object to decide whether to show data, old data with a warning, or an error.

namespace App\Services\Api;

use Illuminate\Support\Carbon;

class ApiResult
{
    private function __construct(
        public readonly bool $ok,          // true = there is data to show (fresh or snapshot)
        public readonly array $data,
        public readonly bool $stale,       // true = the call failed and this is the old snapshot
        public readonly ?Carbon $fetchedAt,
        public readonly ?string $error,
    ) {
    }

    public static function fresh(array $data, Carbon $fetchedAt): self
    {
        return new self(true, $data, false, $fetchedAt, null);
    }

    public static function snapshot(array $data, Carbon $fetchedAt, string $error): self
    {
        return new self(true, $data, true, $fetchedAt, $error);
    }

    public static function failed(string $error): self
    {
        return new self(false, [], false, null, $error);
    }

    // The message to show above a panel, or null when everything is fine
    public function notice(): ?string
    {
        if (! $this->ok) {
            return $this->error;
        }

        if ($this->stale) {
            return $this->error.' Showing saved data, updated '.$this->fetchedAt->diffForHumans().'.';
        }

        return null;
    }
}
