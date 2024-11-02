<?php

namespace App\Livewire;

use App\Models\Part;
use App\Models\PartTransfer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TransferPartForm extends Component
{
    public $partId;
    public $technicianId;
    public $quantity;

    public function transferPart()
    {
        $this->validate([
            'partId' => 'required|exists:parts,id',
            'technicianId' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $part = Part::find($this->partId);

        if ($this->quantity > $part->quantity) {
            session()->flash('error', 'Недостаточно запчастей на складе.');
            return;
        }

        // Снижаем количество запчастей
        $part->update(['quantity' => $part->quantity - $this->quantity]);

        // Регистрируем передачу
        PartTransfer::create([
            'part_id' => $this->partId,
            'technician_id' => $this->technicianId,
            'quantity' => $this->quantity,
            'manager_id' => Auth::id(),
        ]);

        session()->flash('message', 'Запчасть успешно передана.');
        $this->reset(['partId', 'technicianId', 'quantity']);
    }

    public function render()
    {
        $users = User::all();
        $technicians = collect();
        foreach ($users as $user) {
            if($user->inRole('technician')){
                $technicians->add($user);
            }
        }
        return view('livewire.manager.transfer-part-form', [
            'parts' => Part::all(),
            'technicians' => $technicians,
        ])->layout('layouts.app');
    }
}
