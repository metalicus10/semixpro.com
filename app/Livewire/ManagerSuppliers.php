<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

class ManagerSuppliers extends Component
{
    public $suppliers;
    public $newSupplierName = '';
    public string $search = '';
    public $showAddSupplierModal = false;
    public $errorMessage = '';
    public $notificationMessage = '';
    public $notificationType = 'info';
    public $supplierToDelete = null;

    protected $rules = [
        'newSupplierName' => 'required|unique:suppliers,name',
    ];

    public function mount()
    {
        $this->loadSuppliers();
    }

    public function loadSuppliers()
    {
        $this->suppliers = Supplier::where('manager_id', Auth::id())->get();
    }

    public function getFilteredSuppliersProperty()
    {
        return Supplier::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('nomenclatures')
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'initials' => strtoupper(substr($supplier->name, 0, 1)),
                    'contact_person' => $supplier->contact_name,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                    'receivables' => number_format($supplier->receivables, 2),
                    'used_credits' => number_format($supplier->used_credits, 2),
                    'address' => $supplier->address,
                    'product_count' => $supplier->products_count,
                    'active' => $supplier->is_active,
                ];
            });
    }

    public function addSupplier()
    {
        // Проверка на уникальность имени
        $this->validate();

        Supplier::create([
            'name' => $this->newSupplierName,
            'manager_id' => Auth::id(),
        ]);

        $this->resetForm();
        $this->loadSuppliers();
        $this->notificationMessage = 'Supplier added successfully';
        $this->notificationType = 'success';
        $this->showAddSupplierModal = false;
        $this->dispatch('supplier-added');
    }

    public function confirmDelete($supplierId)
    {
        $this->supplierToDelete = $supplierId;
        $this->dispatch('confirm-delete'); // Отправляем событие для открытия модального окна
    }

    public function deleteSupplier()
    {
        Supplier::findOrFail($this->supplierToDelete)->delete();

        $this->loadSuppliers();

        // Сбрасываем переменную после удаления
        $this->supplierToDelete = null;
        $this->dispatch('supplier-deleted'); // Отправляем событие для закрытия модального окна
    }

    public function clearNotification()
    {
        $this->notificationMessage = '';
    }

    public function resetForm()
    {
        $this->newSupplierName = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.manager-suppliers', [
            'suppliers' => $this->filteredSuppliers,
        ]);
    }
}
