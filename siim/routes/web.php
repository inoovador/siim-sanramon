<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use App\Http\Controllers\RevealSurveyContactController;
use App\Http\Controllers\SurveyBuilderController;
use App\Http\Controllers\SurveyExportController;
use App\Http\Controllers\SurveyIndexController;
use App\Http\Controllers\SurveyResultsController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/health', HealthController::class)->name('health');

Route::view('/', 'welcome');

Volt::route('/encuesta', 'public.survey.show')
    ->middleware('throttle:20,1')
    ->name('survey.show');
Volt::route('/encuesta/gracias', 'public.survey.thanks')
    ->middleware('throttle:20,1')
    ->name('survey.thanks');

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('/panel', 'panel.dashboard')->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('/panel/encuestas/nueva', [SurveyBuilderController::class, 'create'])->name('panel.surveys.create');
        Route::post('/panel/encuestas', [SurveyBuilderController::class, 'store'])->name('panel.surveys.store');
        Route::post('/panel/encuestas/{slug}/respuestas/{response}/contacto', RevealSurveyContactController::class)
            ->middleware('throttle:30,1')
            ->name('panel.surveys.contact');
    });

    Route::middleware('role:admin|analyst')->group(function () {
        Route::get('/panel/encuestas', SurveyIndexController::class)->name('panel.surveys');
        Route::get('/panel/encuestas/{slug}/export', SurveyExportController::class)->name('panel.surveys.export');
        Route::get('/panel/encuestas/{slug}', SurveyResultsController::class)->name('panel.surveys.results');
    });

    Volt::route('/panel/comentarios', 'panel.comments.index')->name('panel.comments');
    Volt::route('/panel/temas', 'panel.topics.index')->name('panel.topics');
    Volt::route('/panel/fuentes', 'panel.sources.index')->name('panel.sources');
    Volt::route('/panel/chat-rag', 'panel.rag-chat.index')->name('panel.rag-chat');
    Volt::route('/panel/reportes', 'panel.reports.index')->name('panel.reports');
    Volt::route('/panel/auditoria', 'panel.audit.index')->name('panel.audit');

    Route::middleware('role:admin')->group(function () {
        Volt::route('/panel/usuarios', 'panel.users.index')->name('panel.users');
        Volt::route('/panel/configuracion', 'panel.config.index')->name('panel.config');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
