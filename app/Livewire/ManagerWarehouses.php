<?php

namespace App\Livewire;

use App\Models\Nomenclature;
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

    protected $listeners = ['defaultWarehouseUpdated' => 'render', 'partUpdated' => 'render', 'warehouseListUpdated' => '$refresh'];

    protected $rules = [
        'newWarehouseName' => 'required|string|max:255',
        'destinationWarehouse' => 'required|exists:warehouses,id',
    ];

    public function mount()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // Фильтрация запчастей, принадлежащих текущему менеджеру
        $this->parts = Nomenclature::whereHas('category', function ($query) use ($userId) {
            $query->where('manager_id', $userId);
        })
            ->with('parts') // Загружаем связанные запчасти
            ->get()
            ->pluck('parts') // Получаем только запчасти
            ->flatten(); // Разворачиваем в один массив

        //$this->warehouses = Warehouse::where('manager_id', $userId)->orderBy('position')->get();
        if ($user->inRole('technician')) {
            // Если техник, показываем только склады, назначенные ему
            $this->warehouses = $user->warehouses()->with('parts')->get();
        } else {
            // Если менеджер, показываем все склады
            $this->warehouses = Warehouse::where('manager_id', $userId)->orderBy('position')->get();
        }
    }

    public function createWarehouse()
    {
        if (empty($this->newWarehouseName)) {
            $this->dispatch('showNotification', 'error', 'Please enter a warehouse name');
            return;
        }

        $this->validate(['newWarehouseName' => 'required|string|max:255']);

        Warehouse::create([
            'manager_id' => Auth::id(),
            'name' => $this->newWarehouseName,
        ]);

        $this->dispatch('warehouseListUpdated');
        $this->dispatch('warehouseTabsUpdated');

        // Сброс имени склада и сообщения об ошибке
        $this->dispatch('showNotification', 'success', 'Warehouse '.$this->newWarehouseName.' created successfully');
        $this->newWarehouseName = '';
        $this->errorMessage = '';
        $this->mount();
    }

    public function deleteWarehouse($warehouseId)
    {
        $warehouse = Warehouse::find($warehouseId);

        if (!$warehouse) {
            $this->dispatch('showNotification', 'error', 'Warehouse not found');
            return;
        }

        if ($warehouse->name === 'No warehouse') {
            $this->dispatch('showNotification', 'error', 'Cannot delete "No warehouse".');
            return;
        }

        // Проверяем, есть ли запчасти на складе
        $hasParts = $warehouse->parts()->exists();

        /*if ($hasParts) {
            // Перемещаем запчасти в склад "No warehouse"
            $noWarehouse = Warehouse::firstOrCreate([
                'name' => 'No warehouse',
                'manager_id' => auth()->id(),
            ]);

            $warehouse->parts()->update(['warehouse_id' => $noWarehouse->id]);
        }*/

        // Удаляем склад
        $warehouse->delete();

        $this->dispatch('showNotification', 'success', 'Warehouse deleted successfully');
        $this->mount(); // Перезагружаем данные
    }

    public function setDefaultWarehouse($warehouseId)
    {
        // Сброс флага `is_default` для всех складов текущего менеджера
        Warehouse::where('manager_id', Auth::id())->update(['is_default' => false]);

        // Установка выбранного склада по умолчанию
        Warehouse::where('id', $warehouseId)->update(['is_default' => true]);

        // Обновление локального свойства
        $this->dispatch('showNotification', 'success', 'Warehouse set as Default');
        $this->mount();
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
        $this->dispatch('reset-selected-parts');
        $this->dispatch('showNotification', 'success', 'Parts moved successfully');
        $this->dispatch('partUpdated');
        $this->mount();
    }

    public function render()
    {
        return view('livewire.manager-warehouses', [
            'parts' => $this->parts,
        ]);
    }
}
