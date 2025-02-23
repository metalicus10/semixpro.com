<?php

namespace App\Livewire\Components;

use App\Models\Part;
use Livewire\Component;

class Price extends Component
{
    public $part;

    public function render()
    {
        return view('livewire.manager.components.price');
    }
}
