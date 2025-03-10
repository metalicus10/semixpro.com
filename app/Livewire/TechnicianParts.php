<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Part;
use App\Models\TechnicianPart;
use App\Models\TechnicianPartUsage;
use App\Models\TechnicianWarehouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TechnicianParts extends Component
{
    public $parts = [];
    public $categories = [];
    public $brands = [];
    public $selectedCategory = null;
    public $selectedBrand = null;
    public $partsWithWarehouse, $partsWithoutWarehouse, $allParts;
    public $selectedWarehouse = null;
    public $groupedParts = null;

    protected $listeners = ['partUsed' => 'loadAssignedParts', 'updateAssignedParts' => 'loadAssignedParts'];

    public function mount()
    {
        $this->loadAssignedParts();
        $this->loadCategoriesAndBrands();
    }

    public function loadAssignedParts()
    {
        $technicianId = auth()->id();

        // Получаем запчасти, назначенные технику, и приводим к модели Part
        $manualParts = TechnicianPart::with([
            'part.category',
            'part.brands',
            'part.nomenclatures',
            'part.warehouse'
        ])
            ->where('technician_id', $technicianId)
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($tp) {
                // Клонируем модель Part, чтобы не менять оригинальные данные
                $part = clone $tp->part;
                $part->quantity = $tp->quantity; // Подменяем quantity из TechnicianPart
                return $part;
            });

        // Получаем ID складов, доступных технику
        $warehouseIds = TechnicianWarehouse::where('technician_id', $technicianId)
            ->pluck('warehouse_id');

        // Получаем запчасти со складов, к которым у техника есть доступ
        $warehouseParts = Part::with([
            'category',
            'brands',
            'nomenclatures',
            'warehouse'
        ])
            ->whereIn('warehouse_id', $warehouseIds)
            ->get();
        dd($warehouseParts );
        // Объединяем обе коллекции, убираем дубликаты по id, приоритет - `manualParts`
        $this->allParts = $manualParts->merge($warehouseParts)
            ->unique('id') // Убираем дубликаты по id
            ->values(); // Сбрасываем ключи

        // Разделяем запчасти на складские и без склада
        $this->partsWithWarehouse = $this->allParts->filter(fn($part) => isset($part->warehouse_id));
        $this->partsWithoutWarehouse = $this->allParts->filter(fn($part) => !isset($part->warehouse_id));
    }

    public function loadCategoriesAndBrands()
    {
        $technicianId = Auth::id();

        // Загружаем категории, связанные с запчастями, переданными технику через TechnicianPart
        $this->categories = Category::whereHas('parts', function($query) use ($technicianId) {
            $query->whereHas('technicianParts', function($subQuery) use ($technicianId) {
                $subQuery->where('technician_id', $technicianId);
            });
        })->get();

        // Получаем все `part_id` для запчастей, переданных технику
        $partIds = TechnicianPart::where('technician_id', $technicianId)
            ->pluck('part_id')
            ->toArray();

        if (empty($partIds)) {
            $this->brands = collect(); // Если нет данных, присваиваем пустую коллекцию
            return;
        }

        // Загрузка брендов, связанных с переданными запчастями
        $this->brands = Brand::whereHas('parts', function($query) use ($partIds) {
            $query->whereIn('brand_part.part_id', $partIds);
        })->get();
    }

    public function usePart($partId)
    {
        $partTransfer = TechnicianPart::find($partId);

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
            $this->loadAssignedParts();
        }
    }

    public function refreshParts()
    {
        $this->mount();
    }

    public function updatedSelectedCategory()
    {
        $this->loadAssignedParts();
    }

    public function updatedSelectedBrand()
    {
        $this->loadAssignedParts();
    }

    public function render()
    {
        return view('livewire.technician.technician-parts', [
            'categories' => $this->categories,
            'brands' => $this->brands,
        ])->layout('layouts.app');
    }
}
