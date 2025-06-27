<div class="w-full p-2 md:p-4 space-y-2">
    <div class="flex flex-col justify-between items-start mb-6 space-y-4 sm:space-y-0">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Dashboard</h1>
        <p class="text-slate-400 text-sm mt-1">
            <span x-data="{ date: new Date() }" x-text="date.toLocaleDateString('ru-RU', { weekday: 'long', day: 'numeric', month: 'long' })"></span>
        </p>
    </div>

    <div class="flex gap-5 w-full mb-6 columns-auto">
        {{-- Уровень запасов --}}
        <livewire:stock-level/>
        {{-- Популярные товары --}}
        <livewire:popular-items/>
    </div>

    {{-- Общие показатели --}}
    @include('livewire.manager.dashboard.stats')

    <div class="flex gap-5 w-full mb-6 columns-auto" style="height: 500px;">
        <livewire:active-work-order />
        <livewire:sales-order-summary />
    </div>

    <!-- Статистика отгрузок -->
    <livewire:manager-dashboard-stats/>

    <!-- Статистика остатков на складе -->
    <livewire:manager-inventory-stats/>

    <!-- Здесь можно добавить другие блоки Dashboard -->
</div>
