<div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <label for="date-range" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-400">Select date range</label>
    <input type="date" wire:model="startDate" class="mb-4 p-2 rounded-lg shadow-sm border focus:ring-blue-500 focus:border-blue-500">
    <input type="date" wire:model="endDate" class="mb-4 p-2 rounded-lg shadow-sm border focus:ring-blue-500 focus:border-blue-500">

    <div class="overflow-y-auto max-h-80">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Date of change</th>
                <th scope="col" class="px-6 py-3">Price</th>
            </tr>
            </thead>
            <tbody>
            @foreach($this->getPriceHistory() as $history)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4">{{ $history->changed_at }}</td>
                    <td class="px-6 py-4">${{ number_format($history->price, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
