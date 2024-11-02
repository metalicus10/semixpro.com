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
    public $parts;
    public $categories;
    public $brands;
    public $selectedCategory = null;
    public $selectedBrand = null;

    protected $listeners = ['partUsed' => 'refreshParts'];

    public function mount()
    {
        $this->loadParts();
        $this->loadCategoriesAndBrands();
    }

    public function loadParts()
    {
        $this->parts = TechnicianPart::with('part.category', 'part.brands')
            ->where('technician_id', Auth::id())
            ->where('quantity', '>', 0)
            ->get();
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
            'parts' => $this->parts,
            'categories' => $this->categories,
            'brands' => $this->brands,
        ])->layout('layouts.app');
    }
}
