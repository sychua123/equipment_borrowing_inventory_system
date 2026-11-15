<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use App\Http\Controllers\TicketStatusController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Home: Admin goes to the dashboard, everyone else to their tickets
Route::get('/', fn () => redirect()->route(Auth::user()?->isAdmin() ? 'dashboard' : 'tickets.index'));

// ----- Module 4 (Lai Chee Kit): Support Ticket, Reporting, Dashboard -----
Route::middleware('auth')->group(function () {
    // Support Ticket
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{ticket}/status', [TicketStatusController::class, 'update'])->name('tickets.status');
    Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store'])->name('tickets.replies.store');
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');

    // In-app notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');

    // Dashboard and reports: Admin only
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', fn () => redirect()->route('reports.show', 'tickets'))->name('reports.index');

        Route::get('/reports/{type}', [ReportController::class, 'show'])
            ->whereIn('type', array_keys(ReportController::REPORTS))
            ->name('reports.show');

        Route::get('/reports/{type}/export/{format}', [ReportController::class, 'export'])
            ->whereIn('type', array_keys(ReportController::REPORTS))
            ->whereIn('format', ['csv', 'pdf'])
            ->name('reports.export');
    });
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

        return redirect('/');
    })->name('dev.login');
}
