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
    public $notificationMessage = '';
    public $notificationType = 'info';

    public function clearNotification()
    {
        $this->notificationMessage = '';
    }

    public function transferPart()
    {
        $this->validate([
            'partId' => 'required|exists:parts,id',
            'technicianId' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $part = Part::find($this->partId);

        if ($this->quantity > $part->quantity) {
            $this->notificationMessage = 'Недостаточно запчастей на складе';
            $this->notificationType = 'warning';
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

        $this->reset(['partId', 'technicianId', 'quantity']);
        $this->notificationMessage = 'Запчасть успешно передана';
        $this->notificationType = 'success';
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
