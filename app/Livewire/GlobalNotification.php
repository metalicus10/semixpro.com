<?php

namespace App\Livewire;

use Livewire\Component;

class GlobalNotification extends Component
{
    public $notifications = [];

    protected $listeners = ['notificationAdded' => 'loadNotifications'];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = \App\Models\Notification::where('user_id', auth()->id())
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'message' => $n->message,
                'read' => $n->read,
                'created_at_diff' => $n->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function markAsRead($id)
    {
        $note = \App\Models\Notification::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $note->update(['read' => true]);
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.global-notification');
    }
}
