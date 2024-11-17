<?php

namespace App\Livewire;

use App\Models\Part;
use App\Models\TechnicianPart;
use App\Models\TechnicianPartUsage;
use Livewire\Component;

class ManagerStatistics extends Component
{
    public $transfers;
    public $usedParts = [];
    public $notificationMessage = '';
    public $notificationType = 'info';

    protected $listeners = ['partUsedByTechnician' => 'updatePartQuantity', 'partUsed' => 'refreshUsedParts'];

    public function mount()
    {
        $managerId = auth()->id();

        // Получаем все записи перемещения запчастей от этого менеджера
        $this->transfers = TechnicianPart::with(['part', 'technician'])
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

    public function updatePartQuantity($partId)
    {
        // Находим запчасть по ее ID
        $part = Part::find($partId);

        if ($part) {
            // Получаем список всех техников, которым была передана эта запчасть
            $transfers = TechnicianPart::with('technician')
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
