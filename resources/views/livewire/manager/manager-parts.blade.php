@php
    $isEditing = fn($id) => $editingWarehouseId === $id;
@endphp
<div class="p-2 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <!-- Индикатор загрузки -->
    <div wire:loading.flex wire:target="switchTab"
         class="absolute inset-0 flex items-center justify-center bg-gray-900 opacity-50 z-50">
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-orange-500"></div>
    </div>

    <!-- Спиннер, отображаемый при загрузке -->
    <div x-data="{ isLoading: @entangle('isLoading') }"
         x-show="isLoading"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
         style="display: none;">
        <div class="w-12 h-12 border-4 border-blue-500 border-dashed rounded-full animate-spin"></div>
    </div>

    <div class="flex flex-row space-x-4">
        <div x-data="{
                scrollContainer: null,
                canScrollLeft: false,
                canScrollRight: false,
                editingTabId: null,
                newTabName: '', search: '', selectedCategory: '', selectedBrand: '',
                tabs: [],
                transferPartsModalOpen: false,
                deletePartsModalOpen: false,
                transferAll: false,
                activeTab: @entangle('selectedWarehouseId'),
                selectedParts: @entangle('selectedParts'),
                selectedPartNames: @entangle('selectedPartNames'),
                parts: @entangle('parts'),
                categories: @entangle('categories'),
                brands: @entangle('brands'),
                partStock: {},
                partQuantities: {},
                highlightedParts: null,
                highlightedWarehouse: null,
                currentWarehouseId: null,

                init() {
                    this.scrollContainer = this.$refs.tabContainer;
                    this.checkScroll();
                    $watch('currentTab', () => search = '');

                    window.addEventListener('switch-tab', (event) => {
                        this.currentTab = event.detail.tab;
                        this.selectWarehouseTab(event.detail.warehouseId, event.detail.partIds);
                        setTimeout(() => this.highlightPart(event.detail.partIds), 1000);
                    });
                },
                selectWarehouseTab(warehouseId, partIds) {
                    if (warehouseId) {
                        $wire.selectWarehouse(warehouseId, partIds);
                    }
                },
                highlightPart(partIds, timeout) {
                    if (Array.isArray(this.highlightedParts)) {
                        this.highlightedParts.forEach(id => {
                            const prev = document.getElementById(`part-${id}`);
                            if (prev) prev.classList.remove('highlighted');
                        });
                    }
                    this.highlightedParts = Array.isArray(partIds) ? partIds : [partIds];
                    this.highlightedParts.forEach(id => {
                        const el = document.getElementById(`part-${id}`);
                        if (el) {
                            el.classList.add('highlighted');
                            if (timeout) {
                                setTimeout(() => el.classList.remove('highlighted'), timeout);
                            }
                        }
                    });
                    if (this.highlightedParts.length > 0) {
                        const first = document.getElementById(`part-${this.highlightedParts[0]}`);
                        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
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
                updateActiveWarehouse(tabId) {
                    $wire.selectWarehouse(tabId);
                },
                <!-- Метод выбора всех запчастей -->
                toggleCheckAll(event) {
                if (event.target.checked) {
                    // Выбираем все запчасти
                    this.selectedParts = this.parts.map(part => part.id);
                } else {
                    // Снимаем выделение со всех чекбоксов
                    this.selectedParts = [];
                }

                    this.selectedParts.forEach(partId => {
                        if (!this.partQuantities[partId]) {
                            this.partQuantities[partId] = 1;
                        }
                    });

                    $dispatch('update-part-quantities', { quantities: this.partQuantities });
                },

                <!-- Метод выбора через чекбоксы запчастей и обновление данных -->
                togglePartSelection(partId) {
                    if (this.selectedParts.includes(partId)) {
                        this.selectedParts = this.selectedParts.filter(id => id !== partId);
                    } else {
                        this.selectedParts.push(partId);
                    }

                    // Проверяем, активен ли 'Выбрать все'
                    this.isAllSelected = this.selectedParts.length === this.parts.length;

                    $dispatch('update-part-quantities', { quantities: this.partQuantities });
                },
                setMaxQuantities() {
                    if (this.transferAll) {
                        this.selectedParts.forEach(partId => {
                            this.partQuantities[partId] = this.partStock[partId] || 0;
                        });
                    }
                },
                limitQuantity(partId) {
                    if (this.partQuantities[partId] > this.partStock[partId]) {
                        this.partQuantities[partId] = this.partStock[partId];
                    }
                    $dispatch('update-part-quantities', { quantities: this.partQuantities });
                },
                filteredParts() {
                    return $wire.parts.filter(part =>
                        (this.selectedCategory === '' || (part.category_id == this.selectedCategory)) &&
                        (this.selectedBrand === '' || (
                            part.nomenclatures &&
                            Array.isArray(part.nomenclatures.brands) &&
                            part.nomenclatures.brands.some(brand => brand.id == this.selectedBrand)
                        )) &&
                        (
                            part.name?.toLowerCase().includes(this.search.toLowerCase()) ||
                            part.sku?.toLowerCase().includes(this.search.toLowerCase()) ||
                            (part.pns && part.pns.toLowerCase().includes(this.search.toLowerCase())) ||
                            (part.category?.name && part.category.name.toLowerCase().includes(this.search.toLowerCase())) ||
                            (
                                part.nomenclatures &&
                                Array.isArray(part.nomenclatures.brands) &&
                                part.nomenclatures.brands.some(brand =>
                                    brand.name?.toLowerCase().includes(this.search.toLowerCase())
                                )
                            )
                        )
                    );
                },

            }"
            x-init="init(); checkScroll(); tabs = '{{ $warehouses->values() }}';
                partStock = {{ collect($parts)->pluck('quantity', 'id')->toJson() }};
                $watch('selectedParts', () => fetchSelectedNames());
            "
             @resize.window="checkScroll"
             @tabs-updated.window="(event) => { updateTabs(event.detail.tabs); }"
             class="w-full"
        >
            <!-- Заголовок страницы и фильтры -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
                <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Parts</h1>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <!-- Фильтр по категориям -->
                    <div class="flex flex-row justify-between items-center">
                        <label for="category" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Cat:</label>
                        <select x-model="selectedCategory" id="category"
                                class="w-28 ml-2 p-2 text-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All cats</option>
                            <template x-for="category in categories" :key="category.id">
                                <option :value="category.id" x-text="category.name" class="text-gray-400"></option>
                            </template>
                        </select>
                    </div>
                    <!-- Фильтр по брендам -->
                    <div class="flex flex-row justify-between items-center">
                        <label for="brand" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by
                            Brand:</label>
                        <select x-model="selectedBrand" id="brand"
                                class="w-28 ml-2 p-2 text-gray-400 border border-gray-300 text-gray-50 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All brands</option>
                            <template x-for="brand in brands" :key="brand.id">
                                <option :value="brand.id" x-text="brand.name" class="text-gray-400"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <livewire:manager-part-form/>

            <div class="overflow-x-auto whitespace-nowrap">
                <div class="flex overflow-hidden">
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
                                :class="activeTab === {{ $warehouse['id'] }} ? 'text-orange-500 bg-[#b13a00] dark:bg-green-500 dark:text-white' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'"
                                wire:click="selectWarehouse({{ $warehouse['id'] }})" @click="selectedParts = []"
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
                                            <button
                                                @click="isEditing = false; saveEdit({{ $warehouse['id'] }}, '{{ $warehouse['name'] }}')"
                                                class="bg-green-500 text-white p-1 rounded hover:bg-green-600">✓
                                            </button>
                                            <button @click="isEditing = false; cancelEdit()"
                                                    class="bg-red-500 text-white p-1 rounded hover:bg-red-600">✗
                                            </button>
                                        </div>
                                    </div>
                                    <a href="#"
                                       @click.prevent.debounce.500ms="if (!isEditing) { activeTab = {{ $warehouse['id'] }}, updateActiveTab({{ $warehouse['id'] }}) }"
                                       x-show="editingTabId !== {{ $warehouse['id'] }}"
                                       @dblclick="isEditing = true; startEdit({{ $warehouse['id'] }}, '{{ $warehouse['name'] }}')"
                                       :class="activeTab === {{ $warehouse['id'] }} ? 'text-orange-500 bg-[#b13a00] dark:bg-green-500 dark:text-white' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'"
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
                            <input type="text" id="table-search" x-model.debounce.500ms="search"
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
                                    selectedTechnician: @entangle('selectedTechnician'),
                                    selectedTechnicians: @entangle('selectedTechnicians') || [],
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
                                <div >
                                    <div class="space-y-4">
                                        <!-- Галочка "Передать все" -->
                                        <label class="flex items-center mb-4">
                                            <input type="checkbox" x-model="transferAll" @change="setMaxQuantities()"
                                                   class="mr-2">
                                            Передать все доступные запчасти
                                        </label>
                                        <template x-for="partId in selectedParts" :key="partId">
                                            <div class="mb-2 text-gray-900 dark:text-gray-400">
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

                                            <select x-model="selectedTechnicians" multiple size="3" @change="open = false;"
                                                    class="w-full py-2 px-4 text-sm text-gray-700 bg-white border-none focus:outline-none">
                                                <option value="" disabled selected>Выберите техников</option>
                                                @foreach ($technicians as $technician)
                                                    <option value="{{ $technician->id }}"
                                                            class="px-4 py-2 cursor-pointer hover:bg-gray-100">
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
                                        <button type="button" wire:click="sendParts"
                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800"
                                                :disabled="!isSendButtonEnabled()">Подтвердить
                                        </button>
                                    </div>
                                </div>
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
                                            <template x-for="(name, index) in selectedPartNames" :key="index">
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
                    <h2 class="text-gray-700 dark:text-gray-400 uppercase text-lg font-semibold p-3">Запчасти
                        склада <span class="dark:text-orange-500">{{ $warehouses->where('id', $selectedWarehouseId)->first()?->name }}</span></h2>
                    <div id="parts-table"
                         class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 relative">
                        <!-- Заголовок таблицы -->
                        <div
                            class="hidden md:flex flex-row text-xs font-bold text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 p-1">
                            <!-- Общий Чекбокс -->
                            <div class="flex items-center justify-center px-4 py-2">
                                <input type="checkbox" @click="toggleCheckAll($event)"
                                       :checked="selectedParts.length > 0 && selectedParts.length === parts.length"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500
                                       dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800
                                       focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="checkbox-all-search" class="sr-only">checkbox</label>
                            </div>

                            <!-- SKU -->
                            <div class="w-[100px] px-4 py-2">SKU</div>

                            <!-- Наименование -->
                            <div class="flex-1 px-4 py-2">Наименование</div>

                            <!-- Quantity -->
                            <div class="flex-1 px-4 py-2">Quantity</div>

                            <!-- Price -->
                            <div class="flex-1 px-4 py-2">Price</div>

                            <!-- Total -->
                            <div class="flex-1 px-4 py-2">Total</div>

                            <!-- Изображение -->
                            <div class="w-[150px] px-4 py-2">Изображение</div>

                            <!-- URL (шире остальных) -->
                            <div class="flex-1 flex px-4 py-2 items-center">
                                <span>URL</span>
                                <div x-data="{ showTooltip: false }" @click="showTooltip = !showTooltip"
                                     @click.away="showTooltip = false"
                                     class="relative ml-2 w-5 h-5 bg-blue-500 text-white text-xs font-bold lowercase rounded-full cursor-pointer flex items-center justify-center">
                                    i
                                    <!-- Поповер -->
                                    <div x-show="showTooltip" x-transition
                                         class="absolute z-50 top-full mt-1 w-max px-2 py-1 text-xs bg-blue-500 lowercase text-white rounded shadow-lg">
                                        2-click for edit
                                    </div>
                                </div>
                            </div>

                            <div class="w-[150px] px-4 py-2">Категория</div>

                            <div class="w-[150px] px-4 py-2">Брэнд</div>
                        </div>
                        <div class="flex flex-col space-y-2 md:space-y-0 dark:bg-gray-900">
                            <template x-for="part in filteredParts()" :key="part.id">
                                <template x-if="part.nomenclatures?.is_archived == false">
                                    <div class="flex flex-col md:flex-row w-full md:items-center bg-white border
                                    dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-600 dark:hover:bg-[#162033] p-1 relative" :id="`part-${part.id}`">
                                        <!-- Checkbox -->
                                        <div class="block sm:hidden absolute top-5 right-5 mb-2" wire:ignore>
                                            <input type="checkbox" :value="part.id"
                                                   @click="togglePartSelection(part.id)"
                                                   :checked="selectedParts.includes(part.id)"
                                                   class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                            <label for="checkbox-table-search-part.id"
                                                   class="sr-only">checkbox</label>
                                        </div>
                                        <div
                                            class="w-[48px] flex items-center justify-center hidden sm:block mb-0 px-4 py-2"
                                            wire:ignore>
                                            <input type="checkbox" :value="part.id"
                                                   @click="togglePartSelection(part.id)"
                                                   :checked="selectedParts.includes(part.id)"
                                                   class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300
                                                   rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800
                                                   dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                            <label for="checkbox-table-search-part.id"
                                                   class="sr-only">checkbox</label>
                                        </div>
                                        <!-- SKU -->
                                        <div class="w-[100px] px-4 py-2 mb-2 md:mb-0">
                                            <span class="md:hidden font-semibold">SKU:</span>
                                            <span x-text="part.sku"></span>
                                        </div>
                                        <!-- Name -->
                                        <div x-data="{
                                            showEditMenu: false, editingName: false,
                                            newName: part.name, originalName: part.name,
                                            errorMessage: '',
                                            showPnPopover: false, deletePn: false, showingPn: false,
                                            searchPn: '', newPn: '', addingPn: false,
                                            //availablePns: Object.keys(@entangle('availablePns') || {}).length ? @entangle('availablePns') : {},
                                            //selectedPns: @entangle('selectedPns'),
                                        }"
                                             @pn-added.window="addingPn = false; newPn = ''; errorMessage = ''"
                                             class="flex-1 flex flex-row px-4 py-2 md:mb-0 cursor-pointer relative z-10"
                                        >
                                            <!-- PN -->
                                            <span class="flex items-center md:hidden font-semibold">Name:</span>

                                            <!-- Название с подменю -->
                                            <div class="flex items-center w-full">
                                                <!-- Оверлей -->
                                                <div x-show="editingName || deletePn || addingPn"
                                                     class="flex fixed inset-0 bg-black opacity-50 z-30"
                                                     @click="editingName = false, deletePn = false, addingPn = false;"
                                                     x-cloak>
                                                </div>

                                                <!-- Основное отображение -->
                                                <span x-show="!editingName" @click="editingName = true"
                                                      class="flex z-35 items-center cursor-pointer hover:underline min-h-[30px]">
                                                <span x-text="part.name"></span>
                                            </span>
                                            </div>
                                            <!-- Режим редактирования Name -->
                                            <div x-show="editingName"
                                                 class="flex justify-start items-center w-full absolute top-0 z-40"
                                                 x-cloak>
                                                <input type="text" x-model="newName"
                                                       class="border border-gray-300 rounded-md text-sm px-2 py-1 w-[180px] mr-2"
                                                       @keydown.enter="if (newName !== originalName) { $wire.updateName(part.id, newName); originalName = newName; } editingName = false;"
                                                       @keydown.escape="editingName = false">
                                                <button
                                                    @click="if (newName !== originalName) { $wire.updateName(part.id, newName); originalName = newName; } editingName = false;"
                                                    class="bg-green-500 text-white px-2 py-1 rounded-full w-[28px]">
                                                    ✓
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="flex-1 px-4 py-2 md:mb-0"
                                             @part-updated="event => {
                                                         if (event.detail.partId === part.id) {
                                                            $el.textContent = event.detail.newQuantity;
                                                         }
                                                     }"
                                        >
                                            <span class="md:hidden font-semibold">Quantity:</span>
                                            <span x-text="part.quantity"></span>
                                        </div>

                                        <!-- Price -->
                                        <div
                                            class="flex flex-row flex-1 px-4 py-2 md:mb-0 cursor-pointer relative parent-container z-10"
                                            x-data="{ showPopover: false, editing: false, newPrice: '', popoverX: 0, popoverY: 0 }">

                                            <!-- Кликабельная ссылка с ценой запчасти -->
                                            <span class="md:hidden font-semibold">Price:</span>
                                            <a id="price-item-part.id"
                                                @click="
                                                $nextTick(() => {
                                                    editing = false; // Сбрасываем редактирование при открытии
                                                    newPrice = part.price; // Устанавливаем текущее значение
                                                    const parent = $el.closest('.parent-container');
                                                    const elementOffsetLeft = $el.offsetLeft;
                                                    const elementOffsetTop = $el.offsetTop;

                                                    popoverX = elementOffsetLeft / parent.offsetWidth;
                                                    popoverY = elementOffsetTop / parent.offsetHeight;

                                                    showPopover = true;
                                                });
                                                "
                                               class="cursor-pointer text-sm text-blue-600 hover:underline dark:text-blue-400">
                                                $<span x-text="part.price"></span>
                                            </a>

                                            <!-- Поповер с динамическим позиционированием -->
                                            <div x-show="showPopover" x-transition role="tooltip"
                                                 class="absolute z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg w-56 p-1"
                                                 :style="'top: ' + popoverY + 'px; left: ' + popoverX + 'px;'"
                                                 @click.away="showPopover = false">

                                                <!-- Оверлей -->
                                                <div x-show="editing"
                                                     class="flex fixed inset-0 bg-black opacity-50 z-30"
                                                     @click="editing = false"
                                                     x-cloak>
                                                </div>

                                                <div class="flex flex-row w-full">
                                                    <!-- Кнопка Edit -->
                                                    <button x-show="!editing"
                                                            @click.prevent="editing = true; $nextTick(() => { $refs.priceInput.focus() })"
                                                            class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                        Edit
                                                    </button>

                                                    <!-- Поле ввода новой цены и кнопка подтверждения -->
                                                    <div x-show="editing"
                                                         class="flex justify-center items-center z-40"
                                                         x-transition>
                                                        <input type="number" x-ref="priceInput"
                                                               x-model="newPrice"
                                                               class="border border-gray-300 rounded-md text-sm px-2 py-1 w-[180px] mr-2 focus:outline-none focus:outline-offset-[0px] focus:outline-violet-900"
                                                               placeholder="part.price">
                                                        <button @click="
                                                            if (newPrice !== 'part.price') {
                                                                $wire.set('newPrice', newPrice)
                                                                    .then(() => {
                                                                        $wire.updatePartPrice(part.id, newPrice);
                                                                    });
                                                            }
                                                            showPopover = false;
                                                            editing = false;
                                                        "
                                                                class="bg-green-500 text-white px-2 py-1 rounded-full w-[28px]">
                                                            ✓
                                                        </button>
                                                    </div>

                                                    <!-- Кнопка для открытия истории цен -->
                                                    <button x-show="!editing"
                                                            @click="$dispatch('open-price-modal', { partId: part.id })"
                                                            class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                        History
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total -->
                                        <div class="flex-1 px-4 py-2 md:mb-0">
                                            <span class="md:hidden font-semibold">Total:</span>
                                            $<span x-text="part.total"></span>
                                        </div>

                                        <!-- PartImage -->
                                        <div
                                            class="flex flex-row w-[150px] h-[80px] justify-start space-x-3 px-4 py-2 md:mb-0">
                                            <!-- Миниатюра -->
                                            <span class="md:hidden font-semibold">Image:</span>
                                            <div x-data="{
                                                partName: '',
                                                partImage: part.image,
                                                nomenclatureImage:part.nomenclatures.image,
                                                showTooltip: false,
                                                isUploading: false,
                                                isLoading: false,
                                                uploadProgress: 0,
                                                selectWarehouse(id){
                                                    $wire.selectWarehouse(id);
                                                },
                                                refreshImage(imageUrl) {
                                                    this.isLoading = true;
                                                    this.partImage = imageUrl;
                                                    setTimeout(() => this.isLoading = false, 500);
                                                },
                                            }"
                                                 @image-updated.window="(event) => {
                                                    const imageUrl = event.detail[0].imageUrl;
                                                    refreshImage(imageUrl);
                                                 }"
                                                 class="flex gallery relative"
                                            >
                                                <div class="flex flex-row">
                                                    <!-- Индикатор загрузки -->
                                                    <div x-show="isLoading"
                                                         class="absolute inset-0 bg-black opacity-50 flex items-center justify-center z-10">
                                                        <div class="w-12 h-12 border-4 border-blue-500 border-dashed rounded-full animate-spin"></div>
                                                    </div>
                                                    <!-- Если есть изображение запчасти, оно в приоритете -->
                                                    <template x-if="partImage">
                                                        <img :src="'{{ asset('storage') }}' + partImage"
                                                             :alt="part.name"
                                                             @click="Livewire.dispatch('lightbox', '{{ asset('storage') }}' + partImage)"
                                                             class="object-contain rounded cursor-zoom-in">
                                                    </template>

                                                    <!-- Если нет изображения запчасти, но есть изображение номенклатуры -->
                                                    <template x-if="!partImage && nomenclatureImage">
                                                        <img :src="'{{ asset('storage') }}' + nomenclatureImage"
                                                             :alt="part.name"
                                                             @click="Livewire.dispatch('lightbox', '{{ asset('storage') }}' + nomenclatureImage)"
                                                             class="object-contain rounded cursor-zoom-in">
                                                    </template>

                                                    <!-- Если нет ни изображения запчасти, ни изображения номенклатуры -->
                                                    <template x-if="!partImage && !nomenclatureImage">
                                                    <span class="w-[50px] h-[50px]">
                                                        <div>
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                 xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                 version="1.1" width="56"
                                                                 height="56" viewBox="0 0 256 256"
                                                                 xml:space="preserve">
                                                                             <defs></defs>
                                                                <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;"
                                                                   transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                                                                    <path
                                                                        d="M 89 20.938 c -0.553 0 -1 0.448 -1 1 v 46.125 c 0 2.422 -1.135 4.581 -2.898 5.983 L 62.328 50.71 c -0.37 -0.379 -0.973 -0.404 -1.372 -0.057 L 45.058 64.479 l -2.862 -2.942 c -0.385 -0.396 -1.019 -0.405 -1.414 -0.02 c -0.396 0.385 -0.405 1.019 -0.02 1.414 l 3.521 3.62 c 0.37 0.38 0.972 0.405 1.373 0.058 l 15.899 -13.826 l 21.783 22.32 c -0.918 0.391 -1.928 0.608 -2.987 0.608 H 24.7 c -0.552 0 -1 0.447 -1 1 s 0.448 1 1 1 h 55.651 c 5.32 0 9.648 -4.328 9.648 -9.647 V 21.938 C 90 21.386 89.553 20.938 89 20.938 z"
                                                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                                                        transform=" matrix(1 0 0 1 0 0) "
                                                                        stroke-linecap="round"/>
                                                                    <path
                                                                        d="M 89.744 4.864 c -0.369 -0.411 -1.002 -0.444 -1.412 -0.077 l -8.363 7.502 H 9.648 C 4.328 12.29 0 16.618 0 21.938 v 46.125 c 0 4.528 3.141 8.328 7.356 9.361 l -7.024 6.3 c -0.411 0.368 -0.445 1.001 -0.077 1.412 c 0.198 0.22 0.471 0.332 0.745 0.332 c 0.238 0 0.476 -0.084 0.667 -0.256 l 88 -78.935 C 90.079 5.908 90.113 5.275 89.744 4.864 z M 9.648 14.29 h 68.091 L 34.215 53.33 L 23.428 42.239 c -0.374 -0.385 -0.985 -0.404 -1.385 -0.046 L 2 60.201 V 21.938 C 2 17.721 5.431 14.29 9.648 14.29 z M 2 68.063 v -5.172 l 20.665 -18.568 l 10.061 10.345 L 9.286 75.692 C 5.238 75.501 2 72.157 2 68.063 z"
                                                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                                                        transform=" matrix(1 0 0 1 0 0) "
                                                                        stroke-linecap="round"/>
                                                                    <path
                                                                        d="M 32.607 35.608 c -4.044 0 -7.335 -3.291 -7.335 -7.335 s 3.291 -7.335 7.335 -7.335 s 7.335 3.291 7.335 7.335 S 36.652 35.608 32.607 35.608 z M 32.607 22.938 c -2.942 0 -5.335 2.393 -5.335 5.335 s 2.393 5.335 5.335 5.335 s 5.335 -2.393 5.335 -5.335 S 35.549 22.938 32.607 22.938 z"
                                                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                                                        transform=" matrix(1 0 0 1 0 0) "
                                                                        stroke-linecap="round"/>
                                                                </g>
                                                            </svg>
                                                        </div>
                                                    </span>
                                                    </template>
                                                </div>
                                                <!-- Tooltip и кнопка загрузки -->
                                                <div x-data="{ showTooltip: false }" @mouseenter="showTooltip = true"
                                                     @mouseleave="showTooltip = false">
                                                    <div x-show="showTooltip" x-transition
                                                         class="absolute z-50 -top-6 left-6 w-max px-2 py-1 text-xs bg-green-500 text-white rounded shadow-lg">
                                                        Change Image
                                                    </div>
                                                    <button @click="$wire.openImageModal(part.id)"
                                                            class="text-white rounded-full p-1 cursor-pointer h-[20px]">
                                                        <div>
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                class="h-4 w-4"
                                                                version="1.1" width="256"
                                                                height="256"
                                                                viewBox="0 0 256 256"
                                                                xml:space="preserve">
                                                            <defs></defs>
                                                                <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;"
                                                                   transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                                                                    <circle cx="45" cy="45"
                                                                            r="45"
                                                                            style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(75,174,79); fill-rule: nonzero; opacity: 1;"
                                                                            transform="  matrix(1 0 0 1 0 0) "/>
                                                                    <path
                                                                        d="M 33.255 35.073 L 43 25.329 V 58.83 c 0 1.104 0.896 2 2 2 s 2 -0.896 2 -2 V 25.329 l 9.744 9.744 c 0.391 0.391 0.902 0.586 1.414 0.586 s 1.023 -0.195 1.414 -0.586 c 0.781 -0.781 0.781 -2.047 0 -2.828 L 46.415 19.087 c -0.092 -0.093 -0.194 -0.176 -0.303 -0.249 c -0.027 -0.018 -0.057 -0.029 -0.084 -0.046 c -0.084 -0.051 -0.168 -0.1 -0.259 -0.138 c -0.038 -0.016 -0.079 -0.023 -0.118 -0.037 c -0.084 -0.029 -0.166 -0.06 -0.255 -0.077 C 45.266 18.514 45.134 18.5 45 18.5 s -0.266 0.014 -0.395 0.04 c -0.088 0.018 -0.171 0.049 -0.255 0.077 c -0.039 0.014 -0.08 0.021 -0.118 0.037 c -0.091 0.038 -0.176 0.088 -0.259 0.138 c -0.027 0.016 -0.058 0.028 -0.084 0.046 c -0.109 0.073 -0.211 0.156 -0.303 0.249 L 30.427 32.245 c -0.781 0.781 -0.781 2.047 0 2.828 C 31.208 35.854 32.475 35.854 33.255 35.073 z"
                                                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;"
                                                                        transform=" matrix(1 0 0 1 0 0) "
                                                                        stroke-linecap="round"/>
                                                                    <path
                                                                        d="M 58.158 67.5 H 31.841 c -1.104 0 -2 0.896 -2 2 s 0.896 2 2 2 h 26.317 c 1.104 0 2 -0.896 2 -2 S 59.263 67.5 58.158 67.5 z"
                                                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;"
                                                                        transform=" matrix(1 0 0 1 0 0) "
                                                                        stroke-linecap="round"/>
                                                                </g>
                                                    </svg>
                                                        </div>
                                                    </button>
                                                </div>

                                                <div x-data="{ isUploading: false, uploadProgress: 0 }"
                                                     x-on:livewire-upload-start="isUploading = true"
                                                     x-on:livewire-upload-finish="isUploading = false; uploadProgress = 0"
                                                     x-on:livewire-upload-error="isUploading = false; uploadProgress = 0"
                                                     x-on:livewire-upload-progress="uploadProgress = $event.detail.progress">
                                                    <div x-data="{ showImageModal: @entangle('showImageModal') }">
                                                        <!-- Modal Backdrop -->
                                                        <div x-show="showImageModal"
                                                             class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40"
                                                             x-transition.opacity x-cloak></div>

                                                        <!-- Modal Content -->
                                                        <div x-show="showImageModal"
                                                             class="fixed inset-0 flex items-center justify-center z-50 p-4">
                                                            <div
                                                                class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full">
                                                                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                                                    Upload Image</h3>

                                                                <!-- File Input -->
                                                                <div class="mb-4">
                                                                    <input type="file" wire:model="newImage"
                                                                           class="block w-full text-gray-800 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                                                                    @error('newImage') <span
                                                                        class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                                </div>

                                                                <!-- Прогресс загрузки -->
                                                                <div x-show="isUploading" class="mt-4">
                                                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2 dark:bg-gray-700">
                                                                        <div :style="`width: ${uploadProgress}%`"
                                                                             class="bg-blue-500 h-2.5 rounded-full"></div>
                                                                    </div>
                                                                    <div class="text-white text-center">Uploading... (<span x-text="uploadProgress"></span>%)</div>
                                                                </div>

                                                                <!-- Action Buttons -->
                                                                <div class="flex justify-end space-x-4">
                                                                    <button type="button"
                                                                            @click="showImageModal = false; $wire.closeImageModal();"
                                                                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                                                        Cancel
                                                                    </button>
                                                                    <button type="button"
                                                                            wire:click="uploadImage(part.id)"
                                                                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                                                        Upload
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- URL -->
                                        <div
                                            class="flex-1 px-4 py-2 md:mb-0 cursor-pointer font-semibold parent-container z-10 relative overflow-y-visible"
                                            x-data="{
                                                partId: part.id,
                                                isModalOpen: false,
                                                urlData: JSON.parse(part.url),
                                                clickCount: 0,
                                                showPopover: false,
                                                popoverX: 0,
                                                popoverY: 0
                                            }"
                                            x-init="
                                                window.addEventListener('open-url-modal', event => {
                                                        partId = event.detail.partId;
                                                        isModalOpen = true;

                                                        $wire.call('getUrlData', partId).then(data => {
                                                            managerUrlText = data.text;
                                                            managerUrl = data.url;
                                                        });
                                                    });
                                                Livewire.on('urlUpdated', updatedPartId => {
                                                    if (updatedPartId === partId) {
                                                        $wire.call('getUrlData', partId).then(data => {
                                                            urlData = data;
                                                        });
                                                    }
                                                });
                                            "
                                        >
                                            <!-- Оверлей -->
                                            <div x-show="showPopover"
                                                 class="flex fixed inset-0 bg-black opacity-50 z-30"
                                                 @click="showPopover = false"
                                                 x-cloak>
                                            </div>
                                            <template x-if="urlData?.text || urlData?.url">
                                                <div>
                                                    <span class="md:hidden font-semibold">URL:</span>
                                                    <a :href="urlData?.url"
                                                       x-text="urlData.text || urlData.url"
                                                       class="text-blue-500 underline cursor-pointer"
                                                       :data-part-id="partId"
                                                       @click.prevent="
                                                        $nextTick(() => {
                                                            const parent = $el.closest('.parent-container');
                                                            const elementOffsetLeft = $el.offsetLeft;
                                                            const elementOffsetTop = $el.offsetTop;

                                                            popoverX = elementOffsetLeft / parent.offsetWidth;
                                                            popoverY = elementOffsetTop / parent.offsetHeight;

                                                            showPopover = true;
                                                        });
                                                       ">
                                                    </a>

                                                    <!-- Поповер с кнопками -->
                                                    <div x-show="showPopover" x-transition role="tooltip"
                                                         class="absolute z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg w-56 p-1 border border-gray-600"
                                                         :style="'top: ' + popoverY + 'px; left: ' + popoverX + 'px;'"
                                                         @click.self="showPopover = false">

                                                        <div class="flex flex-row w-full">
                                                            <!-- Кнопка Редактировать -->
                                                            <button @click.prevent="
                                                                    const targetElement = $el.closest('.parent-container').querySelector(`[data-part-id='${partId}']`);
                                                                    if (targetElement) {
                                                                        const rect = targetElement.getBoundingClientRect();
                                                                        $dispatch('open-url-modal', {
                                                                            partId: partId,
                                                                            modalX: rect.left + window.scrollX,
                                                                            modalY: rect.top + window.scrollY + rect.height
                                                                        });
                                                                    }
                                                                    showPopover = false;
                                                                "
                                                                    class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                                Редактировать
                                                            </button>

                                                            <!-- Кнопка Открыть URL -->
                                                            <button @click.prevent="if (urlData?.url) window.open(urlData.url, '_blank'); showPopover = false"
                                                                    class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                                Открыть URL
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <template x-if="!urlData?.text && !urlData?.url">
                                                <span class="text-gray-500 cursor-pointer"
                                                      @click.prevent="
                                                          showPopover = true;
                                                      "
                                                      title="Редактировать URL">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M15.232 5.232l3.536 3.536M9 13h.01M6 9l5 5-3 3h6l-1.293-1.293a1 1 0
                                                                010-1.414l7.42-7.42a2.828 2.828 0 10-4-4l-7.42 7.42a1 1 0 01-1.414 0L6 9z"/>
                                                    </svg>
                                                </span>
                                            </template>
                                            <!-- Модальное окно для редактирования -->
                                            <div
                                                x-data="{
                                                    managerSupplier: @entangle('managerSupplier'),
                                                    managerUrl: @entangle('managerUrl'),
                                                    managerUrlText: @entangle('managerUrlText'),
                                                    suppliers: @entangle('suppliers'),
                                                    modalX: 0,
                                                    modalY: 0,
                                                    updateUrl() {
                                                        $wire.updatePartURL(this.partId, this.managerSupplier, this.managerUrl);
                                                        isModalOpen = false;
                                                    },
                                                    adjustPosition() {
                                                        const modalWidth = 400;
                                                        const modalHeight = 300;
                                                        const viewportWidth = window.innerWidth;
                                                        const viewportHeight = window.innerHeight;

                                                        // Если окно выходит за правый край
                                                        if (this.modalX + modalWidth > viewportWidth) {
                                                            this.modalX = viewportWidth - modalWidth - 100; // Отступ в 100px
                                                        }

                                                        // Если окно выходит за нижний край
                                                        if (this.modalY + modalHeight > viewportHeight) {
                                                            this.modalY = viewportHeight - modalHeight - (modalHeight/2); // Отступ в половину окна
                                                        }
                                                    },
                                                }"
                                                x-init="
                                                    Livewire.on('urlUpdated', updatedPartId => {
                                                        if (updatedPartId === partId) {
                                                            $wire.call('getUrlData', updatedPartId).then(data => {
                                                                managerUrlText = data.text;
                                                                managerUrl = data.url;
                                                            });
                                                        }
                                                    });
                                                    window.addEventListener('open-url-modal', event => {
                                                        partId = event.detail.partId;
                                                        modalX = event.detail.modalX;
                                                        modalY = event.detail.modalY;

                                                        isModalOpen = true;
                                                        adjustPosition();

                                                        $wire.call('getUrlData', partId).then(data => {
                                                            managerUrlText = data.text;
                                                            managerUrl = data.url;
                                                        });
                                                    });

                                                "
                                                x-show="isModalOpen"
                                                x-cloak
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 scale-90"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-90"
                                                class="fixed z-[9999]"
                                                :style="'top: ' + modalY + 'px; left: ' + modalX + 'px;'"
                                            >
                                                <!-- Оверлей -->
                                                <div class="flex fixed inset-0 bg-black opacity-50 z-[-1]"
                                                     @click="isModalOpen = false;"></div>
                                                <div
                                                    class="bg-white p-2 rounded-lg shadow-md w-[400px] max-h-full z-[9999]"
                                                >
                                                    <div class="flex items-center justify-between mb-2">
                                                        <h2 class="text-lg font-semibold text-gray-900">Редактировать ссылку</h2>
                                                        <button @click="isModalOpen = false;"
                                                                class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                                 fill="none"
                                                                 viewBox="0 0 24 24"
                                                                 stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div class="flex flex-row w-full mb-1 gap-1">
                                                        <div class="flex-1">
                                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="selectedSupplier">Supplier:</label>
                                                            <select x-model="managerSupplier" id="selectedSupplier"
                                                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                                <option value="">Select Supplier</option>
                                                                <template x-for="supplier in suppliers" :key="supplier.id">
                                                                    <option :value="supplier.name" x-text="supplier.name"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div class="flex-1">
                                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="managerUrlText">Text:</label>
                                                            <input x-model="managerUrlText" type="text" id="managerUrlText"
                                                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                   placeholder="Enter Supplier Name">
                                                        </div>
                                                    </div>

                                                    <div class="mb-1">
                                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="managerUrl">URL:</label>
                                                        <input x-model="managerUrl" type="text" id="managerUrl"
                                                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                               placeholder="Enter URL">
                                                    </div>

                                                    <div class="flex justify-end space-x-2">
                                                        <button @click="isModalOpen = false;"
                                                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                                            Отмена
                                                        </button>
                                                        <button @click="updateUrl()"
                                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                                            OK
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>
                            <div x-show="filteredParts().length === 0" x-transition class="text-gray-400 text-center mt-4">
                                Ничего не нашлось
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500">Выберите склад для отображения запчастей.</p>
                @endif
            </div>
        </div>
    </div>
</div>
