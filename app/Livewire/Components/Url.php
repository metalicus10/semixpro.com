<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Url extends Component
{
    public $urlData;
    public $part;

    public function render()
    {
        return view('livewire.manager.components.url');
    }
}
