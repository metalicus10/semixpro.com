<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\TechnicianPart;
use App\Models\TechnicianPartUsage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TechnicianParts extends Component
{
    public $parts = [];
    public $categories = [];
    public $brands = [];
    public $selectedCategory = null;
    public $selectedBrand = null;
    public $partsWithWarehouse, $partsWithoutWarehouse;
    public $selectedWarehouse = [];
    public $groupedParts;

    protected $listeners = ['partUsed' => 'refreshParts', 'updateAssignedParts' => 'loadAssignedParts'];

    public function mount()
    {
        $this->loadAssignedParts();
        //$this->selectedWarehouse = $this->groupedParts->keys()->first();
        $this->loadCategoriesAndBrands();
    }

    public function loadAssignedParts()
    {
        $manualParts = TechnicianPart::with('part.category', 'part.brands', 'part.nomenclatures')
            ->where('technician_id', auth()->id())
            ->where('quantity', '>', 0)
            ->get();

        $warehouseParts = auth()->user()->assignedParts();

        // Объединяем две коллекции
        $allParts = $manualParts->merge($warehouseParts);

        // Разделяем на две коллекции
        $this->partsWithWarehouse = $allParts->filter(fn($part) => isset($part->warehouse_id));
        $this->partsWithoutWarehouse = $allParts->filter(fn($part) => !isset($part->warehouse_id));
    }

    public function warehouseParts()
    {
        // Ваш код для получения запчастей со складов
        return $this->partsWithWarehouse; // Или другая переменная, содержащая запчасти
    }

    public function unassignedParts()
    {
        // Ваш код для получения запчастей со складов
        return $this->partsWithoutWarehouse; // Или другая переменная, содержащая запчасти
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
