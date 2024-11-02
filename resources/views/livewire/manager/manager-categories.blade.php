<div class="p-8 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-500 dark:text-gray-400">Categories</h1>
    </div>

    <!-- Кнопка добавления новой категории -->
    <button wire:click="openCategoryModal"
            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
        Add category
    </button>

    <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

    <!-- Таблица с категориями -->
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th scope="col" class="px-5 py-3">ID</th>
            <th scope="col" class="px-5 py-3">Category Name</th>
            <th scope="col" class="px-5 py-3">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $category)
            <tr class="cursor-pointer bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033]">
                <td class="w-4 p-4">{{ $category->id }}</td>
                <td class="w-4 p-4">{{ $category->name }}</td>
                <td class="w-4 p-4">
                    <!-- Кнопка редактирования -->
                    <button wire:click="editCategory({{ $category->id }})"
                            class="px-2 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 mr-2">
                        Edit
                    </button>

                    <!-- Кнопка удаления -->
                    <button wire:click="confirmDeleteCategory({{ $category->id }})"
                            class="px-2 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                        Delete
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Модальное окно для добавления/редактирования категории -->
    @if($showCategoryModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white p-6 rounded-md shadow-lg">
                <h2 class="text-xl mb-4">{{ $isEditMode ? 'Edit Category' : 'Add Category' }}</h2>

                <input type="text" wire:model="categoryName"
                       class="border border-gray-300 rounded-md px-4 py-2 mb-4 w-full"
                       placeholder="Enter category name">

                <button wire:click="saveCategory"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 mr-2">
                    Save
                </button>
                <button wire:click="closeCategoryModal"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    <!-- Модальное окно подтверждения удаления -->
    @if($showDeleteModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
            <div class="bg-white p-6 rounded-lg">
                <h2 class="text-xl mb-4">Confirm deletion ?</h2>
                <button wire:click="deleteCategory({{ $categoryToDelete }})"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Yes, Delete</button>
                <button wire:click="closeDeleteModal" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 ml-2">Cancel</button>
            </div>
        </div>
    @endif
</div>
