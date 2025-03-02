<?php

namespace App\Livewire\Components;

use App\Models\Part;
use Livewire\Component;

class PartName extends Component
{
    public $part;

    protected $listeners = [
        'partNameChanged' => '$refresh',
    ];

    public function updateName($partId, $newName)
    {
        $part = Part::find($partId);

        if (!$part) {
            $this->dispatch('showNotification', 'error', 'Part not found');
            return;
        }

        // Обновляем название
        $part->name = $newName;
        $part->save();

        $this->dispatch('partNameChanged');
        $this->dispatch('showNotification', 'success', 'Part name updated successfully');
    }

    public function render()
    {
        return view('livewire.manager.components.part-name');
    }
}
