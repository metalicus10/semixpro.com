<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Orchid\Platform\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Создание пользователя и назначение роли менеджера
        $managerRole = Role::where('slug', 'manager')->first();

        // Создаем пользователя с правами менеджера
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'permissions' => $managerRole ? $managerRole->permissions : [], // Присваиваем права роли менеджера
        ]);

        // Назначаем пользователю роль "Менеджер" через Orchid
        if ($managerRole) {
            $user->addRole($managerRole);
        }

        Warehouse::create([
            'manager_id' => $user->id,
            'name' => 'Main',
            'is_default' => true,
        ]);

        Category::create([
            'manager_id' => $user->id,
            'name' => 'Default',
        ]);

        Brand::create([
            'manager_id' => $user->id,
            'name' => 'Default',
        ]);

        Supplier::create([
            'manager_id' => $user->id,
            'name' => 'Default',
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('manager.manager', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')"/>
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required
                          autofocus autocomplete="name"/>
            <x-input-error :messages="$errors->get('name')" class="mt-2"/>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')"/>
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required
                          autocomplete="username"/>
            <x-input-error :messages="$errors->get('email')" class="mt-2"/>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')"/>

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="new-password"/>

            <x-input-error :messages="$errors->get('password')" class="mt-2"/>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')"/>

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                          type="password"
                          name="password_confirmation" required autocomplete="new-password"/>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>
        </div>

        <div class="mt-4">
            <label for="terms" class="flex items-center">
                <input wire:model="terms" id="terms" type="checkbox" class="mr-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">
            I agree with the terms of use of the resource and the processing of personal data
        </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2"/>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
               href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
