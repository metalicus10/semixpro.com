<div class="container mx-auto">
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

    <!-- Кнопки для открытия модальных окон -->
    <div class="mb-8">
        <button wire:click="openPartModal" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
            Add new part
        </button>
    </div>

    <!-- Модальное окно для добавления категории -->
    @if ($showCategoryModal)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-gray-800 bg-opacity-50">
            <div class="bg-white rounded-lg p-6 w-96">
                <h2 class="text-xl font-bold mb-4">Add category</h2>

                <input type="text" wire:model="categoryName"
                       class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Название категории">
                @error('categoryName') <span class="text-red-500">{{ $message }}</span> @enderror

                <div class="mt-4 flex justify-end">
                    <button wire:click="closeModal"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mr-4">Cancel
                    </button>
                    <button wire:click="addCategory"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Add
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Модальное окно для добавления запчасти -->
    @if ($showPartModal)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-gray-800 bg-opacity-50">
            <div class="relative bg-white rounded-lg p-6 w-96" x-data @click.away="$wire.closeModal()">

                <!-- Кнопка закрытия (крестик) в правом верхнем углу -->
                <button wire:click="closeModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <h2 class="text-xl font-bold mb-4">Add Part</h2>

                <!-- Шаг 1: Выбор склада -->
                <div class="mb-4">
                    <select wire:model="selectedWarehouse"
                            class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-500">
                        <option value="">Select Warehouse</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedWarehouse') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>          

                <!-- Шаг 1: Выбор категории -->
                <div class="mb-4">
                    <select wire:model="selectedCategory"
                            class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-500">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedCategory') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Шаг 2: Ввод наименования -->
                <div class="mb-4">
                    <input type="text" wire:model="partName"
                           class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Part Name">
                    @error('partName') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Шаг 3: Ввод артикула -->
                <div class="mb-4">
                    <input type="text" wire:model="sku"
                           class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="SKU">
                    @error('sku') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Шаг 4: Ввод количества -->
                <div class="mb-4">
                    <input type="number" wire:model="quantity"
                           class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Quantity">
                    @error('quantity') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Шаг 5: Ввод цены -->
                <div class="mb-4">
                    <input type="text" wire:model="price"
                           class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Price">
                    @error('price') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Дополнительные поля: Бренды, изображение и URL -->
                <div class="mt-4 space-y-4">
                    <!-- Выбор бренда -->
                    <div x-data="{ open: false, selectedBrands: @entangle('selectedBrands').defer || [] }"
                         x-init="$watch('selectedBrands', value => $wire.set('selectedBrands', value))"
                         class="relative w-full text-gray-500"
                    >

                        <!-- Поле ввода брендов, показывающее количество выбранных брендов или их список -->
                        <div @click="open = !open" class="w-full cursor-pointer bg-white border border-gray-300 rounded-lg shadow-sm p-2 flex justify-between items-center text-gray-500">
                            <span x-text="selectedBrands.length > 0 ? selectedBrands.length + ' selected' : 'Select Brands'"></span>
                            <svg class="h-5 w-5 text-gray-400 transform transition-transform" :class="{'rotate-180': open}"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M5.23 7.21a.75.75 0 011.06-.02L10 10.879l3.72-3.67a.75.75 0 111.04 1.08l-4.25 4.2a.75.75 0 01-1.06 0l-4.25-4.2a.75.75 0 01-.02-1.06z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Выпадающий список с мульти-выбором брендов -->
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute z-10 mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <ul class="py-1 text-sm text-gray-700">
                                @foreach ($brands as $brand)
                                    <li class="flex items-center px-4 py-2 cursor-pointer hover:bg-gray-100">
                                        <input type="checkbox" value="{{ $brand->id }}" x-model="selectedBrands"
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label class="ml-2 text-gray-700">{{ $brand->name }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!-- Ввод part number -->
                    <div class="mb-4">
                        <input id="partNumber" type="text" wire:model="pn"
                            class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Part Number">
                    </div>
                    <!-- Кнопка для выбора изображения -->
                    <input type="file" wire:model="image" id="image" accept="image/*"
                           class="border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full">
                    <div wire:loading wire:target="image">Image loading...</div>
                    @error('image') <span class="text-red-500">{{ $message }}</span> @enderror

                    <!-- Предпросмотр загруженного изображения -->
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" class="h-20 w-20 object-cover rounded mt-4">
                    @endif

                    <!-- Ввод URL -->
                    <input type="text" wire:model.defer="url" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="URL">
                    @error('url') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="mt-4 flex justify-between">
                <!-- Кнопка Добавить на последнем шаге -->
                <button wire:click="addPart"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Добавить
                </button>
            </div>
            </div>
        </div>
    @endif

</div>
