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
                        class="w-28 ml-2 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All cats</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Фильтр по брендам -->
            <div class="flex flex-row justify-between items-center">
                <label for="brand" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by
                    Brand:</label>
                <select wire:model.live="selectedBrand" id="brand"
                        class="w-28 ml-2 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All brands</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <livewire:manager-part-form/>

    <div class="flex flex-row space-x-4">
        <div class="relative w-full"
             x-data="{
                    tabs: window.tabs || [],
                    parts: {},
                    activeTab: null,
                    init() {
                        const defaultTab = this.tabs.find(tab => tab.is_default === 1);
                        this.activeTab = defaultTab ? defaultTab.id : (this.tabs[0]?.id || null);

                        // Инициализируем объект с запчастями
                        this.parts = this.tabs.reduce((acc, tab) => {
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
                    }
                }"
             x-init="init(); checkScroll();"
             @resize.window="checkScroll"
             @tabs-updated.window="(event) => {
                    updateTabs(event.detail.tabs);
                }"
        >

            <script>
                window.tabs = @js($warehouses);
            </script>

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
                    class="flex flex-nowrap no-scrollbar text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400"
                    x-ref="tabContainer"
                    @scroll="checkScroll"
                    style="scroll-behavior: smooth; overflow-x: hidden;"
                >

                    <!-- Таб # -->
                    <template x-for="(tab, index) in tabs" :key="tab.id">
                        <li
                            @click.prevent="activeTab = tab.id"
                            class="shrink-0"
                        ><!--draggable="true"
                                @dragstart="startDrag(index)"
                                @dragover.prevent="dragOver(index)"
                                @dragend="endDrag()"-->

                            <!-- Режим редактирования -->
                            <div x-show="editingTabId === tab.id" class="relative">
                                <input type="text"
                                       x-model="newTabName"
                                       @keydown.enter.prevent="saveEdit(tab)"
                                       @keydown.escape="cancelEdit"
                                       class="block w-full text-start p-2 border rounded text-sm text-gray-700 dark:bg-gray-700 dark:text-gray-300 focus:outline-none focus:ring focus:ring-blue-500"
                                />
                                <div class="absolute top-2 right-2">
                                    <button @click="saveEdit(tab)"
                                            class="bg-green-500 text-white p-1 rounded hover:bg-green-600">✓
                                    </button>
                                    <button @click="cancelEdit"
                                            class="bg-red-500 text-white p-1 rounded hover:bg-red-600">✗
                                    </button>
                                </div>
                            </div>
                            <a href="#" x-text="tab.name"
                               @click.prevent="activeTab = tab.id"
                               x-show="editingTabId !== tab.id"
                               @dblclick="startEdit(tab)"
                               :class="activeTab === tab.id ? 'text-blue-600 bg-gray-100 dark:bg-gray-800 dark:text-blue-500' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'"
                               class="inline-block p-2 rounded-t-lg border-t border-x border-gray-700 hover:border-gray-600 border-dashed text-clip"
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
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-md">
                <!-- Поле поиска -->
                <div class="pb-4 bg-white dark:bg-gray-900">
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
                        <input type="text" id="table-search" wire:model.live="search"
                               class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-md w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               placeholder="Поиск по запчастям...">
                    </div>
                </div>

                <!-- Таблица -->
                <div x-data="{
                            selectedParts: @entangle('selectedParts'),
                            selectedPartNames: [],
                            async fetchSelectedNames() {
                                if (this.selectedParts.length) {
                                    this.selectedPartNames = await $wire.call('getSelectedPartNames');
                                } else {
                                    this.selectedPartNames = [];
                                }
                            },
                            partQuantities: {},
                            partStock: @js($parts->pluck('quantity', 'id')),
                            transferPartsModalOpen: false,
                            deletePartsModalOpen: false,
                            toggleCheckAll(event) {
                                this.selectedParts = event.target.checked ? @json($parts->pluck('id')) : [];
                                this.selectedParts.forEach(partId => {
                                    if (!this.partQuantities[partId]) {
                                        this.partQuantities[partId] = 1;
                                    }
                                });

                                $dispatch('update-part-quantities', { quantities: this.partQuantities });
                            },

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
                            openModal() {
                                if (this.selectedParts.length > 0) {
                                    this.transferPartsModalOpen = true;
                                }
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
                            closeModal() {
                                this.transferPartsModalOpen = false;
                            },
                            limitQuantity(partId) {
                                if (this.partQuantities[partId] > this.partStock[partId]) {
                                    this.partQuantities[partId] = this.partStock[partId];
                                }
                                $dispatch('update-part-quantities', { quantities: this.partQuantities });
                            }
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
                            <div class="px-4 py-2">
                                <input type="checkbox" @click="toggleCheckAll($event)"
                                       :checked="selectedParts.length === @json($parts->count())"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="checkbox-all-search" class="sr-only">checkbox</label>
                            </div>
                            <div class="px-4 py-2">SKU</div>
                            <div class="flex-1 px-4 py-2">Наименование</div>
                            <div class="flex-1 px-4 py-2">Категория</div>
                            <div class="flex-1 px-4 py-2">Поставщик</div>
                            <div class="flex-1 px-4 py-2">Брэнд</div>
                            <div class="px-4 py-2">Quantity</div>
                            <div class="px-4 py-2">Price</div>
                            <div class="flex-1 px-4 py-2">Total</div>
                            <div class="flex-1 px-4 py-2">Изображение</div>
                            <div class="flex-1 flex items-center">
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
                            <div class="flex-1 px-4 py-2">Действия</div>
                        </div>

                        <!-- Строки таблицы -->
                        <div class="space-y-2 md:space-y-0 dark:bg-gray-900">
                            @foreach($warehouses as $warehouse)
                                <div class="" x-show="activeTab === {{$warehouse->id}}">
                                    @forelse ($parts as $part)
                                        @if(is_null($part->warehouse?->id))
                                        @else
                                            @if($warehouse->id === $part->warehouse->id)
                                                <div
                                                    class="flex flex-col md:flex-row items-start md:items-center bg-white border dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033] p-2 pt-5 md:pt-2 relative"
                                                >
                                                    <!-- Checkbox -->
                                                    <div class="block sm:hidden absolute top-5 right-5 mb-2">
                                                        <input type="checkbox" :value="{{ $part->id }}"
                                                               @click="togglePartSelection({{ $part->id }})"
                                                               :checked="selectedParts.includes({{ $part->id }})"
                                                               class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                        <label for="checkbox-table-search-{{ $part->id }}"
                                                               class="sr-only">checkbox</label>
                                                    </div>
                                                    <div class="hidden sm:block md:w-1/12 mb-0">
                                                        <input type="checkbox" :value="{{ $part->id }}"
                                                               @click="togglePartSelection({{ $part->id }})"
                                                               :checked="selectedParts.includes({{ $part->id }})"
                                                               class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                        <label for="checkbox-table-search-{{ $part->id }}"
                                                               class="sr-only">checkbox</label>
                                                    </div>
                                                    <!-- SKU -->
                                                    <div class="w-full md:w-1/12 mb-2 md:mb-0">
                                                        <span class="md:hidden font-semibold">SKU:</span>
                                                        {{ $part->sku }}
                                                    </div>
                                                    <!-- Name -->
                                                    <div x-data="{
                                                                showEditMenu: false,
                                                                editingName: false,
                                                                newName: '{{ $part->name }}',
                                                                originalName: '{{ $part->name }}',
                                                                errorMessage: '',
                                                                showPnPopover: false,
                                                                deletePn: false,
                                                                showingPn: false,
                                                                searchPn: '',
                                                                newPn: '',
                                                                addingPn: false,
                                                                availablePns: Object.keys(@entangle('availablePns') || {}).length ? @entangle('availablePns') : {},
                                                                selectedPns: @entangle('selectedPns'),
                                                            }"
                                                         @pn-added.window="addingPn = false; newPn = ''; errorMessage = ''"
                                                         class="flex flex-row w-full md:w-2/12 mb-2 md:mb-0 cursor-pointer relative"
                                                    >

                                                        <!-- PN -->
                                                        <div class="flex relative">

                                                            <!-- Список существующих PNs -->
                                                            <div class="flex z-30 items-center" x-cloak>
                                                                <!-- Кнопка для открытия поповера -->
                                                                <div
                                                                    class="w-4 h-4 md:w-6 md:h-6 flex items-center justify-center bg-blue-500 text-white rounded-full cursor-pointer mr-2 uppercase font-bold text-[8px] md:text-[10px]"
                                                                    @click="showPnPopover = !showPnPopover">
                                                                    PN
                                                                </div>

                                                                <!-- Поповер для редактирования PNs -->
                                                                <div x-show="showPnPopover" x-transition
                                                                     @click.away="showPnPopover = false"
                                                                     class="flex absolute z-40 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1">

                                                                    <!-- Оверлей -->
                                                                    <div
                                                                        x-show="deletePn || addingPn || showPnPopover || showingPn"
                                                                        class="flex fixed inset-0 bg-black bg-opacity-50 z-30"
                                                                        @click="deletePn = false; showEditMenu = false; showingPn = false; showPnsList = false; addingPn = false; showPnPopover = false;"
                                                                        x-cloak>
                                                                    </div>

                                                                    <div
                                                                        class="flex flex-row w-full cursor-pointer z-50"
                                                                        x-cloak>
                                                                        <div
                                                                            @click="addingPn = true; deletePn = false; showEditMenu = false; showingPn = false; showPnsList = false; showPnPopover = false;"
                                                                            class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                                            Add PN
                                                                        </div>
                                                                        <div
                                                                            @click="deletePn = true; showEditMenu = false; showingPn = false; showPnsList = false; addingPn = false; showPnPopover = false;"
                                                                            class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                                            Del PN
                                                                        </div>
                                                                        <div
                                                                            @click="showingPn = true; deletePn = false; showEditMenu = false; showPnsList = false; addingPn = false; showPnPopover = false;"
                                                                            class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                                            Show PN
                                                                        </div>
                                                                    </div>


                                                                </div>
                                                            </div>

                                                            <!-- Список PN запчасти -->
                                                            <div x-show="showingPn"
                                                                 class="flex flex-col absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-52 p-1"
                                                                 x-transition
                                                                 @click.away="showingPn = false;"
                                                            >
                                                                <div class="flex flex-row w-full">
                                                                    <div
                                                                        class="w-full flex justify-center items-center">
                                                                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-28 overflow-y-auto w-52"
                                                                            x-ref="pnList"

                                                                        >
                                                                            @php $pnsArray = json_decode($part->pns, true); @endphp
                                                                            @if (!empty($pnsArray))
                                                                                @foreach ($pnsArray as $pn)
                                                                                    <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                                                                        @click.stop>
                                                                                            <span
                                                                                                class="ml-2">{{ $pn }}</span>
                                                                                    </li>
                                                                                @endforeach
                                                                            @else
                                                                                <p>No PN's</p>
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Поле ввода нового PN -->
                                                            <div x-show="addingPn"
                                                                 class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                                                 x-transition x-cloak
                                                                 @click.away="addingPn = false; newPn = ''; errorMessage = '';"
                                                            >
                                                                <div class="flex flex-row w-full">
                                                                    <div class="flex justify-center items-center">
                                                                        <!-- Поле ввода -->
                                                                        <input type="text" wire:model="newPn"
                                                                               placeholder="Enter new PN"
                                                                               class="border border-gray-300 rounded-md text-sm px-2 py-1 w-3/4 mr-2">

                                                                        <!-- Кнопки действия -->
                                                                        <button wire:click="addPn"
                                                                                class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                                                                            ✓
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Режим удаления PN -->
                                                            <div x-show="deletePn"
                                                                 class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                                                 x-cloak x-transition
                                                                 @click.away="deletePn = false; searchPn='';"
                                                            >
                                                                <!-- Поле поиска -->
                                                                <div class="mb-2" @click.stop>
                                                                    <input type="text" x-model="searchPn"
                                                                           placeholder="Search brands..."
                                                                           class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:bg-gray-700 dark:text-gray-300"/>
                                                                </div>

                                                                <!-- PN -->
                                                                <div
                                                                    class="flex flex-row justify-evenly items-center"
                                                                    @click.stop>
                                                                    <!-- Список PN с мульти-выбором -->
                                                                    <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-28 overflow-y-auto">
                                                                        @php $pns = $this->getPartPns($part->id); @endphp
                                                                        @if (!empty($pns))
                                                                            @foreach ($pns as $pn)
                                                                                <template
                                                                                    x-if="!searchPn || '{{ strtolower($pn->number) }}'.includes(searchPn.toLowerCase())">
                                                                                    <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                                                                        @click.stop>
                                                                                        <input type="checkbox"
                                                                                               value="{{ $pn->id }}"
                                                                                               x-model="selectedPns"
                                                                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                                                               @click.stop>
                                                                                        <label
                                                                                            class="ml-2">{{ $pn->number }}</label>
                                                                                    </li>
                                                                                </template>
                                                                            @endforeach
                                                                        @else
                                                                            <p>No PN's</p>
                                                                        @endif
                                                                    </ul>

                                                                    <!-- Кнопка подтверждения -->
                                                                    <div
                                                                        class="justify-self-center inline-flex self-center items-center">
                                                                        <button @click="$wire.set('selectedPns', selectedPns).then(() => {
                                                                                        $wire.deletePns({{ $part->id }}, selectedPns);
                                                                                        deletePn = false;
                                                                                    });"
                                                                                class="bg-green-500 text-white px-2 py-1 rounded-full hover:bg-green-600">
                                                                            ✓
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <span
                                                            class="flex items-center md:hidden font-semibold">Name:</span>

                                                        <!-- Название с подменю -->
                                                        <div class="flex items-center w-full">
                                                            <!-- Оверлей -->
                                                            <div x-show="editingName || deletePn || addingPn"
                                                                 class="flex fixed inset-0 bg-black bg-opacity-50 z-30"
                                                                 @click="editingName = false, deletePn = false, addingPn = false;"
                                                                 x-cloak>
                                                            </div>

                                                            <!-- Основное отображение -->
                                                            <span x-show="!editingName" @click="editingName = true"
                                                                  class="flex z-35 items-center cursor-pointer hover:underline min-h-[30px]">
                                                                    {{ $part->name }}
                                                                </span>
                                                        </div>
                                                        <!-- Режим редактирования Name -->
                                                        <div x-show="editingName"
                                                             class="flex justify-center items-center w-full relative z-40"
                                                             x-cloak>
                                                            <input type="text" x-model="newName"
                                                                   class="border border-gray-300 rounded-md text-sm px-2 py-1 w-[180px] mr-2"
                                                                   @keydown.enter="if (newName !== originalName) { $wire.updateName({{ $part->id }}, newName); originalName = newName; } editingName = false;"
                                                                   @keydown.escape="editingName = false">
                                                            <button
                                                                @click="if (newName !== originalName) { $wire.updateName({{ $part->id }}, newName); originalName = newName; } editingName = false;"
                                                                class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4 w-[28px]">
                                                                ✓
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!-- Quantity -->
                                                    <div class="w-full md:w-1/12 mb-2 md:mb-0"
                                                         @part-updated="event => {
                                                            if (event.detail.partId === {{ $part->id }}) {
                                                                $el.textContent = event.detail.newQuantity;
                                                            }
                                                        }"
                                                    >
                                                            <span
                                                                class="md:hidden font-semibold">Quantity:</span> {{ $part->quantity }}
                                                    </div>
                                                    <!-- Price -->
                                                    <div
                                                        class="flex flex-row w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer relative parent-container"
                                                        x-data="{ showPopover: false, editing: false, newPrice: '', popoverX: 0, popoverY: 0 }">

                                                        <!-- Кликабельная ссылка с ценой запчасти -->
                                                        <span class="md:hidden font-semibold">Price:</span>
                                                        <a id="price-item-{{ $part->id }}"
                                                           @click="
                                                                $nextTick(() => {
                                                                    editing = false; // Сбрасываем редактирование при открытии
                                                                    newPrice = '{{ $part->price }}'; // Устанавливаем текущее значение
                                                                    const parent = $el.closest('.parent-container');
                                                                    const elementOffsetLeft = $el.offsetLeft;
                                                                    const elementOffsetTop = $el.offsetTop;

                                                                    popoverX = elementOffsetLeft / parent.offsetWidth;
                                                                    popoverY = elementOffsetTop / parent.offsetHeight;

                                                                    showPopover = true;
                                                                });
                                                            "
                                                           class="cursor-pointer text-sm text-blue-600 hover:underline dark:text-blue-400">
                                                            ${{ $part->price }}
                                                        </a>

                                                        <!-- Поповер с динамическим позиционированием -->
                                                        <div x-show="showPopover" x-transition role="tooltip"
                                                             class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                                             :style="'top: ' + popoverY + 'px; left: ' + popoverX + 'px;'"
                                                             @click.away="showPopover = false">

                                                            <div class="flex flex-row w-full">
                                                                <!-- Кнопка Edit -->
                                                                <button x-show="!editing"
                                                                        @click.prevent="editing = true; $nextTick(() => { $refs.priceInput.focus() })"
                                                                        class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                                    Edit
                                                                </button>

                                                                <!-- Поле ввода новой цены и кнопка подтверждения -->
                                                                <div x-show="editing"
                                                                     class="flex justify-center items-center"
                                                                     x-transition>
                                                                    <input type="number" x-ref="priceInput"
                                                                           x-model="newPrice"
                                                                           class="border border-gray-300 rounded-md text-sm px-2 py-1 w-3/4 mr-2"
                                                                           placeholder="{{ $part->price }}">
                                                                    <button @click="
                                                                        if (newPrice !== '{{ $part->price }}') {
                                                                            $wire.set('newPrice', newPrice)
                                                                            .then(() => {
                                                                                $wire.updatePartPrice({{ $part->id }}, newPrice);
                                                                            });
                                                                        }
                                                                        showPopover = false;
                                                                        editing = false;
                                                                    "
                                                                            class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                                                                        ✓
                                                                    </button>
                                                                </div>

                                                                <!-- Кнопка для открытия истории цен -->
                                                                <button x-show="!editing"
                                                                        @click="$dispatch('open-price-modal', { partId: {{ $part->id }} })"
                                                                        class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                                                    History
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Total -->
                                                    <div class="w-full md:w-1/12 mb-2 md:mb-0">
                                                        <span class="md:hidden font-semibold">Total:</span>
                                                        ${{ $part->total }}
                                                    </div>
                                                    <!-- Image -->
                                                    <div
                                                        class="flex flex-row justify-start space-x-3 w-full md:w-1/12 mb-2 md:mb-0">
                                                        <!-- Миниатюра -->
                                                        <span class="md:hidden font-semibold">Image:</span>
                                                        <div x-data="{ isUploading: false, uploadProgress: 0 }"
                                                             class="gallery w-14 relative">
                                                            @if(is_null($part->image))
                                                                <div class="flex flex-row">
                                                                    <span class="w-[56px] h-[56px]">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                             xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                             version="1.1" width="56"
                                                                             height="56" viewBox="0 0 256 256"
                                                                             xml:space="preserve">
                                                                            <defs>
                                                                            </defs>
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
                                                                    </span>
                                                            @else
                                                                    <div class="flex flex-row">
                                                                        <img
                                                                            src="{{ $part->image }}"
                                                                            alt="{{ $part->name }}"
                                                                            @click="$dispatch('lightbox', '{{ $part->image }}')"
                                                                            @click.stop
                                                                            class="object-cover rounded cursor-zoom-in"
                                                                        >
                                                            @endif
                                                                            <div x-data="{ showTooltip: false }"
                                                                                 @mouseenter="showTooltip = true"
                                                                                 @mouseleave="showTooltip = false"
                                                                                 @click.away="showTooltip = false"
                                                                            >
                                                                                <!-- Поповер -->
                                                                                <div x-show="showTooltip"
                                                                                     x-transition
                                                                                     class="absolute z-50 -top-6 left-6 w-max px-2 py-1 text-xs bg-green-500 lowercase text-white rounded shadow-lg">
                                                                                    Change Image
                                                                                </div>
                                                                                <!-- Кнопка загрузки изображения -->
                                                                                <div
                                                                                    for="upload-image-{{ $part->id }}"
                                                                                    wire:click="openImageModal({{ $part->id }})"
                                                                                    class="text-white rounded-full p-1 cursor-pointer h-[20px]">
                                                                                    <svg
                                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                                        class="h-4 w-4"
                                                                                        version="1.1" width="256"
                                                                                        height="256"
                                                                                        viewBox="0 0 256 256"
                                                                                        xml:space="preserve">
                                                                                    <defs>
                                                                                    </defs>
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
                                                                                <div wire:loading
                                                                                     wire:target="uploadImage"
                                                                                     class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
                                                                                    <div class="text-white text-lg">
                                                                                        Uploading...
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                    </div>
                                                                </div>
                                                        </div>
                                                        <!-- Brand -->
                                                        <div id="brand-item-{{ $part->id }}"
                                                             class="w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer parent-container"
                                                             x-data="{ showPopover: false, selectedBrands: @json($part->brands->pluck('id')), search: '', popoverX: 0, popoverY: 0 }"

                                                             @click.away="showPopover = false"
                                                             @mousedown.stop
                                                             @click="
                                                                    const { clientX, clientY } = $event; // Получаем координаты клика

                                                                    $nextTick(() => {
                                                                        popoverX = Math.min(clientX, window.innerWidth - 250);
                                                                        popoverY = Math.min(clientY, window.innerHeight - 200);

                                                                        showPopover = true; // Показываем поповер
                                                                    });
                                                                "
                                                        >

                                                            <!-- Текущие бренды -->
                                                            <div class="flex flex-col h-24 justify-center p-1">
                                                                <span class="md:hidden font-semibold">Brand:</span>
                                                                <div class="overscroll-contain overflow-y-auto">
                                                                    @if(count($part->brands) == 0)
                                                                        <div class="px-3 py-2">---</div>
                                                                    @else
                                                                        @foreach($part->brands as $brand)
                                                                            <span>{{ $brand->name }}{{ !$loop->last ? ', ' : '' }}</span>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Поповер с мульти-выбором брендов -->
                                                            <div x-show="showPopover"
                                                                 class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                                                 :style="`top: ${popoverY}px; left: ${popoverX}px;`"
                                                                 x-init="const onScroll = () => showPopover = false; window.addEventListener('scroll', onScroll)"
                                                                 @click.outside="showPopover = false"
                                                                 x-transition
                                                            >

                                                                <!-- Поле поиска -->
                                                                <div class="mb-2" @click.stop>
                                                                    <input type="text" x-model="search"
                                                                           placeholder="Search brands..."
                                                                           class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:bg-gray-700 dark:text-gray-300"/>
                                                                </div>

                                                                <div
                                                                    class="flex flex-row justify-evenly items-center"
                                                                    @click.stop>
                                                                    <!-- Список брендов с мульти-выбором -->
                                                                    <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-28 overflow-y-auto">
                                                                        @foreach ($brands as $brand)
                                                                            <template
                                                                                x-if="!search || '{{ strtolower($brand->name) }}'.includes(search.toLowerCase())">
                                                                                <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                                                                    @click.stop>
                                                                                    <input type="checkbox"
                                                                                           value="{{ $brand->id }}"
                                                                                           x-model="selectedBrands"
                                                                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                                                           @click.stop>
                                                                                    <label
                                                                                        class="ml-2">{{ $brand->name }}</label>
                                                                                </li>
                                                                            </template>
                                                                        @endforeach
                                                                    </ul>

                                                                    <!-- Кнопка подтверждения -->
                                                                    <div
                                                                        class="justify-self-center inline-flex self-center items-center">
                                                                        <button @click="$wire.set('selectedBrands', selectedBrands).then(() => {
                                                                                    $wire.updatePartBrands({{ $part->id }}, selectedBrands);
                                                                                    showPopover = false;
                                                                                });"
                                                                                class="bg-green-500 text-white px-2 py-1 rounded-full hover:bg-green-600">
                                                                            ✓
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @php
                                                            $urlData = json_decode($part->url, true);
                                                        @endphp
                                                            <!-- URL -->
                                                        <div
                                                            class="w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer font-semibold"
                                                            x-data="{ clickCount: 0 }"
                                                            @click="
                                                                    clickCount++;
                                                                    setTimeout(() => {
                                                                        if (clickCount === 1) {
                                                                            // Одиночный клик - проверка на наличие ссылки
                                                                            if ('{{ $urlData['url'] ?? '' }}') {
                                                                                window.open('{{ $urlData['url'] ?? '' }}', '_blank');
                                                                            }
                                                                        } else if (clickCount === 2) {
                                                                            // Двойной клик - открытие модального окна для редактирования
                                                                            $wire.openManagerPartUrlModal({{ $part->id }});
                                                                        }
                                                                        clickCount = 0; // Сброс счетчика
                                                                    }, 300); // Таймаут для определения двойного клика
                                                                "
                                                        >
                                                            @if(isset($urlData['text']) && $urlData['text'] !== '')
                                                                <!-- Отображение текста, если он есть -->
                                                                <span class="md:hidden font-semibold">URL:</span>
                                                                {{ $urlData['text'] }}
                                                            @elseif(isset($urlData['url']) && $urlData['url'] !== '')
                                                                <!-- Отображение URL, если текст отсутствует, но есть URL -->
                                                                <span class="md:hidden font-semibold">URL:</span>
                                                                {{ $urlData['url'] }}
                                                            @else
                                                                <!-- Отображение иконки, если URL пуст -->
                                                                <span class="text-gray-500" title="Edit URL">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                         class="h-5 w-5 inline-block" fill="none"
                                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                              stroke-width="2"
                                                                              d="M15.232 5.232l3.536 3.536M9 13h.01M6 9l5 5-3 3h6l-1.293-1.293a1 1 0 010-1.414l7.42-7.42a2.828 2.828 0 10-4-4l-7.42 7.42a1 1 0 01-1.414 0L6 9z"/>
                                                                    </svg>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <!-- Actions -->
                                                        <div class="flex flex-col w-full md:w-2/12 flex">
                                                            <!-- Кнопки действий -->
                                                            <div class="flex flex-row w-full"><span
                                                                    class="md:hidden font-semibold">Actions:</span>
                                                            </div>
                                                            <div class="flex flex-row w-full justify-evenly">
                                                                <button wire:click="incrementPart({{ $part->id }})"
                                                                        @click.stop title="Add one"
                                                                        class="w-10 h-10 md:w-8 md:h-8 bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded-md hover:bg-green-800">
                                                                    +
                                                                </button>
                                                                <button
                                                                    wire:click="openQuantityModal({{ $part->id }}, 'add')"
                                                                    @click.stop
                                                                    title="Add some"
                                                                    class="w-10 h-10 md:w-8 md:h-8 bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded-md hover:bg-green-800">
                                                                    ++
                                                                </button>
                                                            </div>
                                                            <hr class="w-full h-px mx-auto my-2 bg-gray-100 border-0 rounded md:my-2 dark:bg-gray-700">
                                                            <div class="flex flex-row w-full justify-evenly">
                                                                <button wire:click="decrementPart({{ $part->id }})"
                                                                        @click.stop
                                                                        title="Remove one"
                                                                        class="w-10 h-10 md:w-8 md:h-8 bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-md bg-red-800">
                                                                    -
                                                                </button>
                                                                <button
                                                                    wire:click="openQuantityModal({{ $part->id }}, 'subtract')"
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
                                                    @empty
                                                    <div
                                                        class="text-sm text-center bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                        No spare parts available
                                                    </div>
                                    @endforelse
                                </div>
                            @endforeach

                            <div x-data="{ transferPartsModalOpen: false }"
                                 x-bind:class="{ 'overflow-hidden': transferPartsModalOpen }">

                                <button
                                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50"
                                    @click="openModal" x-show="selectedParts.length > 0"
                                >
                                    Send parts
                                </button>

                                <!-- Flowbite-стилизованное модальное окно -->
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
                                                Выбранные
                                                запчасти</h3>
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
                            <div x-data="{ deletePartsModalOpen: false }"
                                 x-bind:class="{ 'overflow-hidden': deletePartsModalOpen }">

                                <button
                                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                                    @click="openDeleteModal" x-show="selectedParts.length > 0"
                                >
                                    Delete parts
                                </button>

                                <!-- Flowbite-стилизованное модальное окно -->
                                <div x-show="deletePartsModalOpen"
                                     class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
                                     style="display: none;">
                                    <div
                                        class="relative bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-md w-full p-6">
                                        <!-- Заголовок модального окна -->
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                                Запчасти для
                                                удаления</h3>
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
                                        <form wire:submit.prevent="deleteParts">
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

            <div x-data="{ showImageModal: @entangle('showImageModal') }">
                <!-- Modal Backdrop -->
                <div x-show="showImageModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40"
                     x-transition.opacity
                     x-cloak></div>

                <!-- Modal Content -->
                <div x-show="showImageModal"
                     class="fixed inset-0 flex items-center justify-center z-50 p-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Upload Image</h3>

                        <!-- File Input -->
                        <div class="mb-4">
                            <input type="file" wire:model="newImage"
                                   class="block w-full text-gray-800 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                            @error('newImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-4">
                            <button type="button"
                                    @click="showImageModal = false; $wire.closeImageModal();"
                                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                Cancel
                            </button>
                            <button type="button"
                                    wire:click="uploadImage"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Upload
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Модальное окно для редактирования URL -->
            @if($managerPartUrlModalVisible)
                <div
                    x-data
                    x-init="document.body.classList.add('overflow-hidden')"
                    x-on:close-modal.window="document.body.classList.remove('overflow-hidden')"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                    <div
                        class="bg-white p-6 rounded-t-lg shadow-md w-full max-w-md h-[60%] overflow-y-auto md:rounded-lg md:max-h-[80%]">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Редактировать ссылку</h2>
                            <button wire:click="$set('managerPartUrlModalVisible', false)"
                                    class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2"
                                   for="selectedSupplier">Supplier:</label>
                            <select wire:model="managerPartSupplier" id="selectedSupplier"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->name }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedSupplier')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2"
                                   for="managerPartUrl">URL:</label>
                            <input wire:model="managerPartUrl" type="text" id="managerPartUrl"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Enter URL">
                        </div>

                        <div class="flex flex-col md:flex-row justify-end space-y-2 md:space-y-0 md:space-x-2">
                            <button wire:click="$set('managerPartUrlModalVisible', false)"
                                    class="w-full md:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Отмена
                            </button>
                            <button wire:click="saveManagerPartUrl"
                                    class="w-full md:w-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Общий Lightbox для всех изображений -->

            <div class="lightbox fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center"
                 x-data="{ lightboxOpen: false, imgSrc: '' }"
                 x-show="lightboxOpen"
                 x-transition
                 @lightbox.window="lightboxOpen = true; imgSrc = $event.detail;"
                 style="display: none;">

                <!-- Фон для закрытия -->
                <div class="absolute inset-0 bg-black opacity-75" @click="lightboxOpen = false"></div>

                <!-- Контейнер для изображения -->
                <div class="lightbox-container relative z-10" @click.stop>
                    <!-- Полное изображение -->
                    <img :src="imgSrc" class="object-contain max-w-full max-h-full">
                </div>
            </div>

            <!-- Пагинация (если потребуется) -->
            <div class="mt-4">
                {{ $parts->links() }}
            </div>
        </div>
    </div>
</div>
