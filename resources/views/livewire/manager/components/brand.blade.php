

    <div id="brand-item-{{ $nomenclature['id'] }}"
         class="w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer parent-container"
         x-data="{
             showPopover: false,
             selectedBrands: @entangle('selectedBrands').defer || [],
             search: '',
             popoverX: 0,
             popoverY: 0
        }"
         @brands-updated.window="(event) => {
            if (event.detail === {{ $nomenclature['id'] }}) {
                $wire.set('selectedBrands', @json(collect($nomenclature)->pluck('brands.*.id')));
            }
         }"
         @click.away="showPopover = false"
         @mousedown.stop
         @click="
        const { clientX, clientY } = $event;
        $nextTick(() => {
            popoverX = Math.min(clientX, window.innerWidth - 250);
            popoverY = Math.min(clientY, window.innerHeight - 200);
            showPopover = true;
        });
     "
    >
        <!-- Текущие бренды -->
        <div class="flex flex-col h-24 w-20 justify-center p-1">
            <span class="md:hidden font-semibold">Brand:</span>
            <div class="overscroll-contain overflow-y-auto">
                <template x-if="selectedBrands.length === 0">
                    <div class="px-3 py-2">---</div>
                </template>
                <template x-if="selectedBrands.length > 0">
                    <span x-text="selectedBrands.map(id => brands.find(b => b.id == id)?.name).join(', ')"></span>
                </template>
            </div>
        </div>

        <!-- Поповер с мульти-выбором брендов -->
        <div x-show="showPopover"
             class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
             :style="`top: ${popoverY}px; left: ${popoverX}px;`"
             x-init="const onScroll = () => showPopover = false; window.addEventListener('scroll', onScroll)"
             @click.outside="showPopover = false"
             x-transition>

            <!-- Поле поиска -->
            <div class="mb-2" @click.stop>
                <input type="text" x-model="search"
                       placeholder="Search brands..."
                       class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:bg-gray-700 dark:text-gray-300"/>
            </div>

            <!-- Список брендов с мульти-выбором -->
            <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-28 overflow-y-auto">
                <template x-for="brand in brands.filter(b => !search || b.name.toLowerCase().includes(search.toLowerCase()))" :key="brand.id">
                    <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click.stop>
                        <input type="checkbox" :value="brand.id" x-model="selectedBrands"
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" @click.stop>
                        <label class="ml-2" x-text="brand.name"></label>
                    </li>
                </template>
            </ul>

            <!-- Кнопка подтверждения -->
            <div class="justify-self-center inline-flex self-center items-center">
                <button  @click="$wire.set('selectedBrands', selectedBrands).then(() => {
                    $wire.updateSelectedBrands({{ $nomenclature['id'] }});
                    showPopover = false;
                })"
                        class="bg-green-500 text-white px-2 py-1 rounded-full hover:bg-green-600">
                    ✓
                </button>
            </div>
        </div>
    </div>


