<div class="p-2 md:p-4 bg-white dark:bg-gray-900 rounded-lg overflow-hidden" x-cloak>
    <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">
        {{ __('Profile Settings') }}
    </h1>
    <div class="py-6">
        <div class="flex flex-row gap-5 flex-wrap w-full sm:px-6 lg:px-8 space-y-6 mx-auto">
            <div class="flex p-4 sm:p-8 bg-white shadow sm:rounded-lg max-w-1/3">
                <livewire:profile.update-profile-information-form/>
            </div>

            <div class="flex p-4 sm:p-8 bg-white shadow sm:rounded-lg max-w-1/3">
                <livewire:profile.update-password-form/>
            </div>

            <div class="flex p-4 sm:p-8 bg-white shadow sm:rounded-lg max-w-1/3">
                <livewire:profile-min-quantity />
            </div>

            <div class="flex p-4 sm:p-8 bg-white shadow sm:rounded-lg w-full">
                <livewire:profile.delete-user-form/>
            </div>
        </div>
    </div>
</div>
