<div
     class="p-1 md:p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">

    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="md:text-3xl text-md font-bold text-gray-500 dark:text-gray-400">Categories</h1>
        </div>

        <!-- Кнопка добавления новой категории -->
        <button wire:click="openCategoryModal"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
            Add category
        </button>

        <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

        <!-- Таблица с категориями -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <!-- Заголовок таблицы -->
            <div class="hidden md:flex bg-gray-50 dark:bg-gray-700 text-xs font-bold text-gray-700 uppercase dark:text-gray-400 px-5 py-3">
                <div class="flex-1">Category Name</div>
                <div class="flex-1">Actions</div>
            </div>

            <!-- Ряды таблицы -->
            <div class="space-y-0">
                @foreach($categories as $category)
                    <div
                        class="flex flex-col md:flex-row items-start md:items-center bg-white text-gray-700 dark:text-gray-400 border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#162033] px-5 py-3">
                        <!-- Category Name -->
                        <div class="md:flex-1">
                            <span class="md:hidden font-semibold">Category Name: </span>{{ $category->name }}
                        </div>
                        <!-- Actions -->
                        <div class="md:flex-1 flex space-x-2">
                            <button wire:click="editCategory({{ $category->id }})"
                                    class="px-2 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                Edit
                            </button>
                            <button wire:click="confirmDeleteCategory({{ $category->id }})"
                                    class="px-2 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
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
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Yes, Delete
                </button>
                <button wire:click="closeDeleteModal"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 ml-2">Cancel
                </button>
            </div>
        </div>
    @endif
</div>
