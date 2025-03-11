<?php

namespace App\Livewire;

use App\Models\Part;
use App\Models\TechnicianPart;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseAssignmentLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ManagerAssignWarehouses extends Component
{
    public $technicians;
    public $warehouses;
    public $selectedTechnician = null;
    public $selectedWarehouses = [];
    public $partQuantities = [];
    public $assignAll = false;

    protected $rules = [
        'selectedWarehouses' => 'array',
        'selectedWarehouses.*' => 'integer'
    ];

    public function mount()
    {
        $userId = Auth::id();
        $this->technicians = User::whereHas('roles', function ($query) {
            $query->where('slug', 'technician');
        })->get();

        $this->warehouses = Warehouse::where('manager_id', $userId)->get();
    }

    public function updatedSelectedTechnician($value)
    {
        $this->selectedWarehouses = [];
        if ($value) {
            //$this->warehouses = Warehouse::all();
            $technician = User::find($value);
            if ($technician) {
                $this->selectedWarehouses = $technician->warehouses->pluck('id')->toArray();
            }
        }
    }

    public function assignWarehouses()
    {
        $technicianId = $this->selectedTechnician;
        $technician = User::find($technicianId);

        if (!$technicianId) {
            $this->dispatch('showNotification', 'error', 'Выберите техника перед назначением складов');
            return;
        }

        $oldWarehouses = $technician->warehouses()->pluck('id')->toArray();

        /*foreach ($this->selectedWarehouses as $warehouseId) {

            DB::table('technician_warehouse')->insert([
                'technician_id' => $technicianId->id,
                'warehouse_id' => $warehouseId,
            ]);

        }*/

        foreach ($this->selectedWarehouses as $warehouseId) {
            $warehouseParts = Part::where('warehouse_id', $warehouseId)->get();

            foreach ($warehouseParts as $part) {
                $quantityToTransfer = $this->assignAll ? $part->quantity : ($this->partQuantities[$part->id] ?? 0);

                if ($quantityToTransfer <= 0) continue;

                // Проверяем, есть ли уже такая запчасть у техника
                $technicianPart = TechnicianPart::where('technician_id', $technicianId)
                    ->where('part_id', $part->id)
                    ->first();

                if ($technicianPart) {
                    $technicianPart->increment('quantity', $quantityToTransfer);
                } else {
                    TechnicianPart::create([
                        'technician_id' => $technicianId,
                        'part_id' => $part->id,
                        'quantity' => $quantityToTransfer,
                        'manager_id' => auth()->id(),
                    ]);
                }

                $part->decrement('quantity', $quantityToTransfer);
            }
        }

        // Логируем новые назначения
        foreach ($this->selectedWarehouses as $warehouseId) {
            if (!in_array($warehouseId, $oldWarehouses)) {
                WarehouseAssignmentLog::create([
                    'manager_id' => auth()->id(),
                    'technician_id' => $technicianId->id,
                    'warehouse_id' => $warehouseId,
                    'assigned_at' => now(),
                ]);
            }
        }

        $this->dispatch('showNotification', 'success', 'Склады успешно назначены технику');
        $this->dispatch('updateAssignedParts');
        $this->reset('selectedWarehouses', 'partQuantities', 'assignAll');
    }

    public function loadWarehouseParts($warehouseId)
    {
        $parts = Part::where('warehouse_id', $warehouseId)
            ->select('id', 'name', 'quantity')
            ->get();

        // Проверка перед отправкой
        if ($parts->isEmpty()) {
            Log::info("Склад $warehouseId не имеет запчастей");
        }

        $this->dispatch('warehouse-parts-loaded', [
            'warehouseId' => $warehouseId,
            'parts' => $parts
        ]);
    }

    public function render()
    {
        return view('livewire.manager-assign-warehouses');
    }
}
