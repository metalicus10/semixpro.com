<?php

namespace App\Livewire;

use App\Models\WarehouseAssignmentLog;
use Livewire\Component;

class ManagerWarehouseAssignmentsLog extends Component
{
    public $logs;

    public function mount()
    {
        $this->logs = WarehouseAssignmentLog::with(['manager', 'technician', 'warehouse'])
            ->orderBy('assigned_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.manager-warehouse-assignments-log');
    }
}
