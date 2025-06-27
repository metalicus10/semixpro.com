<?php

namespace App\Livewire;

use App\Models\OrderItem;
use App\Models\Part;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PopularItems extends Component
{
    public $tab = 'parts';
    public $popularParts = [];
    public $popularServices = [];

    public function mount()
    {
        $this->loadPopularParts();
        $this->loadPopularServices();
    }

    public function loadPopularParts()
    {
        // Топ-5 запчастей по количеству в order_items
        $this->popularParts = Part::select('parts.*')
            ->withCount([
                'orderItems as orders' => function ($q) {
                    $q->select(DB::raw('count(*)'))->where('item_type', 'part');
                }
            ])
            ->orderByDesc('orders')
            ->take(5)
            ->get()
            ->map(function ($part) {
                return [
                    'id'       => $part->id,
                    'name'     => $part->name,
                    'image'    => $part->image,
                    'category' => optional($part->category)->name,
                    'stock'    => $part->quantity,
                    'available'=> $part->total,
                    'variants' => 6, // динамика если надо
                    'orders'   => $part->orders,
                ];
            })
            ->toArray();
    }

    public function loadPopularServices()
    {
        // Топ-5 услуг по количеству в order_items
        $this->popularServices = Service::select('services.*')
            ->withCount(['orderItems as orders' => function ($q) {
                $q->select(DB::raw('count(*)'))->where('item_type', 'service');
            }])
            ->orderByDesc('orders')
            ->take(5)
            ->get()
            ->map(function($service) {
                return [
                    'id'       => $service->id,
                    'name'     => $service->name,
                    'category' => optional($service->category)->name,
                    'orders'   => $service->orders,
                ];
            })->toArray();
    }

    public function updatedTab($value)
    {
        // Можно доп. логику если нужно
    }

    public function render()
    {
        return view('livewire.manager.dashboard.popular-items');
    }
}
