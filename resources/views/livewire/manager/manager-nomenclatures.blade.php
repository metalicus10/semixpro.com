<div class="p-2 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden"
     x-data="{
        nomenclatures: @js($nomenclatures) || [],
        localNomenclatures: [],
        serverNomenclatures: [],
        mode: 'alpine',
        nomenclatureCount: @js($nomenclatureCount),
        newNomenclature: @entangle('newNomenclature'),
        selectedNomenclatures: @entangle('selectedNomenclatures'),
        archived_nomenclatures: @entangle('archived_nomenclatures') || [],
        categories: @entangle('categories') || [],
        suppliers: @entangle('suppliers') || [],
        brands: @entangle('brands') || [],
        showModal: false,
        editingMode: false,
        nn:'', name: '', sku: '', image: '', brand_id: '', category_id: '', supplier_id: '', search: '',
        imageInputKey: Date.now(),
        duplicateNameError: null, duplicateNnError: null,
        selectedNomenclatures: [],
        selectedImage: null,
        init() {

            if (this.nomenclatureCount > 500) {
                this.mode = 'livewire';
                this.loadServerNomenclatures();
            } else {
                this.mode = 'alpine';
            }
            window.addEventListener('nomenclature-nn-duplicate', event => {
                this.duplicateNnError = `Номенклатура с номером '${event.detail.nn}' уже существует`;
            });
            window.addEventListener('nomenclature-name-duplicate', event => {
                this.duplicateNameError = `Номенклатура с названием '${event.detail.name}' уже существует`;
            });
            window.addEventListener('open-image-modal', event => {
                nomenclatureId = event.detail.id;
                showImageUploading = true;
                selectedFile = null;
                imgError = null;
            });
        },
        filteredNomenclatures() {
            return this.nomenclatures
                .filter(n => !n.is_archived)
                .filter(n => this.search === '' || n.name.toLowerCase().includes(this.search.toLowerCase()));
        },
        async loadServerNomenclatures() {
            const response = await fetch(`/api/nomenclatures?search=${this.search}&category=${this.selectedCategory}&brand=${this.selectedBrand}`);
            this.serverNomenclatures = await response.json();
        },
        submitNomenclature() {
            $wire.call('addNomenclature', this.newNomenclature)
                .then(() => {
                    this.resetForm();
                    this.showModal = false;
                    this.duplicateNameError = null;
                    this.duplicateNnError = null;
                });
        },
        openNomenclatureModal(mode, nomenclature = null) {
            this.editingMode = mode === 'edit';
            if (this.editingMode && nomenclature) {
                this.editingNomenclature = nomenclature.id;
                this.name = nomenclature.name;
                this.category_id = nomenclature.category_id;
                this.supplier_id = nomenclature.supplier_id;
            } else {
                this.resetForm();
            }
            this.resetForm();
            this.showModal = true;
        },
        closeNomenclatureModal() {
            this.resetForm();
            this.showModal = false;
        },
        closeNomenclatureDelModal() {
            this.confirmDeleteNomenclatureId = null;
            this.isNomenclatureDelModalOpen = false;
        },
        resetForm() {
            this.editingNomenclature = null;
            this.nn = '';
            this.name = '';
            this.category_id = '';
            this.supplier_id = '';
            this.image = null;
            this.selectedImage = null;
            this.imageInputKey = Date.now();
        },
        toggleCheckAll(event) {
            this.selectedNomenclatures = event.target.checked ? Object.values(this.nomenclatures).map(n => n.id) : [];
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
    }" x-init="init();"
