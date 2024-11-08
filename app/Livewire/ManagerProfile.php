<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class ManagerProfile extends Component
{
    public $name;
    public $email;
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $show = false;
    public $showDeleteModal = false;

    public function mount()
    {
        // Инициализация значений из текущего профиля менеджера
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'password' => ['nullable', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->email = $this->email;
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        if ($this->password) {
            $user->password = Hash::make($this->password);
        }
        $user->save();

        session()->flash('message', 'Профиль успешно обновлен.');
    }

    public function confirmDeletion()
    {
        $this->resetErrorBag();
        $this->password = '';
        $this->showDeleteModal = true;
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser()
    {
        $this->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($this->password, Auth::user()->password)) {
            $this->addError('password', __('The provided password does not match our records.'));
            return;
        }

        // Удаление пользователя
        $user = Auth::user();
        Auth::logout();
        $user->delete();

        return redirect('/'); // Перенаправление на главную страницу после удаления
    }

    public function render()
    {
        return view('livewire.manager.manager-profile');
    }
}
