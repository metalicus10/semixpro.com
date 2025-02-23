<?php

namespace App\Livewire\Components;

use App\Models\Part;
use Livewire\Component;

class Pn extends Component
{
    public $part, $newPn;

    public function getPartPns($partId)
    {
        // Получить все PN для запчасти с указанным ID
        $pns = \App\Models\Pn::where('part_id', $partId)->get();

        // Вернуть массив или использовать данные по необходимости
        return $pns;
    }

    public function render()
    {
        return view('livewire.manager.components.pn');
    }
}
