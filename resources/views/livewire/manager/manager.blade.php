<div>
    <div x-show="currentTab === 'nomenclatures'" class="w-full">
        <livewire:manager-nomenclatures :key="'manager-nomenclatures-' . auth()->id()" />
    </div>
    <div x-show="currentTab === 'warehouses'" class="w-full">
        <livewire:manager-warehouses :key="'manager-warehouses-' . auth()->id()" />
    </div>
    <div x-show="currentTab === 'categories'" class="w-full">
        <livewire:manager-categories :key="'manager-categories-' . auth()->id()" />
    </div>
    <div x-show="currentTab === 'brands'" class="w-full">
        <livewire:manager-brands :key="'manager-brands-' . auth()->id()" />
    </div>
    <div x-show="currentTab === 'suppliers'" class="w-full">
        <livewire:manager-suppliers :key="'manager-suppliers-' . auth()->id()" />
    </div>
    <div x-show="currentTab === 'parts'" class="w-full">
        <livewire:manager-parts :key="'manager-parts-' . auth()->id()" />
    </div>
    <div x-show="currentTab === 'statistics'" class="w-full">
        <livewire:manager-statistics :key="'manager-statistics-' . auth()->id()" />
    </div>
    <div x-show="currentTab === 'technicians'" class="w-full">
        <livewire:manager-technicians :key="'manager-technicians-' . auth()->id()" />
    </div>
</div>
