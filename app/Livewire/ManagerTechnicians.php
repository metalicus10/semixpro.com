<?php

namespace App\Livewire;

use App\Models\Technician;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Orchid\Platform\Models\Role;

class ManagerTechnicians extends Component
{
    public $technicians;
    public $addTechnicianModal = false;
    public $name, $email, $password;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:technicians,email',
        'password' => 'required|string|min:8',
    ];

    public function mount()
    {
        $this->technicians = Technician::where('manager_id', Auth::id())->get();
    }

    public function showAddTechnicianModal()
    {
        $this->resetInputFields();
        $this->addTechnicianModal = true;
    }

    public function hideAddTechnicianModal()
    {
        $this->addTechnicianModal = false;
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
    }

    public function addTechnician()
    {
        $this->validate();

        $role = Role::where('slug', 'technician')->first();

        // Создание пользователя в таблице users
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'permissions' => $role->permissions,
        ]);

        // Назначаем роль "Техник" через систему Orchid
        $user->addRole($role);

        // Создание записи в таблице technicians, связанной с users
        Technician::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'manager_id' => Auth::id(),
            'user_id' => $user->id, // Связываем техника с пользователем
        ]);

        $this->hideAddTechnicianModal();
        $this->mount(); // Обновляем список техников
    }

    public function blockTechnician($id)
    {
        $technician = Technician::find($id);
        $technician->is_active = false;
        $technician->save();

        $user = User::find($technician->user_id);
        $user->is_blocked = !$user->is_blocked; // Меняем статус блокировки
        $user->save();

        $this->mount(); // Обновляем список техников
    }

    public function unblockTechnician($id)
    {
        $technician = Technician::find($id);
        $technician->is_active = true;
        $technician->save();

        $user = User::find($technician->user_id);
        $user->is_blocked = !$user->is_blocked; // Меняем статус блокировки
        $user->save();

        $this->mount(); // Обновляем список техников
    }

    public function submit()
    {
        $this->validate();

        // Создаем техника, привязывая его к менеджеру
        Technician::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'manager_id' => Auth::id(),
        ]);

        // Очищаем поля формы
        $this->reset(['name', 'email', 'password']);
        session()->flash('message', 'Техник успешно добавлен.');
    }

    public function render()
    {
        return view('livewire.manager.manager-technicians')->layout('layouts.app');
    }
}
