<?php

namespace App\Livewire;

use App\Models\PartTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ManagerDashboardStats extends Component
{
    public $dailyShipments;
    public $weeklyShipments;
    public $monthlyShipments;
    public $topParts;

    public function mount()
    {
        $this->getStatistics();
    }

    public function getStatistics()
    {
        $userId = Auth::id(); // Получаем ID текущего менеджера

        $this->dailyShipments = PartTransfer::where('manager_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        $this->weeklyShipments = PartTransfer::where('manager_id', $userId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $this->monthlyShipments = PartTransfer::where('manager_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->count();

        $this->topParts = PartTransfer::where('manager_id', $userId)
            ->with('part')
            ->select('part_id', DB::raw('count(*) as total'))
            ->groupBy('part_id')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.manager.manager-dashboard-stats')->layout('layouts.app');
    }
}
