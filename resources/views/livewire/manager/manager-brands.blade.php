<div wire:init="loadComponent" x-data="{ initialized: false }" x-init="setTimeout(() => initialized = true, 100)"
     class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    @if($loaded)
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Brands</h1>
    </div>

    <!-- Кнопка добавления бренда -->
    <button wire:click="openBrandModal" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Add
        Brand
    </button>

    <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

    <!-- Таблица с брендами -->
    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">
                                Name
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        @forelse($brands as $brand)
                            <tr class="hover:bg-[#585c63] dark:hover:bg-[#162033]">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-400">{{ $brand->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-400">
                                    <button wire:click="editBrand({{ $brand->id }})"
                                            class="bg-yellow-500 text-white px-2 py-1 rounded">
                                        Edit
                                    </button>
                                    <button wire:click="deleteBrand({{ $brand->id }})"
                                            class="bg-red-500 text-white px-2 py-1 rounded">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
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
    @endif
    <!-- Модальное окно -->
    @if($isOpen)
        <div class="fixed inset-0 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-lg w-1/3">
                <div class="p-4">
                    <h2 class="text-xl mb-4">Add / Edit Brand</h2>

                    <input type="text" wire:model="name" class="border px-4 py-2 w-full mb-4" placeholder="Brand Name">

                    <div class="flex justify-end">
                        <button wire:click="storeBrand" class="bg-green-500 text-white px-4 py-2 rounded">Save</button>
                        <button wire:click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
