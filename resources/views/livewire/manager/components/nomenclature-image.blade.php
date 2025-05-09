<div x-data="{
        nomenclatureName: '{{ $nomenclature['name'] }}',
        nomenclatureImage:'{{ $nomenclature['image'] }}',
        showTooltip: false,
        isUploading: false,
        uploadProgress: 0,
        showImageUploading: false,
        nomenclatureId: null,
        selectedFile: null,
        error: null,
        setup() {
            window.addEventListener('open-image-modal', event => {
                this.nomenclatureId = event.detail.id;
                this.show = true;
                this.selectedFile = null;
                this.error = null;
            });
        },
        closeImageModal() {
            this.show = false;
            this.selectedFile = null;
            this.error = null;
        },
        handleFileChange(event) {
            this.selectedFile = event.target.files[0];
        },
        uploadImage() {
            if (!this.selectedFile) {
                this.error = 'Пожалуйста, выберите файл для загрузки.';
                return;
            }

            $wire.uploadImage(this.nomenclatureId, this.selectedFile)
                .then(() => {
                    this.close();
                })
                .catch(e => {
                    this.error = 'Ошибка загрузки файла.';
                    console.error(e);
                });
        }
    }" x-init="setup()" x-cloak class="flex gallery relative">
    <div class="flex flex-row w-auto max-w-[120px] max-h-[80px]">
        @if (!empty($nomenclature['image']))
            <img src="{{ asset('storage') . $nomenclature['image'] }}"
                 alt="{{ $nomenclature['name'] }}"
                 onclick="Livewire.dispatch('lightbox', '{{ asset('storage') . $nomenclature['image'] }}')"
                 class="object-contain rounded cursor-zoom-in">
        @else
            <span>
                <livewire:components.empty-image/>
            </span>
        @endif
    </div>
    <!-- Tooltip и кнопка загрузки -->
    <div x-data="{ showTooltip: false }" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
        <div x-show="showTooltip" x-transition
             class="absolute z-50 -top-6 left-6 w-max px-2 py-1 text-xs bg-green-500 text-white rounded shadow-lg">
            Change Image
        </div>
        <button @click="$wire.openImageModal({{ $nomenclature['id'] }})"
                class="text-white rounded-full p-1 cursor-pointer h-[20px]">
            <livewire:components.upload-green-arrow :key="'upload-green-arrow-nomenclatures'.auth()->id()" />
        </button>
    </div>
    <!-- Прогресс загрузки -->
    <div x-show="isUploading" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
        <div class="text-white text-lg">Uploading... (<span x-text="uploadProgress"></span>%)</div>
    </div>

    <div x-data="{ showImageModal: @entangle('showImageModal') }">
        <!-- Modal Backdrop -->
        <div x-show="showImageUploading" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40"
             x-transition.opacity x-cloak></div>

        <!-- Modal Content -->
        <div x-show="showImageUploading" x-transition
             class="fixed inset-0 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Upload Image</h3>

                <!-- File Input -->
                <div class="mb-4">
                    <input type="file" @change="handleFileChange"
                           class="block w-full text-gray-800 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                    <template x-if="error">
                        <p class="mt-2 text-red-500 text-sm" x-text="error"></p>
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
