<div x-data="{ showAddSupplierModal: false, showDeleteConfirmModal: false }"
     @supplier-added.window="showAddSupplierModal = false"
     @confirm-delete.window="showDeleteConfirmModal = true"
     @supplier-deleted.window="showDeleteConfirmModal = false"
    class="p-1 md:p-4 dark:bg-brand-background dark:border-gray-700 shadow-md rounded-lg overflow-hidden">

    <!-- Таблица поставщиков -->
    <div x-data="{
        view: 'list',
        scanning: false,
        html5QrCode: null,
        async loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },
        async startScan() {
            if (typeof Html5Qrcode === 'undefined') {
                console.warn('QR script not loaded, loading...');
                await this.loadScript('https://unpkg.com/html5-qrcode');
            }

            if (typeof Html5Qrcode === 'undefined') {
                console.error('html5-qrcode still not available');
                return;
            }

            this.scanning = true;

            if (!this.html5QrCode) {
                this.html5QrCode = new Html5Qrcode('qr-reader');
            }

            this.html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: 250 },
                (decodedText) => {
                    $wire.set('search', decodedText);
                    this.stopScan();
                },
                (err) => {}
            ).catch((e) => {
                console.error(e);
                this.stopScan();
            });
        },
        stopScan() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.scanning = false;
                }).catch((err) => {
                    console.error('Stop scan error', err);
                });
            }
        }
    }"
        x-init="setTimeout(() => {
            if (typeof html5QrCode !== 'undefined') {
                console.log('QR ready');
            }
        }, 300)"
         class="space-y-4">

        {{-- Верхняя панель --}}
        <div class="flex items-center justify-between gap-4 w-full">

                <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400 max-w-2xl">Suppliers</h1>

                <div class="relative w-full max-w-3xl">
                    <input type="text" wire:model.live="search" placeholder="Search product..." class="flex h-10 rounded-md border border-input px-3 text-sm ring-0 ring-offset-background file:border-0 bg-transparent file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10 pr-10 py-2 bg-sidebar w-full" />
                    <div class="absolute inset-y-0 right-0 flex items-center px-3">
                        <button @click="startScan" class="flex items-center gap-1 text-green-400 outline-1 rounded outline-gray-700 hover:text-white hover:bg-[#28282ba3] focus:outline-gray-700 transition cursor-pointer">
                            @include('icons.scan')
                            <span class="hidden sm:inline">Scan</span>
                        </button>
                    </div>
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div x-show="scanning" @click.away="scanning = false;" class="fixed inset-0 flex items-center justify-center">
                        <div id="qr-reader" class="w-64 h-64 bg-black rounded-lg overflow-hidden"></div>
                    </div>
                </div>
                <div class="flex items-center space-x-5">
                    <div class="inline-flex items-center rounded-xl border overflow-hidden bg-gray-900 text-white">
                        <button
                            @click="view = 'list'"
                            :class="view === 'list' ? 'bg-gray-700 text-green-400 border-green-400!' : 'bg-gray-900 border-gray-700'"
                            class="p-2 transition-colors duration-200 cursor-pointer rounded-l-xl border hover:border-green-400! active:border-green-400 hover:text-green-400!"
                        >
                            <!-- Иконка списка -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <button
                            @click="view = 'grid'"
                            :class="view === 'grid' ? 'bg-gray-700 text-green-400 border-green-400!' : 'bg-gray-900 border-gray-700'"
                            class="p-2 transition-colors duration-200 cursor-pointer rounded-r-xl border hover:border-green-400! active:border-green-400 hover:text-green-400!"
                        >
                            <!-- Иконка сетки -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 4h4v4H4V4zm6 0h4v4h-4V4zm6 0h4v4h-4V4zM4 10h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4zM4 16h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z" />
                            </svg>
                        </button>
                    </div>

                    <button class="p-3 hover:bg-accent text-white text-accent-foreground! hover:text-accent-foreground transition-colors duration-200 rounded-md bg-gray-800 text-sm font-medium ring-offset-background cursor-pointer">@include('icons.menu')</button>
                    <button @click="showAddSupplierModal = true" class="px-4 py-2 bg-brand-accent text-white rounded-md hover:bg-green-600 cursor-pointer">Add Supplier</button>
                </div>

        </div>

        {{-- Список поставщиков --}}
        <div :class="view === 'grid' ? 'grid grid-cols-2 gap-4' : 'flex flex-col gap-2'">
            <template x-for="supplier in @js($suppliers)" :key="supplier.id">
                <div class="bg-gray-800 rounded-xl p-4 text-white flex flex-col gap-2">

                    {{-- Верхняя часть --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center font-bold text-sm overflow-hidden">
                                <template x-if="supplier.image">
                                    <img :src="supplier.image" alt="supplier.name" />
                                </template>
                                <template x-if="!supplier.image">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink"
                                     version="1.1" width="32"
                                     height="32" viewBox="0 0 256 256"
                                     xml:space="preserve">
                                                                             <defs></defs>
                                    <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;"
                                       transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                                        <path
                                            d="M 89 20.938 c -0.553 0 -1 0.448 -1 1 v 46.125 c 0 2.422 -1.135 4.581 -2.898 5.983 L 62.328 50.71 c -0.37 -0.379 -0.973 -0.404 -1.372 -0.057 L 45.058 64.479 l -2.862 -2.942 c -0.385 -0.396 -1.019 -0.405 -1.414 -0.02 c -0.396 0.385 -0.405 1.019 -0.02 1.414 l 3.521 3.62 c 0.37 0.38 0.972 0.405 1.373 0.058 l 15.899 -13.826 l 21.783 22.32 c -0.918 0.391 -1.928 0.608 -2.987 0.608 H 24.7 c -0.552 0 -1 0.447 -1 1 s 0.448 1 1 1 h 55.651 c 5.32 0 9.648 -4.328 9.648 -9.647 V 21.938 C 90 21.386 89.553 20.938 89 20.938 z"
                                            style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"/>
                                        <path
                                            d="M 89.744 4.864 c -0.369 -0.411 -1.002 -0.444 -1.412 -0.077 l -8.363 7.502 H 9.648 C 4.328 12.29 0 16.618 0 21.938 v 46.125 c 0 4.528 3.141 8.328 7.356 9.361 l -7.024 6.3 c -0.411 0.368 -0.445 1.001 -0.077 1.412 c 0.198 0.22 0.471 0.332 0.745 0.332 c 0.238 0 0.476 -0.084 0.667 -0.256 l 88 -78.935 C 90.079 5.908 90.113 5.275 89.744 4.864 z M 9.648 14.29 h 68.091 L 34.215 53.33 L 23.428 42.239 c -0.374 -0.385 -0.985 -0.404 -1.385 -0.046 L 2 60.201 V 21.938 C 2 17.721 5.431 14.29 9.648 14.29 z M 2 68.063 v -5.172 l 20.665 -18.568 l 10.061 10.345 L 9.286 75.692 C 5.238 75.501 2 72.157 2 68.063 z"
                                            style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"/>
                                        <path
                                            d="M 32.607 35.608 c -4.044 0 -7.335 -3.291 -7.335 -7.335 s 3.291 -7.335 7.335 -7.335 s 7.335 3.291 7.335 7.335 S 36.652 35.608 32.607 35.608 z M 32.607 22.938 c -2.942 0 -5.335 2.393 -5.335 5.335 s 2.393 5.335 5.335 5.335 s 5.335 -2.393 5.335 -5.335 S 35.549 22.938 32.607 22.938 z"
                                            style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"/>
                                    </g>
                                </svg>
                                </template>
                            </div>
                            <div>
                                <div class="font-semibold text-lg" x-text="supplier.name"></div>
                                <div class="text-sm text-gray-300" x-text="supplier.contact_person"></div>
                            </div>
                        </div>
                        <div class="flex gap-8 text-sm">
                            <div>
                                <div class="text-gray-400">Contacts</div>
                                <div class="flex flex-col">
                                    <span x-text="supplier.email"></span>
                                    <span x-text="supplier.phone"></span>
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-400">Receivables</div>
                                <div>$<span x-text="supplier.receivables"></span></div>
                            </div>
                            <div>
                                <div class="text-gray-400">Used Credits</div>
                                <div>$<span x-text="supplier.used_credits"></span></div>
                            </div>
                        </div>

                        <div x-data="{ open: false }" class="">
                            <button @click="open = !open"
                                    class="btn btn-sm bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 rounded-full">
                                @include('icons.menu')
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open" @click.outside="open = false"
                                 x-transition
                                 class="absolute right-8 mt-2 w-32 bg-white rounded-md shadow-lg z-50 overflow-hidden text-sm text-gray-800">
                                <button @click="$wire.editSupplier(supplier.id)"
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                                    ✏️ Edit
                                </button>
                                <button @click="$wire.confirmDelete(supplier.id)"
                                        class="block w-full text-left px-4 py-2 hover:bg-red-100 text-red-600">
                                    🗑 Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Нижняя часть --}}
                    <div class="flex justify-between items-center text-sm text-gray-300 pt-2 border-t border-gray-700 mt-2">
                        <div class="flex items-center gap-2">
                            @include('icons.location')
                            <a href="#" class="hover:underline" x-text="supplier.address"></a>
                        </div>
                        <div class="flex items-center gap-2">
                            @include('icons.package')
                            <span x-text="`${supplier.product_count} product(s)`"></span>
                        </div>
                        <div>
                            <span :class="supplier.active ? 'bg-blue-600 text-white px-2 py-1 rounded' : 'bg-gray-600 text-gray-300 px-2 py-1 rounded'" x-text="supplier.active ? 'Active' : 'Inactive'"></span>
                        </div>
                    </div>

                </div>
            </template>
        </div>

    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div x-show="showDeleteConfirmModal" class="fixed inset-0 flex items-center justify-center">
        <!-- Оверлей -->
        <div x-show="showDeleteConfirmModal"
             class="flex fixed inset-0 bg-black opacity-50 z-30"
             @click="showDeleteConfirmModal = false"
             x-cloak>
        </div>
        <div class="bg-white p-6 rounded shadow-lg z-50">
            <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
            <p class="mb-4">Are you sure you want to delete this supplier?</p>

            <div class="flex justify-end space-x-2">
                <button @click="showDeleteConfirmModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button wire:click="deleteSupplier" class="px-4 py-2 bg-red-500 text-white rounded">Delete</button>
            </div>
        </div>
    </div>

    <!-- Модальное окно для добавления поставщика -->
    <div x-data="{
        supplier: {
            name: '',
            contact_name: '',
            email: '',
            phone: '',
            receivables: '',
            used_credits: '',
            address: '',
            is_active: true,
        },
        imageFile: null,
        imagePreview: null,
        open() { showAddSupplierModal = true },
        close() {
            showAddSupplierModal = false;
            this.reset();
        },
        reset() {
            this.supplier = {
                name: '', contact_name: '', email: '', phone: '',
                receivables: '', used_credits: '', address: '', is_active: true
            };
            this.imageFile = null;
            this.imagePreview = null;
            this.$refs.fileInput.value = '';
        },
        submit() {
            const formData = new FormData();
            Object.entries(this.supplier).forEach(([key, val]) => formData.append(key, val));
            if (this.imageFile) {
                formData.append('image', this.imageFile);
            }

            $wire.upload('image', this.imageFile, () => {
                $wire.addSupplier(this.supplier);
                this.close();
            });
        },
        previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.imageFile = file;

            const reader = new FileReader();
            reader.onload = e => {
                this.imagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },
    }"
         @click.away="showAddSupplierModal = false"
         x-show="showAddSupplierModal"
         x-cloak
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative" @click.away="showAddSupplierModal = false">
            <h3 class="text-xl font-semibold mb-4">Add New Supplier</h3>

            <div class="grid grid-cols-1 gap-3">
                <input type="text" x-model="supplier.name" placeholder="Supplier Name" class="input input-bordered w-full">
                <input type="text" x-model="supplier.contact_name" placeholder="Contact Name" class="input input-bordered w-full">
                <input type="email" x-model="supplier.email" placeholder="Email" class="input input-bordered w-full">
                <input type="tel" x-model="supplier.phone" placeholder="Phone" class="input input-bordered w-full">
                <input type="text" x-model="supplier.address" placeholder="Address" class="input input-bordered w-full">
                <div class="inline-flex justify-between">
                    <input type="file" accept="image/*"
                           @change="previewImage"
                           x-ref="fileInput"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100 p-4 py-2">
                    <template x-if="imagePreview">
                        <img :src="imagePreview" alt="Preview" class="w-24 h-24 rounded object-cover border border-gray-300">
                    </template>
                </div>

                <label class="flex items-center gap-2 mt-2">
                    <input type="checkbox" x-model="supplier.is_active" class="form-checkbox">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex justify-end mt-6 gap-2">
                <button @click="close"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button @click="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Add
                </button>
            </div>
        </div>
    </div>

</div>
