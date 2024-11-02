<?php

namespace App\Livewire;

use Livewire\Component;

class TechnicianDashboard extends Component
{
    public function render()
    {
        return view('livewire.technician.technician-dashboard')->layout('layouts.app');
    }
}
