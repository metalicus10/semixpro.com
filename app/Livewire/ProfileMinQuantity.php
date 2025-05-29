<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfileMinQuantity extends Component
{
    public $min_quantity;
    public $errorMessage = '';

    public function mount()
    {
        $this->min_quantity = Auth::user()->min_quantity;
    }

    public function save()
    {
        try {
            $this->validate([
                'min_quantity' => 'required|integer|min:0|max:100000',
            ]);
            $user = Auth::user();
            $user->min_quantity = $this->min_quantity;
            $user->save();
            $this->errorMessage = '';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = $e->validator->errors()->first('min_quantity');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.profile-min-quantity');
    }
}
