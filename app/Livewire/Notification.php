<?php

namespace App\Livewire;

use Livewire\Component;

class Notification extends Component
{
    public $type = '';      // success, error, warning, info
    public $message = '';
    public $visible = false;

    protected $listeners = ['showNotification'];

    public function mount($message = '')
    {
        $this->message = $message;
    }

    public function showNotification($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
        $this->visible = true;

        // Автоматическое скрытие уведомления через 5 секунд
        $this->dispatch('hide-notification', ['timeout' => 3000]);
    }

    public function render()
    {
        return view('livewire.notification');
    }
}
