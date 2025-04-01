<div x-data="{ showAddSupplierModal: false, showDeleteConfirmModal: false }"
     @supplier-added.window="showAddSupplierModal = false"
     @confirm-delete.window="showDeleteConfirmModal = true"
     @supplier-deleted.window="showDeleteConfirmModal = false"
    class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Suppliers</h1>
    </div>

    <!-- Таблица поставщиков -->
    <div x-data="{ view: 'list' }" class="p-4 space-y-4">

        {{-- Верхняя панель --}}
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-2 w-2/3">
                <div class="flex items-center w-2/3 relative">
                    <input type="text" wire:model.live="search" placeholder="Search product..." class="w-full border border-gray-300 rounded-xl" />
                    <hr />
                    <button class="btn btn-sm bg-gray-700 text-white flex items-center gap-1 absolute right-0 w-32">
                        @include('icons.scan') Scan
                    </button>
                </div>
                <button @click="view = 'list'" :class="view === 'list' ? 'bg-gray-800 text-white' : 'bg-gray-100'" class="btn btn-sm">
                    @include('icons.list')
                </button>
                <button @click="view = 'grid'" :class="view === 'grid' ? 'bg-gray-800 text-white' : 'bg-gray-100'" class="btn btn-sm">
                    @include('icons.grid')
                </button>
            </div>
            <div class="flex items-center gap-2 w-1/3">
                <button class="btn btn-sm bg-gray-600">@include('icons.menu')</button>
                <button @click="showAddSupplierModal = true" class="px-4 py-2 bg-brand-accent text-white rounded-md hover:bg-green-600">Add Supplier</button>
            </div>
        </div>

        {{-- Список поставщиков --}}
        <div>
            <template x-for="supplier in @js($suppliers)" :key="supplier.id">
                <div class="bg-gray-800 rounded-xl p-4 text-white flex flex-col gap-2">

                    {{-- Верхняя часть --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center font-bold text-sm" x-text="supplier.initials"></div>
                            <div>
                                <div class="font-semibold text-lg" x-text="supplier.name"></div>
                                <div class="text-sm text-gray-300" x-text="supplier.contact_person"></div>
                            </div>
                        </div>
                        <div class="flex gap-8 text-sm">
                            <div>
                                <div class="text-gray-400">Contacts</div>
                                <div class="flex flex-col">
                                    <span x-text="supplier.email"></span>
                                    <span x-text="supplier.phone"></span>
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-400">Receivables</div>
                                <div>$<span x-text="supplier.receivables"></span></div>
                            </div>
                            <div>
                                <div class="text-gray-400">Used Credits</div>
                                <div>$<span x-text="supplier.used_credits"></span></div>
                            </div>
                        </div>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                    class="btn btn-sm bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 rounded-full">
                                @include('icons.menu')
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open" @click.outside="open = false"
                                 x-transition
                                 class="absolute right-0 mt-2 w-32 bg-white rounded-md shadow-lg z-50 overflow-hidden text-sm text-gray-800">
                                <button @click="$wire.editSupplier(supplier.id)"
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                                    ✏️ Edit
                                </button>
                                <button @click="$wire.confirmDelete(supplier.id)"
                                        class="block w-full text-left px-4 py-2 hover:bg-red-100 text-red-600">
                                    🗑 Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Нижняя часть --}}
                    <div class="flex justify-between items-center text-sm text-gray-300 pt-2 border-t border-gray-700 mt-2">
                        <div class="flex items-center gap-2">
                            @include('icons.location')
                            <a href="#" class="hover:underline" x-text="supplier.address"></a>
                        </div>
                        <div class="flex items-center gap-2">
                            @include('icons.package')
                            <span x-text="`${supplier.product_count} product(s)`"></span>
                        </div>
                        <div>
                            <span :class="supplier.active ? 'bg-blue-600 text-white px-2 py-1 rounded' : 'bg-gray-600 text-gray-300 px-2 py-1 rounded'" x-text="supplier.active ? 'Active' : 'Inactive'"></span>
                        </div>
                    </div>

                </div>
            </template>
        </div>

    </div>

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


    </div>

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
