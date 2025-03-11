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
    public $modalOpen = false;
    public $warehouseParts, $selectedWarehouses = [];
    public $technicians;
    public $warehouses;
    public $selectedTechnician = null;
    public $selectedWarehouse = '';
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


        $warehouseParts = Part::where('warehouse_id', $this->selectedWarehouse)->get();

        foreach ($warehouseParts as $part) {
            $quantityToTransfer = $this->assignAll
                ? (int)$part->quantity
                : (isset($this->partQuantities[$this->selectedWarehouse][$part->id])
                    ? (int)$this->partQuantities[$this->selectedWarehouse][$part->id]
                    : 0);

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
                    'total_transferred' => $quantityToTransfer,
                    'manager_id' => auth()->id(),
                ]);
            }

            $part->decrement('quantity', $quantityToTransfer);
        }

        // Логируем новые назначения
        if (!in_array($this->selectedWarehouse, $oldWarehouses)) {
            WarehouseAssignmentLog::create([
                'manager_id' => auth()->id(),
                'technician_id' => $technicianId,
                'warehouse_id' => $this->selectedWarehouse,
                'assigned_at' => now(),
            ]);
        }

        $this->dispatch('showNotification', 'success', 'Склады успешно назначены технику');
        $this->dispatch('updateAssignedParts');
        $this->reset('selectedWarehouses', 'partQuantities', 'assignAll');
    }

    public function loadWarehouseParts($warehouseId)
    {
        if (!$warehouseId) return;

        $warehouse = Warehouse::find($warehouseId);
        $parts = Part::where('warehouse_id', $warehouseId)
            ->select('id', 'name', 'quantity')
            ->get()->toArray();;

        // Проверка перед отправкой
        /*if ($parts->isEmpty()) {
            Log::info("Склад $warehouseId не имеет запчастей");
        }*/

        $this->warehouseParts[$warehouseId] = [
            'warehouseName' => $warehouse ? $warehouse->name : 'Неизвестный склад',
            'parts' => $parts
        ];
        //dd($this->warehouseParts);
    }

    public function setMaxQuantities()
    {
        foreach ($this->warehouseParts as $warehouseId => $parts) {
            // Если склад еще не существует в partQuantities, создаем пустой массив
            if (!isset($this->partQuantities[$warehouseId]) || !is_array($this->partQuantities[$warehouseId])) {
                $this->partQuantities[$warehouseId] = [];
            }

            foreach ($parts as $part) {
                // Проверяем, что part->quantity действительно является числом
                if (is_numeric($part['quantity'])) {
                    $this->partQuantities[$warehouseId][$part['id']] = (int)$part['quantity'];
                }
            }
        }
    }

    /*public function updatedSelectedWarehouse($value, $key)
    {
        // Если чекбокс включен, добавляем ID склада
        if (in_array($key, $this->selectedWarehouse)) {
            if (!in_array($key, $this->selectedWarehouse)) {
                $this->selectedWarehouses[] = $key;
            }
        } else {
            // Если чекбокс выключен, удаляем ID склада
            $this->selectedWarehouses = array_filter($this->selectedWarehouses, fn($id) => $id != $key);
        }
    }*/

    public function openModal()
    {
        if (!empty($this->selectedWarehouses)) {
            $this->modalOpen = true;
        }
    }

    public function closeModal()
    {
        $this->modalOpen = false;
    }

    public function render()
    {
        return view('livewire.manager-assign-warehouses');
    }
}
