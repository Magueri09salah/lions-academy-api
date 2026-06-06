<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\ConcoursSettingsController as AdminConcoursSettingsController;
use App\Http\Controllers\Api\V1\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\V1\Admin\FormationController as AdminFormationController;
use App\Http\Controllers\Api\V1\Admin\PrincipleController as AdminPrincipleController;
use App\Http\Controllers\Api\V1\Admin\ProgramMonthController as AdminProgramMonthController;
use App\Http\Controllers\Api\V1\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\V1\Admin\RegistrationConcoursController as AdminRegistrationConcoursController;
use App\Http\Controllers\Api\V1\Admin\RegistrationConcoursExportController;
use App\Http\Controllers\Api\V1\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Api\V1\Admin\RegistrationExportController;
use App\Http\Controllers\Api\V1\Admin\TrainerController as AdminTrainerController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Media\MediaController;
use App\Http\Controllers\Api\V1\Public\ConcoursSettingsController as PublicConcoursSettingsController;
use App\Http\Controllers\Api\V1\Public\ContactMessageController as PublicContactMessageController;
use App\Http\Controllers\Api\V1\Public\FormationController as PublicFormationController;
use App\Http\Controllers\Api\V1\Public\PrincipleController as PublicPrincipleController;
use App\Http\Controllers\Api\V1\Public\ProgramMonthController as PublicProgramMonthController;
use App\Http\Controllers\Api\V1\Public\ProjectController as PublicProjectController;
use App\Http\Controllers\Api\V1\Public\RegistrationConcoursController as PublicRegistrationConcoursController;
use App\Http\Controllers\Api\V1\Public\RegistrationController as PublicRegistrationController;
use App\Http\Controllers\Api\V1\Public\TrainerController as PublicTrainerController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 routes
|--------------------------------------------------------------------------
| Bucket conventions:
|   - public-read  : cacheable GETs from the public site
|   - public-write : POST /registrations, /contact-messages (anti-spam)
|   - auth         : login + password-reset flows
|   - admin        : authenticated /admin/* endpoints
*/

