<div wire:init="loadComponent" x-data="{ initialized: false }" x-init="setTimeout(() => initialized = true, 100)"
     class="p-2 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    @if($loaded)
        @if ($notificationMessage)
            <div
                class="flex justify-center left-1/3 text-white text-center p-4 rounded-lg mb-6 transition-opacity duration-1000 z-50 absolute top-[10%] w-1/2"
                x-data="{ show: true }"
                x-init="
            setTimeout(() => show = false, 3500);
            setTimeout(() => $wire.clearNotification(), 3500);
        "
                x-show="show"
                x-transition:enter="opacity-0"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="opacity-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="{
            'bg-blue-700': '{{ $notificationType }}' === 'info',
            'bg-green-500': '{{ $notificationType }}' === 'success',
            'bg-yellow-500': '{{ $notificationType }}' === 'warning'
        }"
            >
                {{ $notificationMessage }}
            </div>
        @endif

        <!-- Заголовок страницы и фильтры -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
            <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Parts</h1>
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <!-- Фильтр по категориям -->
                <div class="flex flex-row justify-between items-center">
                    <label for="category" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Cat:</label>
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
                    <label for="brand" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Brand:</label>
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

        <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-800">

        <!-- Таблица с запчастями -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-md">
            <!-- Поле поиска -->
            <div class="pb-4 bg-white dark:bg-gray-900">
                <label for="table-search" class="sr-only">Поиск</label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                selectedParts: [],
                partQuantities: {},
                partStock: @js($parts->pluck('quantity', 'id')),
                transferPartsModalOpen: false,
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
                 @keydown.escape="closeModal"
                 class="w-full rounded-md bg-gray-300 dark:bg-gray-800 md:border-neutral-300 md:dark:border-neutral-500"
            >
                <div id="parts-table"
                     class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 relative">
                    <!-- Заголовок таблицы -->
                    <div class="hidden md:flex text-xs font-bold text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 p-3">
                        <div class="flex items-center w-1/12">
                            <input type="checkbox" @click="toggleCheckAll($event)"
                                :checked="selectedParts.length === @json($parts->count())"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="checkbox-all-search" class="sr-only">checkbox</label>
                        </div>
                        <div class="w-1/12">SKU</div>
                        <div class="w-2/12">Name</div>
                        <div class="w-1/12">Quantity</div>
                        <div class="w-1/12">Price</div>
                        <div class="w-1/12">Total</div>
                        <div class="w-1/12">Image</div>
                        <div class="w-1/12">Brand</div>
                        <div class="w-2/12 flex items-center">
                            <span>URL</span>
                            <div x-data="{ showTooltip: false }" @click="showTooltip = !showTooltip" @click.away="showTooltip = false"
                                class="relative ml-2 w-5 h-5 bg-blue-500 text-white text-xs font-bold lowercase rounded-full cursor-pointer flex items-center justify-center">
                                i
                                <!-- Поповер -->
                                <div x-show="showTooltip" x-transition
                                    class="absolute z-50 top-full mt-1 w-max px-2 py-1 text-xs bg-blue-500 lowercase text-white rounded shadow-lg">
                                    2-click for edit
                                </div>
                            </div>
                        </div>
                        <div class="w-2/12">Actions</div>
                    </div>

                    <!-- Строки таблицы -->
                    <div class="space-y-2 md:space-y-0 dark:bg-gray-900">
                        @forelse ($parts as $part)
                            <div
                                class="flex flex-col md:flex-row items-start md:items-center bg-white border dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033] p-2 relative">
                                <div class="block sm:hidden absolute top-5 right-5 mb-2">
                                    <input type="checkbox" :value="{{ $part->id }}"
                                           @click="togglePartSelection({{ $part->id }})"
                                           :checked="selectedParts.includes({{ $part->id }})"
                                           class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="checkbox-table-search-{{ $part->id }}" class="sr-only">checkbox</label>
                                </div>
                                <div class="hidden sm:block md:w-1/12 mb-0">
                                    <input type="checkbox" :value="{{ $part->id }}"
                                           @click="togglePartSelection({{ $part->id }})"
                                           :checked="selectedParts.includes({{ $part->id }})"
                                           class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="checkbox-table-search-{{ $part->id }}" class="sr-only">checkbox</label>
                                </div>
                                <!-- SKU -->
                                <div class="w-full md:w-1/12 mb-2 md:mb-0">
                                    <span class="md:hidden font-semibold">SKU:</span> {{ $part->sku }}
                                </div>
                                <!-- Name -->
                                <div x-data="{ 
                                    showPnPopover: false, 
                                    showEditMenu: false,
                                    showAddMenu: false,
                                    editingName: false, 
                                    editingPn: false,
                                    addingPn: false, 
                                    newName: '{{ $part->name }}',
                                    originalName: '{{ $part->name }}',
                                    selectedPns: @js($part->pns ? $part->pns->pluck('number')->toArray() : []),
                                    availablePns: Object.keys(@entangle('availablePns') || {}).length ? @entangle('availablePns') : {},
                                    searchPn: '',
                                    errorMessage: '', 
                                    newPn: '', 
                                    savePn() {
                                        if (!this.newPn.trim()) {
                                            this.errorMessage = 'PN cannot be empty.';
                                            return;
                                        }
                                        // Проверяем на дублирование
                                        if (this.selectedPns.includes(this.newPn)) {
                                            this.errorMessage = 'PN already exists.';
                                            return;
                                        }
                                        this.errorMessage = '';
                                        this.selectedPns.push(this.newPn);
                                        this.newPn = '';
                                        addingPn = false;
                                    }
                                }" 
                                class="w-full md:w-2/12 mb-2 md:mb-0 flex items-center relative">
                                    <span class="md:hidden font-semibold">Name:</span>

                                    <!-- Синий кружок для PN -->
                                    <div class="w-6 h-6 flex items-center justify-center bg-blue-500 text-white rounded-full cursor-pointer mr-2"
                                        @click="showPnPopover = !showPnPopover">
                                        PN
                                    </div>

                                    <!-- Поповер для PN -->
                                    <div x-show="showPnPopover"
                                        class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                        @click.away="showPnPopover = false" 
                                        x-cloak>
                                        <h4 class="text-gray-700 dark:text-gray-400 text-sm font-semibold mb-2">Part Numbers</h4>
                                        <ul>
                                        @if(!is_null($part->pns))
                                        @if($part->pns->isNotEmpty())
                                        <!-- Если массив не пуст -->
                                            @foreach ($part->pns as $pn)
                                                <li class="text-gray-600 text-sm mb-1"><span>{{ $pn->number }}</span></li>
                                            @endforeach
                                        @else
                                            <li class="text-gray-600 text-sm mb-1"><span>No PN's</span></li>
                                        @endif
                                        @endif
                                        </ul>
                                    </div>

                                    <!-- Название с подменю -->
                                    <div class="flex items-center w-full relative">
                                        <!-- Оверлей -->
                                        <div x-show="editingName || editingPn || addingPn" 
                                            class="fixed inset-0 bg-black bg-opacity-50 z-40" 
                                            @click="editingName = false; editingPn = false; showEditMenu = false;" 
                                            x-cloak>
                                        </div>

                                        <!-- Основное отображение -->
                                        <span x-show="!editingName && !editingPn" 
                                            @click="showEditMenu = !showEditMenu" 
                                            class="flex items-center cursor-pointer hover:underline min-h-[30px]">
                                            {{ $part->name }}
                                        </span>

                                        <!-- Подменю -->
                                        <div x-show="showEditMenu" 
                                            class="absolute flex flex-row w-full z-50 bg-white dark:bg-gray-800 text-xs border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1" 
                                            @click.away="showEditMenu = false" 
                                            x-cloak>
                                            <div class="flex flex-row w-full">
                                                <div @click="addingPn = true; showAddMenu = false;" class="w-1/3 p-2 cursor-pointer hover:bg-gray-100 hover:text-black">
                                                    Add PN
                                                </div>
                                                <div @click="editingPn = true; showEditMenu = false;" class="w-1/3 p-2 cursor-pointer hover:bg-gray-100 hover:text-black">
                                                    Edit PN
                                                </div>
                                                <div @click="editingName = true; showEditMenu = false;" class="w-1/3 p-2 cursor-pointer hover:bg-gray-100 hover:text-black">
                                                    Edit Name
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Режим редактирования PN -->
                                    <div x-show="editingPn"
                                        class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                        x-cloak>
                                        <h4 class="text-gray-700 dark:text-gray-400 text-sm font-semibold mb-2">Edit Part Numbers</h4>
                                        <input type="text" placeholder="Search PN's..." x-model="searchPn"
                                            class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        <div class="flex flex-row justify-between items-center">
                                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-40 overflow-y-auto">
                                                <!-- Если список отфильтрованных PNs пуст -->
                                                <template x-if="availablePns.filter(pn => pn.toLowerCase().includes(searchPn.toLowerCase())).length === 0">
                                                    <li class="text-gray-600 text-sm mb-1">No PN's</li>
                                                </template>

                                                <!-- Если список отфильтрованных PNs не пуст -->
                                                <template x-for="pn in availablePns.filter(pn => pn.toLowerCase().includes(searchPn.toLowerCase()))" :key="pn">
                                                    <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                                        <input type="checkbox" :value="pn" x-model="selectedPns" class="mr-2">
                                                        <span x-text="pn"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                            <div class="flex justify-end">
                                                <button @click="$wire.savePns({{ $part->id }}, selectedPns); editingPn = false;"
                                                    class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4 w-[28px]">
                                                ✓
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                        <!-- Модальное окно для добавления нового PN -->
                                        <div x-show="addingPn" 
                                            class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                            x-cloak @click.away="addingPn = false" >
                                            <h4 class="text-gray-700 text-sm font-semibold mb-2">Add Part Number</h4>
                                            
                                            <!-- Поле ввода -->
                                            <input type="text" x-model="newPn" placeholder="Enter new PN"
                                                class="w-full border border-gray-300 rounded-md mb-2 px-2 py-1 text-sm">
                                            
                                            <!-- Ошибка -->
                                            <div x-show="errorMessage" class="text-red-500 text-sm mb-2" x-text="errorMessage"></div>
                                            
                                            <!-- Кнопки действия -->
                                            <div class="flex justify-end space-x-2">
                                                <button @click="addingPn = false; newPn = ''; errorMessage = '';"
                                                    class="px-3 py-1 text-sm text-gray-700 bg-white border rounded hover:bg-gray-100">
                                                    Cancel
                                                </button>
                                                <button @click="
                                                    if (newPn !== '') {
                                                    selectedPns.push(newPn);
                                                    $wire.savePns({{ $part->id }}, selectedPns).then(() => {
                                                        newPn = '';
                                                        addingPn = false;
                                                    });
                                                }
                                                " class="px-3 py-1 text-sm text-white bg-blue-500 rounded hover:bg-blue-600">
                                                    Save
                                                </button>
                                            </div>
                                        </div>

                                    <!-- Режим редактирования Name -->
                                    <div x-show="editingName" 
                                        class="flex justify-center items-center w-full relative z-50" 
                                        x-cloak>
                                        <input type="text" x-model="newName" 
                                            class="border border-gray-300 rounded-md text-sm px-2 py-1 w-[180px] mr-2"
                                            @keydown.enter="if (newName !== originalName) { $wire.updateName({{ $part->id }}, newName); originalName = newName; } editingName = false;"
                                            @keydown.escape="editingName = false">
                                        <button @click="if (newName !== originalName) { $wire.updateName({{ $part->id }}, newName); originalName = newName; } editingName = false;" 
                                                class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4 w-[28px]">
                                            ✓
                                        </button>
                                    </div>
                                </div>
                                <!-- Quantity -->
                                <div class="w-full md:w-1/12 mb-2 md:mb-0">
                                    <span class="md:hidden font-semibold">Quantity:</span> {{ $part->quantity }}
                                </div>

                                <div class="w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer relative parent-container"
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
                                            <div x-show="editing" class="flex justify-center items-center" x-transition>
                                                <input type="number" x-ref="priceInput" x-model="newPrice"
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
                                <div class="w-full md:w-1/12 mb-2 md:mb-0"><span class="md:hidden font-semibold">Total:</span>${{ $part->total }}</div>
                                <div class="flex flex-row justify-start space-x-3 w-full md:w-1/12 mb-2 md:mb-0">
                                    <!-- Миниатюра -->
                                    <span class="md:hidden font-semibold">Image:</span>
                                    <div x-data class="gallery h-12 w-12">
                                        <img
                                            src="{{ $part->image }}" alt="{{ $part->name }}"
                                            @click="$dispatch('lightbox', '{{ $part->image }}')"
                                            @click.stop class="object-cover rounded cursor-zoom-in">
                                    </div>
                                </div>
                                <div id="brand-item-{{ $part->id }}" class="w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer relative parent-container"
                                    x-data="{ showPopover: false, selectedBrands: @json($part->brands->pluck('id')), search: '', popoverX: 0, popoverY: 0 }"

                                    @click.away="showPopover = false"
                                    @mousedown.stop
                                    @click="
                                            $nextTick(() => {
                                                const parent = $el.closest('.parent-container');
                                                const elementOffsetLeft = $el.offsetLeft;
                                                const elementOffsetTop = $el.offsetTop;

                                                popoverX = elementOffsetLeft / parent.offsetWidth ;
                                                popoverY = elementOffsetTop / parent.offsetHeight ;

                                                showPopover = true;
                                            });
                                        ">

                                    <!-- Текущие бренды -->
                                    <div>
                                        <span class="md:hidden font-semibold">Brand:</span>
                                        @if(count($part->brands) == 0)
                                            <div class="px-3 py-2">---</div>
                                        @else
                                            @foreach($part->brands as $brand)
                                                <span>{{ $brand->name }}{{ !$loop->last ? ', ' : '' }}</span>
                                            @endforeach
                                        @endif
                                    </div>

                                    <!-- Поповер с мульти-выбором брендов -->
                                    <div x-show="showPopover"
                                        class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                        :style="'top: ' + popoverY + 'px; left: ' + popoverX + 'px;'"
                                        @click.outside="showPopover = false"
                                        x-transition>

                                        <!-- Поле поиска -->
                                        <div class="mb-2" @click.stop>
                                            <input type="text" x-model="search"
                                                placeholder="Search brands..."
                                                class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:bg-gray-700 dark:text-gray-300"/>
                                        </div>

                                        <div class="flex flex-row justify-between items-center">
                                            <!-- Список брендов с мульти-выбором -->
                                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 max-h-40 overflow-y-auto">
                                                @foreach ($brands as $brand)
                                                    <template x-if="!search || '{{ strtolower($brand->name) }}'.includes(search.toLowerCase())">
                                                        <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                                            <input type="checkbox" value="{{ $brand->id }}" x-model="selectedBrands"
                                                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" @click.stop>
                                                            <label class="ml-2">{{ $brand->name }}</label>
                                                        </li>
                                                    </template>
                                                @endforeach
                                            </ul>

                                            <!-- Кнопка подтверждения -->
                                            <div class="flex justify-end">
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

                                <div class="w-full md:w-2/12 mb-2 md:mb-0 cursor-pointer font-semibold" x-data="{ clickCount: 0 }"
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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15.232 5.232l3.536 3.536M9 13h.01M6 9l5 5-3 3h6l-1.293-1.293a1 1 0 010-1.414l7.42-7.42a2.828 2.828 0 10-4-4l-7.42 7.42a1 1 0 01-1.414 0L6 9z"/>
                                        </svg>
                                    </span>
                                    @endif
                                </div>
                                <div class="flex flex-col w-full md:w-2/12 flex">
                                    <!-- Кнопки действий -->
                                    <div class="flex flex-row w-full"><span class="md:hidden font-semibold">Actions:</span></div>
                                    <div class="flex flex-row w-full justify-evenly">
                                        <button wire:click="incrementPart({{ $part->id }})" @click.stop title="Add one"
                                                class="w-10 h-10 md:w-8 md:h-8 bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded-md hover:bg-green-800">
                                            +
                                        </button>
                                        <button wire:click="openQuantityModal({{ $part->id }}, 'add')" @click.stop title="Add some"
                                                class="w-10 h-10 md:w-8 md:h-8 bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded-md hover:bg-green-800">
                                            ++
                                        </button>
                                    </div>
                                    <hr class="w-full h-px mx-auto my-2 bg-gray-100 border-0 rounded md:my-2 dark:bg-gray-700">
                                    <div class="flex flex-row w-full justify-evenly">
                                        <button wire:click="decrementPart({{ $part->id }})" @click.stop title="Remove one"
                                                class="w-10 h-10 md:w-8 md:h-8 bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-md bg-red-800">
                                            -
                                        </button>
                                        <button wire:click="openQuantityModal({{ $part->id }}, 'subtract')" @click.stop title="Remove some"
                                                class="w-10 h-10 md:w-8 md:h-8 bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-md bg-red-800">
                                            --
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="text-sm text-center bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                No spare parts available
                            </div>
                        @endforelse
                    </div>
                </div>

                <div x-data="{ transferPartsModalOpen: false }" x-bind:class="{ 'overflow-hidden': transferPartsModalOpen }">

                    <button
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50"
                        @click="openModal" x-show="selectedParts.length > 0"
                    >
                        Send part
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
                        <div class="relative bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-md w-full p-6">
                            <!-- Заголовок модального окна -->
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Выбранные запчасти</h3>
                                <button @click="closeModal"
                                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                        aria-label="Close">
                                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
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
                                            <label>Запчасть #<span x-text="partId"></span> (Доступно: <span
                                                    x-text="partStock[partId]"></span>)</label>
                                            <input id="quantity" type="number" min="1" :max="partStock[partId]"
                                                class="input mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="Количество" x-model="partQuantities[partId]"
                                                @input="limitQuantity(partId)">
                                        </div>
                                    </template>

                                    <div
                                        class="relative w-full text-gray-500"
                                    >
                                        <label for="technician"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Техник</label>

                                        <!-- Поле выбора техников для передачи запчастей -->
                                        <div @click="open = !open" class="w-full cursor-pointer bg-white border border-gray-300 rounded-lg shadow-sm p-2 flex justify-between items-center text-gray-500">
                                            <span x-text="selectedTechnicians.length > 0 ? selectedTechnicians.length + ' selected' : 'Select Technicians'"></span>
                                            <svg class="h-5 w-5 text-gray-400 transform transition-transform" :class="{'rotate-180': open}"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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
                                                        <input type="checkbox" value="{{ $technician->id }}" x-model="selectedTechnicians"
                                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                        <label class="ml-2 text-gray-700">{{ $technician->name }}</label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Кнопки действия -->
                                <div class="flex items-center justify-end mt-6 space-x-4">
                                    <button type="button" @click="closeModal, transferPartsModalOpen = false; document.body.classList.remove('overflow-hidden')"
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
            </div>
        </div>

        <!-- Модальное окно для добавления/уменьшения количества -->
        @if($showQuantityModal)
            <div class="fixed z-10 inset-0 overflow-y-auto" x-data @click.away="$wire.resetQuantityModal()">
                <div class="flex items-center justify-center min-h-screen">
                    <div class="flex flex-col justify-center items-center border border-gray-400 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 relative">
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
                        <h2 id="modal-title" class="text-xl font-semibold text-gray-900 dark:text-white">Price Change
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

        <!-- Модальное окно показа большого изображения -->
        <div>
            @if ($fullImage)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
                    <div class="w-6/12 h-6/12 flex items-center justify-center">
                        <div id="image-container"
                             class="relative z-55 bg-white p-6 rounded-lg shadow-lg max-w-6/12 max-h-6/12 overflow-hidden">
                            <!-- Изображение -->
                            <img src="{{ $fullImage }}" class="object-contain max-w-full max-h-full">

                            <!-- Кнопки закрытия и управления -->
                            <div class="absolute top-4 right-4 flex space-x-2">
                                <button wire:click="closeImage"
                                        class="text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md">
                                    Close
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Модальное окно для редактирования URL -->
        @if($managerPartUrlModalVisible)
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded shadow-md w-1/3">
                    <h2 class="text-xl font-semibold mb-4">Редактировать ссылку</h2>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="selectedSupplier">Supplier:</label>
                        <select wire:model="managerPartSupplier" id="selectedSupplier"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->name }}">{{ $supplier->name }}</option>
                            @endforeach
                       </select>
                       @error('selectedSupplier') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="managerPartUrl">URL:</label>
                        <input wire:model="managerPartUrl" type="text" id="managerPartUrl"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="$set('managerPartUrlModalVisible', false)"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Отмена
                        </button>
                        <button wire:click="saveManagerPartUrl"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">OK
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
    @endif
</div>
