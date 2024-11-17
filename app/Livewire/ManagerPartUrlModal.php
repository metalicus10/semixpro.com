<?php

namespace App\Livewire;

use App\Models\Part;
use App\Models\Supplier;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ManagerPartUrlModal extends Component
{
    public $partId;
    public $text = '';
    public $selectedSupplier;
    public $url = '';
    public $isUrlModalOpen = false;
    public $suppliers;

    protected $listeners = ['openModal' => 'open'];

    public function mount()
    {
        $this->loadSuppliers();
    }

    public function loadSuppliers()
    {
        $this->suppliers = Supplier::where('manager_id', Auth::id())->get();
    }

    public function open($partId)
    {
        $this->partId = $partId;
        $part = Part::find($partId);

        $this->text = $part->text ?? '';
        $this->url = $part->url;
        $this->isUrlModalOpen = true;
    }

    public function save()
    {
        $part = Part::find($this->partId);
        $part->text = $this->text;
        $part->url = $this->url;
        $part->save();

        $this->isUrlModalOpen = false;
        $this->dispatch('refreshParts'); // Для обновления списка запчастей
    }
    public function render()
    {
        return view('livewire.manager.manager-part-url-modal');
    }
}
