<div class="p-2 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">

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
        <div class="relative w-full"
             x-data="{
                    warehouses: @js($warehouses->values()->toArray()),
                    partStock: @js(collect($nomenclatures)->pluck('quantity', 'id')->toArray()),
                    partQuantities: {},
                    parts: [],
                    tabs: [],
                    activeTab: @entangle('activeTab'),
                    init() {
                        const defaultTab = this.warehouses.find(tab => tab.is_default === 1);
                        activeTab = defaultTab ? defaultTab.id : (this.warehouses[0]?.id || null);

                        // Инициализируем объект с запчастями
                        this.parts = this.warehouses.reduce((acc, tab) => {
                            acc[tab.id] = tab.parts || [];
                            return acc;
                        }, {});
                    },
                    updateTabs(tabs) {
                        this.tabs = tabs;
                    },
                    editingTabId: null,
                    newTabName: '',
                    canScrollLeft: false,
                    canScrollRight: false,
                    draggingIndex: null,
                    checkScroll() {
                        const scrollable = this.$refs.tabContainer;
                        this.canScrollLeft = scrollable.scrollLeft > 0;
                        this.canScrollRight = scrollable.scrollLeft + scrollable.clientWidth < scrollable.scrollWidth;
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
                    startDrag(index) {
                        this.draggingIndex = index;
                    },
                    dragOver(index) {
                        if (this.draggingIndex === null || this.draggingIndex === index) return;
                        const draggedTab = this.tabs[this.draggingIndex];
                        this.tabs.splice(this.draggingIndex, 1);
                        this.tabs.splice(index, 0, draggedTab);
                        this.draggingIndex = index;
                    },
                    endDrag() {
                        this.draggingIndex = null;

                        // Формируем массив с актуальными порядковыми номерами
                        const updatedTabs = this.tabs.map((tab, index) => {
                            return { id: tab.id, position: index };
                        });

                        // Отправляем массив с обновленными позициями на сервер
                        // Возможно, нужно передавать не только порядок табов, но и данные о запчастях
                        $wire.updateTabOrder(updatedTabs);
                    },
                    startEdit(tab) {
                        this.editingTabId = tab.id;
                        this.newTabName = tab.name;
                    },
                    saveEdit(tab) {
                        if (this.newTabName.trim() === '') return;
                        tab.name = this.newTabName;
                        this.editingTabId = null;

                        // Сохранение нового имени на сервере
                        $wire.renameWarehouse(tab.id, this.newTabName);
                    },
                    cancelEdit() {
                        this.editingTabId = null;
                        this.newTabName = '';
                    },
                    limitQuantity(partId) {
                        if (this.partQuantities[partId] > this.partStock[partId]) {
                            this.partQuantities[partId] = this.partStock[partId];
                        }
                        $dispatch('update-part-quantities', { quantities: this.partQuantities });
                    },
                    transferPartsModalOpen: false,
                        deletePartsModalOpen: false,
                        selectedPartNames: @entangle('selectedPartNames'),
                        async fetchSelectedNames() {
                            if (this.selectedParts.length) {
                                this.selectedPartNames = await $wire.call('getSelectedPartNames');
                            } else {
                                this.selectedPartNames = [];
                            }
                        },
                        openSendModal() {
                            if (this.selectedParts.length > 0) {
                                this.transferPartsModalOpen = true;
                                console.log(this.transferPartsModalOpen);
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
             }"
             x-init="init(); checkScroll(); tabs = warehouses;"
             @resize.window="checkScroll"
             @tabs-updated.window="(event) => { this.updateTabs(event.detail.tabs); }"

        >

            <div class="relative overflow-hidden">
                <!-- Кнопка для прокрутки влево -->
                <button @click="scrollBy(-100)" @dblclick="scrollToStart()"
                        class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-100 dark:bg-gray-700 p-2 rounded-full shadow z-10"
                        x-show="canScrollLeft"
                        x-transition
                >
                    &larr;
                </button>

                <!-- Таб заголовки -->
                <ul id="draggable-tabs"
                    class="flex flex-nowrap gap-1 no-scrollbar text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400"
                    x-ref="tabContainer"
                    @scroll="checkScroll"
                    style="scroll-behavior: smooth; overflow-x: hidden;"
                >

                    <!-- Таб # -->
                    <template x-for="warehouse in warehouses" :key="warehouse.id">
                        <li
                            @click="activeTab = warehouse.id"
                            :class="{'bg-gray-900 text-white': activeTab === warehouse.id, 'bg-gray-900': activeTab !== warehouse.id}"
                            class="shrink-0"
                        ><!--draggable="true"
                                @dragstart="startDrag(index)"
                                @dragover.prevent="dragOver(index)"
                                @dragend="endDrag()"-->

                            <!-- Режим редактирования -->
                            <div x-show="editingTabId === warehouse.id" class="relative">
                                <input type="text"
                                       x-model="newTabName"
                                       @keydown.enter.prevent="saveEdit(warehouse)"
                                       @keydown.escape="cancelEdit"
                                       class="block w-full text-start p-2 border rounded text-sm text-gray-700 dark:bg-gray-700 dark:text-gray-300 focus:outline-none focus:ring focus:ring-blue-500"
                                />
                                <div class="absolute top-2 right-2">
                                    <button @click="saveEdit(warehouse)"
                                            class="bg-green-500 text-white p-1 rounded hover:bg-green-600">✓
                                    </button>
                                    <button @click="cancelEdit"
                                            class="bg-red-500 text-white p-1 rounded hover:bg-red-600">✗
                                    </button>
                                </div>
                            </div>
                            <a href="#" x-text="warehouse.name"
                               @click.prevent="activeTab = warehouse.id" wire:click="activeTab = 'warehouse.id'"
                               x-show="editingTabId !== warehouse.id"
                               @dblclick="startEdit(warehouse)"
                               :class="activeTab === warehouse.id ? 'text-gray-300 bg-[#b13a00] dark:bg-[#ff8144] dark:text-gray-800' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'"
                               class="bg-gray-800 inline-block p-2 rounded-t-lg border-t border-x border-gray-700 hover:border-gray-600 border-dashed text-clip"
                            ></a>
                        </li>
                    </template>
                </ul>

                <!-- Кнопка для прокрутки вправо -->
                <button @click="scrollBy(100)" @dblclick="scrollToEnd()"
                        class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-100 dark:bg-gray-700 p-2 rounded-full shadow z-10"
                        x-show="canScrollRight"
                        x-transition
                >
                    &rarr;
                </button>

            </div>

            <hr class="h-px mt-0 mb-3 bg-gray-200 border-0 dark:bg-gray-800">

            <!-- Таблица с запчастями -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-md"
                 x-data="{
                    selectedParts: @entangle('selectedParts'),
                    search: '',
                }"
            >
                <!-- Поле поиска -->
                <div class="flex pb-4 bg-white dark:bg-gray-900 gap-2"
                     x-data
                >
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
                    <div x-data>
                        <div x-show="transferPartsModalOpen"
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
                             style="display: none;">
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

                                            <!-- Поле выбора техников для передачи запчастей -->
                                            <div @click="open = !open"
                                                 class="w-full cursor-pointer bg-white border border-gray-300 rounded-lg shadow-sm p-2 flex justify-between items-center text-gray-500">
                                                    <span
                                                        x-text="selectedTechnicians.length > 0 ? selectedTechnicians.length + ' selected' : 'Select Technicians'"></span>
                                                <svg
                                                    class="h-5 w-5 text-gray-400 transform transition-transform"
                                                    :class="{'rotate-180': open}"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                          d="M5.23 7.21a.75.75 0 011.06-.02L10 10.879l3.72-3.67a.75.75 0 111.04 1.08l-4.25 4.2a.75.75 0 01-1.06 0l-4.25-4.2a.75.75 0 01-.02-1.06z"
                                                          clip-rule="evenodd"/>
                                                </svg>
                                            </div>

                                            <!-- Выпадающий список с мульти-выбором техников -->
                                            <div x-show="open" @click.away="open = false" x-transition
                                                 class="absolute z-10 mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                                <ul id="technician" class="py-1 text-sm text-gray-700">
                                                    @foreach ($technicians as $technician)
                                                        <li class="flex items-center px-4 py-2 cursor-pointer hover:bg-gray-100">
                                                            <input type="checkbox"
                                                                   value="{{ $technician->id }}"
                                                                   x-model="selectedTechnicians"
                                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                            <label
                                                                class="ml-2 text-gray-700">{{ $technician->name }}</label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
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
                    </div>
                    <!-- Модальное окно удаления запчастей -->
                    <div x-show="deletePartsModalOpen"
                         class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
                         style="display: none;">
                        <div
                            class="relative bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-md w-full p-6"
                            @click.away="deletePartsModalOpen = false">
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

                <!-- Таблица -->
                <div x-data="{
                            <!-- Метод выбора всех запчастей -->
                            toggleCheckAll(event) {
                                this.selectedParts = event.target.checked ? @json(collect($nomenclatures)->pluck('id')) : [];
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
                                    delete this.partQuantities[partId];
                                } else {
                                    this.selectedParts.push(partId);
                                    if (!this.partQuantities[partId]) {
                                        this.partQuantities[partId] = 1;
                                    }
                                }

                                $dispatch('update-part-quantities', { quantities: this.partQuantities });
                            },
                    parts: @js($nomenclatures ?? []),

                }"
                     x-init="$watch('selectedParts', () => fetchSelectedNames())"
                     @keydown.escape="closeModal"
                     class="w-full rounded-md bg-gray-300 dark:bg-gray-800 md:border-neutral-300 md:dark:border-neutral-500"
                >
                    <div id="parts-table"
                         class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 relative">
                        <!-- Заголовок таблицы -->
                        <div
                            class="hidden md:flex text-xs font-bold text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 p-3">
                            <!-- Чекбокс -->
                            <div class="flex items-center justify-center px-4 py-2">
                                <input type="checkbox" @click="toggleCheckAll($event)"
                                       :checked="selectedParts.length === @json(collect($nomenclatures)->count())"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="checkbox-all-search" class="sr-only">checkbox</label>
                            </div>

                            <!-- SKU -->
                            <div class="w-[100px] px-4 py-2">SKU</div>

                            <!-- Наименование (шире остальных) -->
                            <div class="flex-[1] px-4 py-2">Наименование</div>

                            <!-- Quantity -->
                            <div class="w-[120px] px-4 py-2">Quantity</div>

                            <!-- Price -->
                            <div class="w-[120px] px-4 py-2">Price</div>

                            <!-- Total -->
                            <div class="w-[120px] px-4 py-2">Total</div>

                            <!-- Изображение -->
                            <div class="w-[120px] px-4 py-2">Изображение</div>

                            <!-- URL (шире остальных) -->
                            <div class="flex-[1] flex px-4 py-2 items-center">
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

                            <!-- Действия -->
                            <div class="w-[200px] px-4 py-2">Действия</div>
                        </div>

                        <!-- Строки таблицы -->
                        <div class="space-y-2 md:space-y-0 dark:bg-gray-900"
                             x-data="{
                                filteredParts() {
                                    return {{$nomenclatures}}.filter(part =>
                                        part.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                        part.sku.toLowerCase().includes(this.search.toLowerCase()) ||
                                        (part.pns && part.pns.toLowerCase().includes(this.search.toLowerCase())) ||
                                        (part.category && part.category.name.toLowerCase().includes(this.search.toLowerCase())) ||
                                        (part.brand && part.brand.name.toLowerCase().includes(this.search.toLowerCase()))
                                    );
                                }
                            }"
                        >
                            @foreach($nomenclatures as $nomenclature)
                                @if($activeTab === $nomenclature->warehouse_id)
                                    @dd($nomenclature)
                                    @if(!empty($nomenclature->nomenclatures) && $nomenclature->nomenclatures->is_archived === 0)
                                        <div
                                            class="flex flex-col md:flex-row md:items-center bg-white border dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033] p-3 pt-5 md:pt-2 relative">
                                            <!-- Checkbox -->
                                            <div class="block sm:hidden absolute top-5 right-5 mb-2" wire:ignore>
                                                <input type="checkbox" value="{{ $nomenclature->id }}"
                                                       @click="togglePartSelection({{ $nomenclature->id }})"
                                                       :checked="selectedParts.includes({{ $nomenclature->id }})"
                                                       class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="checkbox-table-search-{{ $nomenclature->id }}"
                                                       class="sr-only">checkbox</label>
                                            </div>
                                            <div
                                                class="w-[48px] flex items-center justify-center hidden sm:block mb-0 px-4 py-2"
                                                wire:ignore>
                                                <input type="checkbox" value="{{ $part->id }}"
                                                       @click="togglePartSelection({{ $part->id }})"
                                                       :checked="selectedParts.includes({{ $part->id }})"
                                                       class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="checkbox-table-search-{{ $part->id }}"
                                                       class="sr-only">checkbox</label>
                                            </div>
                                            <!-- SKU -->
                                            <div class="w-[100px] px-4 py-2 mb-2 md:mb-0">
                                                <span class="md:hidden font-semibold">SKU:</span>
                                                {{ $part->sku }}
                                            </div>

                                            <!-- Name -->
                                            <livewire:components.part-name :part="$part"/>

                                            <!-- Quantity -->
                                            <div class="w-[120px] px-4 py-2 md:mb-0"
                                                 @part-updated="event => {
                                                         if (event.detail.partId === {{ $part->id }}) {
                                                            $el.textContent = event.detail.newQuantity;
                                                         }
                                                     }"
                                            >
                                                <span class="md:hidden font-semibold">Quantity:</span>
                                                {{ $part->quantity }}
                                            </div>

                                            <!-- Price -->
                                            <livewire:components.price :part="$part"/>

                                            <!-- Total -->
                                            <div class="w-[120px] px-4 py-2 md:mb-0">
                                                <span class="md:hidden font-semibold">Total:</span>
                                                <span>${{ $part->total }}</span>
                                            </div>

                                            <!-- PartImage -->
                                            <div
                                                class="flex flex-row w-[120px] h-[80px] justify-start space-x-3 px-4 py-2 md:mb-0">
                                                <!-- Миниатюра -->
                                                <span class="md:hidden font-semibold">Image:</span>
                                                <livewire:components.part-image :part="$part"/>
                                            </div>

                                            <!-- Колонка URL -->
                                            <livewire:components.url :part="$part" :suppliers="$suppliers" wire:key="part-{{ $part->id }}"/>

                                            <!-- Actions -->
                                            <div class="flex flex-col w-[200px]">
                                                <!-- Кнопки действий -->
                                                <div class="flex flex-row w-full"><span
                                                        class="md:hidden font-semibold">Actions:</span>
                                                </div>
                                                <div class="flex flex-row w-full justify-evenly">
                                                    <button wire:click="incrementPart('{{ $part->id }}')"
                                                            @click.stop title="Add one"
                                                            class="w-10 h-10 md:w-8 md:h-8 bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded-md hover:bg-green-800">
                                                        +
                                                    </button>
                                                    <button
                                                        wire:click="openQuantityModal('{{ $part->id }}', 'add')"
                                                        @click.stop
                                                        title="Add some"
                                                        class="w-10 h-10 md:w-8 md:h-8 bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded-md hover:bg-green-800">
                                                        ++
                                                    </button>
                                                </div>
                                                <hr class="w-full h-px mx-auto my-2 bg-gray-100 border-0 rounded md:my-2 dark:bg-gray-700">
                                                <div class="flex flex-row w-full justify-evenly">
                                                    <button wire:click="decrementPart('{{ $part->id }}')"
                                                            @click.stop
                                                            title="Remove one"
                                                            class="w-10 h-10 md:w-8 md:h-8 bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-md bg-red-800">
                                                        -
                                                    </button>
                                                    <button
                                                        wire:click="openQuantityModal('{{ $part->id }}', 'subtract')"
                                                        @click.stop
                                                        title="Remove some"
                                                        class="w-10 h-10 md:w-8 md:h-8 bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-md bg-red-800">
                                                        --
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Модальное окно для добавления/уменьшения количества -->
            @if($showQuantityModal)
                <div class="fixed z-10 inset-0 overflow-y-auto" x-data @click.away="$wire.resetQuantityModal()">
                    <div class="flex items-center justify-center min-h-screen">
                        <div
                            class="flex flex-col justify-center items-center border border-gray-400 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 relative">
                            <!-- Кнопка закрытия -->
                            <button @click="$wire.resetQuantityModal()"
                                    class="absolute top-0 right-0 mt-2 mr-2 text-gray-400 hover:text-gray-600">
                                &times;
                            </button>

                            <h3 class="text-lg font-semibold mb-4 text-center text-gray-500 dark:text-gray-400">
                                @if($operation === 'add')
                                    Quantity to add
                                @else
                                    Quantity to remove
                                @endif
                            </h3>

                            <!-- Сообщение об ошибке -->
                            @if($errorMessage)
                                <div class="bg-red-500 text-white p-2 rounded mb-4">
                                    {{ $errorMessage }}
                                </div>
                            @endif

                            <div class="flex flex-row w-32 justify-center items-center">
                                <input type="number" wire:model="quantityToAdd" min="1"
                                       class="w-2/3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg p-2 text-sm text-gray-900 dark:text-white"/>

                                <div class="w-1/3 flex justify-end">
                                    <button wire:click="modifyQuantity"
                                            class="@if($operation === 'add') bg-green-500 hover:bg-green-600 @else bg-red-500 hover:bg-red-600 @endif text-white font-bold py-2 px-4 rounded-md">
                                        @if($operation === 'add')
                                            +
                                        @else
                                            -
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($openPriceModal)
                <!-- Фон модального окна -->
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
                    <!-- Контейнер модального окна -->
                    <div x-transition
                         @click.away="$dispatch(closePriceModal)"
                         class="relative bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-lg w-full p-6 mx-4"
                         role="dialog" aria-modal="true" aria-labelledby="modal-title">

                        <!-- Заголовок и кнопка закрытия -->
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="modal-title" class="text-xl font-semibold text-gray-900 dark:text-white">Price
                                Change
                                History</h2>
                            <button wire:click="closePriceModal"
                                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                    aria-label="Close">
                                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414 1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                          clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Содержимое модального окна -->
                        <div class="overflow-y-auto max-h-96">
                            <livewire:part-price-history :part-id="$selectedPartId"/>
                        </div>

                        <!-- Кнопка закрытия внизу окна -->
                        <div class="flex justify-end mt-4">
                            <button wire:click="closePriceModal"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Пагинация (если потребуется) -->
            <div class="mt-4">
                {{ $paginatedParts->links() }}
            </div>
        </div>
    </div>
</div>
