<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginModal extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $show = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|string',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function login(): void
    {
        $this->validate();

        try {
            $this->form->authenticate(); // если используешь Laravel Fortify

            if (Auth::user()->is_blocked) {
                session()->flash('error', 'Your account has been blocked');
                Auth::logout();
                $this->redirect(route('/'));
                return;
            }

            session()->regenerate();

            if (Auth::user()->hasAccess('platform.index')) {
                $this->redirectIntended(route('platform.index'), navigate: true);
                return;
            }

            if (Auth::user()->hasAccess('manager')) {
                $this->redirectIntended(route('manager.manager'), navigate: true);
                return;
            }

            if (Auth::user()->hasAccess('technician')) {
                $this->redirectIntended(route('technician.technician'), navigate: true);
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->addError('email', 'Ошибка входа: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.login-modal');
    }
}
