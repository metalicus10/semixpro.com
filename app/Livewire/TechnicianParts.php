<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Part;
use App\Models\TechnicianPart;
use App\Models\TechnicianPartUsage;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TechnicianParts extends Component
{
    public $parts, $nomenclatures = [];
    public $categories = [];
    public $brands = [];
    public $selectedCategory = null;
    public $selectedBrand = null;
    public $partsWithWarehouse, $partsWithoutWarehouse;
    public $selectedWarehouse = [];
    public $groupedParts, $defaultWarehouse, $warehouses;
    public $warehouseParts, $unassignedParts;

    protected $listeners = ['partUsed' => 'refreshParts', 'updateAssignedParts' => 'loadAssignedParts'];

    public function mount()
    {
        $this->loadWarehouses();
        $this->loadAssignedParts();
        $this->warehouseParts = $this->getWarehouseParts()->toArray();
        $this->unassignedParts = $this->getUnassignedParts()->toArray();
        //$this->selectedWarehouse = $this->groupedParts->keys()->first();
        $this->loadCategoriesAndBrands();
    }

    public function loadWarehouses()
    {
        $this->warehouses = Warehouse::whereIn('id', function ($query) {
            $query->select('warehouse_id')->from('parts')->whereNotNull('warehouse_id');
        })->get()->toArray();
    }

    public function loadAssignedParts()
    {
        $manualParts = TechnicianPart::with([
                'part.nomenclatures' => function ($query) {
                    $query->with('brands', 'category');
                },
            ])
            ->where('technician_id', auth()->id())
            ->where('quantity', '>', 0)
            ->get();

        $warehouseParts = auth()->user()->assignedParts()->load('nomenclatures');

        // Объединяем две коллекции
        $allParts = $manualParts->merge($warehouseParts);

        // Разделяем на две коллекции
        $this->partsWithWarehouse = $allParts->filter(fn($part) => isset($part->warehouse_id));
        $this->partsWithoutWarehouse = $allParts->filter(fn($part) => !isset($part->warehouse_id));
    }

    public function getWarehouseParts()
    {
        // Ваш код для получения запчастей со складов
        return $this->partsWithWarehouse; // Или другая переменная, содержащая запчасти
    }

    public function getUnassignedParts()
    {
        // Ваш код для получения запчастей со складов
        return $this->partsWithoutWarehouse; // Или другая переменная, содержащая запчасти
    }

    public function loadCategoriesAndBrands()
    {
        $technicianId = Auth::id();

        // Загружаем категории из переданных технику запчастей (TechnicianPart)
        $manualPartCategories = Category::whereHas('parts', function($query) use ($technicianId) {
            $query->whereHas('technicianParts', function($subQuery) use ($technicianId) {
                $subQuery->where('technician_id', $technicianId);
            });
        })->get();

        // Получаем ID складов, назначенных технику
        $warehouseIds = auth()->user()->assignedWarehouses()->pluck('warehouses.id')->toArray();

        // Загружаем категории из запчастей, находящихся на назначенных складах
        $warehousePartCategories = Category::whereHas('parts', function($query) use ($warehouseIds) {
            $query->whereIn('warehouse_id', $warehouseIds);
        })->get();

        // Объединяем категории
        $this->categories = $manualPartCategories->merge($warehousePartCategories)->unique('id');

        // Получаем все ID запчастей, переданных технику напрямую
        $manualPartIds = TechnicianPart::where('technician_id', $technicianId)
            ->pluck('part_id')
            ->toArray();

        // Получаем ID запчастей, которые находятся на назначенных складах
        $warehousePartIds = Part::whereIn('warehouse_id', $warehouseIds)
            ->pluck('id')
            ->toArray();

        // Объединяем ID запчастей
        $allPartIds = array_merge($manualPartIds, $warehousePartIds);

        if (empty($allPartIds)) {
            $this->brands = collect(); // Если нет данных, присваиваем пустую коллекцию
            return;
        }

        // Загружаем бренды, связанные с переданными запчастями
        $this->brands = Brand::whereHas('parts', function($query) use ($allPartIds) {
            $query->whereIn('brand_part.part_id', $allPartIds);
        })->get();
    }

    public function usePart($transferId)
    {
        $partTransfer = TechnicianPart::find($transferId);

        if ($partTransfer && $partTransfer->quantity > 0) {
            // Уменьшаем количество на 1
            $partTransfer->quantity -= 1;
            $partTransfer->save();

            // Записываем использование запчасти
            TechnicianPartUsage::updateOrCreate([
                'technician_id' => Auth::id(),
                'part_id' => $partTransfer->part_id,
                'quantity_used' => 1,
            ]);

            // Отправляем событие менеджеру
            $this->dispatch('partUsedByTechnician', $partTransfer->part_id);
            $this->dispatch('showNotification', 'success', 'Запчасть успешно списана');

            // Обновляем список запчастей
            $this->loadParts();
        }
    }

    public function refreshParts()
    {
        $this->mount();
    }

    public function updatedSelectedCategory()
    {
        $this->loadParts();
    }

    public function updatedSelectedBrand()
    {
        $this->loadParts();
    }

    public function render()
    {
        return view('livewire.technician.technician-parts', [

            'categories' => $this->categories,
            'brands' => $this->brands,
        ])->layout('layouts.app');
    }
}
