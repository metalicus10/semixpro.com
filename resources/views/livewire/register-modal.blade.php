<div
    x-init="$watch('openRegister', value => value ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))"
    x-show="openRegister"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
>
    <div
        @click.away="openRegister = false"
        class="w-full max-w-md bg-[#101624] text-white p-6 rounded-2xl shadow-2xl border border-gray-700"
    >
        <h2 class="text-2xl font-bold text-yellow-400 mb-6 text-center">Регистрация</h2>

        <form wire:submit.prevent="register" class="space-y-5">
            {{-- Имя --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300">Имя</label>
                <input
                    wire:model="name"
                    type="text"
                    id="name"
                    class="mt-1 block w-full bg-[#0f1a2b] text-white border border-gray-600 rounded-md px-4 py-2 focus:ring-2 focus:ring-green-400"
                >
                @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                <input
                    wire:model="email"
                    type="email"
                    id="email"
                    class="mt-1 block w-full bg-[#0f1a2b] text-white border border-gray-600 rounded-md px-4 py-2 focus:ring-2 focus:ring-green-400"
                >
                @error('email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Пароль --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">Пароль</label>
                <input
                    wire:model="password"
                    type="password"
                    id="password"
                    class="mt-1 block w-full bg-[#0f1a2b] text-white border border-gray-600 rounded-md px-4 py-2 focus:ring-2 focus:ring-green-400"
                >
                @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Подтверждение пароля --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Повторите пароль</label>
                <input
                    wire:model="password_confirmation"
                    type="password"
                    id="password_confirmation"
                    class="mt-1 block w-full bg-[#0f1a2b] text-white border border-gray-600 rounded-md px-4 py-2 focus:ring-2 focus:ring-green-400"
                >
            </div>

            {{-- Согласие с условиями --}}
            <div class="flex items-start space-x-2">
                <input
                    type="checkbox"
                    id="terms"
                    wire:model="terms"
                    class="form-checkbox mt-1 text-green-500 bg-[#0f1a2b] border-gray-600"
                >
                <label for="terms" class="text-sm text-gray-400 leading-tight">
                    Я соглашаюсь с <a href="#" class="text-green-400 hover:underline">условиями использования</a>
                </label>
            </div>
            @error('terms') <span class="text-sm text-red-500">{{ $message }}</span> @enderror

            {{-- Кнопка --}}
            <button
                type="submit"
                class="w-full bg-green-500 text-black font-semibold py-2 rounded-md hover:bg-green-400 transition"
            >
                Зарегистрироваться
            </button>
        </form>
    </div>
</div>
