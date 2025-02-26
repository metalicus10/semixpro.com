<?php

namespace App\Livewire\Components;

use App\Models\Part;
use Livewire\Component;

class UrlModal extends Component
{
    public $part;
    public $managerUrlModalVisible = false;
    public $managerSupplier, $managerUrl, $selectedPartId;

    public function saveManagerUrl()
    {
        $part = Part::find($this->selectedPartId);
        //$part->url = json_encode(['text' => '', 'url' => $this->url]);
        $part->url = json_encode([
            'text' => $this->managerSupplier,
            'url' => $this->managerUrl,
        ]);
        $part->save();

        $this->managerUrlModalVisible = false;
        $this->refreshComponent();
    }

    public function render()
    {
        return view('livewire.manager.components.url-modal');
    }
}
