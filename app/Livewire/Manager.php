<?php

namespace App\Livewire;

use Livewire\Component;

class Manager extends Component
{
    public function render()
    {
        return view('livewire.manager.manager')->layout('layouts.app');
    }
}
