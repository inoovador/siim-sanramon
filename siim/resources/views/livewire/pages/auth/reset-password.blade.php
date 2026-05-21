<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <header class="text-center mb-6">
        <h2 class="text-2xl font-serif font-bold text-brand-canopy">Nueva contraseña</h2>
        <p class="mt-1 text-sm text-ink-soft">Elige una contraseña segura para tu cuenta</p>
    </header>

    <form wire:submit="resetPassword" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Nueva contraseña')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full btn-primary justify-center py-2.5">
            Restablecer contraseña
        </button>
    </form>
</div>
