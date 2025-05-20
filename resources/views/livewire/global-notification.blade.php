<div x-data="{
        open: false,
        notifications: @js($notifications),
        refresh() {
            $wire.loadNotifications().then(result => {
                this.notifications = $wire.notifications;
            });
        },
        markAsRead(id) {
            $wire.markAsRead(id).then(() => {
                const n = this.notifications.find(n => n.id === id);
                if (n) n.read = true;
            });
        }
    }" class="relative flex flex-col w-[32px] h-[32px] bg-[#1a2e35] justify-center align-center items-center rounded" x-cloak>
    <button @click="open = !open" class="relative flex justify-center items-center w-[24px] h-[24px]" x-init="console.log(notifications);">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18" height="18" viewBox="0 0 300 300">
            <image id="layer-1" data-name="Layer 1" width="100%" height="100%" xlink:href="data:img/png;base64,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"/>
        </svg>
        <span
            x-show="notifications.filter(n => !n.read).length"
            class="absolute top-0 right-0 w-3 h-3 bg-orange-500 rounded-full flex items-center justify-center text-[10px] font-bold text-white shadow"
            x-text="notifications.filter(n => !n.read).length"
        ></span>
    </button>

    <!-- Слайд уведомлений -->
    <div
        class="fixed right-0 top-[65px] h-[calc(90vh-64px)] w-full sm:w-96 mr-2.5 bg-white border-1 border-green-500 rounded-l-2xl rounded-r-lg dark:bg-gray-900 shadow-lg z-50 transform transition-transform duration-300 ease-in-out"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @keydown.escape.window="open = false"
        @click.away="open = false"
    >
        <!-- Шапка -->
        <div class="flex justify-between items-center px-4 py-3 border-b dark:border-gray-700">
            <h2 class="text-lg text-gray-400 font-semibold">Уведомления</h2>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                ✕
            </button>
        </div>

        <!-- Список -->
        <div class="p-3 overflow-y-auto h-[calc(100%-56px)]">
            <template x-for="note in notifications" :key="note.id">
                <div
                    class="flex items-start p-3 mb-2 rounded-lg cursor-pointer transition hover:bg-gray-100 dark:hover:bg-gray-800"
                    @click="markAsRead(note.id)"
                >
                    <div
                        class="w-2.5 h-2.5 rounded-full mt-1 mr-3"
                        :class="note.read ? 'bg-transparent' : 'bg-orange-500'"
                    ></div>
                    <div>
                        <p class="text-sm font-medium break-all text-left text-gray-400 w-[305px]" x-text="note.message"></p>
                        <p class="text-xs text-green-500 mt-1" x-text="note.created_at_diff"></p>
                    </div>
                </div>
            </template>

            <template x-if="notifications.length === 0">
                <p class="text-sm text-gray-500 text-center mt-6">Нет уведомлений</p>
            </template>
        </div>
    </div>
</div>
