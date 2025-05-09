<div
    class="flex-1 px-4 py-2 md:mb-0 cursor-pointer font-semibold truncate"
    x-data="{ managerUrlModalVisible: @entangle('managerUrlModalVisible'), clickCount: 0, partId: {{ $part['id'] }}, modalOpen: false, urlData: @js(json_decode($part->url, true)) }"
    x-init="
        window.addEventListener('modal-close', () => {
            modalOpen = false;
        });
        Livewire.on('urlUpdated', updatedPartId => {
            if (updatedPartId === {{ $part->id }}) {
                $wire.call('getUrlData', {{ $part->id }}).then(data => {
                    urlData = data;
                });
            }
        });
    "
    @click="
        if (modalOpen) return;
        clickCount++;
        setTimeout(() => {
        if (clickCount === 1) {
            // Одиночный клик - проверка на наличие ссылки
            if (urlData?.url) {
                window.open(urlData.url, '_blank');
            }
        } else if (clickCount === 2) {
            // Двойной клик - открытие модального окна для редактирования
                modalOpen = true;
                $wire.openManagerUrlModal(partId);
            }
            clickCount = 0; // Сброс счетчика
        }, 300); // Таймаут для определения двойного клика
    "
    @modal-close.window="modalOpen = false"
>
    <div x-data>
        <template x-if="urlData?.text">
            <div>
                <span class="md:hidden font-semibold">URL:</span>
                <span x-text="urlData.text"></span>
            </div>
        </template>

        <template x-if="!urlData?.text && urlData?.url">
            <div>
                <span class="md:hidden font-semibold">URL:</span>
                <span x-text="urlData.url"></span>
            </div>
        </template>

        <template x-if="!urlData?.text && !urlData?.url">
        <span class="text-gray-500" title="Edit URL">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.232 5.232l3.536 3.536M9 13h.01M6 9l5 5-3 3h6l-1.293-1.293a1 1 0
                    010-1.414l7.42-7.42a2.828 2.828 0 10-4-4l-7.42 7.42a1 1 0 01-1.414 0L6 9z"
                />
            </svg>
        </span>
        </template>
    </div>

    <template x-if="managerUrlModalVisible">
        <div
            x-data="{
            managerSupplier: @entangle('managerSupplier'),
            managerUrl: @entangle('managerUrl'),
            updateUrl() {
                $wire.updateUrl();
            }
        }"
            x-init="document.body.classList.add('overflow-hidden')"
            x-on:close-modal.window="document.body.classList.remove('overflow-hidden')"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 w-full h-full"
        >
            <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-2xl max-h-full overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Редактировать ссылку</h2>
                    <button @click="managerUrlModalVisible = false"
                            class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="selectedSupplier">Supplier:</label>
                    <select x-model="managerSupplier" id="selectedSupplier"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->name }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="managerUrl">URL:</label>
                    <input x-model="managerUrl" type="text" id="managerUrl"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter URL">
                </div>

                <div class="flex flex-col md:flex-row justify-end space-y-2 md:space-y-0 md:space-x-2">
                    <button @click="managerUrlModalVisible = false"
                            class="w-full md:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Отмена
                    </button>
                    <button @click="updateUrl()"
                            class="w-full md:w-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
