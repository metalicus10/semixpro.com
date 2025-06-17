<?php

namespace App\Livewire;

use App\Models\Part;
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
    public $weekOptions = [];
    public $selectedMonth;

    protected $listeners = [
        'transfer' => 'updateTechnicianPartWeekly',
        'return' => 'updateTechnicianPartWeekly',
    ];

    public function mount()
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->loadChartWeeks();
        //$this->selectDefaultWeek();
    }

    public function loadChartWeeks()
    {
        $parts = TechnicianPart::with('parts')->where('manager_id', auth()->id())->get();
        $datesWithData = [];
        $sumTransfers = array_fill(0, 7, 0);
        $sumReturns = array_fill(0, 7, 0);

        foreach ($parts as $part) {
            foreach (['daily_transfers', 'daily_returns'] as $col) {
                $data = $part->$col ?? [];
                foreach (array_keys($data) as $date) {
                    $datesWithData[$date] = true;
                }
            }
        }

        // Строим недели месяца
        $weeks = [];
        $startOfMonth = Carbon::createFromFormat('Y-m', $this->selectedMonth)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $weekStart = $startOfMonth->copy()->startOfWeek();

        while ($weekStart->lte($endOfMonth)) {
            $weekEnd = $weekStart->copy()->endOfWeek();
            // Есть ли даты с данными в этом диапазоне?
            $hasData = false;
            foreach ($datesWithData as $date => $_) {
                if ($date >= $weekStart->format('Y-m-d') && $date <= $weekEnd->format('Y-m-d')) {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                $weeks[] = [
                    'start' => $weekStart->format('Y-m-d'),
                    'end' => $weekEnd->format('Y-m-d'),
                    'label' => $weekStart->format('j M') . ' – ' . $weekEnd->format('j M'),
                    'weekKey' => $weekStart->format('Y-m-d') . '_' . $weekEnd->format('Y-m-d'),
                ];
            }
            $weekStart->addWeek();
        }

        foreach ($weeks as $week) {
            $weekDates = self::getDatesBetween($week['start'], $week['end']);

            $sum = 0;
            foreach ($weekDates as $date) {
                foreach ($parts as $part) {
                    $raw = $part->daily_transfers[$date] ?? 0;
                    if (is_array($raw)) {
                        $count = $raw['count'] ?? 0;
                        $price = $raw['price'] ?? ($part->part->price ?? 0);
                    } else {
                        $count = $raw;
                        $price = $part->part->price ?? 0;
                    }
                    $sum += $count * $price;
                }
            }
        }

        $this->weekOptions = $weeks;
        $this->weekKeys = array_column($weeks, 'weekKey');

        if (!empty($weeks)) {
            $lastWeek = $weeks[count($weeks) - 1]; // например, последнюю (или первую, если хочешь)
            $this->changeWeek($lastWeek['start'], $lastWeek['end']);
        } else {
            $this->transfers = array_fill(0, 7, 0);
            $this->returns = array_fill(0, 7, 0);
        }
    }

    public function changeWeek($start, $end)
    {
        $start = \Carbon\Carbon::parse($start);
        $end = \Carbon\Carbon::parse($end);
        $parts = TechnicianPart::with('parts')->where('manager_id', auth()->id())->get();

        $transfers = array_fill(0, 7, 0);
        $returns = array_fill(0, 7, 0);

        // Собираем суммы по дням недели

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            foreach ($parts as $part) {
                $rawTransfer = $part->daily_transfers[$date] ?? 0;
                if (is_array($rawTransfer)) {
                    $count = $rawTransfer['qty'] ?? 0;
                    $price = $rawTransfer['price'] ?? ($part->part->price ?? 0);
                } else {
                    $count = $rawTransfer;
                    $price = $part->part->price ?? 0;
                }
                $transfers[$i] += $count * $price;

                $rawReturn = $part->daily_returns[$date] ?? 0;
                if (is_array($rawReturn)) {
                    $count = $rawReturn['qty'] ?? 0;
                    $price = $rawReturn['price'] ?? ($part->part->price ?? 0);
                } else {
                    $count = $rawReturn;
                    $price = $part->part->price ?? 0;
                }
                $returns[$i] += $count * $price;
            }
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
            $weekStart->subDays(($weekStart->dayOfWeek + 6) % 7);
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
        $partModel = Part::find($partId);
        $price = $partModel->price;

        if ($type === 'transfer') {
            $arr = $part->daily_transfers ?? [];
            $arr[$dayKey] = [
                'qty' => ($arr[$dayKey]['qty'] ?? 0) + $quantity,
                'price' => $price,
            ];
            $part->daily_transfers = $arr;
        } elseif ($type === 'return') {
            $arr = $part->daily_returns ?? [];
            $arr[$dayKey] = [
                'qty' => ($arr[$dayKey]['qty'] ?? 0) + $quantity,
                'price' => $price,
            ];
            $part->daily_returns = $arr;
        }
        $part->save();
    }

    public static function getDatesBetween($start, $end)
    {
        $dates = [];
        $current = Carbon::parse($start);
        $last = Carbon::parse($end);

        while ($current->lte($last)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }

    public function render()
    {
        return view('livewire.sales-order-summary');
    }
}
