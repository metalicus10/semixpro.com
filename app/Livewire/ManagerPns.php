<?php

namespace App\Livewire;

use App\Models\Part;
use App\Models\Pn;
use Livewire\Component;

class ManagerPns extends Component
{
    public $partId;
    public $newPn;
    public $errorMessage = null;
    public $searchPn = '';
    public $selectedPns = [];
    public $availablePns = [];

    protected $rules = [
        'newPn' => 'required|string|max:255|unique:pns,number',
    ];

    public function mount($partId)
    {
        $this->partId = $partId;

        // Загружаем связанные PNs для запчасти
        //$part = Part::findOrFail($partId);
        $partPns = $this->getPartPns($partId);
    }

    /*public function clearNotification()
    {
        $this->notificationMessage = '';
    }*/

    public function getPartPns($partId)
    {
        // Получить все PN для запчасти с указанным ID
        $pns = Pn::where('part_id', $partId)->pluck('number');

        // Вернуть массив или использовать данные по необходимости
        return $pns;
    }

    public function updatePns()
    {
        $part = Part::find($this->partId);

        if (!$part) {
            $this->errorMessage = 'Part not found';
            return;
        }

        // Удаляем существующие PNs
        $part->pns()->delete();

        // Сохраняем новые PNs
        foreach ($this->selectedPns as $pn) {
            Pn::create([
                'number' => $pn,
                'part_id' => $this->partId,
            ]);
        }

        $this->notificationType = 'success';
        $this->notificationMessage = 'PNs updated successfully';

        $this->dispatch('notification', ['type' => 'success', 'message' => 'PNs updated successfully']);
    }

    public function addPn()
    {
        $this->validate();

        // Проверяем существование PN
        if (Pn::where('number', $this->newPn)->exists()) {
            $this->dispatch('notification', ['type' => 'error', 'message' => 'PN already exists']);
            return;
        }

        $part = Part::find($this->partId);
        $this->updatePartPnsJson($part);
        
        // Добавляем новый PN
        Pn::create([
            'number' => $this->newPn,
            'part_id' => $this->partId,
            'manager_id' => auth()->id(),
        ]);
        
        $this->dispatch('pn-added');
        $this->dispatch('showNotification', 'success', 'PN added successfully');
        $this->newPn = null;
    }

    public function updatePartPnsJson($part)
    {
        $pns = Pn::where('part_id', $part->id)->where('manager_id', auth()->id())->pluck('number')->toArray();

        $json = json_encode((object)$pns);

        $part->update([
            'pns' => $json,
        ]);
    }

    public function render()
    {
        return view('livewire.manager-pns');
    }
}
