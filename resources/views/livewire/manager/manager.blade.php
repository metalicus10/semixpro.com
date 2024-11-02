<!-- Main Content Area -->
<div class="p-3">
    <!-- Dynamically loaded content based on the selected tab -->
    <div x-show="currentTab === 'dashboard'">
        @livewire('manager-dashboard')
    </div>
    <div x-show="currentTab === 'categories'">
        @livewire('manager-categories')
    </div>
    <div x-show="currentTab === 'brands'">
        @livewire('manager-brands')
    </div>
    <div x-show="currentTab === 'parts'">
        @livewire('manager-parts')
    </div>
    <div x-show="currentTab === 'statistics'">
        @livewire('manager-statistics')
    </div>
    <div x-show="currentTab === 'technicians'">
        @livewire('manager-technicians')
    </div>
</div>
