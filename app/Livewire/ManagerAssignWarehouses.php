<?php

namespace App\Livewire;

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
        $technician = User::find($this->selectedTechnician);

        if ($technician) {
            $oldWarehouses = $technician->warehouses()->pluck('id')->toArray();

            //dd($this->selectedWarehouses);
            foreach ($this->selectedWarehouses as $warehouseId) {

                    DB::table('technician_warehouse')->insert([
                        'technician_id' => $technician->id,
                        'warehouse_id' => $warehouseId,
                    ]);

            }

            //$technician->warehouses()->sync($this->selectedWarehouses);

            // Логируем новые назначения
            foreach ($this->selectedWarehouses as $warehouseId) {
                if (!in_array($warehouseId, $oldWarehouses)) {
                    WarehouseAssignmentLog::create([
                        'manager_id'   => auth()->id(),
                        'technician_id' => $technician->id,
                        'warehouse_id' => $warehouseId,
                        'assigned_at'  => now(),
                    ]);
                }
            }

            $this->dispatch('showNotification', 'success', 'Склады успешно назначены технику');
        }
    }

    public function render()
    {
        return view('livewire.manager-assign-warehouses');
    }
}
