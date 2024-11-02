<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PartPriceHistory extends Component
{
    public $partId;
    public $startDate;
    public $endDate;

    public function mount($partId)
    {
        $this->partId = $partId;
    }

    public function getPriceHistory()
    {
        $query = DB::table('part_price_history')
            ->where('part_id', $this->partId)
            ->orderBy('changed_at', 'desc');

        if ($this->startDate) {
            $query->whereDate('changed_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('changed_at', '<=', $this->endDate);
        }

        return $query->get();
    }

    public function render()
    {
        return view('livewire.manager.part-price-history');
    }
}
