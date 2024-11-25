<div class="p-2 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden" 
x-data="{
    nomenclatures: @entangle('nomenclatures'),
    newNomenclature: @entangle('newNomenclature'),
    selectedNomenclatures: @entangle('selectedNomenclatures'),
    toggleCheckAll(event) {
        this.selectedNomenclatures = event.target.checked ? this.nomenclatures.map(n => n.sku) : [];
    },
    toggleNomenclatureSelection(sku) {
        if (this.selectedNomenclatures.includes(sku)) {
            this.selectedNomenclatures = this.selectedNomenclatures.filter(id => id !== sku);
        } else {
            this.selectedNomenclatures.push(sku);
        }
    }
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="md:text-3xl text-md font-bold text-gray-500 dark:text-gray-400">Nomenclature</h1>
    </div>
    
    <!-- Таблица с номенклатурами -->
    <div class="overflow-x-auto">
        <!-- Заголовки -->
        <div class="hidden md:flex text-sm font-semibold text-gray-700 uppercase bg-gray-50 border-b dark:bg-gray-700 dark:text-gray-400">
            <div class="px-4 py-2">
                <input type="checkbox" 
                       @click="toggleCheckAll($event)"
                       :checked="selectedNomenclatures.length === nomenclatures.length"
                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="flex-1 px-4 py-2">SKU</div>
            <div class="flex-1 px-4 py-2">PN</div>
            <div class="flex-1 px-4 py-2">Name</div>
            <div class="flex-1 px-4 py-2">Category</div>
            <div class="flex-1 px-4 py-2">Supplier</div>
            <div class="flex-1 px-4 py-2">Brand</div>
            <div class="flex-1 px-4 py-2">URL</div>
        </div>
    
        <!-- Список номенклатур -->
        <div x-data>
            <template x-for="nomenclature in nomenclatures" :key="nomenclature.sku">
                <div class="flex items-center text-sm border-b dark:border-gray-600 dark:text-gray-300">
                    <!-- Checkbox -->
                    <div class="px-4 py-2">
                        <input type="checkbox" 
                            :value="nomenclature.sku" 
                            @click="toggleNomenclatureSelection(nomenclature.sku)" 
                            :checked="selectedNomenclatures.includes(nomenclature.sku)"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <!-- SKU -->
                    <div class="flex-1 px-4 py-2">
                        <span class="md:hidden font-semibold">SKU:</span>
                        <div class="flex-1" x-text="nomenclature.sku"></div>
                    </div>
                    <!-- PN + Name -->
                    <div x-data="{
                        showEditMenu: false,
                        editingName: false,
                        newName: '',
                        originalName: '',
                        errorMessage: '',
                        showPnPopover: false,
                        deletePn: false,
                        showingPn: false,
                        searchPn: '',
                        newPn: '',
                        addingPn: false,
                        availablePns: @entangle('availablePns'),
                        selectedPns: @entangle('selectedPns'),
                    }"
                        @pn-added.window="addingPn = false; newPn = ''; errorMessage = ''"
                        class="flex flex-row w-full mb-2 relative"
                    >
                            <!-- PN -->
                            <div class="flex relative">
                    
                            <!-- Кнопка для открытия поповера -->
                            <div
                                class="w-4 h-4 md:w-6 md:h-6 flex items-center justify-center bg-blue-500 text-white rounded-full cursor-pointer mr-2 uppercase font-bold text-[8px] md:text-[10px]"
                                @click="showPnPopover = !showPnPopover">
                                PN
                            </div>
                    
                            <!-- Поповер для управления PNs -->
                            <div x-show="showPnPopover" x-transition
                                @click.away="showPnPopover = false"
                                class="absolute z-40 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-2">
                    
                                <!-- Оверлей -->
                                <div
                                    x-show="deletePn || addingPn || showPnPopover || showingPn"
                                    class="flex fixed inset-0 bg-black bg-opacity-50 z-30"
                                    @click="deletePn = false; showEditMenu = false; showingPn = false; addingPn = false; showPnPopover = false;"
                                    x-cloak>
                                </div>
                    
                                <!-- Меню управления PN -->
                                <div class="flex flex-row justify-around w-full">
                                    <button @click="addingPn = true; showPnPopover = false;"
                                            class="text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded">
                                        Add PN
                                    </button>
                                    <button @click="deletePn = true; showPnPopover = false;"
                                            class="text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded">
                                        Del PN
                                    </button>
                                    <button @click="showingPn = true; showPnPopover = false;"
                                            class="text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 px-2 py-1 rounded">
                                        Show PN
                                    </button>
                                </div>
                            </div>
                    
                            <!-- Список существующих PNs -->
                            <div x-show="showingPn"
                                class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-52 p-2"
                                x-transition
                                @click.away="showingPn = false;">
                                <ul class="text-sm text-gray-700 dark:text-gray-300 max-h-28 overflow-y-auto">
                                    <template x-for="pn in nomenclatures.pns" :key="pn">
                                        <li class="px-2 py-1 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <span x-text="pn"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                    
                            <!-- Поле ввода нового PN -->
                            <div x-show="addingPn"
                                class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-2"
                                x-transition
                                @click.away="addingPn = false; newPn = ''; errorMessage = '';"
                                x-cloak>
                                <div class="flex items-center">
                                    <input type="text" x-model="newPn"
                                        placeholder="Enter new PN"
                                        class="border border-gray-300 rounded-md text-sm px-2 py-1 w-3/4 mr-2">
                                    <button @click="$wire.addPn(newPn).then(() => { newPn = ''; errorMessage = ''; });"
                                            class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                                        ✓
                                    </button>
                                </div>
                                <p class="text-red-500 text-xs mt-1" x-text="errorMessage"></p>
                            </div>
                    
                            <!-- Удаление PN -->
                            <div x-show="deletePn"
                                class="absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-2"
                                x-transition
                                x-cloak
                                @click.away="deletePn = false;">
                                <ul class="text-sm text-gray-700 dark:text-gray-300 max-h-28 overflow-y-auto">
                                    <template x-for="pn in availablePns" :key="pn">
                                        <li class="flex items-center px-2 py-1 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <input type="checkbox" :value="pn" x-model="selectedPns"
                                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <label class="ml-2" x-text="pn"></label>
                                        </li>
                                    </template>
                                </ul>
                                <button @click="$wire.deletePns(selectedPns).then(() => { deletePn = false; });"
                                        class="bg-green-500 text-white px-2 py-1 mt-2 rounded-full w-full">
                                    Confirm
                                </button>
                            </div>
                        </div>
                
                        <!-- Название -->
                        <div class="flex flex-col w-full">
                            <!-- Отображение названия -->
                            <div x-show="!editingName" @click="editingName = true"
                                class="cursor-pointer hover:underline text-gray-800 dark:text-gray-200">
                                <span x-text="originalName"></span>
                            </div>
                    
                            <!-- Редактирование названия -->
                            <div x-show="editingName" class="flex items-center" x-cloak>
                                <input type="text" x-model="newName"
                                    class="border border-gray-300 rounded-md text-sm px-2 py-1 w-3/4 mr-2">
                                <button @click="if (newName !== originalName) { $wire.updateName(newName); originalName = newName; } editingName = false;"
                                        class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                                    ✓
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 px-4 py-2" x-text="nomenclature.pn"></div>
                    <div class="flex-1 px-4 py-2" x-text="nomenclature.name"></div>
                    <!-- Category -->
                    <div class="flex-1 px-4 py-2" x-text="nomenclature.category"></div>
                    <!-- Supplier -->
                    <div class="flex-1 px-4 py-2" x-text="nomenclature.supplier"></div>
                    <!-- Brand -->
                    <div class="flex-1 px-4 py-2">
                        <template x-for="brand in nomenclature.brand" :key="brand">
                            <span class="inline-block bg-gray-200 text-gray-800 text-xs font-medium mr-1 px-2 py-1 rounded dark:bg-gray-700 dark:text-gray-300" x-text="brand"></span>
                        </template>
                    </div>
                    <!-- URL -->
                    <div class="flex-1 px-4 py-2">
                        <a :href="nomenclature.url.url" x-text="nomenclature.url.text" class="text-blue-500 hover:underline"></a>
                    </div>
                </div>
            </template>
        </div>
    </div>     

    <!-- Форма для добавления новой номенклатуры -->
    <div class="p-6 bg-white shadow-md rounded-lg">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Добавить Номенклатуру</h2>
        <form x-on:submit.prevent="$wire.addNomenclature()">
            <div class="grid grid-cols-2 gap-6">
                <!-- SKU -->
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                    <input type="text" id="sku" x-model="newNomenclature.sku" 
                        placeholder="Введите SKU" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.sku" x-text="$wire.errors?.newNomenclature?.sku"></p>
                    @error('sku')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- PN -->
                <div>
                    <label for="pn" class="block text-sm font-medium text-gray-700">PN</label>
                    <input type="text" id="pn" x-model="newNomenclature.pn" 
                        placeholder='{"0": "PN1", "1": "PN2"}' 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.pn" x-text="$wire.errors?.newNomenclature?.pn"></p>
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
                    <input type="text" id="name" x-model="newNomenclature.name" 
                        placeholder="Введите название" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.name" x-text="$wire.errors?.newNomenclature?.name"></p>
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Категория</label>
                    <input type="text" id="category" x-model="newNomenclature.category" 
                        placeholder="Введите категорию" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.category" x-text="$wire.errors?.newNomenclature?.category"></p>
                </div>

                <!-- Supplier -->
                <div>
                    <label for="supplier" class="block text-sm font-medium text-gray-700">Поставщик</label>
                    <input type="text" id="supplier" x-model="newNomenclature.supplier" 
                        placeholder="Введите поставщика" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.supplier" x-text="$wire.errors?.newNomenclature?.supplier"></p>
                </div>

                <!-- Brand -->
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700">Бренд</label>
                    <input type="text" id="brand" x-model="newNomenclature.brand" 
                        placeholder="Введите бренды (через запятую)" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.brand" x-text="$wire.errors?.newNomenclature?.brand"></p>
                </div>

                <!-- URL -->
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                    <input type="url" id="url" x-model="newNomenclature.url.url" 
                        placeholder="https://example.com" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.url?.url" x-text="$wire.errors?.newNomenclature?.url?.url"></p>
                </div>

                <div>
                    <label for="url_text" class="block text-sm font-medium text-gray-700">Текст ссылки</label>
                    <input type="text" id="url_text" x-model="newNomenclature.url.text" 
                        placeholder="Текст ссылки (например, 'Amazon')" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.url?.text" x-text="$wire.errors?.newNomenclature?.url?.text"></p>
                </div>
            </div>

            <button type="submit" 
                class="mt-6 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md shadow-md focus:outline-none focus:ring focus:ring-blue-200">
                Добавить
            </button>
        </form>
    </div>
</div>