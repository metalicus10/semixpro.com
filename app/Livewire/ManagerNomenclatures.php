<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Nomenclature;
use Illuminate\Support\Facades\Auth;

class ManagerNomenclatures extends Component
{
    public $nomenclatures = [];
    public $selectedNomenclatures = [];
    public $availablePns = [];
    public $selectedPns = [];
    public $newPn;

    // Массив для добавления новой номенклатуры
    public $newNomenclature = [
        'sku' => '',
        'pns' => '',
        'name' => '',
        'category' => '',
        'supplier' => '',
        'brand' => [],
        'url' => ['url' => '', 'text' => ''],
        'manager_id' => '',
    ];

    public function mount()
    {
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())
        ->with('pns')
        ->get()
        ->toArray();
    }

    public function deleteNomenclature()
    {
        Nomenclature::whereIn('sku', $this->selectedNomenclatures)->delete();
        $this->selectedNomenclatures = [];
    }

    public function addNomenclature()
    {
        $validatedData = $this->validate([
            'newNomenclature.sku' => 'required|string|max:255|unique:nomenclatures,sku',
            'newNomenclature.pns' => 'nullable|json', // PN может быть пустым
            'newNomenclature.name' => 'required|string|max:255',
            'newNomenclature.category' => 'nullable|string|max:255', // Необязательное поле
            'newNomenclature.supplier' => 'nullable|string|max:255', // Необязательное поле
            'newNomenclature.brand' => 'nullable|string', // Необязательное поле
            'newNomenclature.url.url' => 'nullable|url', // Необязательное поле
            'newNomenclature.url.text' => 'nullable|string|max:255', // Необязательное поле
        ]);

        $validatedData['newNomenclature']['pns'] = json_decode($validatedData['newNomenclature']['pns'] ?? '[]', true);
        $validatedData['newNomenclature']['manager_id'] = Auth::id();

        // Создаём запись в БД
        Nomenclature::create($validatedData['newNomenclature']);

        // Добавляем в локальный массив для отображения
        $this->nomenclatures = Nomenclature::where('manager_id', Auth::id())->get()->toArray();
        $this->reset('newNomenclature');
        $this->dispatch('showNotification', 'success', 'New nomenclature created successfully');
    }

    public function addPn($nomenclatureId)
    {
        $this->validate();

        // Проверяем существование PN
        if (Pn::where('number', $this->newPn)->exists()) {
            $this->dispatch('showNotification', 'error', 'PN already exists');
            return;
        }

        $nomenclature = Nomenclature::find($nomenclatureId);

        // Добавляем новый PN
        Pn::create([
            'number' => $this->newPn,
            'part_id' => $nomenclatureId,
            'manager_id' => auth()->id(),
        ]);
        $this->updatePartPnsJson($nomenclature);

        $this->dispatch('pn-added');
        $this->dispatch('showNotification', 'success', 'PN added successfully');
        $this->newPn = null;
    }

    public function updatePartPnsJson($nomenclature)
    {
        $pns = Pn::where('nomenclature_id', $nomenclature->id)->where('manager_id', auth()->id())->pluck('number')->toArray();
        $json = json_encode((object)$pns);

        $nomenclature->update([
            'pns' => $json,
        ]);
    }

    public function deletePns($nomenclatureId, $selectedPns)
    {
        $nomenclature = Nomenclature::find($nomenclatureId);

        if (!$nomenclature) {
            $this->dispatch('showNotification', 'error', 'Nomenclature not found');
            return;
        }

        if (!empty($selectedPns)) {
            // Удаляем выбранные PN из таблицы pns
            $nomenclature->pns()->whereIn('id', $selectedPns)->get()->each->delete();

            // Обновляем JSON в колонке parts.pns
            $this->updatePartPnsJson($nomenclature);

            $this->dispatch('showNotification', 'success', 'PNs deleted successfully');
        } else {
            $this->dispatch('showNotification', 'error', 'No PNs selected for deletion');
        }
    }

    public function render()
    {
        return view('livewire.manager-nomenclatures');
    }
}
