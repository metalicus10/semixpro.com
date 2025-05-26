<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfileMinQuantity extends Component
{
    public $default_min_quantity;

    public function mount()
    {
        $this->default_min_quantity = Auth::user()->default_min_quantity;
    }

    public function save()
    {
        $this->validate([
            'default_min_quantity' => 'required|integer|min:1|max:100000',
        ]);
        Auth::user()->update([
            'default_min_quantity' => $this->default_min_quantity,
        ]);
        session()->flash('success', 'Минимальный остаток сохранён!');
    }

    public function render()
    {
        return view('livewire.profile-min-quantity');
    }
}
