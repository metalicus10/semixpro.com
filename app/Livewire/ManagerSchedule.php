<?php

namespace App\Livewire;

use App\Models\Customer;
use Illuminate\Console\View\Components\Task;
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
        $this->employees = Technician::with(['tasks' => function ($q) {
            $q->whereBetween('start_time', [now()->startOfDay(), now()->endOfDay()]);
        }])->get()->map(function ($technician) {
            return [
                'id' => $technician->id,
                'name' => $technician->name,
                'tasks' => $technician->tasks->map(fn ($task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'start_time' => $task->start_time,
                    'end_time' => $task->end_time,
                ]),
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
        //dd(array_values($results));
        $this->dispatch('search-customers-result', $results);
    }

    public function saveJob($form)
    {
        \App\Models\Task::create([
            'title' => $form['items'][0]['name'] ?? 'Job',
            'technician_id' => Technician::where('name', $form['dispatch'])->first()?->id,
            'start_time' => $form['schedule_from'],
            'end_time' => $form['schedule_to'],
            'customer_id' => $form['selectedCustomerId'] ?? null,
        ]);

        $this->loadSchedule();
    }

    public function updateTaskPosition($taskId, $x)
    {
        $task = Task::findOrFail($taskId);
        $duration = strtotime($task->end_time) - strtotime($task->start_time);

        $newStart = now()->startOfDay()->addMinutes(($x / 128) * 60);
        $newEnd = $newStart->copy()->addSeconds($duration);

        $task->update([
            'start_time' => $newStart,
            'end_time' => $newEnd
        ]);

        $this->loadSchedule();
    }

    public function render()
    {
        return view('livewire.manager-schedule');
    }
}
