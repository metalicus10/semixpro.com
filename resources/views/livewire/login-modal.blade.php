<div
    x-cloak
    x-show="openLogin"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
>
    <div
        @click.away="openLogin = false"
        class="w-full max-w-md bg-brand-darker text-white p-6 rounded-2xl shadow-2xl border border-gray-700"
    >
        <h2 class="text-2xl font-bold text-brand-primary mb-6 text-center">
            Войти
        </h2>

        <form wire:submit.prevent="login" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                <input
                    id="email"
                    type="email"
                    wire:model="email"
                    class="mt-1 block w-full px-4 py-2 rounded-md bg-[#0f1a2b] border border-gray-600 text-white focus:ring-2 focus:ring-green-400 focus:outline-none"
                >
                @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">Пароль</label>
                <input
                    id="password"
                    type="password"
                    wire:model="password"
                    class="mt-1 block w-full px-4 py-2 rounded-md bg-[#0f1a2b] border border-gray-600 text-white focus:ring-2 focus:ring-green-400 focus:outline-none"
                >
                @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-between items-center text-sm text-gray-400">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model="remember" class="form-checkbox text-green-500 bg-[#0f1a2b] border-gray-600">
                    <span class="ml-2">Запомнить</span>
                </label>
                <a href="{{ route('password.request') }}" class="hover:underline text-blue-400">Забыли пароль?</a>
            </div>

            <button
                type="submit"
                class="w-full bg-brand-accent text-black font-semibold py-2 rounded-md hover:bg-brand-accent/80 transition"
            >
                Войти
            </button>
        </form>

        <div class="mt-5 text-center text-sm text-gray-400">
            Нет аккаунта?
            <a href="{{ route('register') }}" class="text-brand-primary hover:underline">Зарегистрироваться</a>
        </div>
    </div>
</div>
