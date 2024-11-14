<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Warehouse;
use App\Models\Part;
use Illuminate\Support\Facades\Auth;

class ManagerWarehouses extends Component
{
    public $warehouses;
    public $newWarehouseName;
    public $selectedWarehouse;
    public $partToMove;
    public $destinationWarehouse;
    public $parts;
    public $errorMessage;

    protected $listeners = ['defaultWarehouseUpdated' => 'render'];

    protected $rules = [
        'newWarehouseName' => 'required|string|max:255',
        'destinationWarehouse' => 'required|exists:warehouses,id',
    ];

    public function mount()
    {
        $userId = Auth::id();

        // Фильтрация запчастей, принадлежащих текущему менеджеру
        $this->parts = Part::whereHas('category', function ($query) use ($userId) {
            $query->where('manager_id', $userId);
        })->get();

        $this->warehouses = Warehouse::where('manager_id', $userId)->get();
    }

    public function createWarehouse()
    {
        if (empty($this->newWarehouseName)) {
            $this->errorMessage = 'Please enter a warehouse name.';
            return;
        }
    
        $this->validate(['newWarehouseName' => 'required|string|max:255']);
        
        Warehouse::create([
            'manager_id' => Auth::id(),
            'name' => $this->newWarehouseName,
        ]);
    
        // Сброс имени склада и сообщения об ошибке
        $this->newWarehouseName = '';
        $this->errorMessage = '';
        $this->warehouses = Warehouse::where('manager_id', Auth::id())->get();
    }

    public function setDefaultWarehouse($warehouseId)
    {
        // Сброс флага `is_default` для всех складов текущего менеджера
        Warehouse::where('manager_id', Auth::id())->update(['is_default' => false]);

        // Установка выбранного склада по умолчанию
        Warehouse::where('id', $warehouseId)->update(['is_default' => true]);

        // Обновление локального свойства
        $this->dispatch('defaultWarehouseUpdated', $warehouseId);
    }

    public function movePart()
    {
        $this->validate([
            'partToMove' => 'required|array|min:1',
            'destinationWarehouse' => 'required|exists:warehouses,id',
        ]);
        foreach ($this->partToMove as $partId) {
            $part = Part::find($partId);
            if ($part) {
                $part->warehouse_id = $this->destinationWarehouse;
                $part->save();
            }
        }
        
        $this->reset(['partToMove', 'destinationWarehouse']);
    }

    public function render()
    {
        return view('livewire.manager-warehouses', [
            'parts' => $this->parts,
        ]);
    }
}
