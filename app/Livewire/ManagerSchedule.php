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
    public $jobModalForm = [];

    public $tasks;
    public array $days = [];
    public array $timeSlots = [];

    public function mount()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $this->days[] = $startOfWeek->copy()->addDays($i)->toDateString();
        }

        $start = Carbon::createFromTimeString('06:00');
        $end   = Carbon::createFromTimeString('22:00');
        while ($start->lt($end)) {
            $this->timeSlots[] = $start->format('g:i A');
            $start->addMinutes(30);
        }

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

    public function saveJob($form)
    {
        $customerId = $form['customer_id'] ?? null;

        foreach ($form['items'] as $item){
            //if($item['tax']) { $form['total'] += $item['taxTotal']; }
            $form['total'] += floatval($item['total']) ?? 0.00;
        }

        $order = Order::create([
            'customer_id' => $customerId,
            'manager_id'  => auth()->id(),
            'status'      => 'pending',
            'total'       => floatval($form['total']) ?? 0.00,
        ]);

        foreach ($form['items'] as $item) {
            OrderItem::create([
                'order_id'  => $order->id,
                'item_type' => $item['type'],
                'item_id'   => $item['id'],
                'quantity'  => $item['qty'],
                'price'     => $item['unit_price'] ?? 0.00,
                'total'     => $item['total'] ?? 0.00,
            ]);
        }

        $startTime = $form['schedule_from_date'] . ' ' . $form['schedule_from_time'];
        $endTime   = $form['schedule_to_date'] . ' ' . $form['schedule_to_time'];

        $task = Task::create([
            'title'          => $form['items'][0]['name'] ?? 'Job',
            'day'            => $form['schedule_from_date'],
            //'technician_ids' => collect($form['employees'])->pluck('id'),
            'start_time'     => $startTime,
            'end_time'       => $endTime,
            'customer_id'    => $customerId,
            'order_id'       => $order->id,
        ]);

        $syncData = collect($form['employees'])
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
                'start'      => $task->start_time->format('g:i A'),
                'end'        => $task->end_time->format('g:i A'),
                'client'     => $task->customer_id,
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

    public function moveTask($id, $newStart)
    {
        $task = Task::find($id);
        if (!$task) {
            return;
        }

        $start = Carbon::createFromFormat('g:i A', $newStart);
        $durationSec = Carbon::parse($task->end_time)
            ->diffInSeconds(Carbon::parse($task->start_time));
        $end = $start->copy()->addSeconds($durationSec);

        $technicianId = $task->technicians->first()->id;
        // Проверка пересечения (исключаем саму задачу)
        $overlap = Task::where('day', $task->day)
            ->where('id', '!=', $task->id)
            ->whereTime('start_time', '<', $end->format('H:i:s'))
            ->whereTime('end_time',   '>', $start->format('H:i:s'))
            ->whereHas('technicians', function($q) use ($technicianId) {
                $q->where('technician_id', $technicianId);
            })
            ->exists();

        if ($overlap) {
            $this->dispatch('alert', [
                'message' => 'Ошибка: перенос невозможен из‑за пересечения.'
            ]);
            return;
        }

        // Сохраняем новые времена
        $task->update([
            'start_time' => $start->format('H:i:s'),
            'end_time'   => $end->format('H:i:s'),
        ]);

        $this->loadTasks();
    }

    public function render()
    {
        return view('livewire.manager-schedule');
    }
}
