<?php

namespace App\Livewire;

use Livewire\Component;

class Technician extends Component
{
    public function render()
    {
        return view('livewire.technician.technician')->layout('layouts.app');
    }
}
