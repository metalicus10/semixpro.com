@php
    $isEditing = fn($id) => $editingWarehouseId === $id;
@endphp
<div class="p-2 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <!-- Индикатор загрузки -->
    <div wire:loading.flex wire:target="switchTab" class="absolute inset-0 flex items-center justify-center bg-gray-900 opacity-50 z-50">
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-orange-500"></div>
    </div>

    <!-- Заголовок страницы и фильтры -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Parts</h1>
        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
            <!-- Фильтр по категориям -->
            <div class="flex flex-row justify-between items-center">
                <label for="category" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by
                    Cat:</label>
                <select wire:model.live="selectedCategory" id="category"
                        class="w-28 ml-2 p-2 text-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All cats</option>
                    @foreach ($categories as $cat)
                        <option class="text-gray-400" value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Фильтр по брендам -->
            <div class="flex flex-row justify-between items-center">
                <label for="brand" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by
                    Brand:</label>
                <select wire:model.live="selectedBrand" id="brand"
                        class="w-28 ml-2 p-2 text-gray-400 border border-gray-300 text-gray-50 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All brands</option>
                    @foreach ($brands as $brand)
                        <option class="text-gray-400" value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <livewire:manager-part-form/>

    <div class="flex flex-row space-x-4">
        <div x-data="{
                scrollContainer: null,
                canScrollLeft: false,
                canScrollRight: false,
                editingTabId: null,
                newTabName: '', search: '',
                tabs: [],
                transferPartsModalOpen: false,
                deletePartsModalOpen: false,
                transferAll: false,
                activeTab: @entangle('selectedWarehouseId'),
                selectedParts: @entangle('selectedParts'),
                selectedPartNames: @entangle('selectedPartNames'),
                parts: @entangle('parts'),

                init() {
                    this.scrollContainer = this.$refs.tabContainer;
                    this.checkScroll();
                    $watch('currentTab', () => search = '');
                },

                updateTabs(tabs) {
                    this.tabs = tabs;
                },

                scrollBy(amount) {
                    const scrollable = this.$refs.tabContainer;
                    scrollable.scrollBy({ left: amount, behavior: 'smooth' });
                    this.checkScroll();
                },

                scrollToEnd() {
                    const scrollable = this.$refs.tabContainer;
                    scrollable.scrollLeft = scrollable.scrollWidth - scrollable.clientWidth;
                    this.checkScroll();
                },

                scrollToStart() {
                    const scrollable = this.$refs.tabContainer;
                    scrollable.scrollLeft = 0;
                    this.checkScroll();
                },

                checkScroll() {
                    const scrollable = this.$refs.tabContainer;
                    this.canScrollLeft = scrollable.scrollLeft > 0;
                    this.canScrollRight = scrollable.scrollLeft + scrollable.clientWidth < scrollable.scrollWidth;
                },

                startEdit(tabId, tabName) {
                    this.editingTabId = tabId;
                    this.newTabName = tabName;
                },

                saveEdit(tabId, tabName) {
                    if (this.newTabName.trim() === '') return;
                    this.editingTabId = null;
                    $wire.updateWarehouseName(tabId, this.newTabName);
                },

                cancelEdit() {
                    this.editingTabId = null;
                    this.newTabName = '';
                },

                openSendModal() {
                    if (this.selectedParts.length > 0) {
                        this.transferPartsModalOpen = true;
                    }
                },

                closeModal() {
                    this.transferPartsModalOpen = false;
                },

                openDeleteModal() {
                    if (this.selectedParts.length > 0) {
                        this.fetchSelectedNames();
                        this.deletePartsModalOpen = true;
                    }
                },

                closeDeleteModal() {
                            this.deletePartsModalOpen = false;
                },

                async fetchSelectedNames() {
                    if (this.selectedParts.length) {
                        this.selectedPartNames = await $wire.call('getSelectedPartNames');
                    } else {
                        this.selectedPartNames = [];
                    }
                },

                updateActiveTab(tabId){
                    this.activeTab = tabId;
                    search = '';
                },

                searchValues: {},
                currentTab: @entangle('selectedWarehouseId'),
                get search() { return this.searchValues[this.currentTab] || ''; },
                set search(value) { this.searchValues[this.currentTab] = value; }

            }" x-init="init(); checkScroll(); tabs = '{{ $warehouses->values() }}';" @resize.window="checkScroll"
             @tabs-updated.window="(event) => { updateTabs(event.detail.tabs); }"
             class="relative w-full"
        >
            <div class="overflow-x-auto whitespace-nowrap">
                <div class="flex relative overflow-hidden">
                    <!-- Кнопка для прокрутки влево -->
                    <livewire:tabs-scroll-left/>
                    <ul class="flex flex-nowrap gap-1 no-scrollbar text-sm font-medium text-center text-gray-500
                        border-b border-gray-200 dark:border-gray-700 dark:text-gray-400"
                        x-ref="tabContainer"
                        @scroll="checkScroll"
                        style="scroll-behavior: smooth; overflow-x: hidden;">
                        @foreach ($warehouses as $warehouse)
                            <li class="shrink-0 cursor-pointer"
                                :class="{'bg-gray-900 text-gray-100': activeTab === {{ $warehouse['id'] }}, 'bg-gray-900': activeTab !== {{ $warehouse['id'] }}}"
                                wire:click="selectWarehouse({{ $warehouse['id'] }})"
                                draggable="true"
                                @dragstart="event.dataTransfer.setData('warehouseId', '{{ $warehouse['id'] }}')"
                                @drop="reorderWarehouses($event.dataTransfer.getData('warehouseId'), '{{ $warehouse['id'] }}')"
                                @dragover.prevent>

                                <div x-data="{ isEditing: false }">
                                    <!-- Режим редактирования -->
                                    <div x-show="editingTabId === {{ $warehouse['id'] }}" class="relative">
                                        <input type="text"
                                               x-model="newTabName"
                                               @keydown.enter.prevent="isEditing = false; saveEdit({{ $warehouse['id'] }}, '{{ $warehouse['name'] }}')"
                                               @keydown.escape="isEditing = false; cancelEdit();"
                                               @focus="isEditing = true"
                                               @blur="isEditing = false"
                                               class="block w-full text-start p-2 border rounded text-sm text-gray-700 dark:bg-gray-700 dark:text-gray-300 focus:outline-none focus:ring focus:ring-blue-500"
                                        />
                                        <div class="absolute top-2 right-2">
                                            <button @click="isEditing = false; saveEdit({{ $warehouse['id'] }}, '{{ $warehouse['name'] }}')"
                                                    class="bg-green-500 text-white p-1 rounded hover:bg-green-600">✓
                                            </button>
                                            <button @click="isEditing = false; cancelEdit()"
                                                    class="bg-red-500 text-white p-1 rounded hover:bg-red-600">✗
                                            </button>
                                        </div>
                                    </div>
                                    <a href="#" @click.prevent.debounce.500ms="if (!isEditing) { activeTab = {{ $warehouse['id'] }}, updateActiveTab({{ $warehouse['id'] }}) }"
                                       x-show="editingTabId !== {{ $warehouse['id'] }}"
                                       @dblclick="isEditing = true; startEdit({{ $warehouse['id'] }}, '{{ $warehouse['name'] }}')"
                                       :class="activeTab === {{ $warehouse['id'] }} ? 'text-orange-500 bg-[#b13a00] dark:bg-[#ff8144] dark:text-orange-500' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'"
                                       class="bg-gray-800 inline-block p-2 rounded-t-lg border-t border-x border-gray-700 hover:border-gray-600 border-dashed text-clip"
                                    >{{ $warehouse['name'] }}</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <!-- Кнопка для прокрутки вправо -->
                    <livewire:tabs-scroll-right/>
                </div>
            </div>

            <hr class="h-[2px] mt-0 mb-3 bg-gray-200 border-0 dark:bg-gray-800">

            <div class="bg-white dark:bg-gray-800 shadow-md rounded-md">
                @if (isset($selectedWarehouseId) && isset($parts))
                    <div class="flex pb-4 bg-white dark:bg-gray-900 gap-2">
                        <!-- Поле поиска -->
                        <label for="table-search" class="sr-only">Поиск</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="none"
                                     viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input type="text" id="table-search" x-model="search"
                                   class="block py-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-md w-80 bg-gray-50
                                focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                                dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                   placeholder="Поиск по запчастям...">
                        </div>
                        <div class="flex flex-row justify-around gap-2">
                            <!-- Кнопка открытия модального окна для передачи запчастей -->
                            <button
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50"
                                @click="openSendModal" x-show="selectedParts.length > 0"
                            >
                                Send parts
                            </button>
                            <!-- Кнопка открытия модального окна для удаления запчастей -->
                            <button
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                                @click="openDeleteModal" x-show="selectedParts.length > 0"
                            >
                                Delete parts
                            </button>
                        </div>
                        <!-- Модальное окно перемещения запчастей технику -->
                            <div x-show="transferPartsModalOpen" x-cloak
                                 x-data="{
                                    open: false,
                                    selectedTechnicians: @entangle('selectedTechnicians').defer || [],
                                    isSendButtonEnabled() {
                                        return this.selectedTechnicians &&
                                            this.selectedParts.every(partId =>
                                                this.partQuantities[partId] > 0 &&
                                                this.partQuantities[partId] <= this.partStock[partId]
                                            );
                                    },
                                }"
                                 x-init="$watch('selectedTechnicians', value => $wire.set('selectedTechnicians', value))"
                                 class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
                                 >
                                <div
                                    class="relative bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-md w-full p-6">
                                    <!-- Заголовок модального окна -->
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                            Выбранные запчасти
                                        </h3>
                                        <button @click="closeModal"
                                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                                aria-label="Close">
                                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor"
                                                 viewBox="0 0 20 20"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd"
                                                      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                      clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Содержимое модального окна -->
                                    <form wire:submit.prevent="sendParts">
                                        <div class="space-y-4">
                                            <!-- Галочка "Передать все" -->
                                            <label class="flex items-center mb-4">
                                                <input type="checkbox" x-model="transferAll" @change="setMaxQuantities()" class="mr-2">
                                                Передать все доступные запчасти
                                            </label>
                                            <template x-for="partId in selectedParts" :key="partId">
                                                <div class="mb-2 text-gray-900 dark:text-gray-400" x-init="console.log(partStock);">
                                                    <label>Запчасть #<span x-text="partId"></span>
                                                        (Доступно: <span
                                                            x-text="partStock[partId]"></span>)</label>
                                                    <input id="quantity" type="number" min="1"
                                                           :max="partStock[partId]"
                                                           class="input mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                                           placeholder="Количество"
                                                           x-model="partQuantities[partId]"
                                                           @input="limitQuantity(partId)">
                                                </div>
                                            </template>

                                            <div
                                                class="relative w-full text-gray-500"
                                            >
                                                <label for="technician"
                                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">Техник</label>

                                                <select x-model="selectedTechnicians" @change="open = false"
                                                        class="w-full py-2 px-4 text-sm text-gray-700 bg-white border-none focus:outline-none">
                                                    <option value="" selected>Выберите техника</option>
                                                    @foreach ($technicians as $technician)
                                                        <option value="{{ $technician->id }}" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                                            {{ $technician->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Кнопки действия -->
                                        <div class="flex items-center justify-end mt-6 space-x-4">
                                            <button type="button"
                                                    @click="closeModal, transferPartsModalOpen = false; document.body.classList.remove('overflow-hidden')"
                                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg dark:text-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                Отменить
                                            </button>
                                            <button type="submit"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800"
                                                    :disabled="!isSendButtonEnabled()">Подтвердить
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <!-- Модальное окно удаления запчастей -->
                        <div x-show="deletePartsModalOpen"
                             class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
                             >
                            <div
                                class="relative bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-md w-full p-6"
                                @click.away="deletePartsModalOpen = false" x-cloak>
                                <!-- Заголовок модального окна -->
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                        Запчасти для удаления
                                    </h3>
                                    <button @click="closeDeleteModal"
                                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                            aria-label="Close">
                                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor"
                                             viewBox="0 0 20 20"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                  clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Содержимое модального окна -->
                                <form wire:submit.prevent="deleteParts()">
                                    <div class="space-y-4">
                                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-40 overflow-y-auto">
                                            <template x-for="name in selectedPartNames" :key="name">
                                                <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    <span x-text="name"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

                                    <!-- Кнопки действия -->
                                    <div class="flex items-center justify-end mt-6 space-x-4">
                                        <button type="button"
                                                @click="closeDeleteModal, deletePartsModalOpen = false; document.body.classList.remove('overflow-hidden')"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg dark:text-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Отменить
                                        </button>
                                        <button type="button"
                                                @click="$wire.deleteParts().then(() => closeDeleteModal())"
                                                class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800">
                                            Подтвердить
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <h2 class="text-lg font-semibold mb-2">Запчасти
                        склада {{ $warehouses->where('id', $selectedWarehouseId)->first()?->name }}</h2>
                    <table class="w-full border" x-init="console.log(activeTab);">
                        <thead>
                            <tr class="border-b">
                                <th class="p-2">Название</th>
                                <th class="p-2">Количество</th>
                            </tr>
                        </thead>
                        <tbody>
                        <template x-for="part in parts" :key="part.id">
                            <tr class="border-b">
                                <td class="p-2" x-text="part.name"></td>
                                <td class="p-2" x-text="part.quantity"></td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">Выберите склад для отображения запчастей.</p>
                @endif
            </div>
        </div>
    </div>
</div>
