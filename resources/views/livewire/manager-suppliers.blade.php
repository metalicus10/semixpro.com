<div x-data="{ showAddSupplierModal: false }" 
    @supplier-added.window="showAddSupplierModal = false" 
    class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg">
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

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Suppliers</h1>
    </div>

    <!-- Кнопка для добавления поставщика -->
    <button @click="showAddSupplierModal = true"
            class="mb-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
        Add Supplier
    </button>

    <!-- Таблица поставщиков -->
    <div class="overflow-x-auto" 
        x-data="{ showDeleteConfirmModal: false }" 
        @confirm-delete.window="showDeleteConfirmModal = true" 
        @supplier-deleted.window="showDeleteConfirmModal = false">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50">
                    <th class="py-2 px-4">Name</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ $supplier->name }}</td>
                        <td class="py-2 px-4">
                            <!-- Кнопка для дополнительных действий -->
                            <button @click="$wire.confirmDelete({{ $supplier->id }})" 
                                class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="py-2 px-4 text-center text-gray-500">No suppliers available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Модальное окно подтверждения удаления -->
        <div x-show="showDeleteConfirmModal" class="fixed inset-0 flex items-center justify-center z-50 bg-gray-800 bg-opacity-50">
            <div class="bg-white p-6 rounded shadow-lg">
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
         class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
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
