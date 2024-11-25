<div class="p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-600 dark:text-gray-300 mb-6">Manage Warehouses</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Левая колонка -->
        <div class="space-y-8">
            <!-- Создание нового склада -->
            <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow">
                <h3 class="font-semibold text-lg text-gray-600 dark:text-gray-300 mb-4">Create Warehouse</h3>
                <input type="text" wire:model="newWarehouseName" placeholder="Warehouse Name"
                       class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 mb-4">
                @if ($errorMessage)
                    <div class="text-red-500 text-sm mb-2">{{ $errorMessage }}</div>
                @endif
                <button wire:click="createWarehouse"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                    Create Warehouse
                </button>
            </div>

            <!-- Список складов -->
            <div x-data="{ deleteWarehouseId: null, showDeleteModal: false }" class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow max-h-128 overflow-y-auto">
                <h3 class="font-semibold text-lg text-gray-600 dark:text-gray-300 mb-4">Manage Warehouses</h3>

                <!-- Список складов -->
                <ul class="space-y-2">
                    @foreach($warehouses as $warehouse)
                        <li class="flex justify-between items-center py-2 px-4 rounded-lg border
                            @if($warehouse->is_default) bg-orange-500 text-white dark:bg-orange-600 border-orange-600
                            @else text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-700 @endif"
                            wire:click="setDefaultWarehouse({{ $warehouse->id }})"
                        >
                            <!-- Название склада -->
                            <span>{{ $warehouse->name }}</span>

                            <!-- Кнопка удаления -->
                            @if($warehouse->name !== 'No warehouse')
                            <button @click="deleteWarehouseId = {{ $warehouse->id }}; showDeleteModal = true;"
                                    class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-700">
                                Delete
                            </button>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <!-- Модальное окно подтверждения -->
                <div x-show="showDeleteModal" x-transition class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" style="display: none;">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-sm w-full">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">Are you sure?</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">This will delete the selected warehouse and cannot be undone.</p>

                        <div class="flex justify-end space-x-4">
                            <button @click="showDeleteModal = false; deleteWarehouseId = null;" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300">
                                Cancel
                            </button>
                            <button @click="$wire.deleteWarehouse(deleteWarehouseId).then(() => { showDeleteModal = false; deleteWarehouseId = null; })"
                                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Правая колонка -->
        <div x-data="{
                open: false,
                selectedParts: @entangle('partToMove').defer || [],
                search: '',
                resetSelectedParts() {
                    this.selectedParts = [];
                    this.search = '';
                }
             }"
             x-init="$watch('selectedParts', value => $wire.set('partToMove', value))"
             @reset-selected-parts.window="resetSelectedParts()"
             class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow space-y-4">
            <h3 class="font-semibold text-lg text-gray-600 dark:text-gray-300">Move Part</h3>

            <!-- Выпадающий список для выбора запчастей -->
            <div class="relative">
                <div @click="open = !open"
                     class="cursor-pointer bg-white border border-gray-300 rounded-lg shadow-sm p-2 flex justify-between items-center">
                    <span
                        x-text="selectedParts.length > 0 ? selectedParts.length + ' selected' : 'Select Parts'"></span>
                    <svg class="h-5 w-5 text-gray-400 transform transition-transform" :class="{'rotate-180': open}"
                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M5.23 7.21a.75.75 0 011.06-.02L10 10.879l3.72-3.67a.75.75 0 111.04 1.08l-4.25 4.2a.75.75 0 01-1.06 0l-4.25-4.2a.75.75 0 01-.02-1.06z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute z-50 mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                    <div class="p-2">
                        <input type="text" x-model="search"
                               placeholder="Search parts..."
                               class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <ul class="py-1 text-sm">
                        @foreach ($parts as $part)
                            <template x-if="!search || '{{ strtolower($part->name) }}'.includes(search.toLowerCase())">
                                <li class="flex justify-between items-center px-4 py-2 cursor-pointer hover:bg-gray-100">
                                    <div>
                                        <input type="checkbox" value="{{ $part->id }}" x-model="selectedParts"
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label class="ml-2">
                                            {{ $part->name }}
                                        </label>
                                    </div>
                                    <div>
                                        @if($part->warehouse)
                                            <span class="text-gray-500 text-xs">{{ $part->warehouse->name }}</span>
                                        @else
                                            <span class="text-gray-500 text-xs">No warehouse</span>
                                        @endif
                                    </div>
                                </li>
                            </template>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Склад назначения -->
            <select wire:model="destinationWarehouse"
                    class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                <option value="">Select Destination Warehouse</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>

            <!-- Кнопка перемещения -->
            <button wire:click="movePart" class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Move Part
            </button>
        </div>
    </div>

    <!-- Подключение удалённого склада -->
    <div class="mt-8 text-center">
        <button class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Connect Remote Warehouse (Future Integration)
        </button>
    </div>
</div>
