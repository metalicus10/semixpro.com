<?php

namespace App\Livewire;

use App\Models\Part;
use App\Models\Technician;
use App\Models\TechnicianPart;
use App\Models\TechnicianPartUsage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ManagerStatistics extends Component
{
    public $transfers, $technicians, $parts;
    public ?int $selectedTechnicianId = null;
    public $usedParts = [];
    public $notificationMessage = '';
    public $notificationType = 'info';
    public bool $isLoading = false;

    protected $listeners = ['partUsedByTechnician' => 'updatePartQuantity', 'partUsed' => 'refreshUsedParts'];

    public function mount()
    {
        $managerId = auth()->id();
        $this->loadTechniciansWithParts();

        // Получаем все записи перемещения запчастей от этого менеджера
        $this->transfers = TechnicianPart::with(['parts', 'technician'])
            ->where('manager_id', $managerId)
            ->get();

        // Используем total_transferred для отображения общего количества переданных запчастей
        foreach ($this->transfers as $transfer) {
            $this->usedParts[$transfer->part_id] = [
                'total_transferred' => $transfer->total_transferred,
                'quantity_remaining' => $transfer->quantity,
            ];
        }
    }

    public function loadTechniciansWithParts()
    {
        $this->technicians = Technician::where('manager_id', Auth::id())->orderBy('id')
            ->get();

        // Устанавливаем активный склад по умолчанию (первый в списке)
        if (empty($this->selectedTechnicianId) && !empty($this->technicians)) {
            $this->selectedTechnicianId = $this->technicians[0]['id'];
        }

        // Загружаем запчасти по складам
        $this->loadParts($this->selectedTechnicianId);
    }

    public function loadParts(int $technicianId)
    {
        $this->parts = Cache::remember("parts_for_technician_{$technicianId}", 60, function () use ($technicianId) {
            return TechnicianPart::where('manager_id', Auth::id())
                ->whereHas('technicianDetails', function ($query) use ($technicianId) {
                    $query->where('id', $technicianId);
                })
                ->with(['parts', 'technicianDetails'])
                ->get()
                ->toArray();
        });
    }

    public function selectTechnician(int $technicianId)
    {
        $this->isLoading = true;
        $this->selectedTechnicianId = $technicianId;
        $this->loadParts($technicianId);
        $this->isLoading = false;
    }

    public function updatePartQuantity($partId)
    {
        // Находим запчасть по ее ID
        $part = Part::find($partId);

        if ($part) {
            // Получаем список всех техников, которым была передана эта запчасть
            $transfers = TechnicianPart::with('technicians')
                ->where('part_id', $partId)
                ->get();

            // Массив для хранения количества использованных запчастей по техникам
            $technicianUsageStats = [];

            foreach ($transfers as $transfer) {
                // Получаем количество использованных запчастей техником
                $usageCount = TechnicianPartUsage::where('part_id', $partId)
                    ->where('technician_id', $transfer->technician_id)
                    ->sum('quantity_used');

                // Сохраняем статистику по техникам
                $technicianUsageStats[$transfer->technician->name] = $usageCount;
            }

            $this->notificationMessage = 'Обновлена статистика по использованию запчасти';
            $this->notificationType = 'info';

            // Вы можете отобразить статистику на странице или сохранить данные, как вам нужно
            return $technicianUsageStats;  // Либо вы можете передать эти данные в компонент для отображения
        } else {
            $this->notificationMessage = 'Запчасть не найдена';
            $this->notificationType = 'error';
        }
    }

    // Метод для подсчета количества использованных запчастей техниками
    public function countUsedParts($partId)
    {
        return TechnicianPartUsage::where('part_id', $partId)->sum('quantity_used');
    }

    public function refreshUsedParts($partId)
    {
        $this->usedParts[$partId] = $this->countUsedParts($partId);
    }

    public function clearNotification()
    {
        $this->notificationMessage = '';
    }

    public function render()
    {
        //$usageStats = $this->updatePartQuantity($partId);
        return view('livewire.manager.manager-statistics');
    }
}
