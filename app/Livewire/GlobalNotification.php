<?php

namespace App\Livewire;

use Livewire\Component;

class GlobalNotification extends Component
{
    public $notifications;

    protected $listeners = ['notificationAdded' => 'loadNotifications'];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = \App\Models\Notification::where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get();
    }

    public function markAsRead($id)
    {
        \App\Models\Notification::where('id', $id)->update(['read' => true]);
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.global-notification');
    }
}
