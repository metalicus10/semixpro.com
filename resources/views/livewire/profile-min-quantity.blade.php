<div>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Минимальный остаток по умолчанию</h2>
        <p class="mt-1 text-sm text-gray-600">
            Введите мин. количество запчастей для получения уведомлений
        </p>
    </header>
    <form wire:submit.prevent="save" class="flex flex-col justify-between mt-6 space-y-6 h-max">
        <div>
            <label for="default_min_quantity" class="block font-medium text-sm text-gray-700">---</label>
            <input type="number" min="0" max="100000" width="100px" wire:model="default_min_quantity"
                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                   id="default_min_quantity">
            @error('default_min_quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="inline-flex w-1/3 justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md
        font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900
        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Сохранить
        </button>
        @if (session()->has('success'))
            <div class="text-green-500 text-xs mt-1">{{ session('success') }}</div>
        @endif
    </form>
</div>
