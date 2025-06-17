<?php

namespace App\Livewire;

use App\Models\Part;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use PhpParser\Node\Expr\Cast\Object_;

class StockLevel extends Component
{
    public $items = [];

    public function mount()
    {
        $managerId = Auth::user()->id;

        // Получаем parts для этого менеджера
        $parts = Part::where('manager_id', $managerId)->get();

        // Группируем по stock-level (здесь диапазоны условные)
        $high      = $parts->where('quantity', '>', 10)->count();
        $nearLow   = $parts->where('quantity', '>', 5)->where('quantity', '<=', 10)->count();
        $low       = $parts->where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
        $outOfStock = $parts->where('quantity', '<=', 0)->count();

        $this->items = [
            [
                'label' => 'HIGH STOCK PRODUCT',
                'value' => $high,
                'color' => '#00DD7F',
                'bar'   => 'bg-[#00DD7F]',
            ],
            [
                'label' => 'NEAR-LOW STOCK PRODUCT',
                'value' => $nearLow,
                'color' => '#E6F700',
                'bar'   => 'bg-[#E6F700]',
            ],
            [
                'label' => 'LOW STOCK PRODUCT',
                'value' => $low,
                'color' => '#FFE658',
                'bar'   => 'bg-[#FFE658]',
            ],
            [
                'label' => 'OUT OF STOCK PRODUCT',
                'value' => $outOfStock,
                'color' => '#E65075',
                'bar'   => 'bg-[#E65075]',
            ],
        ];
    }

    public function render()
    {
        $total = collect($this->items)->sum('value');
        $items = $this->items;
        $labels = collect($items)->pluck('label');
        $data = collect($items)->pluck('value');
        $colors = collect($items)->pluck('color');
        return view('livewire.stock-level', [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
            'total' => $total,
        ]);
    }
}
