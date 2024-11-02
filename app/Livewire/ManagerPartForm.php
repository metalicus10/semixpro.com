<?php

namespace App\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Livewire\Component;
use App\Models\Category;
use App\Models\Part;
use Livewire\WithFileUploads;
use function Termwind\render;

class ManagerPartForm extends Component
{
    use WithFileUploads;

    public $categoryName;
    public $partName;
    public $sku;
    public $brand;
    public $brands;
    public $categories;
    public $quantity;
    public $image;
    public $selectedCategory;
    public array $selectedBrands = [];
    public $showCategoryModal = false;
    public $showPartModal = false;
    public $price = null;

    protected $listeners = ['categoryUpdated' => 'refreshCategories', 'brandUpdated' => 'refreshBrands'];

    protected $rules = [
        'categoryName' => 'required|string|max:255',
        'partName' => 'required|string|max:255',
        'sku' => 'required|string|max:255',
        'brand' => 'exists:brands,id',
        'quantity' => 'required|integer|min:1',
        'price' => 'nullable|numeric|min:0',
        'selectedCategory' => 'exists:categories,id',
    ];

    public function mount()
    {
        $this->refreshBrands();
        $this->refreshCategories();
    }

    public function refreshBrands()
    {
        $this->brands = Brand::where('manager_id', Auth::id())->get();
    }

    public function refreshCategories()
    {
        $this->categories = Category::where('manager_id', Auth::id())->get();
    }

    // Метод для добавления новой категории
    public function addCategory()
    {
        $this->validate([
            'categoryName' => 'required|string|max:255',
        ]);

        Category::create(['name' => $this->categoryName, 'user_id' => Auth::id()]);

        session()->flash('message', 'Категория успешно добавлена.');
        $this->categoryName = '';
        $this->showCategoryModal = false; // Закрываем модальное окно

        // Событие добавления категории
        $this->dispatch('categoryUpdated');
    }

    // Метод для добавления новой запчасти
    public function addPart()
    {
        $this->validate([
            'partName' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'selectedBrands' => 'nullable|array|exists:brands,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:5200',
            'selectedCategory' => 'required|exists:categories,id',
        ]);

        $this->price = $this->price !== null ? (float) $this->price : null;

        // Получаем идентификатор текущего пользователя
        $userId = auth()->id();

        // Путь для сохранения изображений
        $path = 'partsImages/' . $userId;
        $imagePath = null;

        if ($this->image) {

            // Скачиваем изображение из временного URL
            $tempPath = $this->image->getRealPath();
            $tempContents = Storage::disk('s3')->get($tempPath);

            $manager = new ImageManager(new Driver());

            // Используем make для создания изображения
            $img = $manager->read($tempContents)
                ->resize(1024, 1024, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            $encodedImg = $img->toJpeg(80);

            $filename = $this->image->hashName();
            //$imagePath = $path . '/' . $filename;
            $imagePath = $this->image->storeAs($path, $filename, 's3');

            //Storage::disk('s3')->put($imagePath, (string) $encodedImg, 'public');
        }

        $part = Part::create([
            'name' => $this->partName,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'image' => $imagePath,
            'category_id' => $this->selectedCategory,
        ]);

        // Привязываем выбранные бренды к запчасти
        $part->brands()->sync($this->selectedBrands);

        session()->flash('message', 'The spare part has been added successfully.');
        $this->reset(['partName', 'sku', 'selectedBrands', 'quantity', 'image', 'selectedCategory']);
        $this->showPartModal = false; // Закрываем модальное окно

        // Событие добавления запчасти
        $this->dispatch('partUpdated');
    }

    // Открытие и закрытие модальных окон
    public function openCategoryModal()
    {
        $this->showCategoryModal = true;
    }

    public function openPartModal()
    {
        $this->showPartModal = true;
    }

    public function closeModal()
    {
        $this->showCategoryModal = false;
        $this->showPartModal = false;
        $this->reset(['partName', 'sku', 'selectedBrands', 'quantity', 'image', 'selectedCategory']);
    }

    public function render()
    {
        return view('livewire.manager.manager-part-form')->layout('layouts.app');
    }
}
