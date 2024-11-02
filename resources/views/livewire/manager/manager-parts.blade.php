<div class="p-8 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden" id="app">
    <!-- Сообщения об ошибке -->
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
                selected: @entangle('selectedRows').defer || [],
                checkAll: false,
                isSendButtonDisabled: @entangle('isSendButtonDisabled'),
                itemsCount: {{ count($parts) }},
                toggleAll() {
                    // Переключаем все чекбоксы на основе checkAll
                    this.checkAll = !this.checkAll;
                    this.selected = this.checkAll ? [...document.querySelectorAll('.row-checkbox')].map(cb => cb.value) : [];
                }
            }"
             x-init="
                $watch('selected', value => {
                    checkAll = value.length === itemsCount;
                })
            "
             class="overflow-hidden w-full overflow-x-auto rounded-md border border-neutral-300 dark:border-neutral-500">
            <table id="parts-table" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center">
                            <input type="checkbox" x-model="checkAll" @click="toggleAll"
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            >
                            <label for="checkbox-all-search" class="sr-only">checkbox</label>
                        </div>
                    </th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">Part name</th>
                    <th scope="col" class="px-5 py-3 w-32 truncate whitespace-nowrap overflow-hidden">SKU</th>
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
                                       x-bind:value="{{ $part->id }}"
                                       x-model="selected"
                                       class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                >
                                <label for="checkbox-table-search-{{ $part->id }}" class="sr-only">checkbox</label>
                            </div>
                        </td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">{{ $part->name }}</td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">{{ $part->sku }}</td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">{{ $part->quantity }}</td>
                        <td class="px-5 py-5 w-32 truncate whitespace-nowrap overflow-hidden">{{ $part->price }}</td>

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
                                <img src="@if ($part->image == null) @endif" alt="{{ $part->name }}"
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
                        <td colspan="8"
                            class="px-5 py-5 text-sm text-center bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            No spare parts available
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <button
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50"
                @click="if(Array.isArray(selected)) $dispatch('triggerOpenModal', selected)"
                x-bind:disabled="!selected || selected.length === 0"
            >
                Send part
            </button>
        </div>
    </div>

    <!-- Передать запчасть -->
    <div x-data="{ isOpen: @entangle('isOpen').defer, isSendButtonDisabled: @entangle('isSendButtonDisabled').defer }">
        <div x-show="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="bg-white rounded-lg p-8 max-w-lg w-full">
                <h2 class="text-xl font-bold mb-4">Transfer of spare parts to the technician</h2>

                <form wire:submit.prevent="transferSelectedParts">
                    @foreach ($selectedRows as $partId)
                        @php
                            $part = \App\Models\Part::find($partId);
                        @endphp
                        <div class="mb-4">
                            <label for="quantity-{{ $partId }}" class="block text-sm font-medium text-gray-700">
                                {{ $part->name }} ({{ $part->quantity }} in stock)
                            </label>
                            <input
                                type="number"
                                id="quantity-{{ $partId }}"
                                wire:model.defer="transferQuantities.{{ $partId }}"
                                class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                min="1"
                                max="{{ $part->quantity }}"
                            >
                        </div>
                    @endforeach
                    <div class="mb-4">
                        <label for="technician" class="block text-sm font-medium text-gray-700">Technician</label>
                        <select wire:model.defer="technicianId" id="technician"
                                class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="">Select a technician</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50"
                        x-bind:disabled="isSendButtonDisabled"
                    >
                        Send
                    </button>
                    <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
                            wire:click="closeModal"
                    >
                        Cancel
                    </button>
                </form>
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

    <div x-data="{ open: @entangle('isPriceHistoryModalOpen') }">
        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.away="open = false" class="bg-white dark:bg-gray-800 rounded-lg p-4 max-w-lg w-full mx-4">
                <button @click="open = false"
                        class="float-right text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">&times;
                </button>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Price change history</h2>
                <livewire:part-price-history :part-id="$selectedPartId"/>
            </div>
        </div>
    </div>

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
