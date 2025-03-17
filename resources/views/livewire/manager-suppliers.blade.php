<div x-data="{ showAddSupplierModal: false }"
    @supplier-added.window="showAddSupplierModal = false"
    class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Suppliers</h1>
    </div>

    <!-- Кнопка для добавления поставщика -->
    <button @click="showAddSupplierModal = true"
            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
        Add Supplier
    </button>

    <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

    <!-- Таблица поставщиков -->
    <div class="overflow-x-auto"
        x-data="{ showDeleteConfirmModal: false }"
        @confirm-delete.window="showDeleteConfirmModal = true"
        @supplier-deleted.window="showDeleteConfirmModal = false">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">
                    Name
                </th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-bold text-gray-400 uppercase dark:text-neutral-500">
                    Actions
                </th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($suppliers as $supplier)
                    <tr class="hover:bg-[#585c63] dark:hover:bg-[#162033]">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-400">{{ $supplier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-400">
                            <!-- Кнопки действий -->
                            <button wire:click="editSupplier({{ $supplier->id }})"
                                    class="bg-yellow-500 text-white px-2 py-1 rounded">
                                Edit
                            </button>
                            <button wire:click="confirmDelete({{ $supplier->id }})"
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

        <!-- Модальное окно подтверждения удаления -->
        <div x-show="showDeleteConfirmModal" class="fixed inset-0 flex items-center justify-center">
            <!-- Оверлей -->
            <div x-show="showDeleteConfirmModal"
                 class="flex fixed inset-0 bg-black opacity-50 z-30"
                 @click="showDeleteConfirmModal = false"
                 x-cloak>
            </div>
            <div class="bg-white p-6 rounded shadow-lg z-50">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                <p class="mb-4">Are you sure you want to delete this supplier?</p>

                <div class="flex justify-end space-x-2">
                    <button @click="showDeleteConfirmModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                    <button wire:click="deleteSupplier" class="px-4 py-2 bg-red-500 text-white rounded">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для добавления поставщика -->
    <div x-show="showAddSupplierModal" x-cloak
         x-transition
         class="fixed inset-0 flex items-center justify-center bg-gray-800">
        <!-- Оверлей -->
        <div x-show="showAddSupplierModal"
             class="flex fixed inset-0 bg-black opacity-50 z-30"
             @click="showAddSupplierModal = false"
             x-cloak>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3 z-50">
            <h3 class="text-lg font-semibold mb-4">Add New Supplier</h3>

            <!-- Поле ввода имени поставщика -->
            <input type="text" wire:model="newSupplierName" placeholder="Supplier Name"
                   class="w-full p-2 border border-gray-300 rounded mb-2">
            @error('newSupplierName') <span class="text-red-500">{{ $message }}</span> @enderror
            @if($errorMessage) <span class="text-red-500">{{ $errorMessage }}</span> @endif

            <!-- Кнопки -->
            <div class="flex justify-end mt-4">
                <button @click="showAddSupplierModal = false; $wire.resetForm()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 mr-2">
                    Cancel
                </button>
                <button wire:click="addSupplier"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Add
                </button>
            </div>
        </div>
    </div>
</div>
