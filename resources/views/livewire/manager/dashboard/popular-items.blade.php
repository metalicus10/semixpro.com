<div x-data="{
        tab: @entangle('tab'),
        parts: @entangle('popularParts'),
        services: @entangle('popularServices')
    }" class="bg-[#101623] rounded-xl border border-slate-700 p-6 flex flex-col w-full">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-white text-xl font-bold">Популярные запчасти/услуги</h2>
        <div class="flex gap-2 text-slate-400">
            <button
                :class="tab === 'parts' ? 'text-green-400 border-b-2 border-green-400' : 'text-slate-400'"
                class="text-sm px-2 pb-1 transition"
                @click="tab = 'parts'">
                Запчасти
            </button>
            <button
                :class="tab === 'services' ? 'text-green-400 border-b-2 border-green-400' : 'text-slate-400'"
                class="text-sm px-2 pb-1 transition"
                @click="tab = 'services'">
                Услуги
            </button>
        </div>
        <a href="#" class="text-green-400 text-sm flex items-center gap-1 hover:underline ml-auto">ПОКАЗАТЬ ЕЩЁ →</a>
    </div>

    <template x-if="tab === 'parts'">
        <div>
            <template x-for="item in parts" :key="item.id">
                <!-- Разметка карточки запчасти -->
                <div class="flex items-center gap-4 bg-[#181F32] rounded-lg p-2 transition mb-3">
                    <img :src="item.image" alt="" class="w-12 h-12 rounded-lg object-cover" />
                    <div class="flex flex-col">
                        <span class="text-white font-semibold text-sm" x-text="item.name"></span>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="inline-flex items-center rounded-full bg-blue-600 text-white text-xs px-2 py-0.5 font-semibold" x-text="item.variants + ' вариантов'"></span>
                            <span class="text-slate-400 text-sm" x-text="item.category"></span>
                            <span class="text-slate-400 text-sm" x-text="item.stock + ' на складе'"></span>
                            <span class="text-white text-sm font-medium" x-text="item.available + ' в наличии'"></span>
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <template x-if="tab === 'services'">
        <div>
            <template x-for="service in services" :key="service.id">
                <!-- Карточка услуги -->
                <div class="flex items-center gap-4 bg-[#181F32] rounded-lg p-2 transition mb-3">
                    <div class="w-12 h-12 rounded-lg bg-blue-800 flex items-center justify-center text-white text-2xl">
                        <!-- Можно вставить SVG-иконку услуги -->
                        <svg>...</svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-white font-semibold text-sm" x-text="service.name"></span>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="inline-flex items-center rounded-full bg-blue-600 text-white text-xs px-2 py-0.5 font-semibold" x-text="service.category"></span>
                            <span class="text-white text-sm font-medium" x-text="'Заказов: ' + service.orders"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
