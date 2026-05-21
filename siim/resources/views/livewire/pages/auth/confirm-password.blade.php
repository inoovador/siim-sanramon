<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <header class="text-center mb-6">
        <h2 class="text-2xl font-serif font-bold text-brand-canopy">Confirma tu contraseña</h2>
        <p class="mt-1 text-sm text-ink-soft">Zona segura — verifica tu identidad para continuar</p>
    </header>

    <form wire:submit="confirmPassword" class="space-y-5">
        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button type="submit" class="w-full btn-primary justify-center py-2.5">
            Confirmar
        </button>
    </form>
</div>
