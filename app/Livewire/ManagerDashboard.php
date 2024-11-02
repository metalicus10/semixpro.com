<?php

namespace App\Livewire;

use Livewire\Component;

class ManagerDashboard extends Component
{
    public function render()
    {
        return view('livewire.manager.manager-dashboard')->layout('layouts.app');
    }
}
