<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <header class="text-center mb-6">
        <h2 class="text-2xl font-serif font-bold text-brand-canopy">Crear cuenta</h2>
        <p class="mt-1 text-sm text-ink-soft">Acceso ciudadano y de prueba</p>
    </header>

    <form wire:submit="register" class="space-y-5">
        <div>
            <x-input-label for="name" :value="__('Nombre')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="text-ink-deep font-medium" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full rounded-lg border-gray-300 focus:border-brand-canopy focus:ring-brand-canopy" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full btn-primary justify-center py-2.5">
            Registrarse
        </button>

        <p class="text-center text-sm text-ink-soft">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="text-brand-river hover:text-brand-canopy underline" wire:navigate>Inicia sesión</a>
        </p>
    </form>
</div>
