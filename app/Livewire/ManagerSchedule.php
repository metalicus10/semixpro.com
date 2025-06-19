<?php

namespace App\Livewire;

use App\Models\Customer;
use Illuminate\Console\View\Components\Task;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use App\Models\Technician;

class ManagerSchedule extends Component
{
    public $employees = [];
    public $customerSearch = '';
    public $customerResults = [];

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

    public function createCustomer($data)
    {
        try {
            $validated = Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:customers,email',
                'phone' => 'nullable|string|max:25|unique:customers,phone',
                'address' => 'nullable|string|max:255',
            ])->validate();

            $customer = Customer::create($validated);

            $this->dispatchBrowserEvent('customer-created', ['name' => $customer->name]);
        } catch (ValidationException $e) {
            $this->dispatchBrowserEvent('customer-validation-error', [
                'errors' => $e->errors(),
            ]);
        }
    }

    public function updatedCustomerSearch()
    {
        $term = $this->customerSearch;
        $this->customerResults = Customer::query()
            ->where('name', 'like', "%$term%")
            ->orWhere('email', 'like', "%$term%")
            ->orWhere('phone', 'like', "%$term%")
            ->limit(10)
            ->get();
    }

    public function saveJob($form)
    {
        Task::create([
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
