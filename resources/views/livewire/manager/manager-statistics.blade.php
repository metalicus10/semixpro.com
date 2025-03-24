<div class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <div class="flex justify-between items-center mb-6">
        <h1 class="md:text-3xl text-md font-bold text-gray-500 dark:text-gray-400">Transferred spare parts</h1>
    </div>

    <div class="flex flex-col" x-data="technicianTabs">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <!-- Табы с именами техников -->
                    <div class="flex overflow-x-auto mb-4">
                        <template x-for="(technician, index) in technicians" :key="index" x-init="console.log(technicians);">
                            <button
                                @click="activeTab = index"
                                :class="{'bg-blue-500 text-white': activeTab === index, 'bg-gray-200 text-gray-700': activeTab !== index}"
                                class="px-4 py-2 rounded-lg mr-2">
                                <span x-text="technician.name"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Версия таблицы для десктопных устройств -->
                    <div class="flex flex-col">
                        <!-- Заголовок таблицы -->
                        <div class="hidden sm:flex bg-gray-50 dark:bg-gray-700 text-gray-200 uppercase text-xs font-bold">
                            <div class="px-6 py-3 w-1/5 text-start dark:text-neutral-500">Name</div>
                            <div class="px-6 py-3 w-1/5 text-start dark:text-neutral-500">Technician</div>
                            <div class="px-6 py-3 w-1/5 text-start dark:text-neutral-500">Transmitted</div>
                            <div class="px-6 py-3 w-1/5 text-start dark:text-neutral-500">Remained</div>
                            <div class="px-6 py-3 w-1/5 text-start dark:text-neutral-500">Used</div>
                        </div>

                        <!-- Тело таблицы -->
                        <div class="">
                            @forelse ($transfers as $transfer)
                                <div class="flex flex-col md:flex-row items-start md:items-center bg-white text-gray-700 dark:text-gray-400 border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033] p-4">
                                    <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-700 dark:text-gray-400">
                                        <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Name:</span>
                                        {{ $transfer->part->name }}
                                    </div>
                                    <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-700 dark:text-gray-400">
                                        <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Technician:</span>
                                        {{ $transfer->technician->name }}
                                    </div>
                                    <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-800 dark:text-gray-400">
                                        <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Transmitted:</span>
                                        {{ $transfer->total_transferred }}
                                    </div>
                                    <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-800 dark:text-gray-400">
                                        <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Remained:</span>
                                        {{ $transfer->quantity }}
                                    </div>
                                    <div class="px-6 py-2 w-full sm:w-1/5 text-sm font-medium text-gray-800 dark:text-gray-400">
                                        <span class="block sm:hidden font-bold text-gray-500 dark:text-gray-400">Used:</span>
                                        {{ $transfer->total_transferred - $transfer->quantity ?? 0 }}
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-5 text-sm text-center text-gray-400 bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    No data
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <!-- AlpineJS инициализация -->
                <script>
                    document.addEventListener('alpine:init', () => {
                        Alpine.data('technicianTabs', () => ({
                            technicians: @json($this->transfers),
                            activeTab: 0
                        }));
                    });
                </script>
            </div>
        </div>
    </div>
</div>
