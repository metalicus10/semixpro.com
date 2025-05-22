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
            ->map(function ($n) {
                $payload = is_string($n->payload) ? json_decode($n->payload, true) : $n->payload;
                $payload = is_array($payload) ? $payload : [];
                $part_id = null;
                if (!empty($payload['part_ids']) && is_array($payload['part_ids'])) {
                    $part_id = $payload['part_ids'][0]; // или перебери если несколько
                }
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'message' => $n->message,
                    'read' => $n->read,
                    'created_at_diff' => $n->created_at->diffForHumans(),
                    'nomenclature_id' => $payload['nomenclature_id'] ?? null,
                    'part_id' => $payload['part_id'] ?? null,
                    'warehouse_id' => $payload['warehouse_id'] ?? null,
                ];
            })
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
