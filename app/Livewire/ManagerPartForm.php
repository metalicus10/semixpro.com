<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Nomenclature;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Livewire\Component;
use App\Models\Category;
use App\Models\Part;
use App\Models\Pn;
use Livewire\WithFileUploads;
use Exception;
use function Termwind\render;

class ManagerPartForm extends Component
{
    use WithFileUploads;

    public $categoryName;
    public $partName;
    public $sku;
    public $pn;
    public $brand;
    public $nomenclatures;
    public $brands;
    public $warehouses;
    public $categories;
    public $quantity;
    public $image;
    public $selectedNomenclature, $selectedCategory, $selectedWarehouse;
    public array $selectedBrands = [];
    public $showCategoryModal = false;
    public $showPartModal = false;
    public $price = null;
    public $notificationMessage = '';
    public $notificationType = 'info';
    public $imgUrl = null;
    public $url, $text = null;

    protected $listeners = ['categoryUpdated' => 'refreshCategories', 'brandUpdated' => 'refreshBrands', 'defaultWarehouseUpdated' => 'refreshWarehouses'];

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
        $defaultWarehouse = Warehouse::where('manager_id', Auth::id())->where('is_default', true)->first();
        $this->selectedWarehouse = $defaultWarehouse ? $defaultWarehouse->id : null;
        $this->refreshNomenclatures();
        $this->refreshWarehouses();
        $this->refreshBrands();
        $this->refreshCategories();
    }

    public function clearNotification()
    {
        $this->notificationMessage = '';
    }

    public function refreshNomenclatures()
    {
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())->get();
    }

    public function refreshWarehouses()
    {
        $this->warehouses = Warehouse::where('manager_id', Auth::id())->get();
    }

    public function refreshBrands()
    {
        $this->brands = Brand::where('manager_id', Auth::id())->get();
    }

    public function refreshCategories()
    {
        $this->categories = Category::where('manager_id', Auth::id())->get();
    }

    public function updatedSelectedWarehouse($value)
    {
        // Проверяем, задан ли уже склад по умолчанию
        if (empty($this->selectedWarehouse)) {
            $this->selectedWarehouse = $value;
        }
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
            'selectedNomenclature' => 'required|exists:nomenclatures,id',
            'selectedWarehouse' => 'required|exists:warehouses,id',
            'selectedBrands' => 'nullable|array|exists:brands,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'selectedCategory' => 'required|exists:categories,id',
            'url' => 'nullable|url',
            'pn' => 'nullable|string|max:255',
        ]);

        $this->price = $this->price !== null ? (float) $this->price : null;

        if (Pn::where('number', $this->pn)->exists() && $this->pn != null) {
            $this->dispatch('showNotification', 'error', 'Part number already exists');
            return;
        }

        $existingPart = Part::where('sku', $this->sku)->first();

        if ($existingPart) {
            // 🔹 Если запись найдена, отправляем уведомление об ошибке
            $this->dispatch('showNotification', 'error', 'Запчасть с таким SKU уже существует');
            return;
        }

        $fileName = '';
        if ($this->image) {

            $tempPath = $this->image->getRealPath();
            $tempImg = Storage::disk('public')->get($tempPath);

            $manager = new ImageManager(Driver::class);

            $processedImage = $manager->read($tempImg)
            ->resize(1024, 768)
            ->toWebp(quality: 60);

            $imagePath = '/images/parts/' . Auth::id();

            // Генерируем уникальное имя для файла
            $fileName = $imagePath. '/' . uniqid() . '.webp';

            // Сохраняем закодированное изображение в local
            Storage::disk('public')->put($fileName, $processedImage);
        }

        $part = Part::create([
            'name' => $this->partName,
            'sku' => $this->sku,
            'nomenclature_id' => (int)$this->selectedNomenclature,
            'warehouse_id' => (int)$this->selectedWarehouse,
            'category_id' => (int)$this->selectedCategory,
            'manager_id' => Auth::id(),
            'quantity' => (int)$this->quantity,
            'price' => $this->price,
            'url' => json_encode(['url' => $this->url, 'text' => $this->text ?? '']),
            'total' => $this->quantity * $this->price,
            'image' => $fileName,
        ]);

        if($this->pn != null)
        {
            Pn::create([
                'number' => $this->pn,
                'part_id' => $part->id,
                'manager_id' => auth()->id(),
                'nomenclature_id ' => $part->nomenclature_id,
            ]);
        }

        // Привязываем выбранные бренды к запчасти
        $part->brands()->sync($this->selectedBrands);

        $this->dispatch('showNotification', 'success', 'The spare part has been added successfully');
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
