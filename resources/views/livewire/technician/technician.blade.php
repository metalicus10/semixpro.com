<div class="p-3">
    <div x-cloak x-show="currentTab === 'parts'">
        @livewire('technician-parts')
    </div>
    <div x-cloak x-show="currentTab == 'profile'">
        <livewire:profile :key="'profile-' . auth()->id()" />
    </div>
</div>
