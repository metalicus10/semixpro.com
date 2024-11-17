<!-- Main Content Area -->
<div>
    <!-- Dynamically loaded content based on the selected tab -->
    <div x-show="currentTab === 'dashboard'">
        @livewire('manager-dashboard', [], key('manager-dashboard'))
    </div>
    <div x-show="currentTab === 'warehouses'">
        @livewire('manager-warehouses', [], key('manager-warehouses'))
    </div>
    <div x-show="currentTab === 'categories'">
        @livewire('manager-categories', [], key('manager-categories'))
    </div>
    <div x-show="currentTab === 'brands'">
        @livewire('manager-brands', [], key('manager-brands'))
    </div>
    <div x-show="currentTab === 'suppliers'">
        @livewire('manager-suppliers', [], key('manager-suppliers'))
    </div>
    <div x-show="currentTab === 'parts'">
        @livewire('manager-parts', [], key('manager-parts'))
    </div>
    <div x-show="currentTab === 'statistics'">
        @livewire('manager-statistics', [], key('manager-statistics'))
    </div>
    <div x-show="currentTab === 'technicians'">
        @livewire('manager-technicians', [], key('manager-technicians'))
    </div>
    <div x-show="currentTab === 'profile'">
        @livewire('manager-profile', [], key('manager-profile'))
    </div>
</div>
