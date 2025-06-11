<?php

namespace App\Livewire;

use App\Models\TechnicianPart;
use Carbon\Carbon;
use Livewire\Component;

class SalesOrderSummary extends Component
{
    public $partId; // если нужно фильтровать по конкретной запчасти
    public $transfers = [];
    public $returns = [];
    public $weekLabels = [];
    public $weekKeys = [];
    public $selectedMonth;

    protected $listeners = [
        'transfer' => 'updateTechnicianPartWeekly',
        'return' => 'updateTechnicianPartWeekly',
    ];

    public function mount()
    {
        //$this->partId = $partId;
        $this->selectedMonth = now()->format('Y-m');
        $this->loadChartData();
    }

    /*public function changeMonth($month)
    {
        $this->selectedMonth = $month;
        $this->loadChartData();
    }*/

    public function loadChartData()
    {
        $parts = \App\Models\TechnicianPart::where('manager_id', auth()->id())->get();
        $weekKeys = [];
        foreach ($parts as $part) {
            if ($part->daily_transfers) {
                foreach (array_keys($part->daily_transfers) as $key) {
                    $weekKeys[$key] = true;
                }
            }
            if ($part->daily_returns) {
                foreach (array_keys($part->daily_returns) as $key) {
                    $weekKeys[$key] = true;
                }
            }
        }
        $this->weekKeys = array_keys($weekKeys);

        $weeks = $this->getWeeksForMonth($this->selectedMonth);

        $this->weekLabels = [];
        $transfers = [];
        $returns = [];

        foreach ($weeks as $week) {
            $key = $week['start'] . '_' . $week['end'];
            if (!in_array($key, $this->weekKeys)) {
                continue; // пропускаем недели без данных
            }

            $this->weekLabels[] = $week['label'];

            $sumTransfers = 0;
            $sumReturns = 0;
            foreach ($parts as $part) {
                $sumTransfers += $part->daily_transfers[$key] ?? 0;
                $sumReturns   += $part->daily_returns[$key] ?? 0;
            }
            $transfers[] = $sumTransfers;
            $returns[] = $sumReturns;
        }

        $this->transfers = $transfers;
        $this->returns = $returns;

    }

    public function changeWeek($start, $end)
    {
        $parts = \App\Models\TechnicianPart::where('manager_id', auth()->id())->get();
        $weekKeys = [];
        foreach ($parts as $part) {
            if ($part->weekly_transfers) {
                foreach (array_keys($part->weekly_transfers) as $key) {
                    $weekKeys[$key] = true;
                }
            }
            if ($part->weekly_returns) {
                foreach (array_keys($part->weekly_returns) as $key) {
                    $weekKeys[$key] = true;
                }
            }
        }
        $this->weekKeys = array_keys($weekKeys);

        // Для графика по дням недели — массив из 7 элементов
        $transfers = [];
        $returns = [];
        $current = Carbon::parse($start);
        while ($current->lte(Carbon::parse($end))) {
            $dayKey = $current->format('Y-m-d');
            $t = 0; $r = 0;
            foreach ($parts as $part) {
                $t += $part->daily_transfers[$dayKey] ?? 0;
                $r += $part->daily_returns[$dayKey] ?? 0;
            }
            $transfers[] = $t;
            $returns[] = $r;
            $current->addDay();
        }
        $this->transfers = $transfers;
        $this->returns = $returns;
    }

    public function getWeeksForMonth($ym)
    {
        if (empty($ym)) {
            throw new \Exception("Не передан месяц (ym)");
        }
        $parts = explode('-', $ym);
        if (count($parts) < 2) {
            throw new \Exception("Некорректный формат ym: '$ym'");
        }
        [$year, $month] = $parts;

        $firstDay = Carbon::createFromDate($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();

        // Найти первый понедельник, который меньше или равен первому дню месяца
        $weekStart = $firstDay->copy();
        if ($weekStart->dayOfWeek !== Carbon::MONDAY) {
            $weekStart->subDays(($weekStart->dayOfWeek + 6) % 7); // До ближайшего предыдущего понедельника
        }

        $weeks = [];
        $ruMonths = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

        while ($weekStart->lte($lastDay)) {
            $weekEnd = $weekStart->copy()->addDays(6);
            if ($weekEnd->gt($lastDay)) $weekEnd = $lastDay->copy();

            $weeks[] = [
                'start' => $weekStart->format('Y-m-d'),
                'end' => $weekEnd->format('Y-m-d'),
                'label' => $weekStart->format('j') . '–' . $weekEnd->format('j') . ' ' . $ruMonths[$weekStart->month - 1],
            ];
            $weekStart->addDays(7);
        }
        return $weeks;
    }

    function updateTechnicianPartWeekly($params)
    {
        $technicianId = $params['technicianId'];
        $partId = $params['partId'];
        $quantity = $params['quantity'];
        $type = $params['type'] ?? 'transfer';
        $date = $params['date'] ?? null;

        $date = $date ? Carbon::parse($date) : now();
        $dayKey = Carbon::parse($date)->format('Y-m-d');

        $part = TechnicianPart::firstOrCreate([
            'technician_id' => $technicianId,
            'part_id' => $partId,
        ]);

        if ($type === 'transfer') {
            $arr = $part->daily_transfers ?? [];
            $arr[$dayKey] = ($arr[$dayKey] ?? 0) + $quantity;
            $part->weekly_transfers = $arr;
        } elseif ($type === 'return') {
            $arr = $part->daily_returns ?? [];
            $arr[$dayKey] = ($arr[$dayKey] ?? 0) + $quantity;
            $part->weekly_returns = $arr;
        }
        $part->save();
    }

    public function render()
    {
        return view('livewire.sales-order-summary');
    }
}
