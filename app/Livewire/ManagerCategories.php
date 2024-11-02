<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManagerCategories extends Component
{
    public $categories;
    public $categoryId;
    public $categoryName;
    public $categoryToDelete;
    public $isEditMode = false;
    public $showCategoryModal = false;
    public $showDeleteModal = false;

    protected $listeners = ['confirmDelete' => 'deleteCategory'];

    // Загружаем категории менеджера
    public function mount()
    {
        $this->loadCategories();
    }

    // Получаем все категории, добавленные менеджером
    public function loadCategories()
    {
        $managerId = auth()->id();

        // Проверка: существует ли у менеджера хотя бы одна категория
        if (Category::where('manager_id', $managerId)->exists()) {
            $this->categories = Category::where('manager_id', $managerId)->get();
        } else {
            $this->categories = collect(); // Пустая коллекция, если категорий нет
        }
    }

    // Открытие модального окна для добавления/редактирования категории
    public function openCategoryModal($id = null)
    {
        if ($id) {
            $this->isEditMode = true;
            $category = Category::findOrFail($id);
            $this->categoryId = $category->id;
            $this->categoryName = $category->name;
        } else {
            $this->isEditMode = false;
            $this->resetForm();
        }

        $this->showCategoryModal = true;
    }

    // Закрытие модального окна
    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->resetForm();
    }

    // Сброс формы
    public function resetForm()
    {
        $this->categoryId = null;
        $this->categoryName = '';
        $this->isEditMode = false;
    }

    // Сохранение или обновление категории
    public function saveCategory()
    {
        $this->validate([
            'categoryName' => 'required|string|max:255',
        ]);

        if ($this->isEditMode) {
            $category = Category::findOrFail($this->categoryId);
            $category->name = $this->categoryName;
            $category->save();
        } else {
            Category::create([
                'name' => $this->categoryName,
                'manager_id' => Auth::id(),
            ]);
        }

        $this->closeCategoryModal();
        $this->loadCategories();
    }

    // Редактирование категории
    public function editCategory($id)
    {
        $this->isEditMode = true;
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->categoryName = $category->name;
        $this->showCategoryModal = true;
        $this->loadCategories();
    }

    // Удаление категории
    public function deleteCategory($id)
    {
        Category::destroy($this->categoryToDelete);
        $this->closeDeleteModal();
        $this->loadCategories();
        session()->flash('message', 'Category deleted successfully.');
    }

    public function confirmDeleteCategory($id)
    {
        $this->categoryToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }

    public function render()
    {
        return view('livewire.manager.manager-categories')->layout('layouts.app');
    }
}
