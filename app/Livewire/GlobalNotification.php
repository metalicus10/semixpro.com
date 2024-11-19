<?php

namespace App\Livewire;

use Livewire\Component;

class GlobalNotification extends Component
{
    public $type;      // Тип уведомления: success, error, warning, info
    public $message;   // Текст уведомления
    public $visible = false; // Флаг видимости уведомления

    protected $listeners = ['showNotification'];

    public function showNotification($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
        $this->visible = true;

        // Автоматическое скрытие уведомления через 5 секунд
        $this->dispatch('hide-notification', ['timeout' => 3500]);
    }

    public function render()
    {
        return view('livewire.global-notification');
    }
}
