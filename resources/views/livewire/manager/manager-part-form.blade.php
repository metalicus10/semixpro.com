<div>
    <!-- Кнопки для открытия модальных окон -->
    <div class="mb-1 w-full md:w-auto">
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
            <div class="max-w-4xl mx-auto shadow-md p-4 bg-white rounded-lg" x-data @click.away="$wire.closeModal()">

                <!-- Кнопка закрытия (крестик) в правом верхнем углу -->
                <button wire:click="closeModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <h2 class="text-xl font-bold mb-4">Add Part</h2>

                <div class="grid grid-cols-2 gap-4 text-gray-500 dark:text-gray-400 mb-4">
                    <!-- Левая колонка -->
                    <div class="flex flex-col space-y-4">
                        <!-- Выбор номенклатуры -->
                        <div>
                            <select wire:model="selectedNomenclature"
                                    class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-500">
                                <option value="">Select Nomenclature</option>
                                @foreach ($nomenclatures as $nomenclature)
                                    <option value="{{ $nomenclature->id }}">{{ $nomenclature->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedNomenclature') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Выбор склада -->
                        <div>
                            <select wire:model="selectedWarehouse"
                                    class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-500">
                                <option value="">Select Warehouse</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedWarehouse') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Выбор категории -->
                        <div>
                            <select wire:model="selectedCategory"
                                    class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-500">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedCategory') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Выбор бренда -->
                        <div x-data="{ open: false, selectedBrands: @entangle('selectedBrands').defer || [] }"
                             x-init="$watch('selectedBrands', value => $wire.set('selectedBrands', value))"
                             class="relative text-gray-500"
                        >

                            <!-- Поле ввода брендов, показывающее количество выбранных брендов или их список -->
                            <div @click="open = !open"
                                 class="w-full cursor-pointer bg-white border border-gray-300 rounded-lg shadow-sm p-2 flex justify-between items-center text-gray-500">
                            <span
                                x-text="selectedBrands.length > 0 ? selectedBrands.length + ' selected' : 'Select Brands'"></span>
                                <svg class="h-5 w-5 text-gray-400 transform transition-transform"
                                     :class="{'rotate-180': open}"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.23 7.21a.75.75 0 011.06-.02L10 10.879l3.72-3.67a.75.75 0 111.04 1.08l-4.25 4.2a.75.75 0 01-1.06 0l-4.25-4.2a.75.75 0 01-.02-1.06z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </div>

                            <!-- Выпадающий список с мульти-выбором брендов -->
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute z-10 mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto order-9">
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
                    </div>
                    <!-- Правая колонка -->
                    <div class="flex flex-col space-y-4 text-gray-500">
                        <!-- Ввод артикула -->
                        <div>
                            <input type="text" wire:model="sku"
                                   class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="SKU">
                            @error('sku') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Дополнительные поля: Бренды, изображение и URL -->
                        <!-- Ввод наименования -->
                        <div>
                            <input type="text" wire:model="partName" id="floating_part_name"
                                   class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Part Name">
                            <label for="floating_part_name" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Part Name</label>
                            @error('partName') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <!-- Ввод part number -->
                        <div>
                            <input id="partNumber" type="text" wire:model="pn"
                                   class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Part Number">
                        </div>

                        <!-- Ввод количества -->
                        <div>
                            <input type="number" wire:model="quantity"
                                   class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Quantity">
                            @error('quantity') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Ввод цены -->
                        <div>
                            <input type="text" wire:model="price"
                                   class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Price">
                            @error('price') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-gray-500">
                    <!-- Ввод URL -->
                    <div class="relative z-0">
                        <input type="text" id="url" wire:model.defer="url" class="block py-2.5 px-0 w-full text-sm text-gray-500 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder="">
                        <label for="url" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">URL</label>
                        @error('url') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="relative z-0">
                        <input type="text" id="url_text" wire:model.defer="text" class="block py-2.5 px-0 w-full text-sm text-gray-500 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder="">
                        <label for="url_text" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">URL Text</label>
                        @error('text') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
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
