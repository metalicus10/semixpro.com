<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Orchid\Platform\Models\Role;
use Illuminate\Validation\Rules;

class RegisterModal extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $managerRole = Role::where('slug', 'manager')->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'permissions' => $managerRole ? $managerRole->permissions : [],
        ]);

        if ($managerRole) {
            $user->addRole($managerRole);
        }

        Warehouse::create([
            'manager_id' => $user->id,
            'name' => 'Main',
            'is_default' => 1,
        ]);

        Category::create([
            'manager_id' => $user->id,
            'name' => 'Default',
        ]);

        Brand::create([
            'manager_id' => $user->id,
            'name' => 'Default',
        ]);

        Supplier::create([
            'manager_id' => $user->id,
            'name' => 'Default',
        ]);

        event(new Registered($user));
        Auth::login($user);

        $this->redirect(route('manager.manager', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.register-modal');
    }
}
