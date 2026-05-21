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
    {{-- Heading --}}
    <header class="mb-8">
        <h1 class="text-3xl lg:text-4xl font-serif font-bold text-brand-canopy">Log In</h1>
        <p class="mt-2 text-sm text-ink-soft">Acceso para funcionarios autorizados de Imagen Institucional</p>
    </header>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">

        {{-- Email --}}
        <div>
            <input
                wire:model="form.email"
                id="email"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="username"
                placeholder="Correo electrónico"
                class="w-full px-5 py-3.5 rounded-xl border border-brand-canopy/15 bg-brand-mist/30 placeholder:text-ink-soft/60 focus:outline-none focus:border-brand-canopy focus:ring-2 focus:ring-brand-canopy/15 transition"
            />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
            <input
                wire:model="form.password"
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Contraseña"
                class="w-full px-5 py-3.5 rounded-xl border border-brand-canopy/15 bg-brand-mist/30 placeholder:text-ink-soft/60 focus:outline-none focus:border-brand-canopy focus:ring-2 focus:ring-brand-canopy/15 transition"
            />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        {{-- Remember + forgot --}}
        <div class="flex items-center justify-between text-sm">
            <label for="remember" class="inline-flex items-center gap-2 text-ink-soft cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember"
                       class="rounded border-brand-canopy/20 text-brand-canopy focus:ring-brand-canopy/30" />
                Recordarme
            </label>

            @if (Route::has('password.request'))
                <a class="text-brand-river hover:text-brand-canopy font-medium underline-offset-2 hover:underline transition" href="{{ route('password.request') }}" wire:navigate>
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full py-3.5 rounded-xl bg-brand-canopy text-white font-medium shadow-brand hover:bg-brand-canopy/90 hover:shadow-brand-lg focus:outline-none focus:ring-2 focus:ring-brand-gold/50 transition"
            wire:loading.attr="disabled"
            wire:target="login"
        >
            <span wire:loading.remove wire:target="login">Iniciar sesión</span>
            <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Iniciando...
            </span>
        </button>

        @if (Route::has('register'))
            <p class="text-center text-sm text-ink-soft pt-2">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" class="text-brand-river hover:text-brand-canopy font-medium underline-offset-2 hover:underline" wire:navigate>Solicita acceso</a>
            </p>
        @endif
    </form>
</div>
