<div class="p-3">
    <div x-show="currentTab === 'dashboard'">
        @livewire('technician-dashboard')
    </div>
    <div x-show="currentTab === 'parts'">
        @livewire('technician-parts')
    </div>
</div>
