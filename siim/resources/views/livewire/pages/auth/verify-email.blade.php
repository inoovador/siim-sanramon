<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <header class="text-center mb-6">
        <h2 class="text-2xl font-serif font-bold text-brand-canopy">Verifica tu correo</h2>
        <p class="mt-1 text-sm text-ink-soft">Un paso más antes de continuar</p>
    </header>

    <p class="mb-4 text-sm text-ink-soft">
        {{ __('Gracias por registrarte. Antes de comenzar, verifica tu dirección de correo haciendo clic en el enlace que te enviamos. Si no lo recibiste, podemos enviarte otro.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('Se ha enviado un nuevo enlace de verificación a tu correo electrónico.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3">
        <button wire:click="sendVerification" class="btn-primary py-2.5 px-4">
            {{ __('Reenviar verificación') }}
        </button>

        <button wire:click="logout" type="button" class="text-sm text-ink-soft hover:text-brand-clay underline">
            {{ __('Cerrar sesión') }}
        </button>
    </div>
</div>
