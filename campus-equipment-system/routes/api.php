<?php
// Author: Lai Chee Kit
// Web services that Module 4 exposes to the other modules. Every URL here starts with /api.

use App\Http\Controllers\Api\OpenFaultTicketController;
use Illuminate\Support\Facades\Route;

// throttle:60,1 = at most 60 calls per minute; module.key = the caller must send the shared X-API-KEY
Route::middleware(['throttle:60,1', 'module.key'])->group(function () {
    Route::get('/m4/equipment/{equipmentId}/open-fault-tickets', OpenFaultTicketController::class)
        ->name('api.m4.open-fault-tickets');
});
