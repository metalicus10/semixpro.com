<div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Remaining stock</h2>
        <!-- Кнопка с подменю для выбора формата скачивания -->
        <div class="relative inline-block text-left" x-data="{ open: false }">
            <button @click="open = !open" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Download full statistics
            </button>
            <!-- Подменю для выбора формата (XLSX или PDF) -->
            <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                    <button wire:click="export('xlsx')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download XLSX</button>
                    <button wire:click="export('pdf')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download PDF</button>
                </div>
            </div>
        </div>
    </div>
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3">Name</th>
            <th scope="col" class="px-6 py-3">SKU</th>
            <th scope="col" class="px-6 py-3">Brand</th>
            <th scope="col" class="px-6 py-3">Remainder</th>
        </tr>
        </thead>
        <tbody>
        @foreach($inventory as $item)
            <tr class="bg-white border-b hover:bg-gray-50">
                <td class="px-6 py-4">{{ $item->name }}</td>
                <td class="px-6 py-4">{{ $item->sku }}</td>
                <td class="px-6 py-4">{{ $item->brand }}</td>
                <td class="px-6 py-4">{{ $item->quantity }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>


</div>
