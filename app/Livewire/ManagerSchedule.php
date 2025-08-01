<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Task;
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

    public function loadSchedule()
    {
        $this->tasks = Task::whereBetween('start_time', [now()->startOfDay(), now()->addDays(7)->endOfDay()])->get();
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
                        'id' => $task->id,
                        'title' => $task->title,
                        'start_time' => $task->start_time,
                        'end_time' => $task->end_time,
                    ];
                })->values(),
            ];
        });
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
                'id'      => $customer->id,
                'name'    => $customer->name,
                'email'   => $customer->email,
                'phone'   => $customer->phone,
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
        $validator = Validator::make($jobModalForm, [
            'customer_id' => 'required|integer|exists:customers,id',
            'message' => 'nullable|string',
            'employees' => 'required|array|min:1',
            'employees.*.id' => 'required|integer|exists:users,id',
            'employees.*.name' => 'required|string|max:255',
            'employees.*.tasks' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|numeric',
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
            'schedule_from_time' => 'required|date_format:H:i',
            'schedule_to_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            // Если нужно: вернуть ошибки на фронт или кинуть исключение
            $this->dispatch('showNotification', 'error', $validator->errors()->all());
            return;
        }

        $data = $validator->validated();
        $customerId = $data['customer_id'] ?? null;

        foreach ($data['items'] as $item){
            //if($item['tax']) { $form['total'] += $item['taxTotal']; }
            $data['total'] += floatval($item['total']) ?? 0.00;
        }

        $order = Order::create([
            'customer_id' => $customerId,
            'manager_id'  => auth()->id(),
            'status'      => 'pending',
            'total'       => floatval($data['total']) ?? 0.00,
        ]);

        foreach ($data['items'] as $item) {
            OrderItem::create([
                'order_id'  => $order->id,
                'item_type' => $item['type'],
                'item_id'   => $item['id'],
                'quantity'  => $item['qty'],
                'price'     => $item['unit_price'] ?? 0.00,
                'total'     => $item['total'] ?? 0.00,
            ]);
        }

        $startTime = $data['schedule_from_date'] . ' ' . $data['schedule_from_time'];
        $endTime   = $data['schedule_to_date'] . ' ' . $data['schedule_to_time'];

        $task = Task::create([
            'title'          => $data['items'][0]['name'] ?? 'Job',
            'day'            => $data['schedule_from_date'],
            //'technician_ids' => collect($form['employees'])->pluck('id'),
            'start_time'     => $startTime,
            'end_time'       => $endTime,
            'customer_id'    => $customerId,
            'order_id'       => $order->id,
            'message'        => $data['message'] ?? '',
        ]);

        $syncData = collect($data['employees'])
            ->pluck('id')
            ->mapWithKeys(function($techId) {
                return [
                    $techId => [
                        'status'      => 'new',
                        'assigned_at' => now(),
                    ]
                ];
            })
            ->all();

        $task->technicians()->sync($syncData);
        $this->loadTasks();
    }

    public function updateTaskPosition($taskId, $newStart, $newEnd)
    {
        if($newStart == "0000-00-00 00:00:00" || $newEnd == "0000-00-00 00:00:00") {return;}
        $task = Task::findOrFail($taskId);
        $task->update([
            'start_time' => $newStart,
            'end_time' => $newEnd,
        ]);
        $this->loadTasks();
    }

    protected function loadTasks()
    {
        $this->tasks = Task::with('technicians')->get()->flatMap(function(Task $task) {
            return $task->technicians->map(fn($tech) => [
                'id'         => $task->id,
                'technician'=> $tech->id,
                'day'        => $task->day->toDateString(),
                'start'      => $task->start_time,
                'end'        => $task->end_time,
                'client' => $task->customer ? $task->customer->name : '',
                'status'     => $tech->pivot->status,
                'assigned_at' => $tech->pivot->assigned_at
                    ? Carbon::parse($tech->pivot->assigned_at)->format('Y-m-d H:i:s')
                    : null,
            ]);
        });
        //dd(Task::with('technicians')->get());
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
        $end   = Carbon::createFromFormat('g:i A', $endTime);

        // Валидация на пересечение
        $overlap = Task::where('employee_id', $employeeId)
            ->where('day', $day)
            ->whereTime('start_time', '<',  $end->format('H:i:s'))
            ->whereTime('end_time',   '>',  $start->format('H:i:s'))
            ->exists();

        if ($overlap) {
            $this->dispatch('alert', [
                'message' => 'Ошибка: время пересекается с существующей задачей.'
            ]);
            return;
        }

        // Создать и сохранить
        $task = Task::create([
            'employee_id' => $employeeId,
            'day'         => $day,
            'start_time'  => $start->format('H:i:s'),
            'end_time'    => $end->format('H:i:s'),
            'customer_id'      => $client,
        ]);

        $task->technicians()->attach($employeeId, [
            'status'      => 'new',
            'assigned_at' => now(),
        ]);

        $this->loadTasks();
    }

    public function moveTask(int $id, int $newIdx)
    {
        $task = Task::find($id);
        if (!$task) {
            return;
        }

        $timeString = $task->start_time instanceof \Carbon\Carbon
            ? $task->start_time->format('H:i:s')
            : $task->start_time;
        $taskStart = Carbon::createFromFormat('H:i:s', $timeString);

        $taskEnd = $task->end_time instanceof \Carbon\Carbon
            ? $task->end_time->copy()
            : Carbon::createFromFormat('H:i:s', $task->end_time);

        $totalMinutes = $taskStart->hour * 60 + $taskStart->minute;
        $zeroMinutes  = 6 * 60;
        $diffMinutes = $totalMinutes - $zeroMinutes;
        $oldIdx      = intval($diffMinutes / 30);

        // Если вдруг не нашли — выходим
        if ($oldIdx === false) {
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
            ->whereTime('end_time',   '>', $newStart->format('H:i:s'))
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
            'end_time'   => $newEnd->format('H:i:s'),
        ]);

        //Перезагружаем задачи для Alpine
        $this->loadTasks();
    }

    public function render()
    {
        return view('livewire.manager-schedule');
    }
}
