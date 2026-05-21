<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <header class="text-center mb-6">
        <h2 class="text-2xl font-serif font-bold text-brand-canopy">Recuperar contraseña</h2>
        <p class="mt-1 text-sm text-ink-soft">Te enviaremos un enlace a tu correo electrónico</p>
    </header>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" class="w-full btn-primary justify-center py-2.5">
            Enviar enlace de recuperación
        </button>

        <p class="text-center text-sm text-ink-soft">
            <a href="{{ route('login') }}" class="text-brand-river hover:text-brand-canopy underline" wire:navigate>Volver al inicio de sesión</a>
        </p>
    </form>
</div>
