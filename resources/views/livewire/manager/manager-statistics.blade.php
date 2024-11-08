<div class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    @if ($notificationMessage)
        <div
            class="flex justify-center left-1/3 text-white text-center p-4 rounded-lg mb-6 transition-opacity duration-1000 z-50 absolute top-[10%] w-1/2"
            x-data="{ show: true }"
            x-init="
            setTimeout(() => show = false, 3500);
            setTimeout(() => $wire.clearNotification(), 3500);
        "
            x-show="show"
            x-transition:enter="opacity-0"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="opacity-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="{
            'bg-blue-700': '{{ $notificationType }}' === 'info',
            'bg-green-500': '{{ $notificationType }}' === 'success',
            'bg-yellow-500': '{{ $notificationType }}' === 'warning',
            'bg-red-500': '{{ $notificationType }}' === 'error'
        }"
        >
            {{ $notificationMessage }}
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-6 dark:text-white">Statistics of transferred spare parts</h2>

    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">Name</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">Technician</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">Transmitted</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">Remained with the technician</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">How much was used</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        @forelse ($transfers as $transfer)
                            <tr class="hover:bg-gray-100 dark:hover:bg-[#162033]">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-400">{{ $transfer->part->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-400">{{ $transfer->technician->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-400">{{ $transfer->total_transferred }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-400">{{ $transfer->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-400">{{ $usedParts[$transfer->part_id]['total_transferred'] - $transfer->quantity ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="px-5 py-5 text-sm text-center text-gray-400 bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    No data
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