>
    <div class="flex justify-between items-center mb-6">
        <h1 class="md:text-3xl text-md font-bold text-gray-500 dark:text-gray-400">Nomenclature</h1>
        <livewire:manager.nomenclature-archive/>
        <!-- Добавить новую номенклатуру -->
        <button @click="openNomenclatureModal('create')"
                class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 mb-4">Добавить
            Номенклатуру
        </button>
    </div>

    <!-- Таблица с номенклатурами -->
    <div class="w-full overflow-x-auto">
        <!-- Заголовки -->
        <div
            class="hidden md:grid grid-cols-8 w-full content-start text-left text-sm font-semibold text-gray-700 uppercase bg-gray-50 border-b dark:bg-gray-700 dark:text-gray-400">
            <div class="w-1/8 text-center p-2">
                <input type="checkbox"
                       @click="toggleCheckAll($event)"
                       :checked="selectedNomenclatures.length === nomenclatures.length"
                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="w-1/8 p-2">NN</div>
            <div class="w-2/8 p-2">Наименование</div>
            <div class="w-1/8 p-2">Категория</div>
            <div class="w-2/8 p-2">Поставщик</div>
            <div class="w-1/8 p-2">Брэнд</div>
            <div class="w-2/8 p-2">Изображение</div>
            <div class="w-2/8 p-2">Действия</div>
        </div>

        <!-- Список номенклатур -->
        <template x-if="mode === 'alpine'">
            <template x-if="nomenclatures && nomenclatures.length > 0">
                <template x-for="nomenclature in filteredNomenclatures()" :key="nomenclature.id">

                    <div
                        class="grid grid-cols-8 w-full content-start text-sm border-b dark:border-gray-600 dark:text-gray-300 py-1">
                        <!-- Checkbox -->
                        <div class="w-1/8 block sm:hidden absolute top-5 right-5 mb-2">
                            <input type="checkbox" :value="nomenclature.id"
                                   @click="toggleNomenclatureSelection(nomenclature.id)"
                                   :checked="selectedNomenclatures.includes(nomenclature.id)"
                                   class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="checkbox-table-search-nomenclature.id"
                                   class="sr-only">checkbox</label>
                        </div>
                        <div class="hidden w-1/8 md:flex items-center justify-center sm:flex p-2">
                            <input type="checkbox" :value="nomenclature.id"
                                   @click="toggleNomenclatureSelection(nomenclature.id)"
                                   :checked="selectedNomenclatures.includes(nomenclature.id)"
                                   class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="checkbox-table-search-nomenclature.id"
                                   class="sr-only">checkbox</label>
                        </div>
                        <!-- NN -->
                        <div class="w-1/8 text-center flex items-center p-2">
                            <span class="md:hidden font-semibold">NN: </span>
                            <span x-text="nomenclature.nn"></span>
                        </div>
                        <!-- Name -->
                        <div x-data="{
                                showEditMenu: false,
                                editingName: false,
                                newName: 'nomenclature.name',
                                originalName: 'nomenclature.name',
                                errorMessage: '',
                            }"
                             class="flex flex-col justify-center items-start"
                        >
                            <span class="md:hidden font-semibold">Название: </span>
                            <div class="flex relative">
                                <!-- Название -->
                                <div class="flex flex-col w-full p-2">
                                    <!-- Оверлей -->
                                    <div x-show="editingName"
                                         class="flex fixed inset-0 bg-black opacity-50 z-30"
                                         @click="editingName = false, deletePn = false, addingPn = false;"
                                         x-cloak>
                                    </div>
                                    <!-- Отображение названия -->
                                    <div x-show="!editingName" @click="editingName = true"
                                         class="cursor-pointer hover:underline text-gray-800 dark:text-gray-200">
                                        <span x-text="nomenclature.name"></span>
                                    </div>

                                    <!-- Редактирование названия -->
                                    <div x-show="editingName" class="flex items-center gap-2 z-40" x-cloak>
                                        <input type="text" x-model="newName"
                                               class="border border-gray-300 rounded-md text-sm px-2 py-1 w-3/4 mr-2"
                                               @keydown.enter="if (newName !== originalName) { $wire.updateNomenclature(nomenclature.id, newName); originalName = newName; } editingName = false;"
                                               @keydown.escape="editingName = false; newName = originalName;"
                                        />
                                        <button
                                            @click="if (newName !== originalName) { $wire.updateNomenclature(nomenclature.id, newName); originalName = newName; } editingName = false;"
                                            class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                                            ✓
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Category -->
                        <div class="flex w-1/8 items-center px-2">
                            <span class="md:hidden font-semibold">Категория: </span>
                            <span x-text="nomenclature.category ? nomenclature.category.name : '---'"></span>
                        </div>
                        <!-- Supplier -->
                        <div class="flex w-2/8 items-center px-2">
                            <span class="md:hidden font-semibold">Поставщик: </span>
                            <span x-text="nomenclature.suppliers ? nomenclature.suppliers.name : '---'"></span>
                        </div>
                        <!-- Brand -->
                        <div class="flex w-1/8 items-center px-2">
                            <span class="md:hidden font-semibold">Брэнд: </span>
                            <div id="brand-component-nomenclature.id">
                                <div id="brand-item-nomenclature.id"
                                     class="w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer parent-container"
                                     x-data="{
                                         showPopover: false,
                                         nomenclatureBrands: nomenclature.brands ?? [],
                                         selectedBrands: @entangle('selectedBrands').live || [],
                                         search: '',
                                         popoverX: 0,
                                         popoverY: 0
                                     }"
                                     @brands-updated.window="(event) => {
                                        if (event.detail === nomenclature.id) {
                                            $wire.getUpdatedBrands(nomenclature.id).then((updatedBrands) => {
                                                selectedBrands = updatedBrands;
                                            });
                                        }
                                     }"
                                     @click.away="showPopover = false"
                                     @mousedown.stop
                                     @click="
                                        const { clientX, clientY } = $event;
                                        $nextTick(() => {
                                            popoverX = Math.min(clientX, window.innerWidth - 250);
                                            popoverY = Math.min(clientY, window.innerHeight - 200);
                                            showPopover = true;
                                        });
                                     "
                                >
                                    <!-- Текущие бренды -->
                                    <div class="flex flex-col h-24 w-20 justify-center p-1">
                                        <span class="md:hidden font-semibold">Brand:</span>
                                        <div class="overscroll-contain overflow-y-auto">
                                            <template x-if="selectedBrands.length === 0">
                                                <div class="px-3 py-2">---</div>
                                            </template>
                                            <template x-if="selectedBrands.length > 0">
                                                    <span
                                                        x-text="selectedBrands.map(id => brands.find(b => b.id == id)?.name).join(', ')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Поповер с мульти-выбором брендов -->
                                    <div x-show="showPopover"
                                         class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                         :style="`top: ${popoverY}px; left: ${popoverX}px;`"
                                         x-init="const onScroll = () => showPopover = false; window.addEventListener('scroll', onScroll)"
                                         @click.outside="showPopover = false"
                                         x-transition>

                                        <!-- Поле поиска -->
                                        <div class="mb-2" @click.stop>
                                            <input type="text" x-model="search"
                                                   placeholder="Search brands..."
                                                   class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 dark:bg-gray-700 dark:text-gray-300"/>
                                        </div>

                                        <!-- Список брендов с мульти-выбором -->
                                        <div class="flex flex-row justify-between">
                                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 w-2/3 max-h-28 overflow-y-auto">
                                                <template
                                                    x-for="brand in brands.filter(b => !search || b.name.toLowerCase().includes(search.toLowerCase()))"
                                                    :key="brand.id">
                                                    <li class="flex items-center px-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                                        @click.stop>
                                                        <input type="checkbox" :value="brand.id"
                                                               x-model="selectedBrands"
                                                               :checked="selectedBrands.includes(brand.id)"
                                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                               @click.stop>
                                                        <label class="ml-2" x-text="brand.name"></label>
                                                    </li>
                                                </template>
                                            </ul>

                                            <!-- Кнопка подтверждения -->
                                            <div class="flex justify-center items-center w-1/3">
                                                <button @click="$wire.set('selectedBrands', selectedBrands).then(() => {
                                                    $wire.updateSelectedBrands(nomenclature.id);
                                                    showPopover = false;
                                                })"
                                                        class="bg-green-500 text-white px-2 py-1 rounded-full hover:bg-green-600">
                                                    ✓
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Nomenclature Image -->
                        <div class="flex items-center px-2">
                            <span class="md:hidden font-semibold">Изображение:</span>
                            <div x-data="{
                                isUploading: false,
                                uploadProgress: 0,
                                showImageUploading: false,
                                isLoading: false,
                                showTooltip: false,
                                selectedFile: null,
                                imgError: null,
                                baseStoragePath: '{{ asset('storage') }}',
                                closeImageModal() {
                                    this.showImageUploading = false;
                                    this.selectedFile = null;
                                    this.imgError = null;
                                },
                                handleFileChange(event) {
                                    this.selectedFile = event.target.files[0];
                                },
                                uploadImage() {
                                    if (!this.selectedFile) {
                                        this.imgError = 'Пожалуйста, выберите файл для загрузки.';
                                        return;
                                    }

                                    $wire.uploadImage(nomenclatureId)
                                    .then(() => {
                                            this.closeImageModal();
                                        })
                                        .catch(e => {
                                            this.imgError = 'Ошибка загрузки файла.';
                                            console.error(e);
                                    });
                                },
                                refreshImage(imageUrl) {
                                    this.isLoading = true;
                                    setTimeout(() => {
                                        this.isLoading = false;
                                    }, 500);
                                },
                                computedImagePath(img) {
                                    return img && typeof img === 'string' && img.trim() !== '' ? 'baseStoragePath/' + img : '';
                                },
                                closeModal() {
                                    this.showModal = false;
                                    this.selectedFile = null;
                                    this.imgError = null;
                                },
                            }" x-cloak class="flex gallery relative">
                                <div class="flex flex-row w-auto max-w-[120px] max-h-[80px]">
                                    <template x-if="nomenclature && typeof nomenclature.image === 'string' && nomenclature.image.trim() !== ''">
                                        <img
                                            :src="computedImagePath(nomenclature.image)"
                                            :alt="nomenclature.name"
                                            @click="Livewire.dispatch('lightbox', computedImagePath(nomenclature.image))"
                                            class="object-contain rounded cursor-zoom-in"
                                        >
                                    </template>

                                    <template x-if="!nomenclature || !nomenclature.image || nomenclature.image.trim() === ''">
                                        <livewire:components.empty-image/>
                                    </template>
                                </div>
                                <!-- Tooltip и кнопка загрузки -->
                                <div @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
                                    <div x-show="showTooltip" x-transition
                                         class="absolute z-50 -top-6 left-6 w-max px-2 py-1 text-xs bg-green-500 text-white rounded shadow-lg">
                                        Change Image
                                    </div>
                                    <button @click="showImageUploading = true"
                                            class="text-white rounded-full p-1 cursor-pointer h-[20px]">
                                        <x-icons.upload-arrow class="text-white" />
                                    </button>
                                </div>
                                <!-- Прогресс загрузки -->
                                <div x-show="isUploading"
                                     class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
                                    <div class="text-white text-lg">Uploading... (<span x-text="uploadProgress"></span>%)
                                    </div>
                                </div>

                                <div>
                                    <!-- Modal Backdrop -->
                                    <div x-show="showImageUploading"
                                         class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40"
                                         x-transition.opacity x-cloak></div>

                                    <!-- Modal Content -->
                                    <div x-show="showImageUploading" x-transition
                                         class="fixed inset-0 flex items-center justify-center z-50 p-4">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full">
                                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                                Upload Image</h3>

                                            <!-- File Input -->
                                            <div class="mb-4">
                                                <input type="file" @change="handleFileChange"
                                                       wire:model="nomenclatureImage"
                                                       class="block w-full text-gray-800 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                                                <template x-if="imgError">
                                                    <p class="mt-2 text-red-500 text-sm" x-text="imgError"></p>
                                                </template>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex justify-end space-x-4">
                                                <button type="button"
                                                        @click="closeImageModal"
                                                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                                    Cancel
                                                </button>
                                                <button type="button"
                                                        @click="uploadImage"
                                                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                                    Upload
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Actions -->
                        <div class="flex w-2/8 items-center px-2 gap-2">
                            @if(Auth::user()->inRole('admin'))
                                <button @click="openNomenclatureModal('edit', nomenclature.id)"
                                        class="cursor-pointer px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                    Ред.
                                </button>
                            @endif

                            <button @click="$wire.archiveNomenclature(nomenclature.id)"
                                    class="cursor-pointer px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                Archive
                            </button>

                            @if(Auth::user()->inRole('admin'))
                                <button @click="openNomenclatureDelModal(nomenclature.id)"
                                        class="px-2 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                                    Удалить
                                </button>
                            @endif
                        </div>
                    </div>

                </template>
            </template>
        </template>
        <template x-if="mode === 'livewire'">
            <template x-for="nomenclature in serverNomenclatures" :key="nomenclature.id">
                <div x-text="nomenclature.name"></div>
            </template>
        </template>
        <template x-if="nomenclatures.length === 0">
            <div
                class="text-sm text-center text-gray-600 dark:text-gray-400 bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                No nomenclatures available
            </div>
        </template>
    </div>

    <!-- Форма для добавления новой номенклатуры -->
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-show="showModal" x-cloak>
        <!-- Оверлей -->
        <div x-show="showModal"
             class="flex fixed inset-0 bg-black opacity-50 z-30"
             @click="showModal = false, closeNomenclatureModal()"
             x-cloak>
        </div>
        <div class="relative w-full max-w-4xl p-4 z-50">
            <!-- Modal content -->
            <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-5"
                 @click.away="showModal = false, closeNomenclatureModal()">
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

                <form x-on:submit.prevent="submitNomenclature">
                    <div class="grid grid-cols-2 gap-6 text-gray-500 dark:text-gray-400">
                        <!-- NN -->
                        <div>
                            <label for="nn" class="block text-sm font-medium">Номер номенклатуры <span
                                    class="text-red-600">*</span></label>
                            <input type="text" id="nn" x-model="newNomenclature.nn" required
                                   class="mt-1 p-2 w-full rounded-md bg-gray-600 border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <p class="mt-1 text-sm text-red-600" x-text="$wire.errors?.newNomenclature?.nn"></p>
                            <p class="mt-1 text-sm text-red-600" x-text="duplicateNnError"
                               x-show="duplicateNnError"></p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium">Название <span
                                    class="text-red-600">*</span></label>
                            <input type="text" id="name" x-model="newNomenclature.name" required
                                   class="mt-1 p-2 w-full rounded-md bg-gray-600 border-gray-300 shadow-sm">
                            <p class="mt-1 text-sm text-red-600" x-text="$wire.errors?.newNomenclature?.name"></p>
                            <p class="mt-1 text-sm text-red-600" x-text="duplicateNameError"
                               x-show="duplicateNameError"></p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium">Категория <span
                                    class="text-red-600">*</span></label>
                            <select x-model="newNomenclature.category_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                    required>
                                <option value="">Выберите категорию</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-sm text-red-600"
                               x-text="$wire.errors?.newNomenclature?.category_id"></p>
                        </div>

                        <!-- Supplier -->
                        <div>
                            <label for="supplier" class="block text-sm font-medium">Поставщик</label>
                            <select x-model="newNomenclature.supplier_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                <option value="">Выберите поставщика</option>
                                <template x-for="supplier in suppliers" :key="supplier.id">
                                    <option :value="supplier.id" x-text="supplier.name"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-sm text-red-600"
                               x-text="$wire.errors?.newNomenclature?.supplier_id"></p>
                        </div>

                        <!-- Brand -->
                        <div>
                            <label for="brand" class="block text-sm font-medium">Брэнд</label>
                            <select x-model="newNomenclature.brand_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                <option value="">Выберите брэнд</option>
                                <template x-for="brand in brands" :key="brand.id">
                                    <option :value="brand.id" x-text="brand.name"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-sm text-red-600" x-text="$wire.errors?.newNomenclature?.brand_id"></p>
                        </div>

                        <!-- Image -->
                        <div>
                            <label for="image" class="block text-sm font-medium">Изображение</label>
                            <input type="file" id="image" wire:model="image" @change="previewImage"
                                   :key="imageInputKey"
                                   class="mt-1 p-2 w-full rounded-md bg-gray-600 border-gray-300 shadow-sm text-sm"/>
                            <div class="mt-4">
                                <template x-if="selectedImage">
                                    <img :src="selectedImage" alt="Превью изображения"
                                         class="w-32 h-32 object-cover rounded"/>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center p-6 space-x-2 border-t mt-6 rounded-b dark:border-gray-700">
                        <button type="submit"
                                class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span x-text="editingMode ? 'Сохранить' : 'Создать'"></span>
                        </button>
                        <button type="button" @click="closeNomenclatureModal"
                                class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400">
                            Отмена
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
