<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\{
    DashboardController,
    EventController,
    ParticipantController,
    AttendanceController,
    UnitController,
    UserController
};
use App\Http\Controllers\Public\AttendanceController as PublicAttendanceController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════
//  AUTH
// ══════════════════════════════════════════════════════════════
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', fn() => redirect()->route('login'));

// ══════════════════════════════════════════════════════════════
//  PUBLIC — Halaman absensi (tanpa login)
//  URL unik per event: /absensi/{slug}
// ══════════════════════════════════════════════════════════════
Route::prefix('absensi')->name('attendance.')->group(function () {
    // Halaman utama absensi
    Route::get('/{slug}', [PublicAttendanceController::class, 'show'])->name('show');

    // AJAX endpoints
    Route::post('/{slug}/cari',    [PublicAttendanceController::class, 'find'])->name('find');
    Route::post('/{slug}/hadir',   [PublicAttendanceController::class, 'markAttendance'])->name('mark');
    Route::get('/{slug}/peserta',  [PublicAttendanceController::class, 'participants'])->name('participants');
    Route::get('/{slug}/riwayat',  [PublicAttendanceController::class, 'history'])->name('history');
    Route::get('/{slug}/ruang',    [PublicAttendanceController::class, 'rooms'])->name('rooms');
});

// ══════════════════════════════════════════════════════════════
//  ADMIN (harus login)
// ══════════════════════════════════════════════════════════════
Route::prefix('admin')->name('admin.')->middleware(['auth.admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Events ────────────────────────────────────────────────
    Route::resource('events', EventController::class);
    Route::patch('events/{event}/toggle-active',      [EventController::class, 'toggleActive'])->name('events.toggle');
    Route::post('events/{event}/regenerate-codes',    [EventController::class, 'regenerateCodes'])->name('events.regenerate-codes');
    Route::post('events/{event}/digit-settings',      [EventController::class, 'updateDigitSettings'])->name('events.digit-settings');
    Route::get('events/{event}/detect-digits',        [EventController::class, 'detectDigits'])->name('events.detect-digits');

    // ── Participants (nested dalam event) ─────────────────────
    Route::prefix('events/{event}/participants')->name('participants.')->group(function () {
        Route::get('/',            [ParticipantController::class, 'index'])->name('index');
        Route::get('/create',      [ParticipantController::class, 'create'])->name('create');
        Route::post('/',           [ParticipantController::class, 'store'])->name('store');
        Route::get('/{participant}/edit', [ParticipantController::class, 'edit'])->name('edit');
        Route::put('/{participant}',      [ParticipantController::class, 'update'])->name('update');
        Route::delete('/{participant}',   [ParticipantController::class, 'destroy'])->name('destroy');
        // Import
        Route::get('/import',      [ParticipantController::class, 'importForm'])->name('import');
        Route::post('/import',     [ParticipantController::class, 'importProcess'])->name('import.process');
        Route::get('/template',    [ParticipantController::class, 'downloadTemplate'])->name('template');
    });

    // ── Attendances (nested dalam event) ──────────────────────
    Route::prefix('events/{event}/attendances')->name('attendances.')->group(function () {
        Route::get('/',            [AttendanceController::class, 'index'])->name('index');
        Route::get('/per-ruang',   [AttendanceController::class, 'byRoom'])->name('by-room');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        Route::post('/reset',      [AttendanceController::class, 'reset'])->name('reset');
        Route::get('/export',      [AttendanceController::class, 'export'])->name('export');
    });

    // ── Superadmin only ───────────────────────────────────────
    Route::middleware(['auth.superadmin'])->group(function () {
        Route::resource('units', UnitController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
    });
});
