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
        isUploading: false,
        selectedFile: null,
        selectedNomenclatureId: null,
        uploadProgress: 0,
        showImageUploading: false,
        imgError: null,
        editingMode: false,
        nn:'', name: '', sku: '', image: '', brand_id: '', category_id: '', supplier_id: '', search: '',
        imageInputKey: Date.now(),
        duplicateNameError: null, duplicateNnError: null,
        selectedImage: null, highlightedPart: null,
        refs: {},
        highlightedNomenclatures: [],
        init() {
            if (this.nomenclatureCount > 500) {
                this.mode = 'livewire';
                this.loadServerNomenclatures();
            } else {
                this.mode = 'alpine';
            }
            window.addEventListener('switch-tab', (event) => {
                if (event.detail.tab === 'nomenclatures' && event.detail.partIds) {
                    this.highlightNomenclatures(event.detail.partIds, 1000);
                }
            });
            window.addEventListener('nomenclature-nn-duplicate', event => {
                this.duplicateNnError = `Номенклатура с номером '${event.detail.nn}' уже существует`;
            });
            window.addEventListener('nomenclature-name-duplicate', event => {
                this.duplicateNameError = `Номенклатура с названием '${event.detail.name}' уже существует`;
            });
            window.addEventListener('open-image-modal', event => {
                nomenclatureId = event.detail.id;
                this.showImageUploading = true;
                this.selectedFile = null;
                this.imgError = null;
            });
        },
        highlightNomenclatures(nomenclatureIds, timeout = 1000) {
            const ids = Array.isArray(nomenclatureIds) ? nomenclatureIds : [nomenclatureIds];
            this.highlightedNomenclatures = ids;
            setTimeout(() => {
                this.highlightedNomenclatures = [];
            }, timeout);

            if (ids.length > 0) {
                const first = document.getElementById(`nomenclature-${ids[0]}`);
                if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        get filteredNomenclatures() {
            if (!this.search) return this.nomenclatures;
            return this.nomenclatures.filter(n =>
                n.name.toLowerCase().includes(this.search.toLowerCase()) ||
                n.nn.toLowerCase().includes(this.search.toLowerCase())
            );
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
                this.selectedNomenclatures = this.selectedNomenclatures.filter(selectedId => selectedId !== id);
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
        handleFileChange(event) {
            this.selectedFile = event.target.files[0];
        },
        uploadImage(nomenclatureId) {
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
        closeImageModal() {
            this.showImageUploading = false;
            this.selectedFile = null;
            this.imgError = null;
        },
        bulkEditItems: [],
        showBulkEditModal: false,
        openBulkEditModal() {
            this.bulkEditItems = this.nomenclatures
              .filter(n => this.selectedNomenclatures.includes(n.id))
              .map(n => ({
                id: n.id,
                nn: n.nn,
                name: n.name,
                category_id: n.category?.id ?? null,
                supplier_id: n.suppliers?.id ?? null,
                brands: n.brands
              }));

            this.showBulkEditModal = true;
        },
        submitBulkEdit() {
            $wire.bulkUpdateNomenclatures(this.bulkEditItems);
            this.showBulkEditModal = false;
            this.bulkEditItems = [];
            this.selectedNomenclatures = [];
        },
        cancelBulkEdit() {
            this.showBulkEditModal = false;
            this.bulkEditItems = [];
        },
        handleBulkUpdate(event) {
            const updated = event.detail[0];

            updated.forEach(item => {
                const index = this.nomenclatures.findIndex(n => n.id === item.id);
                if (index !== -1) {
                    this.nomenclatures[index] = {
                        ...this.nomenclatures[index],
                        ...item,
                        category: this.categories.find(c => c.id === item.category_id),
                        supplier: this.suppliers.find(s => s.id === item.supplier_id),
                    };
                }
            });
            this.nomenclatures = [...this.nomenclatures];
        },
    }" x-init="init();"
>
    <div class="flex justify-between items-center mb-4">
        <h1 class="md:text-3xl text-md font-bold text-gray-500 dark:text-[#d2d7df]">Nomenclature</h1>
        <div class="flex gap-2">
            <livewire:manager.nomenclature-archive/>
            <button x-show="selectedNomenclatures.length > 1" @click="openBulkEditModal()"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border hover:text-accent-foreground h-10 px-4 py-2 bg-[#2a3749] border-[#3a4759] text-white hover:bg-[#3a4759] cursor-pointer">
                Массовое изменение
            </button>
            <!-- Добавить новую номенклатуру -->
            <button @click="openNomenclatureModal('create')"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-10 px-4 py-2 bg-green-500 hover:bg-green-600 text-white cursor-pointer">
                Добавить
                Номенклатуру
            </button>
        </div>
    </div>

    <!-- Таблица с номенклатурами -->
    <div class="bg-[#1a2433] rounded-lg shadow-lg overflow-x-auto">
        <!-- Заголовки -->
        <div
            class="hidden md:grid grid-cols-8 w-full content-center items-center text-left text-sm font-semibold text-gray-700 uppercase bg-gray-50 border-b
            dark:bg-[#1a2433] dark:text-gray-400 dark:border-gray-600">
            <div class="w-1/8 p-4 text-left">
                <input type="checkbox"
                       @click="toggleCheckAll($event)"
                       :checked="selectedNomenclatures.length === nomenclatures.length"
                       class="h-4 w-4 rounded bg-white checked:bg-bg-accent checked:border-bg-accent
                       transition-colors duration-200 ease-in-out">
            </div>
            <div class="w-1/8 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">NN</div>
            <div class="w-2/8 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Наименование</div>
            <div class="w-1/8 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Категория</div>
            <div class="w-2/8 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Поставщик</div>
            <div class="w-1/8 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Брэнд</div>
            <div class="w-2/8 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Изображение</div>
            <div class="w-2/8 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Действия</div>
        </div>

        <!-- Список номенклатур -->
        <template x-if="mode === 'alpine'">
            <template x-if="nomenclatures && nomenclatures.length > 0"
                      @nomenclature-updated.window="(event) => {
                    const id = event.detail[0];
                    const nomenclature = nomenclatures.find(n => n.id === id);

                    if (nomenclature) {
                        nomenclature.is_archived = 1;
                    }
                }"
                      @bulk-nomenclature-updated.window="handleBulkUpdate($event)"
            >
                <template x-for="nomenclature in filteredNomenclatures" :key="nomenclature.id">
                    <div x-data="{
                        editing: false,
                        editField: '',
                        form: {
                            name: nomenclature.name,
                            nn: nomenclature.nn,
                            category_id: nomenclature.category_id,
                            supplier_id: nomenclature.supplier_id,
                        },
                        initField(field) {
                            this.editing = true;
                            this.editField = field;
                            this.form[field] = nomenclature[field];
                            this.$nextTick(() => this.$refs[field + 'Input'].focus());
                        },
                        saveField(field) {
                            if (this.form[field] !== nomenclature[field]) {
                                nomenclature[field] = this.form[field];
                                $wire.updateNomenclature(nomenclature.id, { [field]: this.form[field] });
                            }
                            this.editing = false;
                            this.editField = '';
                        },
                        cancelEdit(field) {
                            this.form[field] = nomenclature[field];
                            this.editing = false;
                            this.editField = '';
                        },
                        errorMessage: '',
                        existingNns: [],
                        checkDuplicateNn(newNn) {
                            // Проверяем в активных номенклатурах
                            const duplicateInActive = this.nomenclatures.some(n => n.nn == newNn);

                            // Проверяем в архивных номенклатурах
                            const duplicateInArchived = this.archived_nomenclatures.some(n => n.nn == newNn);

                            return duplicateInActive || duplicateInArchived;
                        },
                        updateField(field, value) {
                            if (value === nomenclature[field]) return;

                            if (field === 'nn') {
                                if (this.existingNns.includes(value)) {
                                    this.errorMessage = 'Номер уже существует';
                                    return;
                                }
                                this.errorMessage = '';
                            }

                            $wire.updateNomenclatureField(nomenclature.id, field, value)
                            .then(() => {
                                nomenclature[field] = value;
                                this.editing = false;
                            })
                            .catch(() => {
                                this.errorMessage = 'Ошибка при обновлении';
                            });
                        },
                        refreshExistingNns() {
                            $wire.call('getAllNns').then((nns) => {
                                this.existingNns = nns.filter(n => n !== this.nomenclature.nn);
                            });
                        },
                        getCategoryName(id) {
                            const cat = this.categories.find(c => c.id === id);
                            return cat ? cat.name : '—';
                        },
                        getSupplierName(id) {
                            const sup = this.suppliers.find(s => s.id === id);
                            return sup ? sup.name : '—';
                        },
                    }"
                         x-init="init();existingNns = @js($allNns).filter(n => n !== nomenclature.nn)"
                         @nomenclature-updated.window="refreshExistingNns()"
                    >
                        <div x-show="!nomenclature.is_archived" :id="`nomenclature-${nomenclature.id}`"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-90"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-90"
                             class="grid grid-cols-8 w-full content-center items-center text-sm border-b dark:border-gray-800 dark:text-gray-300 py-1 max-h-[105px] hover:bg-[#0d1829] transition-colors duration-300 ease-in-out"
                             :class="highlightedNomenclatures.includes(nomenclature.id) ? 'highlighted' : ''"
                        >
                            <!-- Checkbox -->
                            <div class="w-1/8 block sm:hidden absolute top-5 right-5 mb-2">
                                <input type="checkbox" :value="nomenclature.id"
                                       @click="toggleNomenclatureSelection(nomenclature.id)"
                                       :checked="selectedNomenclatures.includes(nomenclature.id)"
                                       class="h-4 w-4 rounded border-[#3a4759] bg-[#111827] color-accent checked:bg-green-500 checked:border-green-500 focus:ring-green-500 focus:ring-offset-0">
                                <label for="checkbox-table-search-nomenclature.id"
                                       class="sr-only">checkbox</label>
                            </div>
                            <div class="hidden w-1/8 md:flex items-left justify-left sm:flex p-4">
                                <input type="checkbox" :value="nomenclature.id"
                                       @click="toggleNomenclatureSelection(nomenclature.id)"
                                       :checked="selectedNomenclatures.includes(nomenclature.id)"
                                       class="h-4 w-4 rounded bg-white checked:accent-green-500
                                       focus:ring-2 focus:ring-green-500 focus:ring-offset-0">
                                <label for="checkbox-table-search-nomenclature.id"
                                       class="sr-only">checkbox</label>
                            </div>
                            <!-- NN -->
                            <div x-data="{
                                    editingNn: false,
                                    newNn: nomenclature.nn,
                                }"
                                 class="flex flex-col justify-center items-start w-[100px]">
                                <span class="md:hidden font-semibold">NN: </span>
                                <div class="flex relative">
                                    <div class="flex flex-col w-full">
                                        <!-- Оверлей -->
                                        <div x-show="editing && editField === 'nn'"
                                             class="flex fixed inset-0 bg-black opacity-50 z-30"
                                             @click="cancelEdit('nn')"
                                             x-cloak>
                                        </div>
                                        <!-- Отображение номера -->
                                        <div x-show="!editing || editField !== 'nn'" @click="initField('nn')"
                                             class="cursor-pointer hover:underline text-gray-800 dark:text-gray-200">
                                            <span x-text="nomenclature.nn"></span>
                                        </div>
                                        <!-- Редактирование номера -->
                                        <div x-show="editing && editField === 'nn'" class="flex items-center gap-2 z-40"
                                             x-cloak>
                                            <input type="text" x-model="form.nn" x-ref="nnInput"
                                                   @input="errorMessage = ''"
                                                   @keydown.enter="updateField('name', form.nn)"
                                                   @keydown.escape="cancelEdit('nn')"
                                                   class="border border-gray-300 rounded-md text-sm text-gray-600 px-2 py-1 w-3/4 mr-2"
                                            >
                                            <template x-if="errorMessage">
                                                <div
                                                    class="absolute top-full left-0 mt-1 w-max px-3 py-1 bg-red-500 text-white text-xs rounded shadow">
                                                    <span x-text="errorNnMessage"></span>
                                                </div>
                                            </template>
                                            <button
                                                @click="updateField('nn', form.nn)"
                                                class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                                                ✓
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Name -->
                            <div x-data="{
                                    showEditMenu: false,
                                    editingName: false,
                                    newName: nomenclature.name,
                                    errorMessage: '',
                                }"
                                 class="flex flex-col justify-center items-start"
                            >
                                <span class="md:hidden font-semibold">Название: </span>
                                <div class="flex relative">
                                    <!-- Название -->
                                    <div class="flex flex-col w-full">
                                        <!-- Оверлей -->
                                        <div x-show="editing && editField === 'name'"
                                             class="flex fixed inset-0 bg-black opacity-50 z-30"
                                             @click="editingName = false, deletePn = false, addingPn = false; cancelEdit('name');"
                                             x-cloak>
                                        </div>
                                        <!-- Отображение названия -->
                                        <div x-show="!editing || editField !== 'name'" @click="initField('name')"
                                             class="cursor-pointer hover:underline text-gray-800 dark:text-gray-200">
                                            <div class="max-h-[90px] flex flex-col items-center justify-start overflow-auto px-1">
                                                <div x-text="nomenclature.name" class="break-all max-w-full text-left leading-snug"></div>
                                            </div>
                                        </div>

                                        <!-- Редактирование названия -->
                                        <div x-show="editing && editField === 'name'"
                                             class="flex items-center gap-2 z-40" x-cloak>
                                            <input type="text" x-model="form.name" x-ref="nameInput"
                                                   class="border border-gray-300 rounded-md text-sm text-gray-600 px-2 py-1 w-3/4 mr-2"
                                                   @keydown.enter="updateField('name', form.name)"
                                                   @keydown.escape="cancelEdit('name')"
                                            />
                                            <button
                                                @click="updateField('name', form.name)"
                                                class="bg-green-500 text-white px-2 py-1 rounded-full w-1/4">
                                                ✓
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Категория -->
                            <div x-show="!editing || editField !== 'category_id'"
                                 class="flex flex-col justify-center items-start">
                                <span class="md:hidden font-semibold">Категория: </span>
                                <span x-text="getCategoryName(nomenclature.category_id)"></span>
                            </div>

                            <!-- Поставщик -->
                            <div x-show="!editing || editField !== 'supplier_id'"
                                 class="flex flex-col justify-center items-start">
                                <span class="md:hidden font-semibold">Поставщик: </span>
                                <span x-text="getSupplierName(nomenclature.supplier_id)"></span>
                            </div>

                            <!-- Category -->
                            <!--<div class="flex items-center px-2">
                                <span class="md:hidden font-semibold">Категория: </span>
                                <span x-text="nomenclature.category ? nomenclature.category.name : '---'" :key="nomenclature.category.id"></span>
                            </div>-->
                            <!-- Supplier -->
                            <!--<div class="flex items-center px-2">
                                <span class="md:hidden font-semibold">Поставщик: </span>
                                <span x-text="nomenclature.suppliers ? nomenclature.suppliers.name : '---'"></span>
                            </div>-->
                            <!-- Brand -->
                            <div class="flex items-center">
                                <span class="md:hidden font-semibold">Брэнд: </span>
                                <div id="brand-component-nomenclature.id">
                                    <div id="brand-item-nomenclature.id"
                                         class="w-full md:w-1/12 mb-2 md:mb-0 cursor-pointer parent-container"
                                         x-data="{
                                             showPopover: false,
                                             allBrands: @js($brands),
                                             get nomenclatureBrands() {
                                                return nomenclature.brands ?? [];
                                             },
                                             selectedBrands: @entangle('selectedBrands').live || [],
                                             nomenclatureId: nomenclature.id,
                                             search: '',
                                             popoverX: 0,
                                             popoverY: 0,
                                             get selectedBrands() {
                                                return this.nomenclature.brands.map(b => b.id);
                                             },
                                             set selectedBrands(value) {
                                                this.nomenclature.brands = this.allBrands.filter(b => value.includes(b.id));
                                             },
                                             submit() {
                                                const ids = this.selectedBrands;
                                                $wire.set('selectedBrands', ids).then(() => {
                                                    $wire.updateNomenclatureBrands(this.nomenclatureId, ids).then(() => {
                                                        $wire.getUpdatedBrands(this.nomenclatureId).then((updated) => {
                                                            this.nomenclature.brands = this.allBrands.filter(b =>
                                                                updated.includes(b.id)
                                                            );
                                                            this.nomenclatureBrands = this.nomenclature.brands;
                                                        });
                                                        this.showPopover = false;
                                                    });
                                                });
                                             },
                                             filteredBrands() {
                                                return this.allBrands.filter(b =>
                                                    b.name.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                             },
                                             init() {

                                             }
                                         }"
                                         x-init="init"
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
                                         @brands-updated.window="(event) => {
                                            if (event.detail === nomenclature.id) {
                                                $wire.getUpdatedBrands(nomenclature.id).then((updated) => {
                                                    nomenclature.brands = allBrands.filter(b => updated.includes(b.id))
                                                })
                                            }
                                         }"
                                    >
                                        <!-- Текущие бренды -->
                                        <div class="flex flex-col h-24 w-20 justify-center p-1">
                                            <span class="md:hidden font-semibold">Brand:</span>
                                            <div class="overscroll-contain overflow-y-auto">
                                                <template x-if="nomenclatureBrands.length === 0">
                                                    <div class="px-3 py-2">---</div>
                                                </template>
                                                <template x-if="nomenclatureBrands.length > 0">
                                                    <span
                                                        x-text="nomenclatureBrands.map(b => b.name).join(', ')"></span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Поповер с мульти-выбором брендов -->
                                        <div x-show="showPopover"
                                             class="fixed z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg w-56 p-1"
                                             :style="`top: ${popoverY}px; left: ${popoverX}px;`"
                                             x-init="const onScroll = () => showPopover = false; window.addEventListener('scroll', onScroll)"
                                             @click.outside="showPopover = false"
                                             x-transition @click.stop>

                                            <!-- Поле поиска -->
                                            <div class="mb-2" @click.stop>
                                                <input type="text" x-model="search"
                                                       placeholder="Search brands..."
                                                       class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500
                                                       text-gray-700 dark:bg-gray-700 dark:text-gray-300"/>
                                            </div>

                                            <!-- Список брендов с мульти-выбором -->
                                            <div class="flex flex-row justify-between">
                                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-300 w-2/3 max-h-28 overflow-y-auto">
                                                    <template x-for="brand in filteredBrands()" :key="brand.id">
                                                        <li class="flex items-center space-x-2">
                                                            <input type="checkbox" :value="brand.id"
                                                                   :checked="selectedBrands.includes(brand.id)"
                                                                   :checked="selectedBrands.includes(brand.id)"
                                                                   @change="
                                                                       if ($event.target.checked) {
                                                                           selectedBrands.push(brand.id);
                                                                           nomenclature.brands.push(brand);
                                                                       } else {
                                                                           selectedBrands = selectedBrands.filter(id => id !== brand.id);
                                                                           nomenclature.brands = nomenclature.brands.filter(b => b.id !== brand.id);
                                                                       }
                                                                       nomenclature.brands = brands.filter(b => selectedBrands.includes(b.id));
                                                                   "
                                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                            <span x-text="brand.name"
                                                                  class="text-gray-700 dark:text-gray-200"></span>
                                                        </li>
                                                    </template>
                                                </ul>

                                                <!-- Кнопка подтверждения -->
                                                <div class="flex justify-center items-center w-1/3">
                                                    <button @click="submit"
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
                            <div class="flex items-center">
                                <span class="md:hidden font-semibold">Изображение:</span>
                                <div x-data="{
                                    isLoading: false,
                                    showTooltip: false,
                                    baseStoragePath: '{{ asset('storage') }}',
                                    nomenclatureId: nomenclature.id,
                                    refreshImage(imageUrl) {
                                        this.isLoading = true;
                                        setTimeout(() => {
                                            this.isLoading = false;
                                        }, 500);
                                    },
                                    computedImagePath(img) {
                                        return img && typeof img === 'string' && img.trim() !== '' ? '{{ asset('storage') }}/' + img : '';
                                    },
                                }"
                                     @nomenclature-image-updated.window="
                                         if ($event.detail.id === nomenclature.id) {
                                             nomenclature.image = $event.detail.image;
                                         }
                                     "
                                     x-on:livewire-upload-start="isUploading = true"
                                     x-on:livewire-upload-finish="isUploading = false"
                                     x-on:livewire-upload-error="isUploading = false"
                                     x-on:livewire-upload-progress="uploadProgress = $event.detail.progress"
                                     x-cloak class="flex gallery relative">
                                    <div class="flex flex-row w-auto max-w-[120px] max-h-[80px]">
                                        <template
                                            x-if="nomenclature && typeof nomenclature.image === 'string' && nomenclature.image.trim() !== ''">
                                            <img
                                                :src="computedImagePath(nomenclature.image)"
                                                :alt="nomenclature.name"
                                                @click="Livewire.dispatch('lightbox', computedImagePath(nomenclature.image))"
                                                class="object-contain rounded cursor-zoom-in"
                                            >
                                        </template>

                                        <template
                                            x-if="!nomenclature || !nomenclature.image || (typeof nomenclature.image === 'string' && nomenclature.image.trim() === '')">
                                            <x-empty-image class="text-white"/>
                                        </template>
                                    </div>
                                    <!-- Tooltip и кнопка загрузки -->
                                    <div @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
                                        <div x-show="showTooltip" x-transition
                                             class="absolute z-50 -top-6 left-6 w-max px-2 py-1 text-xs bg-green-500 text-white rounded shadow-lg">
                                            Change Image
                                        </div>
                                        <button
                                            @click="showImageUploading = true; selectedNomenclatureId = nomenclature.id"
                                            class="text-white rounded-full p-1 cursor-pointer h-[20px]">
                                            <x-icons.upload-arrow class="text-white"/>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Actions -->
                            <div class="flex w-2/8 items-center gap-2">
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
                    </div>
                </template>
                <div
                    @nomenclature-updated.window="(event) => {
                        const id = event.detail[0];
                        const ref = refs[id];

                        if (ref) {
                            ref.__x.$data.show = false; // скрыть через анимацию
                            setTimeout(() => {
                                const index = nomenclatures.findIndex(n => n.id == id);
                                if (index !== -1) {
                                    nomenclatures[index].is_archived = 1;
                                }
                            }, 350);
                        }
                    }"
                ></div>
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

    <!-- Форма для массового редактирования номенклатур -->
    <div x-show="showBulkEditModal" x-cloak x-transition class="fixed inset-0 flex items-center justify-center z-50">
        <!-- Оверлей -->
        <div class="flex fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity duration-300 z-30"
             @click="cancelBulkEdit()"
             x-cloak>
        </div>
        <div
            class="relative max-w-4xl w-full bg-[#1a2433] rounded-lg shadow-xl overflow-hidden transition-all duration-300 opacity-100 scale-100 z-50">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-700">
                <h2 class="text-xl font-medium text-white">Массовое изменение Номенклатур</h2>
                <button @click="cancelBulkEdit()" type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        aria-label="Close">
                    <svg class="w-5 h-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                              clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-5 gap-4 text-sm font-medium text-gray-400 px-4 pt-4">
                <div>Номер</div>
                <div>Название</div>
                <div>Категория</div>
                <div>Поставщик</div>
                <div>Брэнд</div>
            </div>
            <div class="space-y-3 p-4">
                <template x-for="item in bulkEditItems" :key="item.id">
                    <div class="grid grid-cols-5 gap-4">
                        <input type="text" x-model="item.nn" placeholder="Номер"
                               class="w-full bg-[#111827] border border-[#2a3749] rounded px-3 py-2 text-white focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none"/>
                        <input type="text" x-model="item.name" placeholder="Название"
                               class="w-full bg-[#111827] border border-[#2a3749] rounded px-3 py-2 text-white focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none"/>

                        <!-- Категория -->
                        <select x-model.number="item.category_id"
                                class="w-full bg-[#111827] border border-[#2a3749] rounded px-3 py-2 text-white focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none">
                            <!-- Текущая -->
                            <template x-if="categories.find(c => c.id === item.category_id)">
                                <option
                                    :value="item.category_id"
                                    x-text="categories.find(c => c.id === item.category_id)?.name"
                                ></option>
                            </template>

                            <!-- Остальные -->
                            <template x-for="cat in categories.filter(c => c.id !== item.category_id)" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                        <!-- Поставщик -->
                        <select x-model.number="item.supplier_id"
                                class="w-full bg-[#111827] border border-[#2a3749] rounded px-3 py-2 text-white focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none">
                            <!-- Текущая -->
                            <template x-if="categories.find(s => s.id === item.supplier_id)">
                                <option
                                    :value="item.supplier_id"
                                    x-text="suppliers.find(s => s.id === item.supplier_id)?.name"
                                ></option>
                            </template>

                            <!-- Остальные -->
                            <template x-for="sup in suppliers.filter(s => s.id !== item.supplier_id)" :key="sup.id">
                                <option :value="sup.id" x-text="sup.name"></option>
                            </template>
                        </select>
                        <!-- Брэнд -->
                        <div x-data="brandSelector(item, @js($brands))"
                             class="relative flex flex-col justify-center w-full bg-[#111827] border border-[#2a3749] rounded px-3 py-2 text-white focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none">
                            <!-- Отображение выбранных брендов -->
                            <div @click="toggle()" class="cursor-pointer">
                                <template x-if="selected.length === 0">
                                    <span class="text-gray-400">---</span>
                                </template>
                                <template x-if="selected.length > 0">
                                    <span x-text="selected.map(b => b.name).join(', ')"></span>
                                </template>
                            </div>

                            <!-- Выпадающий список -->
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute z-50 mt-1 w-full bg-white border border-gray-300 shadow-lg max-h-60 overflow-y-auto
                                 dark:bg-gray-800 dark:border-gray-600 rounded-lg p-1">
                                <input type="text" x-model="search" placeholder="Поиск брендов..."
                                       class="w-full p-1 border border-gray-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500
                                                       text-gray-700 dark:bg-gray-700 dark:text-gray-300 mb-2"/>

                                <template x-for="brand in filteredBrands()" :key="brand.id">
                                    <label class="flex items-center space-x-2 cursor-pointer mb-1">
                                        <input type="checkbox"
                                               :value="brand.id"
                                               :checked="selectedIds.includes(brand.id)"
                                               @change="toggleBrand(brand)"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"/>
                                        <span x-text="brand.name" class="text-gray-700 dark:text-gray-200"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-end px-6 py-4 bg-[#1a2433] border-t border-[#2a3749]">
                <button @click="cancelBulkEdit()"
                        class="mr-3 px-4 py-2 text-sm font-medium text-white bg-transparent border border-[#2a3749] rounded hover:bg-[#2a3749] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-green-500 transition-colors duration-200">
                    Отмена
                </button>
                <button @click="submitBulkEdit()"
                        class="px-4 py-2 text-sm font-medium text-black bg-green-500 rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-green-500 transition-colors duration-200">
                    Применить
                </button>
            </div>
        </div>
    </div>

    <!-- Форма для замены изображения номенклатуры -->
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-show="showImageUploading" x-cloak
         x-transition>
        <!-- Modal Backdrop -->
        <div x-show="showImageUploading"
             class="flex fixed inset-0 bg-black opacity-50 z-30"
             @click="showImageUploading = false, closeImageModal()"
             x-transition.opacity.50 x-cloak>
        </div>

        <!-- Modal Content -->
        <div x-show="showImageUploading"
             class="relative flex items-center justify-center p-4 z-50 w-1/3">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full">
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
                <!-- Прогресс загрузки -->
                <div x-show="isUploading"
                     class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
                    <div class="text-white text-lg">Uploading... (<span x-text="uploadProgress"></span>%)
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    <button type="button"
                            @click="closeImageModal"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="button"
                            @click="uploadImage(selectedNomenclatureId)"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Upload
                    </button>
                </div>
            </div>
        </div>
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
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                                        focus:ring-primary-500 focus:border-primary-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
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
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                                        focus:ring-primary-500 focus:border-primary-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
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
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                                        focus:ring-primary-500 focus:border-primary-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
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
    <script>
        function brandSelector(item, brands) {
            return {
                open: false,
                search: '',
                all: brands,
                selected: [...(item.brands ?? [])],

                get selectedIds() {
                    return this.selected.map(b => b.id);
                },
                toggle() {
                    this.open = !this.open;
                },
                toggleBrand(brand) {
                    const exists = this.selected.find(b => b.id === brand.id);
                    if (exists) {
                        this.selected = this.selected.filter(b => b.id !== brand.id);
                    } else {
                        this.selected.push(brand);
                    }
                    item.brands = [...this.selected];
                },
                filteredBrands() {
                    return this.all.filter(b => b.name.toLowerCase().includes(this.search.toLowerCase()));
                }
            }
        }
    </script>
</div>
