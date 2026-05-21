<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <header class="text-center mb-6">
        <h2 class="text-2xl font-serif font-bold text-brand-canopy">Bienvenido</h2>
        <p class="mt-1 text-sm text-ink-soft">Acceso para funcionarios autorizados</p>
    </header>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-brand-canopy focus:ring-brand-canopy" name="remember">
                <span class="ms-2 text-sm text-ink-soft">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-brand-river hover:text-brand-canopy underline" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="w-full btn-primary justify-center py-2.5">
            Iniciar sesión
        </button>

        @if (Route::has('register'))
            <p class="text-center text-sm text-ink-soft">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" class="text-brand-river hover:text-brand-canopy underline" wire:navigate>Regístrate</a>
            </p>
        @endif
    </form>
</div>
