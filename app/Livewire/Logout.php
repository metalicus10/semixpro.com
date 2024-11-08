<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login'); // Перенаправление на страницу входа после выхода
    }
    public function render()
    {
        return view('livewire.logout');
    }
}
