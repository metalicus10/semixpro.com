<div x-data="{
    modalOpen: false,
    assignAll: false,
    selectedWarehouses: [],
    warehouseParts: {},
    partQuantities: {},

    openModal() {
        if (this.selectedWarehouses.length > 0) {
            this.modalOpen = true;
        }
    },

    closeModal() {
        this.modalOpen = false;
    },

    loadParts(warehouseId) {
        console.log('Запрос на загрузку запчастей для склада', warehouseId);
        $wire.loadWarehouseParts(warehouseId);
    },

    setMaxQuantities() {
        if (this.assignAll) {
            Object.values(this.warehouseParts).forEach(parts => {
                parts.forEach(part => {
                    this.partQuantities[part.id] = part.stock;
                });
            });
        }
    }
}"
     @warehouse-parts-loaded.window="(event) => {
        // Проверяем, существует ли detail[0] и parts внутри него
        if (!event.detail[0] || !event.detail[0].parts) {
            console.warn('Не удалось получить запчасти для склада:', event.detail[0]?.warehouseId);
            return;
        }
        // Обновляем warehouseParts
        warehouseParts[event.detail[0].warehouseId] = event.detail[0].parts.map(part => ({
            id: part.id,
            name: part.name,
            stock: part.quantity
        }));
     }"
>
    <h2 class="text-lg font-bold mb-2 text-gray-600 dark:text-gray-300">Назначение складов технику</h2>

    <label class="block dark:text-gray-300">Выберите техника:</label>
    <select wire:model.live="selectedTechnician" class="w-full p-2 my-4 border rounded dark:text-gray-300 bg-gray-100 dark:bg-gray-800">
        <option value="">-- Выберите техника --</option>
        @foreach($technicians as $tech)
            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
        @endforeach
    </select>

    @if(!empty($selectedTechnician))
        <div class="mb-4 dark:text-gray-300">
            <label class="block">Выберите склады:</label>
            <div class="flex flex-col space-y-2 mt-2">
                @foreach($warehouses as $warehouse)
                    <div class="flex items-center space-x-4 border p-2 rounded">
                        <input type="checkbox" x-model="selectedWarehouses" @change="loadParts({{ $warehouse->id }})"
                               value="{{ $warehouse->id }}">
                        <span class="flex-1">{{ $warehouse->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <button @click="openModal()" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
            Назначить склады
        </button>
    @endif

    <!-- Модальное окно выбора количества запчастей -->
    <div x-show="modalOpen" class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50">
        <div class="relative bg-white rounded-lg shadow-lg dark:bg-gray-800 border border-solid dark:border-gray-200 rounded-lg max-w-7xl w-full p-6">
            <h2 class="dark:text-gray-300 text-lg font-semibold mb-4">Передача запчастей технику</h2>

            <!-- Галочка "Присвоить все запчасти" -->
            <label class="flex items-center mb-4 dark:text-gray-300">
                <input type="checkbox" x-model="assignAll" @change="setMaxQuantities()" class="mr-2">
                Присвоить все запчасти
            </label>

            <div class="grid grid-cols-{{ count($selectedWarehouses) > 1 ? '2' : '1' }} gap-4 dark:bg-gray-700">
                <template x-for="(parts, warehouseId) in warehouseParts" :key="warehouseId">
                    <div class="border p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Склад #<span x-text="warehouseId" x-init="console.log(warehouseParts[warehouseId]);"></span></h3>
                        <div class="max-h-60 overflow-y-auto">
                            <table class="w-auto">
                                <thead>
                                <tr class="dark:text-gray-300">
                                    <th class="p-2 border">Название</th>
                                    <th class="p-2 border">Доступно</th>
                                    <th class="p-2 border">Передать</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="part in parts" :key="part.id">
                                    <tr class="dark:text-gray-300">
                                        <td class="p-2 border" x-text="part.name"></td>
                                        <td class="p-2 border text-center" x-text="part.stock"></td>
                                        <td class="p-2 border text-center">
                                            <input type="number" min="1"
                                                   :max="part.stock"
                                                   x-model="partQuantities[part.id]"
                                                   class="border rounded p-1 w-20 dark:text-gray-300">
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Кнопки управления -->
            <div class="flex justify-end mt-4 dark:text-gray-300">
                <button @click="closeModal()" class="btn btn-gray mr-2">Отмена</button>
                <button wire:click="assignWarehouses" class="btn btn-success">Подтвердить</button>
            </div>
        </div>
    </div>

</div>
