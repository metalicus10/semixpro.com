<?php

namespace App\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Livewire\Component;
use App\Models\Category;
use App\Models\Part;
use Livewire\WithFileUploads;
use Exception;
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
    public $notificationMessage = '';
    public $notificationType = 'info';
    public $imgUrl = null;
    public $url = null;

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

    public function clearNotification()
    {
        $this->notificationMessage = '';
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

        $this->notificationType = 'success';
        $this->notificationMessage = 'Категория успешно добавлена';
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
            'url' => 'nullable|url',
        ]);

        $this->price = $this->price !== null ? (float) $this->price : null;

        // Получаем идентификатор текущего пользователя
        $userId = auth()->id();

        // Путь для сохранения изображений
        $path = 'partsImages/' . $userId;
        $fileName = null;

        if ($this->image) {
        
            $tempPath = $this->image->getRealPath();
            $tempImg = Storage::disk('s3')->get($tempPath);
        
            $manager = new ImageManager(Driver::class);
            
            $processedImage = $manager->read($tempImg)
            ->resize(1024, 1024, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->toWebp(quality: 60);

            // Генерируем уникальное имя для файла
            $fileName = $path. '/' . uniqid() . '.webp';

            // Сохраняем закодированное изображение в S3
            $result = Storage::disk('s3')->put($fileName, $processedImage);
            $this->imgUrl = Storage::disk('s3')->url($fileName);
        }

        $part = Part::create([
            'name' => $this->partName,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'image' => $this->imgUrl,
            'category_id' => $this->selectedCategory,
            'url' => json_encode(['url' => $this->url, 'text' => $this->text ?? '']),
            'total' => $this->quantity * $this->price,
        ]);

        // Привязываем выбранные бренды к запчасти
        $part->brands()->sync($this->selectedBrands);

        $this->notificationType = 'success';
        $this->notificationMessage = 'The spare part has been added successfully';
        $this->reset(['partName', 'sku', 'selectedBrands', 'quantity', 'image', 'selectedCategory', 'price', 'url']);
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
        $this->reset(['partName', 'sku', 'selectedBrands', 'quantity', 'image', 'selectedCategory', 'price', 'url']);
    }

    public function render()
    {
        return view('livewire.manager.manager-part-form')->layout('layouts.app');
    }
}
