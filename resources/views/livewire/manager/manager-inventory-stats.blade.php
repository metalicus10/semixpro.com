<div class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <div class="flex justify-evenly md:justify-between items-center mb-2 mt-1">
        <h1 class="md:text-3xl text-md font-bold text-gray-500 dark:text-gray-400">Remaining stock</h1>

        <!-- Кнопка с подменю для выбора формата скачивания -->
        <div class="relative inline-block text-left" x-data="{ open: false }">
            <button @click="open = !open" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Full statistics
            </button>

            <!-- Подменю для выбора формата (XLSX или PDF) -->
            <div x-show="open" @click.away="open = false" class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                <div class="flex flex-col py-1 w-full" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                    <button wire:click="export('xlsx')" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download XLSX</button>
                    <button wire:click="export('pdf')" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download PDF</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица через Flexbox для адаптации под мобильные устройства -->
    <div class="hidden rounded-t md:flex bg-gray-50 dark:bg-gray-700 text-xs text-gray-700 uppercase dark:text-gray-400">
        <div class="flex-1 px-6 py-3">Name</div>
        <div class="flex-1 px-6 py-3">SKU</div>
        <div class="flex-1 px-6 py-3">Brand</div>
        <div class="flex-1 px-6 py-3">Remainder</div>
    </div>

    <div class="space-y-2 md:space-y-0">
        @foreach($inventory as $item)
            <div class="flex flex-col md:flex-row text-gray-700 dark:text-gray-400 border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033] rounded">
                <div class="md:flex-1 px-6 py-4">
                    <span class="md:hidden font-semibold">Name: </span>{{ $item->name }}
                </div>
                <div class="md:flex-1 px-6 py-4">
                    <span class="md:hidden font-semibold">SKU: </span>{{ $item->sku }}
                </div>
                <div class="md:flex-1 px-6 py-4">
                    <span class="md:hidden font-semibold">Brand: </span>{{ $item->brand }}
                </div>
                <div class="md:flex-1 px-6 py-4">
                    <span class="md:hidden font-semibold">Remainder: </span>{{ $item->quantity }}
                </div>
            </div>
        @endforeach
    </div>
</div>
