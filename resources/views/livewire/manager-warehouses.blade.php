<div class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg">
    <h2 class="md:text-3xl text-md font-bold text-gray-500 dark:text-gray-400 mb-6">Manage Warehouses</h2>

    <!-- Создание нового склада -->
    <div class="mb-4">
        <input type="text" wire:model="newWarehouseName" placeholder="Warehouse Name"
               class="w-full p-2 border border-gray-300 rounded mb-1">
               
        <!-- Сообщение об ошибке -->
        @if ($errorMessage)
            <div class="text-red-500 text-sm mt-1 mb-1">{{ $errorMessage }}</div>
        @endif
    
        <button wire:click="createWarehouse"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
            Create Warehouse
        </button>
    </div>

    <!-- Список складов -->
    <div class="mt-6">
        <h3 class="font-semibold text-lg mb-2 text-gray-500 dark:text-gray-400">Warehouses</h3>
        <ul>
            @foreach($warehouses as $warehouse)
                <li wire:click="setDefaultWarehouse({{ $warehouse->id }})"
                    class="cursor-pointer py-2 px-4 rounded hover:bg-gray-100 dark:hover:bg-gray-700
                           @if($warehouse->is_default) bg-orange-500 text-white dark:bg-orange-600 @else text-gray-400 dark:text-gray-300 @endif">
                    {{ $warehouse->name }}
                </li>
            @endforeach
        </ul>
    </div>    

    <!-- Перемещение запчастей -->
    <div class="relative mt-8">
        <h3 class="font-semibold text-lg mb-2 text-gray-500 dark:text-gray-400">Move Part</h3>
        <div x-data="{ open: false, search: '', selectedParts: @entangle('partToMove').defer || [] }"
            x-init="$watch('selectedParts', value => $wire.set('partToMove', value))"
            class="w-full text-gray-500">
            
            <!-- Поле ввода, показывающее количество выбранных запчастей или их список -->
            <div @click="open = !open" class="w-full cursor-pointer bg-white border border-gray-300 rounded-lg shadow-sm p-2 flex justify-between items-center text-gray-500">
                <span x-text="selectedParts.length > 0 ? selectedParts.length + ' selected' : 'Select Parts'"></span>
                <svg class="h-5 w-5 text-gray-400 transform transition-transform" :class="{'rotate-180': open}"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06-.02L10 10.879l3.72-3.67a.75.75 0 111.04 1.08l-4.25 4.2a.75.75 0 01-1.06 0l-4.25-4.2a.75.75 0 01-.02-1.06z"
                        clip-rule="evenodd"/>
                </svg>
            </div>

            <!-- Выпадающий список с мульти-выбором и поиском запчастей -->
            <div x-show="open" @click.away="open = false" x-transition
                class="absolute z-50 mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                
                <!-- Поле поиска -->
                <div class="p-2">
                    <input type="text" x-model="search"
                        placeholder="Search parts..."
                        class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                </div>

                <!-- Список запчастей с мульти-выбором -->
                <ul class="py-1 text-sm text-gray-700 max-h-[100px]">
                    @foreach ($parts as $part)
                        <template x-if="!search || '{{ strtolower($part->name) }}'.includes(search.toLowerCase())">
                            <li class="flex items-center px-4 py-2 cursor-pointer hover:bg-gray-100">
                                <input type="checkbox" value="{{ $part->id }}" x-model="selectedParts"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label class="ml-2 text-gray-700">{{ $part->name }}</label>
                            </li>
                        </template>
                    @endforeach
                </ul>
            </div>
        </div>

        <select wire:model="destinationWarehouse" class="w-full p-2 border rounded mb-2">
            <option value="">Select Destination Warehouse</option>
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
        </select>

        <button wire:click="movePart" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            Move Part
        </button>
    </div>

    <!-- Подключить удалённый склад (интеграция через API) -->
    <div class="mt-8">
        <button class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Connect Remote Warehouse (Future Integration)
        </button>
    </div>
</div>