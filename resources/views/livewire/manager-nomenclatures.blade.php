<div class="p-2 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden"
x-data="{
    nomenclatures: @entangle('nomenclatures'),
    newNomenclature: @entangle('newNomenclature'),
    selectedNomenclatures: @entangle('selectedNomenclatures'),
    archived_nomenclatures: @entangle('archived_nomenclatures') || [],
    categories: @entangle('categories') || [],
    suppliers: @entangle('suppliers') || [],
    showModal: false,
    editingMode: false,
    name: '', image: '', brand_id: '', category_id: '', supplier_id: '',
    selectedNomenclatures: [],
    selectedImage: null,
    openNomenclatureModal(mode, nomenclature = null) {
        this.editingMode = mode === 'edit';
        if (this.editingMode && nomenclature) {
            this.editingNomenclature = nomenclature.id;
            this.name = nomenclature.name;
            this.category_id = nomenclature.category_id;
            this.supplier_id = nomenclature.supplier_id;
            this.url.url = nomenclature.url.url;
            this.url.text = nomenclature.url.text;
        } else {
            this.resetForm();
        }
        this.showModal = true;
    },
    closeNomenclatureDelModal() {
        this.confirmDeleteNomenclatureId = null;
        this.isNomenclatureDelModalOpen = false;
    },
    resetForm() {
        this.editingNomenclature = null;
        this.name = '';
        this.category_id = '';
        this.supplier_id = '';
        this.image = null;
        this.selectedImage = null;
    },
    toggleCheckAll(event) {
        this.selectedNomenclatures = event.target.checked ? this.nomenclatures.map(n => n.id) : [];
    },
    toggleNomenclatureSelection(id) {
        if (this.selectedNomenclatures.includes(id)) {
            this.selectedNomenclatures = this.selectedNomenclatures.filter(id => id !== id);
        } else {
            this.selectedNomenclatures.push(id);
        }
    },
    archiveNomenclature(nomenclature) {
        $wire.archiveNomenclature(nomenclature);
    },
    restoreNomenclature(nomenclature) {
        $wire.restoreNomenclature(nomenclature);
        this.showArchived = false;
    },
    previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.selectedImage = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },
}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="md:text-3xl text-md font-bold text-gray-500 dark:text-gray-400">Nomenclature</h1>
        <!-- Добавить новую номенклатуру -->
        <button @click="openNomenclatureModal('create')" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 mb-4">Добавить
            Номенклатуру
        </button>
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
            <div class="flex-1 px-4 py-2">Наименование</div>
            <div class="flex-1 px-4 py-2">Категория</div>
            <div class="flex-1 px-4 py-2">Поставщик</div>
            <div class="flex-1 px-4 py-2">Изображение</div>
            <div class="flex-1 px-4 py-2">URL</div>
            <div class="flex-1 px-4 py-2">Действия</div>
        </div>

        <!-- Список номенклатур -->
        <div x-data>
            <template x-for="nomenclature in nomenclatures" :key="nomenclature.id">
                <div class="flex items-center text-sm border-b dark:border-gray-600 dark:text-gray-300">
                    <!-- Checkbox -->
                    <div class="block sm:hidden absolute top-5 right-5 mb-2">
                        <input type="checkbox" :value="nomenclature.id"
                               @click="toggleNomenclatureSelection(nomenclature.id)"
                               :checked="selectedNomenclatures.includes(nomenclature.id)"
                               class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkbox-table-search-nomenclature.id"
                               class="sr-only">checkbox</label>
                    </div>
                    <div class="hidden sm:block md:w-1/8 mb-0 mr-5">
                        <input type="checkbox" :value="nomenclature.id"
                               @click="toggleNomenclatureSelection(nomenclature.id)"
                               :checked="selectedNomenclatures.includes(nomenclature.id)"
                               class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkbox-table-search-nomenclature.id"
                               class="sr-only">checkbox</label>
                    </div>
                    <!-- Name -->
                    <div x-data="{
                        showEditMenu: false,
                        editingName: false,
                        newName: '',
                        originalName: '',
                        errorMessage: '',
                    }"
                        class="flex flex-row w-full mb-2 relative"
                    >

                        <div class="flex relative">
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
                </div>
            </template>
            <template x-if="nomenclatures.length === 0">
            <div
                class="text-sm text-center text-gray-600 dark:text-gray-400 bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                No nomenclatures available
            </div>
            </template>
        </div>
    </div>

    <!-- Форма для добавления новой номенклатуры -->
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50" x-show="showModal" x-cloak>
        <div class="relative w-full max-w-2xl p-4">
            <!-- Modal content -->
            <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-5" @click.away="showModal = false">
                <!-- Modal header -->
                <div class="flex items-start justify-between p-4 mb-2 border-b rounded-t dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <span x-text="editingMode ? 'Редактировать Номенклатуру' : 'Добавить Номенклатуру'"></span>
                    </h3>
                    <button @click="showModal = false" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            aria-label="Close">
                        <svg class="w-5 h-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                  clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <form x-on:submit.prevent="$wire.addNomenclature()">
                    <div class="grid grid-cols-2 gap-6 text-gray-500 dark:text-gray-400">

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium">Название <span class="relative top-0 text-red-600">*</span></label>
                            <input type="text" id="name" x-model="newNomenclature.name"
                                placeholder="Введите название" required=""
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 required:border-red-500">
                            <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.name" x-text="$wire.errors?.newNomenclature?.name"></p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium">Категория <span class="relative top-0 text-red-600">*</span></label>
                            <select id="category" x-model="newNomenclature.category" x-on:refresh-category-select.window="this.categories = $wire.categories"
                                    class="required:border-red-500 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Выберите категорию" required="">
                                <option value="">Выберите категорию</option>
                                <template x-for="(category, index) in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.category" x-text="$wire.errors?.newNomenclature?.category"></p>
                        </div>

                        <!-- Supplier -->
                        <div>
                            <label for="supplier" class="block text-sm font-medium">Поставщик</label>
                            <select id="supplier" x-model="newNomenclature.supplier" x-on:refresh-supplier-select.window="this.suppliers = $wire.suppliers"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Выберите поставщика">
                                <option value="">Выберите поставщика</option>
                                <template x-for="(supplier, index) in suppliers" :key="supplier.id">
                                    <option value="supplier.id" x-text="supplier.name"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.supplier" x-text="$wire.errors?.newNomenclature?.supplier"></p>
                        </div>

                        <!-- URL -->
                        <div>
                            <label for="url" class="block text-sm font-medium">URL</label>
                            <input type="url" id="url" x-model="newNomenclature.url.url"
                                placeholder="https://example.com"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.url?.url" x-text="$wire.errors?.newNomenclature?.url?.url"></p>
                        </div>

                        <div>
                            <label for="url_text" class="block text-sm font-medium">Текст ссылки</label>
                            <input type="text" id="url_text" x-model="newNomenclature.url.text"
                                placeholder="Текст ссылки (например, 'Amazon')"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <p class="mt-1 text-sm text-red-600" x-show="$wire.errors?.newNomenclature?.url?.text" x-text="$wire.errors?.newNomenclature?.url?.text"></p>
                        </div>

                        <!-- Image -->
                        <div>
                            <label for="image" class="block text-sm font-medium">Изображение</label>
                            <input type="file" id="image" wire:model="image" @change="previewImage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                            <!-- Превью изображения -->

                            <div class="mt-4">
                                <template x-if="selectedImage">
                                    <img :src="selectedImage" alt="Превью изображения" class="w-32 h-32 object-cover rounded" />
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-2 border-t rounded-b dark:border-gray-700">
                        <button type="submit"
                                class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span x-text="editingMode ? 'Сохранить' : 'Создать'"></span>
                        </button>
                        <button type="button" @click="showModal = false"
                                class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400">
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
