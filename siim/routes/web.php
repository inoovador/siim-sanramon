<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/health', HealthController::class)->name('health');

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('/panel', 'panel.dashboard')->name('dashboard');

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
