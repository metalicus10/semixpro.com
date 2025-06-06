<?php

namespace App\Livewire;

use Livewire\Component;

class ActiveWorkOrder extends Component
{
    public $orders = [];

    public function mount()
    {
        // Пример демо-данных
        $this->orders = [
            [
                'name' => 'WO-026',
                'finished' => 500,
                'required' => 1000,
                'date' => '12 October 2022',
                'cost' => 10000,
            ],
            [
                'name' => 'WO-026',
                'finished' => 320,
                'required' => 500,
                'date' => '9 October 2022',
                'cost' => 8500,
            ],
            [
                'name' => 'WO-026',
                'finished' => 320,
                'required' => 500,
                'date' => '9 October 2022',
                'cost' => 8500,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.active-work-order');
    }
}
