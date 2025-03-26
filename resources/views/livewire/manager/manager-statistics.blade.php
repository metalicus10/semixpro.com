<div class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <div class="flex justify-between items-center mb-6">
        <h1 class="md:text-3xl text-md font-bold text-gray-500 dark:text-gray-400">Transferred spare parts</h1>
    </div>

    <div class="flex flex-col" x-data="{
            transfers: @entangle('transfers'), activeTab: @entangle('selectedTechnicianId'), parts: @entangle('parts'), search: '',
            updateActiveTab(tabId){
                this.activeTab = tabId;
                search = '';
            },

            updateActiveWarehouse(tabId) {
                $wire.selectWarehouse(tabId);
            },

            filteredParts() {
                // Проверка на массив
                const partsList = Array.isArray(this.parts) ? this.parts : [this.parts];

                // Фильтрация по критериям поиска
                return partsList.filter(part => {
                    const partName = part.parts?.name?.toLowerCase() || '';
                    const technicianName = part.technician_details?.name?.toLowerCase() || '';

                    return partName.includes(this.search.toLowerCase()) ||
                           technicianName.includes(this.search.toLowerCase());
                });
            },
        }" >
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <!-- Табы с именами техников -->
                    <div class="flex overflow-x-auto mb-4">
                        <ul class="flex flex-nowrap gap-1 no-scrollbar text-sm font-medium text-center text-gray-500
                        border-b border-gray-200 dark:border-gray-700 dark:text-gray-400"
                            x-ref="tabContainer"
                            style="scroll-behavior: smooth; overflow-x: hidden;">
                        @foreach ($technicians as $technician)
                            <li class="shrink-0 cursor-pointer"
                                :class="{'bg-gray-900 text-gray-100': activeTab === {{ $technician->id }}, 'bg-gray-900': activeTab !== {{ $technician->id }}}"
                                wire:click="selectTechnician({{ $technician->id }})" @click="selectedParts = []"
                            >
                                <div x-data>
                                    <a href="#"
                                       @click.prevent.debounce.500ms="updateActiveTab({{ $technician['id'] }})"
                                       :class="activeTab === {{ $technician->id }} ? 'text-orange-500 bg-[#b13a00] dark:bg-[#ff8144] dark:text-orange-500' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'"
                                       class="bg-gray-800 inline-block p-2 rounded-t-lg border-t border-x border-gray-700 hover:border-gray-600 border-dashed text-clip"
                                    >{{ $technician->name }}</a>
                                </div>
                            </li>
                        @endforeach
                        </ul>
                    </div>

                    <!-- Версия таблицы для десктопных устройств -->
                    <div class="flex flex-col w-full">
                        <h2 class="text-gray-700 dark:text-gray-400 uppercase text-lg font-semibold p-3">Запчасти
                            техника <span class="dark:text-orange-500">{{ $technicians->where('id', $selectedTechnicianId)->first()?->name }}</span></h2>
                        <!-- Заголовок таблицы -->
                        <div class="hidden sm:flex w-full bg-gray-50 dark:bg-gray-700 text-gray-200 uppercase text-xs font-bold">
                            <div class="px-6 py-3 w-1/4 text-start dark:text-neutral-500">Name</div>
                            <div class="px-6 py-3 w-1/4 text-start dark:text-neutral-500">Transmitted</div>
                            <div class="px-6 py-3 w-1/4 text-start dark:text-neutral-500">Remained</div>
                            <div class="px-6 py-3 w-1/4 text-start dark:text-neutral-500">Used</div>
                        </div>

                        <!-- Тело таблицы -->
                        <div class="flex flex-col space-y-2 md:space-y-0 dark:bg-gray-900">
                            <template x-for="part in filteredParts()" :key="part.id">
                                <template x-if="Boolean(Number(part.technician_details.is_active)) && part.technician_id == part.technician_details.user_id">

                                    <div class="flex flex-col md:flex-row w-full md:items-center bg-white border
                                        dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-600 dark:hover:bg-[#162033] p-1 relative">
                                                <span x-init="console.log(part);"></span>
                                                <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-700 dark:text-gray-400">
                                                    <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Name:</span>
                                                    <span
                                                        class="cursor-pointer text-blue-500 hover:text-blue-700"
                                                        @click="
                                                            console.log('Click on part', part.id, 'in warehouse', part.warehouse_id);
                                                            $dispatch('highlight-part', { partId: part.id, warehouseId: part.warehouse_id });

                                                        ">
                                                        <span x-text="part.parts.name"></span>
                                                    </span>
                                                </div>
                                                <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-800 dark:text-gray-400">
                                                    <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Transmitted:</span>
                                                    <span x-text="part.total_transferred"></span>
                                                </div>
                                                <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-800 dark:text-gray-400">
                                                    <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Remained:</span>
                                                    <span x-text="part.quantity"></span>
                                                </div>
                                                <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-800 dark:text-gray-400">
                                                    <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Used:</span>
                                                    <span x-text="(part.total_transferred - part.quantity) || 0"></span>
                                                </div>

                                    </div>
                                </template>
                            </template>
                            <template x-if="filteredParts().length <= 0">
                                <div class="px-6 py-5 text-sm text-center text-gray-400 bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    No data
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
