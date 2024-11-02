<?php

namespace App\Livewire;

use App\Models\TechnicianPartUsage;
use Livewire\Component;

class TechnicianPartUsageComponent extends Component
{
    public $partId;
    public $quantityUsed;

    public function registerUsage()
    {
        // Регистрируем использование запчасти техником
        TechnicianPartUsage::create([
            'part_id' => $this->partId,
            'technician_id' => auth()->id(),
            'quantity_used' => $this->quantityUsed,
        ]);

        // Триггерим событие для обновления статистики у менеджера
        $this->dispatch('partUsed', $this->partId);
    }

    public function render()
    {
        return view('livewire.technician.technician-part-usage');
    }
}
