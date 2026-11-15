<?php

use App\Http\Controllers\TicketController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('tickets.index'));

// ----- Module 4 (Lai Chee Kit): Support Ticket -----
Route::middleware('auth')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
});

// ----- TEMPORARY login stand-in (Lai Chee Kit) -----
// Login belongs to Module 1. Until it is ready, pick a seeded test account here.
// Works on a local machine only; remove when Module 1's login is merged.
if (app()->environment('local', 'testing')) {
    Route::get('/login', fn () => view('dev.login', ['users' => User::query()->orderBy('id')->get()]))
        ->name('login');

    Route::post('/dev/login/{user}', function (User $user) {
        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('tickets.index');
    })->name('dev.login');
}
