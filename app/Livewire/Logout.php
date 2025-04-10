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

        return redirect('/'); // Перенаправление на главную страницу после выхода
    }
    public function render()
    {
        return view('livewire.logout');
    }
}