Route::prefix('v1')->group(function (): void {

    // ---- Public health/meta ---------------------------------------------
    Route::get('/health', fn () => ApiResponse::success([
        'status' => 'ok',
        'time' => now()->toIso8601String(),
    ]));

    // ---- Auth (no auth required) ----------------------------------------
    Route::middleware('throttle:auth')->prefix('auth')->group(function (): void {
        Route::post('/login', LoginController::class)->name('auth.login');
        Route::post('/forgot-password', ForgotPasswordController::class)->name('auth.forgot');
        Route::post('/reset-password', ResetPasswordController::class)->name('auth.reset');
    });

    // ---- Auth (authenticated) -------------------------------------------
    Route::middleware(['auth:sanctum', 'throttle:admin'])->prefix('auth')->group(function (): void {
        Route::post('/logout', LogoutController::class)->name('auth.logout');
        Route::get('/me', [MeController::class, 'show'])->name('auth.me');
        Route::patch('/me/password', [MeController::class, 'changePassword'])->name('auth.me.password');
        Route::post('/logout-everywhere', [MeController::class, 'logoutEverywhere'])->name('auth.logout.everywhere');
    });

    // =====================================================================
    // PUBLIC (read-only) — consumed by the TanStack frontend
    // =====================================================================
    Route::middleware('throttle:public-read')->group(function (): void {
        Route::get('/formations', [PublicFormationController::class, 'index'])
            ->name('formations.index');
        Route::get('/formations/{slug}', [PublicFormationController::class, 'show'])
            ->name('formations.show');

        Route::get('/programme', [PublicProgramMonthController::class, 'index'])
            ->name('programme.index');

        Route::get('/projects', [PublicProjectController::class, 'index'])
            ->name('projects.index');
        Route::get('/projects/{project}', [PublicProjectController::class, 'show'])
            ->name('projects.show');

        Route::get('/trainers', [PublicTrainerController::class, 'index'])
            ->name('trainers.index');

        Route::get('/principles', [PublicPrincipleController::class, 'index'])
            ->name('principles.index');

        Route::get('/concours-settings', [PublicConcoursSettingsController::class, 'show'])
            ->name('concours-settings.show');
    });

    // =====================================================================
    // PUBLIC (write, anti-spam) — inscription form
    // =====================================================================
    Route::middleware(['throttle:public-write', 'honeypot'])->group(function (): void {
        Route::post('/registrations', [PublicRegistrationController::class, 'store'])
            ->name('registrations.store');
        Route::post('/contact-messages', [PublicContactMessageController::class, 'store'])
            ->name('contact-messages.store');

        Route::post('/registrations-concours', [PublicRegistrationConcoursController::class, 'store'])
            ->name('registrations-concours.store');
    });

    // =====================================================================
    // ADMIN — Sanctum + active staff account required
    // =====================================================================
    Route::middleware(['auth:sanctum', 'admin', 'throttle:admin'])
        ->prefix('admin')
        ->group(function (): void {

            // Media: upload + delete.
            Route::post('/media', [MediaController::class, 'store'])->name('admin.media.store');
            Route::post('/media/videos', [MediaController::class, 'storeVideo'])->name('admin.media.video');
            Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('admin.media.destroy');

            // ---- Registrations (HIGH PRIORITY MODULE) --------------------
            // CSV export must be registered BEFORE the {registration} param
            // so "/export.csv" does not match the implicit-binding route.
            Route::get('/registrations/export.csv', [RegistrationExportController::class, 'csv'])
                ->name('admin.registrations.export.csv');
            Route::get('/registrations/export.xlsx', [RegistrationExportController::class, 'xlsx'])
                ->name('admin.registrations.export.xlsx');

            Route::get('/registrations', [AdminRegistrationController::class, 'index'])
                ->name('admin.registrations.index');
            Route::get('/registrations/{registration}', [AdminRegistrationController::class, 'show'])
                ->name('admin.registrations.show');
            Route::patch('/registrations/{registration}', [AdminRegistrationController::class, 'update'])
                ->name('admin.registrations.update');
            Route::delete('/registrations/{registration}', [AdminRegistrationController::class, 'destroy'])
                ->name('admin.registrations.destroy');

            // ---- ENA prep leads (Concours) -------------------------------
            // Export routes BEFORE the {lead} param so /export.csv doesn't
            // collide with implicit binding.
            Route::get('/registrations-concours/export.csv', [RegistrationConcoursExportController::class, 'csv'])
                ->name('admin.registrations-concours.export.csv');
            Route::get('/registrations-concours/export.xlsx', [RegistrationConcoursExportController::class, 'xlsx'])
                ->name('admin.registrations-concours.export.xlsx');

            Route::get('/registrations-concours', [AdminRegistrationConcoursController::class, 'index'])
                ->name('admin.registrations-concours.index');
            Route::get('/registrations-concours/{lead}', [AdminRegistrationConcoursController::class, 'show'])
                ->name('admin.registrations-concours.show');
            Route::patch('/registrations-concours/{lead}', [AdminRegistrationConcoursController::class, 'update'])
                ->name('admin.registrations-concours.update');
            Route::delete('/registrations-concours/{lead}', [AdminRegistrationConcoursController::class, 'destroy'])
                ->name('admin.registrations-concours.destroy');

            Route::get('/concours-settings', [AdminConcoursSettingsController::class, 'show'])
                ->name('admin.concours-settings.show');
            Route::patch('/concours-settings', [AdminConcoursSettingsController::class, 'update'])
                ->name('admin.concours-settings.update');

            // ---- Contact messages ----------------------------------------
            Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])
                ->name('admin.contact-messages.index');
            Route::get('/contact-messages/{message}', [AdminContactMessageController::class, 'show'])
                ->name('admin.contact-messages.show');
            Route::patch('/contact-messages/{message}', [AdminContactMessageController::class, 'update'])
                ->name('admin.contact-messages.update');
            Route::delete('/contact-messages/{message}', [AdminContactMessageController::class, 'destroy'])
                ->name('admin.contact-messages.destroy');

            // ---- Principles (content) ------------------------------------
            Route::apiResource('/principles', AdminPrincipleController::class)
                ->parameters(['principles' => 'principle']);

            // ---- Programme (content) -------------------------------------
            Route::apiResource('/programme', AdminProgramMonthController::class)
                ->parameters(['programme' => 'month']);

            // ---- Trainers (content) --------------------------------------
            Route::apiResource('/trainers', AdminTrainerController::class)
                ->parameters(['trainers' => 'trainer']);

            // ---- Projects / Réalisations (content) -----------------------
            Route::apiResource('/projects', AdminProjectController::class)
                ->parameters(['projects' => 'project']);

            // ---- Formations (content) ------------------------------------
            Route::apiResource('/formations', AdminFormationController::class)
                ->parameters(['formations' => 'formation']);
        });
});

// Signed public route for private file downloads.
Route::get('/media/{media}', [MediaController::class, 'download'])
    ->middleware(['signed', 'throttle:media-download'])
    ->name('media.download');
