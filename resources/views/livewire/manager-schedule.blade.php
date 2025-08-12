{{-- resources/views/livewire/job-scheduler.blade.php --}}
<div
    x-data="scheduler()"
    @mouseup.window="$event.button === 0 && endSelection()"
    @unique-slot-error.window="alert($event.detail[0].message)"
    @interval-overlap-error.window="alert($event.detail[0].message)"
    class="overflow-x-auto bg-white text-gray-800 border pb-[10px]"
    x-init="init()"
>
    {{-- Заголовок --}}
    <div class="inline-flex items-center border-b">
        {{-- Первая узкая ячейка для таймзоны или иконки --}}
        <div class="w-32 flex-shrink-0 p-2 text-sm font-medium text-center">
            GMT -04
        </div>
        {{-- Дни недели с часами --}}
        <div class="flex-1 inline-flex">
            <template x-for="day in days" :key="day">
                <div class="flex flex-col">
                    {{-- Дата --}}
                    <div
                        class="h-5 px-2 flex items-center justify-center font-semibold text-sm border-b border-b-gray-300">
                        <span x-text="formatFullDate(day)"></span>
                    </div>
                    {{-- Часы --}}
                    <div class="flex">
                        <template x-for="(time, idx) in timeSlots" :key="idx">
                            <div
                                class="w-[30px] h-8 flex-shrink-0 text-center text-[10px] border-r border-r-gray-300 last:border-r-0">
                                <span x-text="time"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Строки сотрудников --}}
    <template x-for="employee in employees" :key="employee.id">
        <div class="inline-flex items-start border-b group" :data-emp="employee.id">
            {{-- Колонка с аватаром и именем --}}
            <div class="w-32 flex-shrink-0 flex items-center p-2 space-x-2 bg-gray-50">
                <img
                    :src="employee.avatar"
                    alt=""
                    class="w-8 h-8 rounded-full object-cover"
                />
                <span class="text-sm font-medium truncate" x-text="employee.name"></span>
            </div>

            {{-- Семь дней --}}
            <div class="flex-1 inline-flex relative">
                <template x-for="day in days" :key="day">
                    <div class="relative flex-shrink-0" :style="`width:${dayWidth}px`"
                         :class="{ 'day-left-border': day !== 0 }" :data-day="day">
                        {{-- Фоновые ячейки часов --}}
                        <div class="flex">
                            <template x-for="(_, idx) in timeSlots" :key="idx">
                                <div
                                    class="w-[30px] h-16 flex-shrink-0 border-r border-r-gray-300"
                                    :class="{
                                        'bg-blue-100': isSelected(employee.id, day, idx),
                                        'bg-gray-200 pointer-events-none': isPast(day, idx)
                                    }"
                                    @mousedown.prevent="$event.button === 0  && !isPast(day, idx) && startSelection(employee.id, day, idx)"
                                    @mouseenter.prevent="$event.buttons === 1  && !isPast(day, idx) && dragSelection(employee.id, day, idx)"
                                    @contextmenu.prevent="onContextMenu($event, employee.id, day, idx)"
                                ></div>
                            </template>
                        </div>

                        {{-- Задачи --}}
                        <template x-for="task in dayTasks(employee.id, day)" :key="task.id"
                        >
                            <div
                                 class="absolute top-1 h-14 bg-green-500 text-white text-[11px] rounded shadow cursor-move px-1 flex items-center space-x-1"
                                 :class="{
                                    'pointer-events-none opacity-60 bg-[repeating-linear-gradient(45deg,#aeaeae00_0,#10182885_5px,#0000_5px,#0000_18px)]': isTaskPast(task),

                                    'cursor-move': !isTaskPast(task)
                                 }"
                                 @mousedown.prevent="!isTaskPast(task) && startDrag(task, $event)"
                                 @contextmenu.prevent="
                                    contextMenu.x = $event.clientX;
                                    contextMenu.y = $event.clientY;
                                    contextMenu.task = task;
                                    contextMenu.visible = true;
                                 "
                                 x-bind:style="
                                    drag.task && drag.task.id === task.id
                                        ? `left:${drag.previewX}px; width:${drag.widthPx}px;`
                                        : taskStyle(task)
                                "
                            >
                                <div class="flex flex-col">
                                    <span class="truncate" x-text="task.client.name"></span>
                                    <span class="whitespace-wrap" x-text="`${to12Hour(task.start)} – ${to12Hour(task.end)}`"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Контекстное меню таймлайна -->
    <div
        x-show="menuVisible"
        @click.away="closeMenu()"
        class="absolute z-[9999] bg-white shadow border rounded w-40"
        :style="`top: ${menuY}px; left: ${menuX}px;`"
    >
        <button
            @click="openJobModal('createJob', sel); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <span class="font-medium">+Job</span>
        </button>
        <button
            @click="openJobModal('createEstimate'); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <!-- … SVG … -->
            <span class="font-medium">+Estimate</span>
        </button>
        <button
            @click="openJobModal('createEvent'); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <!-- … SVG … -->
            <span class="font-medium">+Event</span>
        </button>
    </div>

    <!-- Контекстное меню задачи -->
    <div
        x-show="contextMenu.visible"
        :style="`top:${contextMenu.y}px; left:${contextMenu.x}px`"
        class="fixed z-50 bg-white border rounded shadow-lg animate-fade-in"
        @click.away="contextMenu.visible = false"
    >
        <button
            class="block w-full px-4 py-2 hover:bg-gray-100 text-left"
            @click="
                contextMenu.visible = false;
                openJobModal('edit', sel, contextMenu);
            "
        >Изменить
        </button>
        <button
            class="block w-full px-4 py-2 hover:bg-gray-100 text-left text-red-600"
            @click="
                contextMenu.visible = false;
                taskToDelete = contextMenu.task;
                confirmDeleteOpen = true;
            "
        >Удалить
        </button>
    </div>

    <!-- Подтверждение удаления задачи -->
    <div
        x-show="confirmDeleteOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30"
    >
        <div class="bg-white rounded shadow-lg p-8 w-full max-w-sm">
            <div class="mb-4 text-lg">Вы уверены, что хотите удалить задачу?</div>
            <div class="flex justify-end space-x-4">
                <button class="px-4 py-2" @click="confirmDeleteOpen = false">Отмена</button>
                <button class="px-4 py-2 bg-red-600 text-white rounded"
                        @click="deleteTask(taskToDelete)"
                >Да
                </button>
            </div>
        </div>
    </div>

    <!-- AddCustomerModal -->
    <div
        x-show="showAddCustomerModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        style="display: none;"
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6"
             @click.away="showAddCustomerModal = false">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Add new customer</h2>
                <button type="button" @click="showAddCustomerModal = false"
                        class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;
                </button>
            </div>
            <form @submit.prevent="saveNewCustomer">
                <template x-if="customerError">
                    <div class="mb-2 text-red-600 text-xs" x-text="customerError"></div>
                </template>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Name*</label>
                    <input type="text" x-model="jobModalForm.new_customer.name" required
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" x-model="jobModalForm.new_customer.email"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" x-model="jobModalForm.new_customer.phone"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Address</label>
                    <input type="text" x-model="jobModalForm.new_customer.address"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="showAddCustomerModal = false"
                            class="px-4 py-2 text-sm bg-gray-100 rounded hover:bg-gray-200">Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                        Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="jobModalOpen"
         class="fixed inset-0 z-[45] flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[95vh]">
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <h2 class="text-xl font-semibold" x-text="jobModalType === 'edit' ? 'Edit job' : 'New job'"></h2>
                <button @click="jobModalOpen = false" class="text-gray-500 hover:text-red-500 text-2xl">&times;
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Column -->
                <div class="w-full lg:w-1/3 space-y-6">
                    <div class="bg-gray-50 rounded-lg border p-4 w-full max-w-[400px]">
                        <div class="font-medium text-sm mb-1 flex items-center gap-1">
                            <svg class="w-4 h-4"/>
                            Customer
                        </div>
                        <div class="relative">
                            <input type="text" x-model="jobModalForm.customer_query"
                                   @input.live.debounce.500ms="searchCustomers"
                                   @focus="showCustomerModal = true"
                                   @blur="setTimeout(() => showCustomerModal = false, 500)"
                                   class="w-full rounded px-2 py-1 text-sm border"
                                   placeholder="Name, email, phone, or address"/>
                            <!-- Список найденных клиентов -->
                            <div x-show="showCustomerModal && jobModalForm.results.length"
                                 class="absolute top-full min-w-full max-w-full bg-white z-30 border rounded shadow mt-1">
                                <template x-for="customer in jobModalForm.results">
                                    <div
                                        @click="selectCustomer(customer)"
                                        class="block px-2 py-1 hover:bg-gray-100 cursor-pointer"
                                        x-text="customer.name + ' ' + (customer.email || '')"
                                    >
                                    </div>
                                </template>
                            </div>
                            <div
                                x-show="showCustomerModal && jobModalForm.customer_query.length > 1 && jobModalForm.results.length === 0"
                                class="block hover:bg-gray-100 px-2 py-1 text-gray-400 bg-white z-30 border shadow rounded mt-1">
                                Ничего не найдено
                            </div>

                        </div>
                        <button type="button" class="text-blue-600 text-xs mt-2" @click="showAddCustomerModal = true">
                            + New customer
                        </button>
                    </div>

                    <!-- Schedule -->
                    <div class="border p-4 rounded space-y-4">
                        <label class="block text-sm font-medium">Schedule</label>
                        <div class="flex flex-col gap-2 items-start">
                            <div class="flex justify-between items-center">
                                <label class="block text-xs text-gray-500 mb-1 w-10">From</label>
                                <div class="flex gap-2 items-center">
                                    <input type="date" x-model="jobModalForm.schedule_from_date"
                                           class="border rounded px-2 py-1">
                                    <div
                                        x-data="{
                                                show: false,
                                                hour: 9,
                                                minute: '00',
                                                ampm: 'AM',
                                                get value() { return `${this.hour}:${this.minute} ${this.ampm}` },
                                                setValue(h, m, a) {
                                                    this.hour = h;
                                                    this.minute = m;
                                                    this.ampm = a;
                                                    this.show = false;
                                                    $dispatch('time-changed', { field: 'from', value: this.value });
                                                },
                                                setFromExternal(val) {
                                                    if (!val) return;
                                                    let [time, ampm] = val.split(' ');
                                                    let [h, m] = time.split(':');
                                                    this.hour = parseInt(h);
                                                    this.minute = m;
                                                    this.ampm = ampm || 'AM';
                                                }
                                            }"
                                        x-init="setFromExternal(jobModalForm.schedule_from_time12)"
                                        x-effect="setFromExternal(jobModalForm.schedule_from_time12)"
                                        @time-changed.window="updateTime($event.detail)"
                                        class="relative w-36"
                                    >
                                        <button type="button"
                                                @click="show = !show"
                                                class="w-full px-2 py-1 border rounded focus:outline-none flex items-center justify-between"
                                        >
                                            <span x-text="value"></span>
                                            <svg class="w-4 h-4 ml-2 text-gray-400" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <div x-show="show"
                                             @click.away="show = false"
                                             class="absolute flex justify-between z-10 bg-white rounded shadow-md mt-1 p-2 gap-2"
                                             style="min-width: 210px"
                                        >
                                            <!-- Часы -->
                                            <select x-model="hour"
                                                    class="w-[60px] border rounded p-1">
                                                <template x-for="h in 12" :key="h">
                                                    <option :value="h" x-text="h"></option>
                                                </template>
                                            </select>
                                            <!-- Минуты -->
                                            <select x-model="minute"
                                                    class="w-[60px] border rounded p-1">
                                                <template x-for="m in [0, 30]" :key="m">
                                                    <option :value="m.toString().padStart(2, '0')"
                                                            x-text="m.toString().padStart(2, '0')"></option>
                                                </template>
                                            </select>
                                            <!-- AM/PM -->
                                            <select x-model="ampm" class="w-[60px] border rounded p-1">
                                                <option>AM</option>
                                                <option>PM</option>
                                            </select>
                                            <button
                                                @click="setValue(hour, minute, ampm)"
                                                class="ml-2 px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                                OK
                                            </button>
                                        </div>
                                    </div>

                                    <input type="hidden" x-model="jobModalForm.schedule_from_time12"
                                           name="schedule_from_time12">
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <label class="block text-xs text-gray-500 mb-1 w-10">To</label>
                                <div class="flex gap-2 items-center">
                                    <input type="date" x-model="jobModalForm.schedule_to_date"
                                           class="border rounded px-2 py-1">
                                    <div
                                        x-data="{
                                                show: false,
                                                hour: 9,
                                                minute: '00',
                                                ampm: 'AM',
                                                get value() { return `${this.hour}:${this.minute} ${this.ampm}` },
                                                setValue(h, m, a) {
                                                    this.hour = h;
                                                    this.minute = m;
                                                    this.ampm = a;
                                                    this.show = false;
                                                    // Если нужна двусторонняя связь с jobModalForm:
                                                    $dispatch('time-changed', { field: 'to', value: this.value });
                                                },
                                                setFromExternal(val) {
                                                    if (!val) return;
                                                    let [time, ampm] = val.split(' ');
                                                    let [h, m] = time.split(':');
                                                    this.hour = parseInt(h);
                                                    this.minute = m;
                                                    this.ampm = ampm || 'AM';
                                                }
                                            }"
                                        x-init="setFromExternal(jobModalForm.schedule_to_time12)"
                                        x-effect="setFromExternal(jobModalForm.schedule_to_time12)"
                                        @time-changed.window="updateTime($event.detail)"
                                        class="relative w-36"
                                    >
                                        <button type="button"
                                                @click="show = !show"
                                                class="w-full px-2 py-1 border rounded focus:outline-none flex items-center justify-between"
                                        >
                                            <span x-text="value"></span>
                                            <svg class="w-4 h-4 ml-2 text-gray-400" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <div x-show="show"
                                             @click.away="show = false"
                                             class="absolute flex justify-between z-10 bg-white rounded shadow-md mt-1 p-2 gap-2"
                                             style="min-width: 210px"
                                        >
                                            <!-- Часы -->
                                            <select x-model="hour"
                                                    class="w-[60px] border rounded p-1">
                                                <template x-for="h in 12" :key="h">
                                                    <option :value="h" x-text="h"></option>
                                                </template>
                                            </select>
                                            <!-- Минуты -->
                                            <select x-model="minute" class="w-[60px] border rounded p-1">
                                                <template x-for="m in [0, 30]" :key="m">
                                                    <option :value="m.toString().padStart(2, '0')"
                                                            x-text="m.toString().padStart(2, '0')"></option>
                                                </template>
                                            </select>
                                            <!-- AM/PM -->
                                            <select x-model="ampm" class="w-[60px] border rounded p-1">
                                                <option>AM</option>
                                                <option>PM</option>
                                            </select>
                                            <button
                                                @click="setValue(hour, minute, ampm)"
                                                class="ml-2 px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                                OK
                                            </button>
                                        </div>
                                    </div>

                                    <input type="hidden" x-model="jobModalForm.schedule_to_time12"
                                           name="schedule_to_time12">
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs text-gray-500 mb-1">Employee</label>
                            <div class="relative">
                                <input type="text"
                                       x-model="jobModalForm.employees_query"
                                       @input.debounce.300ms="searchEmployees"
                                       @focus="showEmployeesDropdown = true"
                                       @blur="setTimeout(() => showEmployeesDropdown = false, 200)"
                                       placeholder="Dispatch employee by name or tag"
                                       class="w-full border rounded px-3 py-2 text-sm"
                                >
                                <!-- ВЫПАДАЮЩИЙ СПИСОК -->
                                <div
                                    x-show="showEmployeesDropdown && jobModalForm.employees_results.length > 0"
                                    class="absolute z-30 mt-1 bg-white w-full rounded border shadow"
                                >
                                    <template x-for="employee in jobModalForm.employees_results" :key="employee.id">
                                        <div @click="addEmployee(employee)"
                                             class="px-3 py-2 hover:bg-gray-100 cursor-pointer flex items-center">
                                            <span x-text="employee.name"></span>
                                            <span class="ml-2 text-xs text-gray-400" x-text="employee.tag"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <!-- ВЫБРАННЫЕ СОТРУДНИКИ -->
                            <div class="flex flex-wrap mt-2 gap-2">
                                <template x-for="(employee, idx) in jobModalForm.employees" :key="employee.id">
                                    <div class="flex items-center bg-gray-200 rounded-full px-3 py-1 text-sm">
                                        <span x-text="employee.name"></span>
                                        <button class="ml-2 text-gray-500 hover:text-red-500"
                                                @click="removeEmployee(idx)">
                                            &times;
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="jobModalForm.notify_customer" class="form-checkbox">
                            <label class="text-sm">Notify customer</label>
                        </div>
                    </div>
                </div>

                <!-- Center Column -->
                <div class="w-full lg:w-2/3 space-y-6">
                    <div class="border p-4 rounded"
                        x-data="{
                            money(v){ return Number(v||0).toLocaleString(undefined,{style:'currency',currency:'USD'}) },
                            openList(it){ it.search.open = true },

                            async autocomplete(it){
                                const q = (it.name || '').trim();
                                it.search.q = q;
                                it.search.loading = true;
                                it.search.open = true;
                                it.search.hi = -1;

                                if (q.length < 2) { it.search.results = []; it.search.loading = false; return; }

                                    try {
                                      const res = await $wire.call('searchParts', q);
                                      it.search.results = Array.isArray(res) ? res : [];
                                    } catch(e){
                                      it.search.results = [];
                                      console.error(e);
                                    } finally {
                                      it.search.loading = false;
                                    }
                            },

                            move(it, dir){
                                if (!it.search.open || it.search.results.length === 0) return;
                                const n = it.search.results.length;
                                it.search.hi = (it.search.hi + dir + n) % n;
                            },

                            choose(it){
                                if (it.search.hi >= 0){
                                    this.selectPart(i, it.search.results[it.search.hi]);
                                } else {
                                    // Enter без выбора — остаётся кастомный материал
                                    it.part_id = null;
                                    it.is_custom = true;
                                    it.search.open = false;
                                }
                            },

                            selectPart(it, p){
                                it.name       = p.name;
                                it.part_id    = p.id;
                                it.item_id    = p.id;
                                it.is_custom  = false;
                                it.unit_price = Number(p.price ?? 0.0);
                                it.unit_cost  = Number(p.cost ?? 0.0);
                                // qty — оставляем то, что ввёл пользователь
                                this.recalcItemsTotal();
                                it.search.open = false;
                            },

                            unlinkPart(it){
                                it.part_id   = null;
                                it.item_id   = null;
                                it.is_custom = true;
                                // имя оставляем — пользователь мог его отредактировать
                                // цену оставляем/обнуляем по твоей политике:
                                // it.unit_price = 0;
                                this.recalcItemsTotal();
                            },

                        }"
                    >
                        <label class="block text-sm font-medium mb-2">Job items</label>
                        <!-- Services -->
                        <button
                            @click="addItem('service')"
                            class="w-full mb-2 flex justify-start items-center px-4 py-2 text-blue-600 text-sm hover:bg-blue-50 border border-dashed border-blue-200 rounded"
                            type="button"
                        >
                            + Add service
                        </button>
                        <template x-for="(item, index) in jobModalForm.items.filter(i => i.type === 'service')"
                                  :key="item.id">
                            <div class="mb-4 space-y-2 border-b pb-2">
                                <div class="flex flex-row justify-between w-full">
                                    <div class="flex w-4/5">
                                        <!-- Drag-handle, если нужен -->
                                        <div class="flex items-start text-gray-400 cursor-move">
                                            <svg class="w-6 h-8" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <circle cx="5" cy="7" r="1.5"/>
                                                <circle cx="5" cy="12" r="1.5"/>
                                                <circle cx="5" cy="17" r="1.5"/>
                                                <circle cx="12" cy="7" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="17" r="1.5"/>
                                            </svg>
                                        </div>

                                        <!-- Service fields (имя, qty, price и т.д.) -->
                                        <div class="flex flex-col bg-white rounded w-full ">
                                            <div class="flex gap-2 mb-2 w-full">

                                                <!-- Service Name + Tax -->
                                                <div class="flex-1 flex flex-col w-3/5">
                                                    <label class="sr-only">Service name</label>
                                                    <div class="relative flex items-center">
                                                        <input x-model="item.name" type="text"
                                                               placeholder="Item name"
                                                               class="w-full rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer text-sm pr-16"/>
                                                        <label
                                                            class="flex flex-col absolute right-3 top-1/2 -translate-y-1/2 items-center gap-0 text-[8px] uppercase text-gray-600 select-none cursor-pointer">
                                                            Tax
                                                            <input type="checkbox" x-model="item.tax"
                                                                   class="form-checkbox accent-blue-600 h-3 w-3 cursor-pointer"/>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="flex w-2/5 gap-1">
                                                    <!-- Qty -->
                                                    <div class="relative flex flex-col w-full">
                                                        <input :id="`qty-${index}`" :name="`name-${index}`"
                                                               x-model="item.qty" type="number" step="1" min="0"
                                                               placeholder=" " @input="recalcItemsTotal()"
                                                               class="block px-2 py-2 w-full text-sm bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"/>
                                                        <label :for="`qty-${index}`"
                                                               class="absolute text-xs text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                                                                        peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                                                            Qty
                                                        </label>
                                                    </div>

                                                    <!-- Unit price -->
                                                    <div class="relative flex flex-col w-full">
                                                        <input :id="`uprice-${index}`" :name="`uprice-${index}`"
                                                               x-model="item.unit_price" type="number"
                                                               step="0.01"
                                                               min="0" @input="recalcItemsTotal()"
                                                               class="block px-2 py-2 w-full text-sm bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"/>
                                                        <label :for="`uprice-${index}`"
                                                               class="absolute text-xs text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                                                                        peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                                                            Unit price
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Вторая строка: Описание и Unit Cost -->
                                            <div class="flex gap-2 w-full">
                                                <div class="flex-1 w-3/5">
                                                        <textarea x-model="item.description"
                                                                  placeholder="Description (optional)" rows="1"
                                                                  class="w-full rounded-lg h-[38px] text-sm border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer overflow-y-auto"></textarea>
                                                </div>
                                                <div class="relative flex flex-col w-2/5">
                                                    <input :id="`ucost-${index}`" x-model="item.unit_cost"
                                                           type="number" step="0.1"
                                                           min="0" @input="recalcItemsTotal()"
                                                           class="block px-2 py-2 w-full text-sm bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"/>
                                                    <label :for="`ucost-${index}`"
                                                           class="absolute text-xs text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                                                                peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                                                        Unit cost
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-row items-start w-1/5">
                                        <!-- Итоговая цена -->
                                        <div class="p-2 text-right text-sm min-w-[70px]">
                                            <span x-text="formatMoney(item.total)"></span>
                                        </div>

                                        <!-- Удалить -->
                                        <button @click="removeItem(item.id)"
                                                class="flex p-2 text-gray-400 items-center hover:text-red-500"
                                                tabindex="-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor">
                                                <path d="M6 6l12 12M6 18L18 6"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Materials -->
                        <button
                            @click="addItem('material')"
                            class="w-full mb-2 flex justify-start items-center px-4 py-2 text-blue-600 text-sm hover:bg-blue-50 border border-dashed border-blue-200 rounded"
                            type="button"
                        >
                            + Add material
                        </button>
                        <template x-for="(item, index) in jobModalForm.items.filter(i => i.type === 'material')"
                                  :key="item.id">
                            <div class="mb-4 space-y-2 border-b pb-2">
                                <!-- Material fields (имя, qty, price и т.д.) -->
                                <div class="flex flex-row justify-between w-full">
                                    <div class="flex w-4/5">
                                        <!-- Drag-handle, если нужен -->
                                        <div class="flex items-start text-gray-400 cursor-move">
                                            <svg class="w-6 h-8" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <circle cx="5" cy="7" r="1.5"/>
                                                <circle cx="5" cy="12" r="1.5"/>
                                                <circle cx="5" cy="17" r="1.5"/>
                                                <circle cx="12" cy="7" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="17" r="1.5"/>
                                            </svg>
                                        </div>

                                        <div class="flex flex-col bg-white rounded w-full ">
                                            <div class="flex gap-2 mb-2 w-full">

                                                <!-- Material Name + Tax -->
                                                <div class="flex-1 flex flex-col w-3/5">
                                                    <label class="sr-only">Material name</label>
                                                    <div class="relative flex items-center">
                                                        <input x-model="item.name" type="text"
                                                               placeholder="Material name"
                                                               @input.debounce.300ms="autocomplete(item)"
                                                               @focus="openList(item)"
                                                               @keydown.arrow-down.prevent="move(item,1)"
                                                               @keydown.arrow-up.prevent="move(item,-1)"
                                                               @keydown.enter.prevent="choose(item)"
                                                               class="w-full rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer text-sm pr-16"/>
                                                        <label
                                                            class="flex flex-col absolute right-3 top-1/2 -translate-y-1/2 items-center gap-0 text-[8px] uppercase text-gray-600 select-none cursor-pointer">
                                                            Tax
                                                            <input type="checkbox" x-model="item.tax"
                                                                   class="form-checkbox accent-blue-600 h-3 w-3 cursor-pointer"/>
                                                        </label>
                                                        <!-- Chip "Linked to part" -->
                                                        <div x-show="item.part_id"
                                                             class="mt-1 flex items-center gap-2 text-xs">
                                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                                                <path d="M10 13a5 5 0 0 0 7.07 0l2.12-2.12a5 5 0 1 0-7.07-7.07L10 5" stroke-width="2"/>
                                                                                <path d="M14 11a5 5 0 0 0-7.07 0L4.8 13.12a5 5 0 1 0 7.07 7.07L14 19" stroke-width="2"/>
                                                                            </svg>
                                                                            Linked to part #<span x-text="item.part_id"></span>
                                                                        </span>

                                                            <button type="button"
                                                                    @click="unlinkPart(index)"
                                                                    class="text-rose-600 hover:text-rose-700 underline underline-offset-2">
                                                                Unlink
                                                            </button>
                                                        </div>

                                                        <!-- выпадающий список -->
                                                        <div x-show="item.search.open"
                                                             class="absolute top-full left-0 z-30 w-full bg-white border rounded shadow mt-1 max-h-56 overflow-auto">
                                                            <template x-if="item.search.loading">
                                                                <div class="px-3 py-2 text-sm text-gray-500">Searching…</div>
                                                            </template>

                                                            <template x-for="(p, idx) in item.search.results" :key="p.id">
                                                                <div @click="selectPart(item, p)"
                                                                     :class="['px-3 py-2 cursor-pointer', idx===item.search.hi ? 'bg-blue-50' : 'hover:bg-gray-50']">
                                                                    <div class="flex items-center gap-2">
                                                                        <img :src="p.image || '/images/no-image.png'"
                                                                             alt=""
                                                                             class="w-9 h-9 rounded object-cover border">

                                                                        <div class="min-w-0">
                                                                            <div class="flex items-center gap-2">
                                                                                <span class="font-medium truncate" x-text="p.name"></span>
                                                                                <span class="text-xs text-gray-500" x-text="p.sku ?? ''"></span>
                                                                            </div>
                                                                            <div class="flex items-center gap-2 text-xs">
                                                                                <span class="text-gray-600" x-text="money(p.price)"></span>
                                                                                <!-- бейдж наличия -->
                                                                                <span
                                                                                    :class="p.quantity>0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                                                                                    class="px-1.5 py-0.5 rounded">
                                                                                    <span x-show="p.quantity>0">In stock: <span x-text="p.quantity"></span></span>
                                                                                    <span x-show="p.quantity<=0">Out of stock</span>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            <div x-show="!item.search.loading && item.search.results.length===0"
                                                                 class="px-3 py-2 text-sm text-gray-500">
                                                                No matches. Press Enter to keep custom name.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex w-2/5 gap-1">
                                                    <!-- Qty -->
                                                    <div class="relative flex flex-col w-full">
                                                        <input :id="`qty-${index}`" :name="`name-${index}`"
                                                               x-model="item.qty" type="number" step="1" min="0"
                                                               placeholder=" " @input="recalcItemsTotal()"
                                                               class="block px-2 py-2 w-full text-sm bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"/>
                                                        <label :for="`qty-${index}`"
                                                               class="absolute text-xs text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                                                                        peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                                                            Qty
                                                        </label>
                                                    </div>

                                                    <!-- Unit price -->
                                                    <div class="relative flex flex-col w-full">
                                                        <input :id="`uprice-${index}`" :name="`uprice-${index}`"
                                                               x-model="item.unit_price" type="number"
                                                               step="0.01"
                                                               min="0" @input="recalcItemsTotal()"
                                                               class="block px-2 py-2 w-full text-sm bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"/>
                                                        <label :for="`uprice-${index}`"
                                                               class="absolute text-xs text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                                                                        peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                                                            Unit price
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Вторая строка: Описание и Unit Cost -->
                                            <div class="flex gap-2 w-full">
                                                <div class="flex-1 w-3/5">
                                                        <textarea x-model="item.description"
                                                                  placeholder="Description (optional)" rows="1"
                                                                  class="w-full rounded-lg h-[38px] text-sm border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer overflow-y-auto"></textarea>
                                                </div>
                                                <div class="relative flex flex-col w-2/5">
                                                    <input :id="`ucost-${index}`" x-model="item.unit_cost"
                                                           type="number" step="0.1"
                                                           min="0" @input="recalcItemsTotal()"
                                                           class="block px-2 py-2 w-full text-sm bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"/>
                                                    <label :for="`ucost-${index}`"
                                                           class="absolute text-xs text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                                                                peer-focus:scale-75 peer-focus:-translate-y-4 left-1">
                                                        Unit cost
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-row items-start w-1/5">
                                        <!-- Итоговая цена -->
                                        <div class="p-2 text-right text-sm min-w-[70px]">
                                            <span x-text="formatMoney(item.total)"></span>
                                        </div>

                                        <!-- Удалить -->
                                        <button @click="removeItem(item.id)"
                                                class="flex p-2 text-gray-400 items-center hover:text-red-500"
                                                tabindex="-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor">
                                                <path d="M6 6l12 12M6 18L18 6"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="border p-4 rounded space-y-4">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal</span>
                            <span x-text="subtotal()"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Tax</span>
                            <span x-text="taxTotal()"></span>
                        </div>
                        <div class="flex justify-between font-semibold text-base">
                            <span>Total</span>
                            <span x-text="total()"></span>
                        </div>
                    </div>

                    <div>
                        <button @click="jobModalForm.message = ''" class="text-blue-600 text-sm">+ Message
                        </button>
                        <template x-if="jobModalForm.message !== null">
                            <textarea x-model="jobModalForm.message"
                                      class="w-full border rounded mt-2 px-3 py-2 text-sm"
                                      placeholder="Add a message..."></textarea>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button @click="onSubmit(jobModalForm); jobModalOpen = false"
                        :disabled="!validateJobModalForm(jobModalForm)"
                        class="disabled:opacity-50 bg-blue-600 text-white px-6 py-2 rounded">Save job
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    function validateJobModalForm(form) {
        //console.log(form.schedule_from_time12);
        if (
            !form.customer_id ||
            !Array.isArray(form.employees) ||
            !form.employees.length ||
            !form.schedule_from_date ||
            !form.schedule_from_time12
        ) {
            return false;
        }

        // Проверяем, что у каждого сотрудника есть id
        for (const emp of form.employees) {
            if (!emp.id) {
                return false;
            }
        }

        return true;
    }

    function defaultJobModalForm() {
        return {
            schedule_from: '',
            schedule_to: '',
            schedule_from_date: '',
            schedule_from_time: '',
            schedule_from_time12: '',
            schedule_from_ampp: 'AM',
            schedule_to_date: '',
            schedule_to_time: '',
            schedule_to_time12: '',
            schedule_to_ampp: 'PM',
            customer_id: null,
            employee_id: null,
            customer_query: '',
            employees_query: '',
            results: [],
            employees: [],
            employees_results: [],
            selectedCustomer: null,
            notify_customer: false,
            items: [],
            total: 0.00,
            private_notes: '',
            tags: '',
            attachments: [],
            message: null,
            new_customer: {
                name: '',
                email: '',
                phone: '',
                address: '',
            },
        };
    }

    function deepMerge(target, source) {
        for (const key in source) {
            if (
                source[key] &&
                typeof source[key] === 'object' &&
                !Array.isArray(source[key])
            ) {
                if (!target[key]) target[key] = {};
                deepMerge(target[key], source[key]);
            } else {
                target[key] = source[key];
            }
        }
        return target;
    }

    function deepAssign(target, source) {
        for (const key in source) {
            if (
                source[key] !== null &&
                typeof source[key] === "object" &&
                !Array.isArray(source[key])
            ) {
                if (!target[key]) target[key] = {};
                deepAssign(target[key], source[key]);
            } else {
                target[key] = source[key];
            }
        }
    }

    function scheduler() {
        return {
            init() {
                window.addEventListener('customer-created', event => {
                    const data = event.detail[0];
                    this.jobModalForm.customer_query = data.name + (data.email ? ' (' + data.email + ')' : '');
                    this.jobModalForm.customer_id = data.id;
                    this.jobModalForm.selectedCustomer = data;
                    this.showCustomerModal = false;
                    this.customerError = '';
                });

                window.addEventListener('search-customers-result', e => {
                    this.jobModalForm.results = Array.isArray(e.detail[0]) ? e.detail.flat() : e.detail;
                });

                window.addEventListener('customer-selected', (event) => {
                    this.jobModalForm.selectedCustomer = event.detail;
                    this.jobModalForm.customer_query = event.detail.name + (event.detail.email ? ' (' + event.detail.email + ')' : '');
                    this.jobModalForm.customer_id = event.detail.id;
                    this.showCustomerModal = false;
                });
            },

            _onDragHandler: null,
            _onDropHandler: null,

            employees: @entangle('employees'),
            days: @entangle('days'),
            timeSlots: @entangle('timeSlots'),
            tasks: @entangle('tasks'),
            now: new Date(),
            slotWidth: 30,
            sel: {emp: null, day: null, startIdx: null, endIdx: null},
            menuX: 0,
            menuY: 0,
            jobModalType: null,
            showAddCustomerModal: false,
            showCustomerModal: false,
            showEmployeesDropdown: false,
            jobModalOpen: false,
            confirmDeleteOpen: false,
            taskToDelete: null,
            menuVisible: false,
            customerError: '',
            contextMenu: {
                visible: false,
                x: 0,
                y: 0,
                task: null,
            },
            jobModalForm: {
                schedule_from: '',
                schedule_to: '',
                schedule_from_date: '',
                schedule_from_time: '',
                schedule_from_time12: '',
                schedule_from_ampp: 'AM',
                schedule_to_date: '',
                schedule_to_time: '',
                schedule_to_ampp: 'PM',
                customer_id: null,
                employee_id: null,
                customer_query: '',
                employees_query: '',
                results: [],
                employees: [],
                employees_results: [],
                selectedCustomer: null,
                notify_customer: false,
                items: [],
                total: 0.00,
                private_notes: '',
                tags: '',
                attachments: [],
                message: null,
                new_customer: {
                    name: '',
                    email: '',
                    phone: '',
                    address: '',
                },
            },

            get currentDayIdx() {
                const today = this.now.toISOString().slice(0, 10); // "YYYY-MM-DD"
                return this.days.findIndex(d => d === today);
            },

            get currentSlotIdx() {
                const h = this.now.getHours();
                const m = this.now.getMinutes();
                const hoursFrom6 = Math.max(0, h - 6);
                // если до 6 утра — блокируем все до первого слота
                if (h < 6) return 0;
                // вычисляем номер слота, округляя вверх до следующей получаса
                const slotNumber = hoursFrom6 * 2 + (m < 30 ? 1 : 2);
                // не выходим за границу
                return Math.min(slotNumber, this.timeSlots.length - 1);
            },

            onContextMenu(event, emp, day, idx) {
                // если щёлкнули вне текущей области — обновляем селекцию
                if (
                    this.sel.emp !== emp ||
                    this.sel.day !== day ||
                    idx < this.sel.startIdx ||
                    idx > this.sel.endIdx
                ) {
                    // начинаем и сразу фиксируем новый односоставный слот
                    this.startSelection(emp, day, idx);
                    this.endSelection();
                }
                // теперь показываем ваше меню
                this.showMenu(event);
            },

            showMenu(event) {
                this.menuX = event.pageX;
                this.menuY = event.pageY;
                this.menuVisible = true;
            },

            closeMenu() {
                this.menuVisible = false;
                this.clearSelection();
            },

            clearSelection() {
                this.sel = {emp: null, day: null, startIdx: null, endIdx: null};
            },

            isPast(day, idx) {
                const di = this.days.indexOf(day);
                if (di < this.currentDayIdx) {
                    return true;
                }
                return di === this.currentDayIdx && idx <= this.currentSlotIdx;
            },

            isTaskPast(task) {
                //const dayIdx = this.days.indexOf(task.day);
                const startIdx = this.timeSlots.indexOf(dayjs(task.start, 'HH:mm:ss').format('h:mm A'));
                const endIdx = this.timeSlots.indexOf(dayjs(task.end, 'HH:mm:ss').format('h:mm A'));
                return this.isPast(task.day, startIdx) && this.isPast(task.day, endIdx);
            },

            dayWidth: function () {
                return this.slotWidth * this.timeSlots.length;
            },

            // Формат "Jul 13 Fri"
            formatFullDate(day) {
                const d = new Date(day);
                return d.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    weekday: 'short'
                });
            },

            updateTime({ field, value }) {
                if (field === 'from') {
                    this.jobModalForm.schedule_from_time12 = value;
                } else if (field === 'to') {
                    this.jobModalForm.schedule_to_time12 = value;
                }
            },

            to12Hour(time24) {
                // time24: "19:30:00" или "08:05:00"
                let [h, m, s] = time24.split(':');
                h = parseInt(h, 10);
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                return `${h}:${m} ${ampm}`;
            },

            // Получить задачи сотрудника на конкретный день
            dayTasks(empId, day) {
                return this.tasks.filter(t => t.technician === empId && t.day === day);
            },

            // Разметка при выделении для создания задачи
            startSelection(emp, day, idx) {
                this.sel = {emp, day, startIdx: idx, endIdx: idx};
            },
            dragSelection(emp, day, idx) {
                if (this.sel.emp !== emp || this.sel.day !== day) return;
                this.sel.endIdx = idx;
            },
            endSelection() {
                // здесь НЕ сбрасываем sel!
                // Просто флагим, что выбор завершён,
                // чтобы дальнейший hover без зажатой ЛКМ не лез в dragSelection.
                this.mouseDown = false;
            },
            isSelected(emp, day, idx) {
                if (this.sel.emp !== emp || this.sel.day !== day) return false;
                const [min, max] = [this.sel.startIdx, this.sel.endIdx].sort((a, b) => a - b);
                return idx >= min && idx < max + 1;
            },

            // Перетаскивание задач
            drag: {task: null, offsetX: 0, widthPx: 0, cell: null, previewX: 0, moved: false, startX: null},
            startDrag(task, evt) {
                this.drag.task = task;
                this.drag.offsetX = evt.offsetX;
                this.drag.moved = false;
                this.drag.startX = evt.clientX;
                this.drag.cell = evt.currentTarget.closest('div[data-day]');
                const startIx = this.timeSlots.indexOf(task.start);
                const endIx = this.timeSlots.indexOf(task.end);
                this.drag.previewX = startIx * this.slotWidth;
                this.drag.widthPx = (endIx - startIx) * this.slotWidth;

                this._onDragHandler = this.onDrag.bind(this);
                this._onDropHandler = this.onDrop.bind(this);

                document.addEventListener('mousemove', this._onDragHandler);
                document.addEventListener('mouseup', this._onDropHandler);
            },
            onDrag(evt) {
                if (!this.drag.task) return;
                const cell = this.drag.cell;
                if (!cell) return;
                if (Math.abs(evt.clientX - this.drag.startX) > 3) {
                    this.drag.moved = true;
                    const day = cell.dataset.day;
                    const emp = +cell.closest('[data-emp]').dataset.emp;
                    if (day !== this.drag.task.day || emp !== this.drag.task.technician) return;
                    const rect = cell.getBoundingClientRect();
                    let slotCount = this.timeSlots.length;
                    let slotWidth = this.slotWidth;
                    let x = evt.clientX - rect.left - this.drag.offsetX;
                    let idx = Math.max(0, Math.min(slotCount - 1, Math.floor(x / slotWidth)));
                    this.drag.previewX = idx * slotWidth;
                    let start = dayjs(this.drag.task.start, 'HH:mm:ss');
                    let end = dayjs(this.drag.task.end, 'HH:mm:ss');
                    let durationSlots = Math.max(1, Math.round((end.diff(start, 'minute')) / 30));
                    this.drag.widthPx = durationSlots * slotWidth;
                }
            },
            onDrop() {
                document.removeEventListener('mousemove', this._onDragHandler);
                document.removeEventListener('mouseup', this._onDropHandler);
                const cell = this.drag.cell;
                if (cell && this.drag.cell && this.drag.task && this.drag.moved) {
                    const rect = cell.getBoundingClientRect();
                    const idx = Math.floor(this.drag.previewX / this.slotWidth);
                    const safeIdx = Math.max(0, Math.min(this.timeSlots.length - 1, idx));
                    const newStart = this.timeSlots[safeIdx];
                    this.$wire.moveTask(this.drag.task.id, safeIdx);
                }
                this.drag.task = null;
                this.drag.cell = null;
                this.drag.previewX = null;
            },

            // Стиль задачи: позиционирование и ширина в px
            taskStyle(task) {
                let startStr = dayjs(task.start, 'HH:mm:ss').format('h:mm A');
                let endStr = dayjs(task.end, 'HH:mm:ss').format('h:mm A');
                const startIx = this.timeSlots.indexOf(startStr);
                const endIx = this.timeSlots.indexOf(endStr);
                const leftPx = startIx * this.slotWidth;
                const wPx = (endIx - startIx) * this.slotWidth;
                return `left:${leftPx}px; width:${wPx}px;`;
            },

            subtotal() {
                return '$' + this.jobModalForm.items.reduce((acc, i) => acc + (i.qty * i.unit_price), 0).toFixed(2);
            },

            taxTotal() {
                return '$' + this.jobModalForm.items.reduce((acc, i) => acc + (i.tax ? i.qty * i.unit_price * 0.1 : 0), 0).toFixed(2);
            },

            total() {
                const sub = this.jobModalForm.items.reduce((acc, i) => acc + (i.qty * i.unit_price), 0);
                const tax = this.jobModalForm.items.reduce((acc, i) => acc + (i.tax ? i.qty * i.unit_price * 0.1 : 0), 0);
                return (sub + tax).toFixed(2);
            },

            formatMoney(val) {
                return '$' + Number(val).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            },

            recalcItemsTotal() {
                this.jobModalForm.items.forEach(item => {
                    const qty   = Number(item.qty) || 0;
                    const price = Number(item.unit_price) || 0;
                    const base  = qty * price;
                    const tax   = item.tax ? base * 0.10 : 0;
                    item.total  = +(base + tax).toFixed(2);
                    item.taxTotal = +tax.toFixed(2);
                });
            },

            addItem(type) {
                this.jobModalForm.items.push({
                    id: Date.now() + Math.random(),
                    name: '',
                    qty: 1,
                    unit_price: 0.00,
                    unit_cost: 0.00,
                    tax: false,
                    taxTotal: 0,
                    description: '',
                    type,
                    total: 0.00,
                    part_id: null,
                    is_custom: false,
                    search: { open: false, q: '', results: [], hi: -1, loading: false }
                });
                this.recalcItemsTotal();
            },

            removeItem(id) {
                this.jobModalForm.items = this.jobModalForm.items.filter(item => item.id !== id);
            },

            searchEmployees() {
                const q = this.jobModalForm.employees_query.trim().toLowerCase();
                if (q.length < 2) {
                    this.jobModalForm.employees_results = [];
                    return;
                }
                // employees — массив всех сотрудников из backend, подгружен заранее!
                this.jobModalForm.employees_results = this.employees.filter(e =>
                    e.name.toLowerCase().includes(q) ||
                    (e.tag && e.tag.toLowerCase().includes(q))
                );
            },
            addEmployee(employee) {
                if (!this.jobModalForm.employees.some(e => e.id === employee.id)) {
                    this.jobModalForm.employees.push(employee);
                }
                this.jobModalForm.employees_query = '';
                this.jobModalForm.employees_results = [];
            },
            removeEmployee(idx) {
                this.jobModalForm.employees.splice(idx, 1);
            },

            searchCustomers() {
                if (this.jobModalForm.customer_query.length < 2) {
                    this.jobModalForm.results = [];
                    return;
                }
                @this.call('searchCustomers', this.jobModalForm.customer_query);
            },
            selectCustomer(customer) {
                if (!customer || !customer.id) {
                    this.selectedCustomer = null;
                    this.jobModalForm.customer_id = null;
                    this.jobModalForm.customer_query = '';
                    this.showCustomerModal = false;
                    return;
                }
                this.jobModalForm.customer_query = customer.name + (customer.email ? ' (' + customer.email + ')' : '');
                this.jobModalForm.customer_id = customer.id;
                this.selectedCustomer = customer;
                this.showCustomerModal = false;
                //Livewire.dispatch('customer-selected', customer);
            },

            onSubmit(jobModalForm) {
                if (validateJobModalForm(jobModalForm)) {
                    this.$wire.saveJob(jobModalForm);
                }
            },

            deleteTask(task) {
                // Можно через Livewire.emit, Livewire.call, fetch/AJAX — как в остальном проекте
                this.$wire.call('deleteTask', task.id)
                    .then(() => {
                        this.confirmDeleteOpen = false;
                        // обновить UI, убрать задачу из локального списка, показать уведомление и т.д.
                    });
            },

            saveNewCustomer() {
                this.customerError = '';
                const customer = this.jobModalForm.new_customer;
                if (!customer.name || (!customer.email && !customer.phone)) {
                    this.customerError = 'Name and either email or phone are required.';
                    return;
                }

                this.$wire.createCustomer(customer);
                this.showAddCustomerModal = false;
                this.jobModalForm.new_customer = {name: '', email: '', phone: '', address: ''};
            },

            openJobModal(type = null, sel, contextMenu = null) {
                if (type) this.jobModalType = type;
                this.jobModalOpen = true;
                if (type === 'edit' && contextMenu.task) {
                    deepAssign(this.jobModalForm, defaultJobModalForm());
                    deepAssign(this.jobModalForm, contextMenu.task);
                    for(customer in this.jobModalForm.results){
                        this.jobModalForm.customer_id = customer;
                    }
                    this.selectCustomer(contextMenu.task.client);
                    this.jobModalForm.schedule_from_date = contextMenu.task.day || '';
                    this.jobModalForm.schedule_to_date   = contextMenu.task.day || '';
                    this.jobModalForm.schedule_from_time12 = toTime12String(contextMenu.task.start);
                    this.jobModalForm.schedule_to_time12   = toTime12String(contextMenu.task.end);
                    this.jobModalForm.items = (contextMenu.task.items || []).map(item => ({
                        id: item.id,
                        type: item.item_type,
                        name: item.item_title ?? '',
                        qty: Number(item.quantity ?? 1),
                        unit_price: Number(item.price ?? 0),
                        unit_cost: Number(item.unit_cost ?? 0),
                        description: item.item_description ?? '',
                        tax: Boolean(item.tax ?? false),
                        taxTotal: Number(item.taxTotal ?? 0),
                        total: Number(item.total ?? 0),
                        item_id: item.item_id ?? null,
                        part_id: item.part_id ?? null,
                        is_custom: item.is_custom ?? (!item.part_id),
                        search: { open: false, q: '', results: [], hi: -1, loading: false }
                    }));
                    this.jobModalForm.message = contextMenu.task.message || '';
                    this.recalcItemsTotal();

                    console.log(this.jobModalForm.message);
                    return;
                }else{
                    deepAssign(this.jobModalForm, defaultJobModalForm());
                }
                const slotDuration = 30;
                const slotsPerDay = 32;
                const minHour = 6;
                const baseDate = dayjs(sel.day, 'YYYY.MM.DD').startOf('day').toDate();
                const startSlot = Math.min(sel.startIdx, slotsPerDay - 1);
                const endSlot = Math.min(sel.endIdx, slotsPerDay - 1);
                const startMinutes = minHour * 60 + startSlot * slotDuration;
                const endMinutes = minHour * 60 + (endSlot + 1) * slotDuration;
                const from = new Date(baseDate);
                from.setMinutes(startMinutes);
                const to = new Date(baseDate);
                to.setMinutes(endMinutes);
                if (to.getDate() !== from.getDate()) {
                    to.setDate(from.getDate());
                    to.setHours(23, 59, 0, 0);
                }

                function toInputDate(d) {
                    return `${d.getFullYear()}-${(d.getMonth() + 1).toString().padStart(2, '0')}-${d.getDate().toString().padStart(2, '0')}`;
                }

                function toTime12String(timeStr) {
                    let [h, m] = timeStr.split(':');
                    h = parseInt(h, 10);
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    const hour12 = h % 12 || 12;
                    return `${hour12}:${m} ${ampm}`;
                }

                function toInputTime12(d) {
                    let h = d.getHours();
                    const m = d.getMinutes().toString().padStart(2, '0');
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    return `${h}:${m} ${ampm}`;
                }

                function toInputTime24(d) {
                    let h = d.getHours().toString().padStart(2, '0');
                    let m = d.getMinutes().toString().padStart(2, '0');
                    return `${h}:${m}`;
                }

                this.jobModalForm.schedule_from_date = toInputDate(from);
                this.jobModalForm.schedule_from_time12 = toInputTime12(from);
                this.jobModalForm.schedule_from_time = toInputTime24(from);

                this.jobModalForm.schedule_to_date = toInputDate(to);
                this.jobModalForm.schedule_to_time12 = toInputTime12(to);
                this.jobModalForm.schedule_to_time = toInputTime24(to);

                const tech = this.employees.find(e => e.id === this.sel.emp);
                if (tech && !this.jobModalForm.employees.some(e => e.id === tech.id)) {
                    this.jobModalForm.employees.push(tech);
                }
                this.jobModalForm.employees_query = '';
                this.menuVisible = false;
                this.clearSelection();
            },
        }
    }
</script>
