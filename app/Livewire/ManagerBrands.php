<?php

namespace App\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManagerBrands extends Component
{
    public $brands, $name, $brandId;
    public $isOpen = false;

    protected $listeners = ['brandUpdated' => 'refreshBrands'];

    public function mount()
    {
        $this->loadBrands();
    }

    public function loadBrands()
    {
        $this->brands = Brand::where('manager_id', Auth::id())->get();
    }

    public function openBrandModal()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->brandId = null;
    }

    public function storeBrand()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $this->brandId,
        ]);

        Brand::updateOrCreate(['id' => $this->brandId], [
            'name' => $this->name,
            'manager_id' => Auth::id(),
        ]);

        session()->flash('message', $this->brandId ? 'Brand updated successfully.' : 'Brand created successfully.');
        $this->dispatch('brandUpdated');

        $this->closeModal();
        $this->resetInputFields();
        $this->loadBrands();
    }

    public function editBrand($id)
    {
        $brand = Brand::findOrFail($id);
        $this->brandId = $id;
        $this->name = $brand->name;

        $this->openModal();
    }

    public function deleteBrand($id)
    {
        Brand::find($id)->delete();
        session()->flash('message', 'Brand deleted successfully.');
        $this->loadBrands();
    }

    public function render()
    {
        return view('livewire.manager.manager-brands');
    }
}
