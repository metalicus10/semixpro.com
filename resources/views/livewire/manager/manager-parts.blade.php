<div class="p-8 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden" id="app">
    <!-- Сообщения об успехе -->
    @if (session()->has('error'))
        <div
            class="bg-red-500 text-white text-center p-4 rounded-lg mb-6 transition-opacity duration-1000 z-50 absolute top-1/6 w-1/2 align-middle"
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3500)"
            x-show="show"
            x-transition:enter="opacity-0"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="opacity-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{ session('error') }}
        </div>
    @endif
    <!-- Сообщения предупреждение -->
    @if (session()->has('warning'))
        <div
            class="bg-yellow-500 text-white p-4 rounded-lg mb-6 transition-opacity duration-1000"
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3500)"
            x-show="show"
            x-transition:enter="opacity-0"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="opacity-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{ session('warning') }}
        </div>
    @endif
    <!-- Сообщения об успехе -->
    @if (session()->has('message'))
        <div
            class="bg-green-500 text-white p-4 rounded-lg mb-6 transition-opacity duration-1000"
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3500)"
            x-show="show"
            x-transition:enter="opacity-0"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="opacity-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{ session('message') }}
        </div>
    @endif

    <!-- Заголовок страницы -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Parts</h1>

        <!-- Фильтр по категориям -->
        <div>
            <label for="category" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Cat:</label>
            <select wire:model.live="selectedCategory" id="category"
                    class="ml-2 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All cats</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

        </div>
        <!-- Фильтр по брендам -->
        <div>
            <label for="brand" class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by Brand:</label>
            <select wire:model.live="selectedBrand" id="brand"
                    class="ml-2 p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All brands</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>

        </div>
    </div>

    <livewire:manager-part-form/>

    <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

    <!-- Таблица с запчастями -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <!-- Поле поиска -->
        <div class="pb-4 bg-white dark:bg-gray-900">
            <label for="table-search" class="sr-only">Поиск</label>
            <div class="relative">
                <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input type="text" id="table-search" wire:model.live="search"
                       class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                       placeholder="Поиск по запчастям...">
            </div>
        </div>

        <!-- Таблица -->
        <div x-data="{
                selectedParts: [],
                partQuantities: {},
                partStock: @js($parts->pluck('quantity', 'id')),
                modalOpen: false,
                selectedTechnician: @entangle('selectedTechnician').defer,
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
                        this.modalOpen = true;
                    }
                },
                closeModal() {
                    this.modalOpen = false;
                },
                isSendButtonEnabled() {
                    return this.selectedTechnician &&
                        this.selectedParts.every(partId =>
                            this.partQuantities[partId] > 0 &&
                            this.partQuantities[partId] <= this.partStock[partId]
                        );
                },
                limitQuantity(partId) {
                    if (this.partQuantities[partId] > this.partStock[partId]) {
                        this.partQuantities[partId] = this.partStock[partId];
                    }
                    $dispatch('update-part-quantities', { quantities: this.partQuantities });
                }
            }"
             @keydown.escape="closeModal"
             class="overflow-hidden w-full overflow-x-auto rounded-md border border-neutral-300 dark:border-neutral-500"
        >
            <table id="parts-table" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center">
                            <input type="checkbox" @click="toggleCheckAll($event)" :checked="selectedParts.length === @json($parts->count())"
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            >
                            <label for="checkbox-all-search" class="sr-only">checkbox</label>
                        </div>
                    </th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">SKU</th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">Part name</th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">Quantity</th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">Price</th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">Category</th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">Brand</th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">Image</th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">Actions</th>
                </tr>
                </thead>

                <!-- Подключаем Livewire с Alpine -->
                <tbody>

                @forelse ($parts as $part)

                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033]"

                    >
                        <td class="w-4 p-4 w-32 truncate whitespace-nowrap overflow-hidden">
                            <div class="flex items-center">
                                <input type="checkbox"
                                       :value="{{ $part->id }}" @click="togglePartSelection({{ $part->id }})"
                                       :checked="selectedParts.includes({{ $part->id }})"
                                       class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                >
                                <label for="checkbox-table-search-{{ $part->id }}" class="sr-only">checkbox</label>
                            </div>
                        </td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">{{ $part->sku }}</td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">{{ $part->name }}</td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">{{ $part->quantity }}</td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">
                            <a data-popover-target="{{ $part->id }}" data-popover-trigger="click" type="button" class="cursor-pointer text-sm text-blue-600 hover:underline dark:text-blue-400">{{ $part->price }}</a>

                            <div data-popover id="{{ $part->id }}" role="tooltip" class="absolute z-10 invisible inline-block w-32 text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-sm opacity-0 dark:text-gray-400 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 py-2">
                                    <button wire:click="updatePartPrice({{ $part->id }})"
                                            class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                        Edit
                                    </button>
                                    <!-- Кнопка для открытия истории цен -->
                                    <button @click="$dispatch('open-price-modal', { partId: {{ $part->id }} })"
                                            class="w-1/2 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                                        History
                                    </button>
                                </div>
                                <div data-popper-arrow ></div>
                            </div>
                        </td>

                        @if(!empty($part->category))
                            <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">{{ $part->category->name }}</td>
                        @else
                            <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden"></td>
                        @endif
                        @if(!empty($part->brands))
                            <td class="px-5 py-5 truncate whitespace-nowrap overflow-hidden flex flex-col">
                                @foreach($part->brands as $brand)
                                    <span>{{ $brand->name }}{{ !$loop->last ? ', ' : '' }}</span>
                                @endforeach
                            </td>
                        @else
                            <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden"></td>
                        @endif

                        <td class="px-5 py-5">
                            <!-- Миниатюра -->
                            <div x-data class="gallery h-12 w-12">
                                <img src="@if ($part->image == null) @else {{ Storage::disk('s3')->url($transfer->part->image) }} @endif" alt="{{ $part->name }}"
                                     @click="$dispatch('lightbox', '@if ($part->image === null) @click.stop @endif')"
                                     @click.stop
                                     class="object-cover rounded cursor-zoom-in">
                            </div>
                        </td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">
                            <!-- Кнопка для увеличения количества на 1 -->
                            <button wire:click="incrementPart({{ $part->id }})" @click.stop
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded-full">
                                +
                            </button>

                            <!-- Кнопка для уменьшения количества на 1 -->
                            <button wire:click="decrementPart({{ $part->id }})" @click.stop
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-full ml-2">
                                -
                            </button>

                            <!-- Кнопка для открытия модального окна для увеличения количества -->
                            <button wire:click="openQuantityModal({{ $part->id }}, 'add')" @click.stop
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-2 rounded-full ml-2">
                                ++
                            </button>

                            <!-- Кнопка для открытия модального окна для уменьшения количества -->
                            <button wire:click="openQuantityModal({{ $part->id }}, 'subtract')" @click.stop
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-full ml-2">
                                --
                            </button>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="9"
                            class="px-5 py-5 text-sm text-center bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            No spare parts available
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <button
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50"
                @click="openModal" x-show="selectedParts.length > 0"
            >
                Send part
            </button>

            <!-- Flowbite-стилизованное модальное окно -->
            <div x-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50" style="display: none;">
                <div class="relative bg-white rounded-lg shadow-lg dark:bg-gray-800 max-w-md w-full p-6">
                    <!-- Заголовок модального окна -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Выбранные запчасти</h3>
                        <button @click="closeModal" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" aria-label="Close">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Содержимое модального окна -->
                    <form wire:submit.prevent="sendParts">
                        <div class="space-y-4">
                            <template x-for="partId in selectedParts" :key="partId">
                                <div class="mb-2">
                                    <label>Запчасть #<span x-text="partId"></span> (Доступно: <span x-text="partStock[partId]"></span>)</label>
                                    <input id="quantity" type="number" min="1" :max="partStock[partId]" class="input mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                           placeholder="Количество" x-model="partQuantities[partId]" @input="limitQuantity(partId)">
                                </div>
                            </template>

                            <!-- Выбор техника -->
                            <div>
                                <label for="technician" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Техник</label>
                                <select id="technician" x-model="selectedTechnician" wire:model="selectedTechnician" class="block w-full p-2 mt-1 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Выберите техника</option>
                                    @foreach ($technicians as $technician)
                                        <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-gray-500">Выбранный техник: <span x-text="selectedTechnician"></span></p>
                            </div>
                        </div>

                        <!-- Кнопки действия -->
                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg dark:text-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">Отменить</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-800" :disabled="!isSendButtonEnabled()">Подтвердить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для добавления/уменьшения количества -->
    @if($showQuantityModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" x-data @click.away="$wire.resetQuantityModal()">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 relative">
                    <!-- Кнопка закрытия -->
                    <button @click="$wire.resetQuantityModal()"
                            class="absolute top-0 right-0 mt-2 mr-2 text-gray-400 hover:text-gray-600">
                        &times;
                    </button>

                    <h3 class="text-lg font-semibold mb-4">
                        @if($operation === 'add')
                            Enter quantity to add
                        @else
                            Enter quantity to remove
                        @endif
                    </h3>

                    <!-- Сообщение об ошибке -->
                    @if($errorMessage)
                        <div class="bg-red-500 text-white p-2 rounded mb-4">
                            {{ $errorMessage }}
                        </div>
                    @endif

                    <input type="number" wire:model="quantityToAdd" min="1"
                           class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg p-2 text-sm text-gray-900 dark:text-white"/>

                    <div class="mt-4 flex justify-end">
                        <button wire:click="modifyQuantity"
                                class="@if($operation === 'add') bg-green-500 hover:bg-green-600 @else bg-red-500 hover:bg-red-600 @endif text-white font-bold py-2 px-4 rounded-full">
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
                    <h2 id="modal-title" class="text-xl font-semibold text-gray-900 dark:text-white">Price Change History</h2>
                    <button wire:click="closePriceModal"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            aria-label="Close">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414 1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <!-- Содержимое модального окна -->
                <div class="overflow-y-auto max-h-96">
                    <livewire:part-price-history :part-id="$selectedPartId" />
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
