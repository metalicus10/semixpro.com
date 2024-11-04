<?php

namespace App\Livewire;

use App\Models\Part;
use Livewire\Component;

class ManagerPartUrlModal extends Component
{
    public $partId;
    public $text = '';
    public $url = '';
    public $isOpen = false;

    protected $listeners = ['openModal' => 'open'];

    public function open($partId)
    {
        $this->partId = $partId;
        $part = Part::find($partId);

        $this->text = $part->text ?? '';
        $this->url = $part->url;
        $this->isOpen = true;
    }

    public function save()
    {
        $part = Part::find($this->partId);
        $part->text = $this->text;
        $part->url = $this->url;
        $part->save();

        $this->isOpen = false;
        $this->dispatch('refreshParts'); // Для обновления списка запчастей
    }
    public function render()
    {
        return view('livewire.manager.manager-part-url-modal');
    }
}
