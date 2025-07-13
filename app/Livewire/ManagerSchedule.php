<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function mount()
    {
        $this->loadSchedule();
    }

    public function loadSchedule()
    {
        $tasks = Task::whereBetween('start_time', [now()->startOfDay(), now()->addDays(7)->endOfDay()])->get();
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
        // 1. Найти/создать заказчика
        $customerId = $form['customer_id'] ?? null;

        // 2. Создать заказ (order)
        $order = Order::create([
            'customer_id' => $customerId,
            'manager_id'  => auth()->id(),
            'status'      => 'pending',
            'total'       => $form['total'] ?? 0,
        ]);

        // 3. Добавить позиции заказа (order_items)
        foreach ($form['items'] as $item) {
            OrderItem::create([
                'order_id'  => $order->id,
                'item_type' => $item['type'],
                'item_id'   => $item['id'],
                'quantity'  => $item['qty'],
                'price'     => $item['unit_price'] ?? 0,
                'total'       => $item['total'] ?? 0,
            ]);
        }

        // 4. Сохранить задачу (Task) — для календаря
        $startTime = $form['schedule_from_date'] . ' ' . $form['schedule_from_time'];
        $endTime   = $form['schedule_to_date'] . ' ' . $form['schedule_to_time'];

        $task = Task::create([
            'title'          => $form['items'][0]['name'] ?? 'Job',
            'technician_ids' => collect($form['employees'])->pluck('id'),
            'start_time'     => $startTime,
            'end_time'       => $endTime,
            'customer_id'    => $customerId,
            'order_id'       => $order->id,
        ]);

        $task->technicians()->sync($task['technician_ids']);
        $this->loadSchedule();
    }

    public function updateTaskPosition($taskId, $newStart, $newEnd)
    {
        $task = Task::findOrFail($taskId);
        $task->update([
            'start_time' => $newStart, // 'Y-m-d H:i:s'
            'end_time' => $newEnd,     // 'Y-m-d H:i:s'
        ]);
        $this->loadSchedule();
    }

    public function render()
    {
        return view('livewire.manager-schedule');
    }
}
