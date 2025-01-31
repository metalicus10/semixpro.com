<div>
    <template x-if="currentTab === 'nomenclatures'">
        <livewire:manager-nomenclatures :key="'manager-nomenclature-'.auth()->id()" />
    </template>
    <template x-if="currentTab === 'warehouses'">
        <livewire:manager-warehouses :key="'manager-warehouses-'.auth()->id()" />
    </template>
    <template x-if="currentTab === 'categories'">
        <livewire:manager-categories :key="'manager-categories-'.auth()->id()" />
    </template>
    <template x-if="currentTab === 'brands'">
        <livewire:manager-brands :key="'manager-brands-'.auth()->id()" />
    </template>
    <template x-if="currentTab === 'suppliers'">
        <livewire:manager-suppliers :key="'manager-suppliers-'.auth()->id()" />
    </template>
    <template x-if="currentTab === 'parts'">
        <livewire:manager-parts :key="'manager-parts-'.auth()->id()" />
    </template>
    <template x-if="currentTab === 'statistics'">
        <livewire:manager-statistics :key="'manager-statistics-'.auth()->id()" />
    </template>
    <template x-if="currentTab === 'technicians'">
        <livewire:manager-technicians :key="'manager-technicians-'.auth()->id()" />
    </template>
    <template x-if="currentTab === 'profile'">
        <livewire:manager-profile :key="'manager-profile-'.auth()->id()" />
    </template>
</div>
