<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        // Проверка на блокировку пользователя после аутентификации
        if (Auth::user()->is_blocked) {
            // Показываем сообщение пользователю
            session()->flash('error', 'Your account has been blocked');

            // Разлогиниваем пользователя после показа сообщения
            Auth::logout();

            // Перенаправляем обратно на страницу входа
            $this->redirect(route('login')); // или ваш маршрут для страницы логина
            return; // Прерываем дальнейшее выполнение
        }

        Session::regenerate();

        if(Auth::user()->hasAccess('platform.index')){
            $this->redirectIntended(default: route('platform.index', absolute: false), navigate: true);
        }
        if (Auth::user()->hasAccess('manager')){
            $this->redirectIntended(default: route('manager.manager', absolute: false), navigate: true);
        }
        if (Auth::user()->hasAccess('technician')){
            $this->redirectIntended(default: route('technician.technician', absolute: false), navigate: true);
        }

    }
};
?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="login" class="space-y-6">
        @csrf
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-brand-darker font-medium mb-2">
                {{ __('Email') }}
                <span class="text-brand-primary">*</span>
            </label>
            <input
                type="email"
                id="email"
                name="email"
                wire:model="form.email"
                class="w-full px-4 py-3 rounded-lg border transition duration-200
                               @error('form.email') border-red-500 bg-red-50 @else border-gray-200 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/10 @enderror"
                placeholder="Введите ваш email"
                required autofocus autocomplete="username"
            >
            @error('form.email')
            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-brand-darker font-medium mb-2">
                {{ __('Password') }}
                <span class="text-brand-primary">*</span>
            </label>

            <input
                type="password"
                id="password"
                wire:model="form.password"
                class="w-full px-4 py-3 rounded-lg border transition duration-200
                               @error('form.password') border-red-500 bg-red-50 @else border-gray-200 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/10 @enderror"
                placeholder="Введите ваш пароль" required autocomplete="current-password"
            >

            @error('form.password')
            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Запомнить меня -->
        <div class="flex items-center">
            <input
                type="checkbox"
                id="remember"
                wire:model="form.remember"
                class="w-4 h-4 text-brand-primary border-gray-300 rounded focus:ring-brand-primary"
            >
            <label for="remember" class="ml-2 text-brand-darker">
                {{ __('Remember me') }}
            </label>
        </div>

        <button
            type="submit"
            class="w-full bg-brand-primary hover:bg-brand-primary/90 text-white font-semibold
                           py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
            wire:loading.class="opacity-75 cursor-not-allowed"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove>{{ __('Log in') }}</span>
            <span wire:loading class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Logging in...') }}
            </span>
        </button>
        <!-- Забыли пароль -->
        @if (Route::has('password.request'))
            <div class="text-right">
                <a href="{{ route('password.request') }}"
                   wire:navigate
                   class="text-brand-primary hover:underline text-sm">
                    {{ __('Forgot your password?') }}
                </a>
            </div>
        @endif
    </form>
</div>
