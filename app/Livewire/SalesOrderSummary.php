<?php

namespace App\Livewire;

use Livewire\Component;

class SalesOrderSummary extends Component
{
    public $sales = [];
    public $returns = [];

    public function changeWeek($start, $end)
    {
        // $start и $end — ISO строки, например, '2024-06-01'
        // Заполняем $this->sales и $this->returns нужными массивами
        // Например:
        // $this->sales = [100, 80, 50, 30, 60, 90, 110]; // 7 дней выбранной недели
        // $this->returns = [10, 5, 8, 6, 7, 4, 12];
    }

    public function render()
    {
        return view('livewire.sales-order-summary');
    }
}
