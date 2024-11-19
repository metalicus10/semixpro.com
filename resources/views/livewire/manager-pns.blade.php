<div class="flex relative">
        
    <!-- Список существующих PNs -->
    <div class="flex z-30" x-cloak>
        <!-- Кнопка для открытия поповера -->
        <div class="w-6 h-6 flex items-center justify-center bg-blue-500 text-white rounded-full cursor-pointer mr-2 text-xs"
            @click="showPnPopover = !showPnPopover">
            PN
        </div>

        <!-- Поповер для редактирования PNs -->
        <div x-show="showPnPopover" x-transition @click.away="showPnPopover = false" class="flex absolute z-40 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1">

            <!-- Оверлей -->
            <div x-show="editingPn || addingPn || showPnPopover" 
                class="flex fixed inset-0 bg-black bg-opacity-50 z-30" 
                @click="editingPn = false; showEditMenu = false; showShowMenu = false; addingPn = false; showPnPopover = false;" 
                x-cloak>
            </div>

            <div class="flex flex-row w-full cursor-pointer z-50" x-cloak>
                <div @click="addingPn = true; showAddMenu = false; showPnPopover = false" class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                    Add PN
                </div>
                <div @click="editingPn = true; showEditMenu = false;" class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                    Edit PN
                </div>
                <div @click="showPn = true; showShowMenu = false;" class="w-1/3 text-center py-1 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 rounded">
                    Show PN
                </div>
            </div>

            
        </div>
    </div>

    <!-- Поле ввода нового PN -->
    <div x-show="addingPn" class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1" x-transition 
        @click.away="addingPn = false; newPn = ''; errorMessage = '';"
    >
        <div class="flex flex-row w-full">
            <div class="flex justify-center items-center">
                <!-- Поле ввода -->
                <input type="text" wire:model="newPn" placeholder="Enter new PN"
                    class="border border-gray-300 rounded-md text-sm px-2 py-1 w-3/4 mr-2">

                <!-- Кнопки действия -->
                <button wire:click="addPn" class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                    ✓
                </button>
            </div>
        </div>
    </div>

    <!-- Режим редактирования PN -->
    <div x-show="editingPn" @click.away="editingPn = false"
        class="fixed inset-0 z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
        x-cloak x-transition>
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
                <button @click="$wire.addPn({{ $partId }}, selectedPns); editingPn = false;"
                    class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4 w-[28px]">
                ✓
                </button>
            </div>
        </div>
    </div>
</div>