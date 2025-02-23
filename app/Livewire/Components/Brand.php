<?php

namespace App\Livewire\Components;

use App\Models\BrandNomenclature;
use App\Models\Nomenclature;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Brand extends Component
{
    public $nomenclatures, $nomenclature;
    public $brands, $currentBrands, $selectedBrands = [];

    public function mount($brands, $nomenclature)
    {
        $this->nomenclature = (object) $nomenclature;
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

        $this->selectedBrands = $nomenclature->brands()->pluck('brands.id')->toArray();
        $this->dispatch('brandsUpdated', $nomenclatureId);
    }


    public function render()
    {
        return view('livewire.manager.components.brand');
    }
}
