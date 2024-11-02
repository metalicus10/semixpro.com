<div class="p-2 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6 dark:text-white">Dashboard Менеджера</h1>

        <!-- Статистика отгрузок -->
        <livewire:manager-dashboard-stats />

        <!-- Статистика остатков на складе -->
        <livewire:manager-inventory-stats />

        <!-- Здесь можно добавить другие блоки Dashboard -->
    </div>
</div>
