<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Part;
use App\Models\Task;
use App\Models\UserSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
    public array $settings = [];

    public $tasks;
    public $weekTasks;
    public array $days = [];
    public array $timeSlots = [];
    public array $defaultTimeSlots = [];
    public int $timeSlotsBaseCount = 0;
    public bool $isLoading = false;
    public string $dayStart = '06:00';
    public string $dayEnd   = '22:00';

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

    private const DEFAULT_SCHEDULER_SETTINGS = [
        'tz' => null,
        'onlyBusiness' => false,
        'fields' => [
            'job_number' => true,
            'date' => true,
            'description' => true,
            'customer' => true,
            'schedule' => true,
            'phone'    => true,
            'price'    => true,
            'team'     => true,
            'arrival_window' => true,
            'technician' => true,
        ],
    ];

    public function mount()
    {
        $this->settings = $this->loadSchedulerSettings();

        $startOfWeek = Carbon::now()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $this->days[] = $startOfWeek->copy()->addDays($i)->toDateString();
        }

        $start = Carbon::createFromTimeString('06:00');
        $end = Carbon::createFromTimeString('22:00');
        while ($start < $end) {
            $this->timeSlots[] = $start->format('g:i A');
            $this->defaultTimeSlots[] = $start->format('g:i A');
            $start->addMinutes(30);
        }
        $this->timeSlots[] = $end->format('g:i A');

        $this->loadTasks();
    }

    private function loadSchedulerSettings(): array
    {
        $userId = Auth::id();
        $row = UserSetting::query()->where('user_id', $userId)->first();

        $fromDb = $row?->scheduler_settings ?? [];

        // Мягкое слияние: дополняем недостающие ключи дефолтами
        $merged = array_replace_recursive(self::DEFAULT_SCHEDULER_SETTINGS, $fromDb);

        // sanity: убедимся что есть 'fields'-массив
        $merged['fields'] = is_array($merged['fields']) ? $merged['fields'] : self::DEFAULT_SCHEDULER_SETTINGS['fields'];

        return $merged;
    }

    public function saveSchedulerSettings(array $payload): array
    {
        $userId = Auth::id();

        // валидация «мягкая», чтобы не падать из-за неожиданных ключей
        $validated = [
            'tz'           => isset($payload['tz']) ? (string) $payload['tz'] : null,
            'onlyBusiness' => (bool) ($payload['onlyBusiness'] ?? false),
            'fields'       => array_merge(self::DEFAULT_SCHEDULER_SETTINGS['fields'], (array) ($payload['fields'] ?? [])),
        ];

        $row = UserSetting::firstOrCreate(['user_id' => $userId]);

        // сливаем с тем, что уже есть (если вдруг сохраняем неполный набор)
        $current = $row->scheduler_settings ?? [];
        $row->scheduler_settings = array_replace_recursive(self::DEFAULT_SCHEDULER_SETTINGS, $current, $validated);
        $row->save();

        // вернём то, чем реально будем пользоваться на фронте
        $this->settings = $this->loadSchedulerSettings();

        return $this->settings;
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
                'customer:id,name,email,phone,address,address_formatted,address_place_id,address_lat,address_lng',
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
                        'address' => $task->customer->address_formatted,
                        'lat'     => $task->customer->address_lat,
                        'lng'     => $task->customer->address_lng,
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

    /**
     * Поиск адресов: возвращаем массив {id, label, lat, lng}
     */
    public function searchAddress(string $query): array
    {
        $q = trim($query);
        if (mb_strlen($q) < 4) return [];

        $cacheKey = 'nom:'.$q;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($q) {
            $resp = Http::withHeaders([
                'User-Agent' => config('app.name').' ('.config('mail.from.address').')',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q'               => $q,
                'format'          => 'jsonv2',
                'limit'           => 7,
                'addressdetails'  => 1,
            ]);

            if (!$resp->ok()) return [];

            return collect($resp->json())->map(function ($i) {
                return [
                    'id'    => (string)($i['place_id'] ?? ''),
                    'label' => (string)($i['display_name'] ?? ''),
                    'lat'   => isset($i['lat']) ? (float)$i['lat'] : null,
                    'lng'   => isset($i['lon']) ? (float)$i['lon'] : null,
                ];
            })->filter(fn($x) => $x['id'] && $x['lat'] && $x['lng'])->values()->all();
        });
    }

    public function saveClientCoords(int $customerId, float $lat, float $lng): void
    {
        Customer::whereKey($customerId)->update([
            'address_lat' => $lat,
            'address_lng' => $lng,
        ]);
    }

    #[On('createCustomer')]
    public function createCustomer(array $data)
    {
        try {
            $validated = Validator::make($data, [
                'name'                => ['required','string','max:191'],
                'email'               => ['required','email','max:191','unique:customers,email'],
                'phone'               => ['required','string','max:25','unique:customers,phone'],
                'address_formatted'   => ['nullable','string','max:191'],
                'address_place_id'    => ['nullable','string','max:191'],
                'address_lat'         => ['nullable','numeric','between:-90,90'],
                'address_lng'         => ['nullable','numeric','between:-180,180'],
            ])->after(function ($validator) use ($data) {
                /*if (empty($data['phone'])) {
                    $validator->errors()->add('contact', 'Phone is required.');
                }*/
                $hasAnyAddress = !empty($data['address_formatted']) ||
                    !empty($data['address_place_id']) ||
                    !empty($data['address_lat']) ||
                    !empty($data['address_lng']);
                if ($hasAnyAddress) {
                    if (empty($data['address_place_id']) ||
                        empty($data['address_lat']) ||
                        empty($data['address_lng'])) {
                        $validator->errors()->add('address', 'Выберите адрес из выпадающего списка.');
                    }
                }
            })->validate();

            $customer = Customer::create([
                'name'               => $validated['name'],
                'email'              => $validated['email'] ?? null,
                'phone'              => $validated['phone'],
                'address'            => $validated['address'] ?? null,
                'address_formatted'  => $validated['address_formatted'] ?? null,
                'address_place_id'   => $validated['address_place_id'] ?? null,
                'address_lat'        => $validated['address_lat'] ?? null,
                'address_lng'        => $validated['address_lng'] ?? null,
            ]);

            $this->dispatch('customer-created', [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address_formatted ?: $customer->address,
                'lat' => $customer->address_lat,
                'lng' => $customer->address_lng,
            ]);
        } catch (ValidationException $e) {
            $errorsString = '<ul class="list-disc list-inside">';
            foreach ($e->errors() as $error){
                $errorsString .= '<li>' . $error[0] . '</li>';
            }
            $errorsString .= '</ul>';
            $this->dispatch('showNotification', 'error', $errorsString);
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
            'items.*.qty' => ['required','integer','min:1', function($attribute, $value, $fail) use ($data) {
                $index  = (int) explode('.', $attribute)[1];
                $item   = $data['items'][$index] ?? null;

                if (($item['type'] ?? '') !== 'material') return;

                $partId = (int) ($item['part_id'] ?? $item['id'] ?? 0);
                if (!$partId) return; // отсутствует ID – пропускаем, чтобы не валиться

                // Дата начала периода – то, что уходит в БД как t.day
                $day = \Carbon\Carbon::parse(
                    $data['schedule_from_date'] ?? $data['schedule_to_date'] ?? now()
                )->toDateString();

                $managerId = (int) ($data['manager_id'] ?? auth()->id());
                $excludeOrderId = !empty($data['order_id']) ? (int)$data['order_id'] : null;

                $available = Part::availableForDay($partId, $day, $managerId, $excludeOrderId);

                if ((int)$value > $available) {
                    $name = $item['name'] ?? 'Material';
                    $fail("Only {$available} left for {$name} on {$day}.");
                }
            }],
        ]);

        if ($validator->fails()) {
            // Если нужно: вернуть ошибки на фронт или кинуть исключение
            $this->dispatch('showNotification', 'error', $validator->errors()->all());
            return;
        }

        $data = $validator->validated();
        $customerId = $data['customer_id'] ?? null;

        foreach ($data['items'] as $item) {
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
        $this->dispatch('tasks-refetch');
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
        $this->tasks = Task::with('technicians', 'order.items', 'order.items.part', 'customer')->get()->flatMap(function (Task $task) {
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
        $maxLanes = 1;
        foreach ($this->employees as $emp) {
            $lanes = $this->computeLanesCount($emp['tasks']->toArray());
            if ($lanes > $maxLanes) {
                $maxLanes = $lanes;
            }
        }
        $this->timeSlots = $this->buildTimeSlots($this->dayStart, $this->dayEnd, 30, $maxLanes);
    }

    /**
     * Жадное раскладывание интервалов по дорожкам (как в вашем JS buildLanes)
     * @param array<int, array{start_time:string,end_time:string}> $tasks
     */
    protected function computeLanesCount(array $tasks): int
    {
        // Сортируем по началу
        usort($tasks, function ($a, $b) {
            return strcmp($a['start_time'], $b['start_time']);
        });

        // В lanes храним «время окончания» последней задачи в каждой дорожке
        $lanesEnd = [];

        foreach ($tasks as $t) {
            $start = Carbon::createFromTimeString($t['start_time']);
            $end   = Carbon::createFromTimeString($t['end_time']);

            $placed = false;
            foreach ($lanesEnd as $i => $endTime) {
                if ($endTime->lte($start)) {
                    // кладём в существующую дорожку
                    $lanesEnd[$i] = $end;
                    $placed = true;
                    break;
                }
            }
            if (!$placed) {
                // создаём новую дорожку
                $lanesEnd[] = $end;
            }
        }

        return max(1, count($lanesEnd));
    }

    /**
     * Строим слоты: шаг 30 минут, минимум 32 * lanesCount
     */
    protected function buildTimeSlots(string $startHm, string $endHm, int $stepMin, int $lanesCount): array
    {
        $start = Carbon::createFromTimeString($startHm);
        $end   = Carbon::createFromTimeString($endHm);

        $slots = [];
        $cursor = $start->copy();
        while ($cursor < $end) {
            $slots[] = $cursor->format('g:i A'); // 6:00 AM, 6:15 AM, ...
            $cursor->addMinutes($stepMin);
        }
        // при желании можете оставить финальную метку конца дня:
        // $slots[] = $end->format('g:i A');

        $minPerLane = 32;
        $minTotal = $minPerLane * max(1, $lanesCount);

        $base = $slots;
        $this->timeSlotsBaseCount = count($base);
        for ($i = count($slots); $i < $minTotal; $i++) {
            $slots[] = $base[$i % $this->timeSlotsBaseCount];
        }

        return $slots;
    }

    public function searchParts(string $q): array
    {
        $q = trim($q);
        if ($q === '') return [];

        $today = now()->toDateString();

        $parts = Part::query()
            // обязательно ограничиваем по менеджеру
            ->where('manager_id', Auth::id())

            // жадно подтягиваем номенклатуру
            ->with(['nomenclature' => function ($q) {
                // вернём только нужные поля
                $q->select('id', 'name', 'image');
            }])

            // поиск по полям самой запчасти
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                ->orWhere('sku',  'like', "%{$q}%");
            })
            ->orWhereHas('nomenclature', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                ->orWhere('nn',  'like', "%{$q}%");
            })
            ->select([
                'id', 'name', 'quantity', 'price', 'image', 'nomenclature_id', 'manager_id'
            ])
            ->selectRaw(
                '(select coalesce(sum(oi.quantity),0)
                from order_items oi
                join orders o on o.id = oi.order_id
                join tasks  t on t.order_id = o.id
                where oi.part_id = parts.id
                 and oi.item_type = "material"
                 and oi.part_id is not null
                 and date(t.day) >= ?
                 and (o.status is null or o.status <> "canceled")
                ) as reserved',
                [$today]
            )
            ->selectRaw('greatest(quantity - (select coalesce(sum(oi2.quantity),0)
                from order_items oi2
                join orders o2 on o2.id = oi2.order_id
                join tasks  t2 on t2.order_id = o2.id
                where oi2.part_id = parts.id
                 and oi2.item_type = "material"
                 and oi2.part_id is not null
                 and date(t2.day) >= ?
                 and (o2.status is null or o2.status <> "canceled")
                ), 0) as available', [$today])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        return $parts->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'quantity' => (int)$p->quantity,
                'reserved'  => (int)($p->reserved ?? 0),
                'available' => (int) ($p->available ?? max($p->quantity - (int)$p->reserved, 0)),
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

    public function partsStockByIds(array $ids): array
    {
        if (empty($ids)) return [];

        $today = now()->toDateString();

        $parts = \App\Models\Part::query()
            ->where('manager_id', Auth::id())
            ->whereIn('id', $ids)
            ->with(['nomenclature' => function ($q) {
                $q->select('id','name','image');
            }])
            ->select('id','name','sku','quantity','price','nomenclature_id')
            ->selectRaw(
                '(select coalesce(sum(oi.quantity),0)
              from order_items oi
              join orders o on o.id = oi.order_id
              join tasks t on t.order_id = o.id
             where oi.part_id = parts.id
               and oi.item_type = "material"
               and oi.part_id is not null
               and date(t.day) >= ?
               and (o.status is null or o.status <> "canceled")
            ) as reserved', [$today]
            )
            ->selectRaw(
                'greatest(quantity - (select coalesce(sum(oi2.quantity),0)
              from order_items oi2
              join orders o2 on o2.id = oi2.order_id
              join tasks t2 on t2.order_id = o2.id
             where oi2.part_id = parts.id
               and oi2.item_type = "material"
               and oi2.part_id is not null
               and date(t2.day) >= ?
               and (o2.status is null or o2.status <> "canceled")
            ), 0) as available', [$today]
            )
            ->get();

        // вернём карту: id => данные
        return $parts->mapWithKeys(function ($p) {
            return [
                $p->id => [
                    'available' => (int)($p->available ?? 0),
                    'reserved'  => (int)($p->reserved ?? 0),
                    'quantity'  => (int)$p->quantity,
                    'price'     => (float)$p->price,
                    'name'      => $p->name,
                    'sku'       => $p->sku,
                ],
            ];
        })->toArray();
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

    private function minutes(string $t): int
    {
        [$h, $m] = array_map('intval', explode(':', substr($t, 0, 5)));
        return $h * 60 + $m;
    }
    private function hhmm(int $mins): string
    {
        $mins = ($mins % (24 * 60) + (24 * 60)) % (24 * 60); // защита от отрицательных
        return sprintf('%02d:%02d:00', intdiv($mins, 60), $mins % 60);
    }

    public function moveTask(int $taskId, string $day, string $slot, ?int $empId = null): void
    {
        if (strlen($slot) === 5) $slot .= ':00';

        $task = Task::with('technicians')->findOrFail($taskId);
        if (!$task) { return; }

        $duration = $this->minutes($task->end_time) - $this->minutes($task->start_time);
        if ($duration <= 0) $duration = 30;

        $task->day = $day;
        $task->start_time = $slot;
        $task->end_time = $this->hhmm($this->minutes($slot) + $duration);

        if ($empId && $task->technicians->doesntContain('id', $empId)) {
            $task->technicians()->syncWithPivotValues([$empId], [
                'status'     => 'new',
                'assigned_at'=> now(),
            ]);
        }

        $task->save();
        $this->loadTasksForRange($task->start_time, $task->end_time);
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
