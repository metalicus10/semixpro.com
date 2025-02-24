<?php

namespace App\Livewire\Components;

use App\Models\BrandNomenclature;
use App\Models\Nomenclature;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Brand extends Component
{
    public $nomenclature;
    public $brands, $currentBrands, $selectedBrands = [];

    public function mount(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
        $this->selectedBrands = $this->nomenclature->brands()->pluck('brands.id')->toArray();
    }

    public function updateNomenclatureBrands($nomenclatureId, $selectedBrands)
    {
        $nomenclature = Nomenclature::find($nomenclatureId);
        $nomenclature->brands()->sync($selectedBrands);
        $this->updateSelectedBrands($nomenclatureId);

        // Обновляем данные в представлении
        $this->dispatch('brandsUpdated');
    }

    public function updateSelectedBrands($nomenclatureId)
    {
        $nomenclature = Nomenclature::where('id', $nomenclatureId)->firstOrFail();
        $nomenclature->brands()->sync($this->selectedBrands);

        $this->dispatch('brandsUpdated', $nomenclatureId);
    }

    public function getUpdatedBrands($nomenclatureId)
    {
        return Nomenclature::find($nomenclatureId)->brands()->pluck('brands.id')->toArray();
    }

    public function render()
    {
        return view('livewire.manager.components.brand');
    }
}
