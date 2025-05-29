<div x-data="{
        min_quantity: @entangle('min_quantity'),
        loading: false,
        success: false,
        message: '',
        errorMessage: @entangle('errorMessage'),
        save() {
            this.loading = true;
            $wire.save()
                .then((result) => {
                    this.loading = false;
                    // Показываем уведомление
                    this.success = true;
                    this.message = 'Минимальный остаток сохранён!';
                    setTimeout(() => this.success = false, 2500);
                })
                .catch((err) => {
                    this.loading = false;
                    this.success = false;
                    this.message = 'Ошибка при сохранении';
                });
        }
    }">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Минимальный остаток по умолчанию</h2>
        <p class="mt-1 text-sm text-gray-600">
            Введите мин. количество запчастей для получения уведомлений
        </p>
    </header>
    <form @submit.prevent="save" class="flex flex-col justify-between mt-6 space-y-6 h-max">
        <div>
            <label for="min_quantity" class="block font-medium text-sm text-gray-700">---</label>
            <input type="number" min="0" max="100000" width="100px" :value="min_quantity"
                   x-model="min_quantity" :disabled="loading"
                   placeholder="Введите минимальный порог" @change="$wire.save()"
                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                   id="min_quantity">
            <template x-if="errorMessage">
                <span class="text-red-500 text-xs" x-text="errorMessage"></span>
            </template>
        </div>
        <button type="submit" :disabled="loading"
                class="inline-flex w-1/3 justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md
        font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900
        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <template x-if="loading">
                <span>Сохранение...</span>
            </template>
            <template x-if="!loading">
                <span>Сохранить</span>
            </template>
        </button>
        <div x-show="message" :class="success ? 'text-green-500' : message=''" class="text-xs mt-1" x-text="message"></div>
    </form>
</div>
