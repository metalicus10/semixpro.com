<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Technician;

class ManagerSchedule extends Component
{
    public $employees = [];
    public $customerSearch = '';
    public $customerResults = [];

    public $tasks;
    public array $days = [];
    public array $timeSlots = [];

    public $jobModalForm = [
        'schedule_from' => '',
        'schedule_to' => '',
        'schedule_from_date' => '',
        'schedule_from_time' => '',
        'schedule_from_time12' => '',
        'schedule_from_ampp' => 'AM',
        'schedule_to_date' => '',
        'schedule_to_time' => '',
        'schedule_to_time12' => '',
        'schedule_to_ampp' => 'PM',
        'customer_id' => null,
        'employee_id' => null,
        'customer_query' => '',
        'employees_query' => '',
        'results' => [],
        'employees' => [],
        'employees_results' => [],
        'selectedCustomer' => null,
        'notify_customer' => false,
        'items' => [],
        'total' => 0,
        'private_notes' => '',
        'tags' => '',
        'attachments' => [],
        'message' => null,
        'new_customer' => [
            'name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
        ],
    ];

    public function mount()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $this->days[] = $startOfWeek->copy()->addDays($i)->toDateString();
        }

        $start = Carbon::createFromTimeString('06:00');
        $end = Carbon::createFromTimeString('22:00');
        while ($start < $end) {
            $this->timeSlots[] = $start->format('g:i A');
            $start->addMinutes(30);
        }
        $this->timeSlots[] = $end->format('g:i A');

        $this->loadTasks();
    }

    public function loadTasksForRange(string $from, string $to)
    {
        $data = Validator::make(
            ['from' => $from, 'to' => $to],
            [
                'from' => 'required|date_format:Y-m-d',
                'to'   => 'required|date_format:Y-m-d|after_or_equal:from',
            ]
        )->validate();

        $fromDate = Carbon::createFromFormat('Y-m-d', $data['from'])->startOfDay();
        $toDate   = Carbon::createFromFormat('Y-m-d', $data['to'])->endOfDay();

        $tasks = Task::query()
            ->with([
                'customer:id,name,email,phone,address',
                'order.items',
                'technicians' => function ($q) {
                    $q->withPivot(['status', 'assigned_at']);
                },
            ])
            ->whereBetween('day', [$fromDate->toDateString(), $toDate->toDateString()])
            ->get();

        $mapped = $tasks->flatMap(function (Task $task) {
            $items = optional($task->order)->items
                ? $task->order->items->map(function ($it) {
                    return [
                        'db_id'       => $it->id,
                        'type'        => $it->item_type,
                        'name'        => $it->item_title,
                        'description' => $it->item_description ?? '',
                        'item_id'     => $it->item_id,
                        'part_id'     => $it->part_id,
                        'qty'         => (int) $it->quantity,
                        'unit_price'  => (float) $it->price,
                        'total'       => (float) $it->total,
                        'is_custom'   => (bool) $it->is_custom,
                    ];
                })->values()->toArray()
                : [];

            return $task->technicians->map(function ($tech) use ($task, $items) {
                $assignedAt = $tech->pivot->assigned_at
                    ? Carbon::parse($tech->pivot->assigned_at)->format('Y-m-d H:i:s')
                    : null;

                return [
                    'id'         => $task->id,
                    'technician' => $tech->id,
                    'day'        => Carbon::parse($task->day)->toDateString(),
                    'start'      => $task->start_time,
                    'end'        => $task->end_time,
                    'client'     => $task->customer ? [
                        'id'      => $task->customer->id,
                        'name'    => $task->customer->name,
                        'email'   => $task->customer->email,
                        'phone'   => $task->customer->phone,
                        'address' => $task->customer->address,
                    ] : null,
                    'status'      => $tech->pivot->status,
                    'assigned_at' => $assignedAt,
                    'items'       => $items,
                    'message'     => $task->message,
                    'order_id'    => $task->order_id,
                ];
            });
        })->values();

        $this->tasks = $mapped;

        return $mapped;
    }

    #[On('createCustomer')]
    public function createCustomer($data)
    {
        try {
            $validated = Validator::make($data, [
                'name' => 'required|string|max:191',
                'email' => 'nullable|email|max:191|unique:customers,email',
                'phone' => 'required|string|max:25|unique:customers,phone',
                'address' => 'nullable|string|max:191',
            ])->after(function ($validator) use ($data) {
                if (empty($data['phone'])) {
                    $validator->errors()->add('contact', 'Phone is required.');
                }
            })->validate();

            $customer = Customer::create($validated);

            $this->dispatch('customer-created', [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('customer-validation-error', [
                'errors' => $e->errors(),
            ]);
        }
    }

    public function searchCustomers($query)
    {
        $results = Customer::where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhere('phone', 'like', "%$query%")
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'address']);
        $this->dispatch('search-customers-result', $results);
    }

    public function saveJob($jobModalForm)
    {
        //dd($jobModalForm);
        $data = $jobModalForm;
        $data['schedule_from_time12'] = $this->normalizeTimeTo24($jobModalForm['schedule_from_time12'] ?? null);
        $data['schedule_to_time12'] = $this->normalizeTimeTo24($jobModalForm['schedule_to_time12'] ?? null);
        $validator = Validator::make($data, [
            'customer_id' => 'required|integer|exists:customers,id',
            'message' => 'nullable|string',
            'employees' => 'required|array|min:1',
            'employees.*.id' => 'required|integer|exists:users,id',
            'employees.*.name' => 'required|string|max:255',
            'employees.*.tasks' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.db_id' => 'nullable|integer|exists:order_items,id',
            'items.*.part_id' => 'nullable|integer|exists:parts,id',
            'items.*.is_custom' => 'required|boolean',
            'items.*.name' => 'nullable|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.tax' => 'required|boolean',
            'items.*.taxTotal' => 'nullable|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'items.*.type' => 'required|string',
            'items.*.total' => 'required|numeric|min:0',
            'total' => 'nullable|numeric',
            'schedule_from_date' => 'required|date',
            'schedule_to_date' => 'required|date',
            'schedule_from_time12' => 'required|date_format:H:i',
            'schedule_to_time12' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            // Если нужно: вернуть ошибки на фронт или кинуть исключение
            $this->dispatch('showNotification', 'error', $validator->errors()->all());
            return;
        }

        $data = $validator->validated();
        $customerId = $data['customer_id'] ?? null;

        foreach ($data['items'] as $item) {
            //if($item['tax']) { $form['total'] += $item['taxTotal']; }
            $data['total'] += floatval($item['total']) ?? 0.00;
        }

        if ($jobModalForm['jobModalType'] === 'edit') {
            $order = Order::findOrFail($jobModalForm['order_id']);
            $order->update(['total' => (float)$data['total']]);
        } else {
            $order = Order::create([
                'customer_id' => $customerId,
                'manager_id' => auth()->id(),
                'status' => 'pending',
                'total' => floatval($data['total']) ?? 0.00,
            ]);
        }

        $rows = collect($data['items'])->map(function ($i) {
            $isMaterial = ($i['type'] ?? '') === 'material';

            return [
                'id' => $i['db_id'] ?? null,
                'item_type' => $i['type'] ?? 'service',
                'item_title' => $i['name'] ?? '',
                'item_description' => $i['description'] ?? '',

                'item_id' => $isMaterial ? ($i['part_id'] ?? null) : ($i['item_id'] ?? null),
                'part_id' => $isMaterial ? ($i['part_id'] ?? null) : null,

                'quantity' => (int)($i['qty'] ?? 1),
                'price' => (float)($i['unit_price'] ?? 0.0),
                'total' => (float)($i['total'] ?? 0.0),
                'is_custom' => (bool)($i['is_custom'] ?? false),
            ];
        });

        DB::transaction(function () use ($order, $rows) {
            $keepIds = [];

            foreach ($rows as $row) {
                $id = $row['id'] ?? null;
                $payload = Arr::except($row, ['id']);

                if ($id && OrderItem::where('order_id', $order->id)->where('id', $id)->exists()) {
                    OrderItem::where('order_id', $order->id)->where('id', $id)->update($payload);
                    $keepIds[] = $id;
                } else {
                    $created = OrderItem::create($payload + ['order_id' => $order->id]);
                    $keepIds[] = $created->id;
                }
            }

            OrderItem::where('order_id', $order->id)
                ->whereNotIn('id', $keepIds)
                ->delete();
        });

        $startTime = $data['schedule_from_date'] . ' ' . $data['schedule_from_time12'];
        $endTime = $data['schedule_to_date'] . ' ' . $data['schedule_to_time12'];

        if ($jobModalForm['jobModalType'] === 'edit') {
            $task = Task::findOrFail($jobModalForm['task_id']);
            $task->update([
                'day'      => $data['schedule_from_date'],
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'customer_id' => $customerId,
                'order_id'    => $order->id,
                'message'     => $data['message'] ?? '',
            ]);
            $order->update(['total' => (float)$data['total']]);
        } else {
            $task = Task::create([
                'day' => $data['schedule_from_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'customer_id' => $customerId,
                'order_id' => $order->id,
                'message' => $data['message'] ?? '',
            ]);
        }

        $syncData = collect($data['employees'])
            ->pluck('id')
            ->mapWithKeys(function ($techId) {
                return [
                    $techId => [
                        'status' => 'new',
                        'assigned_at' => now(),
                    ]
                ];
            })
            ->all();

        $task->technicians()->sync($syncData);
        $this->loadTasks();
    }

    private function normalizeTimeTo24(?string $time): ?string
    {
        if ($time === null) return null;
        $t = trim($time);

        // Уже 'H:i'
        if (preg_match('/^\d{1,2}:\d{2}$/', $t)) {
            return $t;
        }

        // 'H:i:s' -> 'H:i'
        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $t)) {
            try {
                return Carbon::createFromFormat('H:i:s', $t)->format('H:i');
            } catch (\Throwable $e) {
            }
        }

        // '7:30 PM' / '07:30 PM'
        if (preg_match('/am|pm/i', $t)) {
            try {
                return Carbon::createFromFormat('g:i A', strtoupper($t))->format('H:i');
            } catch (\Throwable $e) {
            }
            try {
                return Carbon::createFromFormat('h:i A', strtoupper($t))->format('H:i');
            } catch (\Throwable $e) {
            }
        }

        // Пусть валидатор отловит ошибку формата
        return $t;
    }

    public function updateTaskPosition($taskId, $newStart, $newEnd)
    {
        if ($newStart == "0000-00-00 00:00:00" || $newEnd == "0000-00-00 00:00:00") {
            return;
        }
        $task = Task::findOrFail($taskId);
        $task->update([
            'start_time' => $newStart,
            'end_time' => $newEnd,
        ]);
        $this->loadTasks();
    }

    protected function loadTasks()
    {
        $this->tasks = Task::with('technicians', 'order.items', 'order.items.part')->get()->flatMap(function (Task $task) {
            return $task->technicians->map(fn($tech) => [
                'id' => $task->id,
                'order_id' => $task->order->id,
                'technician' => $tech->id,
                'employees' => $task->technicians ? $task->technicians->values()->toArray() : [],
                'day' => $task->day?->toDateString(),
                'start' => $task->start_time,
                'end' => $task->end_time,
                'client' => $task->customer ? $task->customer->toArray() : [],
                'status' => $tech->pivot->status,
                'items' => $task->order ? $task->order->items->values()->toArray() : [],
                'assigned_at' => $tech->pivot->assigned_at
                    ? Carbon::parse($tech->pivot->assigned_at)->format('Y-m-d H:i:s')
                    : null,
                'message' => $task->message,
            ]);
        });
        //dd(Task::with('technicians', 'order.items')->get());
        $tasks = $this->tasks;

        $technicians = Technician::where('manager_id', Auth::id())->get();
        $pivot = DB::table('task_technician')->get();
        $taskIdsByTechnician = [];
        foreach ($pivot as $row) {
            $taskIdsByTechnician[$row->technician_id][] = $row->task_id;
        }
        $this->employees = $technicians->map(function ($technician) use ($tasks, $taskIdsByTechnician) {
            $ids = $taskIdsByTechnician[$technician->id] ?? [];
            return [
                'id' => $technician->id,
                'name' => $technician->name,
                'tasks' => $tasks->whereIn('id', $ids)->map(function ($task) {
                    return [
                        'id' => $task['id'],
                        'technician' => $task['technician'],
                        'client' => $task['client'],
                        'start_time' => $task['start'],
                        'end_time' => $task['end'],
                        'status' => $task['status'],
                        'assigned_at' => $task['assigned_at'],
                    ];
                })->values(),
            ];
        });
    }

    public function searchParts(string $q): array
    {
        $q = trim($q);
        if ($q === '') return [];

        $parts = \App\Models\Part::query()
            // обязательно ограничиваем по менеджеру
            ->where('manager_id', Auth::id())

            // жадно подтягиваем номенклатуру (оставь имя связи, как у тебя в модели)
            ->with(['nomenclature' => function ($q) {
                // вернём только нужные поля
                $q->select('id', 'name', 'image');
            }])

            // поиск по полям самой запчасти
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%");
                ///->orWhere('sku',  'like', "%{$q}%");
            })

            // и по полям номенклатуры
            ->orWhereHas('nomenclature', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%");
                //->orWhere('sku',  'like', "%{$q}%");
            })
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get([
                'id', 'name', 'quantity', 'price', 'image', 'nomenclature_id', 'manager_id'
            ]);

        return $parts->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                //'sku'        => $p->sku,
                'quantity' => (int)$p->quantity,
                'price' => (float)$p->price,
                'image' => $p->image,
                'nomenclature' => $p->nomenclature ? [
                    'id' => $p->nomenclature->id,
                    'name' => $p->nomenclature->name,
                    'image' => $p->nomenclature->image,
                ] : null,
            ];
        })->values()->toArray();
    }

    public function getStartSlotAttribute($start_time)
    {
        return $start_time ? $start_time->format('g:i A') : null;
    }

    public function getEndSlotAttribute($end_time)
    {
        return $end_time ? $end_time->format('g:i A') : null;
    }

    public function addTask($employeeId, $day, $startTime, $endTime, $client)
    {
        $start = Carbon::createFromFormat('g:i A', $startTime);
        $end = Carbon::createFromFormat('g:i A', $endTime);

        // Валидация на пересечение
        $overlap = Task::where('employee_id', $employeeId)
            ->where('day', $day)
            ->whereTime('start_time', '<', $end->format('H:i:s'))
            ->whereTime('end_time', '>', $start->format('H:i:s'))
            ->exists();

        if ($overlap) {
            $this->dispatch('alert', [
                'message' => 'Ошибка: время пересекается с существующей задачей.'
            ]);
            return;
        }

        $task = Task::create([
            'employee_id' => $employeeId,
            'day' => $day,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'customer_id' => $client,
        ]);

        $task->technicians()->attach($employeeId, [
            'status' => 'new',
            'assigned_at' => now(),
        ]);

        $this->loadTasks();
    }

    public function moveTask(int $taskId, string $day, string $slot, int $empId): void
    {
        $task = Task::with('technicians')->findOrFail($taskId);
        if (!$task) { return; }

        $duration = Carbon::createFromFormat('H:i:s', $task->end_time)
            ->diffInMinutes(Carbon::createFromFormat('H:i:s', $task->start_time));
        $newStart = Carbon::createFromFormat('Y-m-d H:i:s', "{$day} {$slot}");

        $task->day        = $newStart->toDateString();
        $task->start_time = $newStart->format('H:i:s');
        $task->end_time   = $newStart->clone()->addMinutes($duration)->format('H:i:s');

        if ($empId && $task->technicians->doesntContain('id', $empId)) {
            // задача теперь у нового теха (логика на ваш кейс)
            $task->technicians()->syncWithPivotValues([$empId], [
                'status'     => 'new',
                'assigned_at'=> now(),
            ]);
        }

        $task->save();
        $this->loadTasksForRange($task->start_time, $task->end_time);

        /*$timeString = $task->start_time instanceof \Carbon\Carbon
            ? $task->start_time->format('H:i:s')
            : $task->start_time;
        $taskStart = Carbon::createFromFormat('H:i:s', $timeString);*/

        /*$taskEnd = $task->end_time instanceof \Carbon\Carbon
            ? $task->end_time->copy()
            : Carbon::createFromFormat('H:i:s', $task->end_time);

        $totalMinutes = $taskStart->hour * 60 + $taskStart->minute;
        $zeroMinutes = 6 * 60;
        $diffMinutes = $totalMinutes - $zeroMinutes;
        $oldIdx = intval($diffMinutes / 30);

        // Если вдруг не нашли — выходим
        if (!$oldIdx) {
            $this->dispatch('unique-slot-error', [
                'message' => 'Ошибка: не удалось определить начальный слот.'
            ]);
            return;
        }

        $maxIdx = count($this->timeSlots) - 1;
        if ($newIdx > $maxIdx) {
            $newIdx = $maxIdx;
        }

        $delta = $newIdx - $oldIdx;
        $newStart = $taskStart->copy()->addMinutes($delta * 30);
        $newEnd = $taskEnd->copy()->addMinutes($delta * 30);
        $maxStart = Carbon::createFromTimeString('21:30:00');
        $maxEnd = Carbon::createFromTimeString('22:00:00');

        // Проверяем, не ушли ли за пределы дня
        if ($newStart->greaterThan($maxStart) || $newEnd->greaterThan($maxEnd)) {
            $this->dispatch('interval-overlap-error', [
                'message' => 'Задача не может начинаться позже 21:30 или заканчиваться позже 22:00.',
            ]);
            return;
        }

        //Проверка пересечений (ваша существующая логика,
        //но с whereHas('technicians') и без Carbon::parse)
        $techId = $task->technicians->first()->id;
        $overlap = Task::where('day', $task->day)
            ->where('id', '!=', $task->id)
            ->whereTime('start_time', '<', $newEnd->format('H:i:s'))
            ->whereTime('end_time', '>', $newStart->format('H:i:s'))
            ->whereHas('technicians', fn($q) => $q->where('technician_id', $techId))
            ->exists();

        if ($overlap) {
            $this->dispatch('interval-overlap-error', [
                'message' => 'Ошибка: новый интервал пересекается с другой задачей.'
            ]);
            return;
        }

        //Сохраняем в БД в формате H:i:s
        $task->update([
            'start_time' => $newStart->format('H:i:s'),
            'end_time' => $newEnd->format('H:i:s'),
        ]);

        //Перезагружаем задачи для Alpine
        $this->loadTasks();*/
    }

    public function deleteTask($taskId)
    {
        Task::findOrFail($taskId)->delete();
        $this->loadTasks();
    }

    public function render()
    {
        return view('livewire.manager-schedule');
    }
}
