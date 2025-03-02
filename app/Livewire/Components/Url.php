<?php

namespace App\Livewire\Components;

use App\Models\Part;
use Livewire\Component;

class Url extends Component
{
    public $urlData;
    public $part, $suppliers;
    public $managerUrlModalVisible = false;
    public $managerUrl, $selectedId, $managerSupplier;

    protected $listeners = [
        'urlChanged' => '$refresh',
    ];

    public function refreshComponent()
    {
        $this->render();
    }

    public function openManagerUrlModal($partId)
    {
        $this->selectedId = $partId;
        $part = Part::find($partId);

        $data = json_decode($part->url, true) ?? [];
        $this->managerSupplier = $data['text'] ?? '';
        $this->managerUrl = $data['url'] ?? '';
        $this->managerUrlModalVisible = true;
    }

    public function closeUrlModal()
    {
        $this->managerUrlModalVisible = false;
        $this->dispatch('modal-close');
    }

    public function updateUrl()
    {
        $part = Part::find($this->selectedId);
        //$part->url = json_encode(['text' => '', 'url' => $this->url]);
        $part->url = json_encode([
            'text' => $this->managerSupplier,
            'url' => $this->managerUrl,
        ]);
        $part->save();

        $this->managerUrlModalVisible = false;
        $this->dispatch('urlUpdated', updatedPartId: $this->selectedId);
    }

    public function render()
    {
        return view('livewire.manager.components.url');
    }
}
