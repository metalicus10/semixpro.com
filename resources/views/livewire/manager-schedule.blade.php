{{-- resources/views/livewire/job-scheduler.blade.php --}}
<div
    x-data="scheduler()"
    @mouseup.window="$event.button === 0 && endSelection()"
    @unique-slot-error.window="alert($event.detail[0].message)"
    @interval-overlap-error.window="alert($event.detail[0].message)"
    class="overflow-x-auto bg-white text-gray-800 border"

>
    <div class="sticky top-0 z-5 flex items-center justify-between px-3 py-2 border-b">
        <div class="flex items-center gap-2">
            <button type="button" class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200"
                    @click="moveWeek(-1)">←
            </button>
            <button type="button" class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200"
                    @click="goToday()">Today
            </button>
            <button type="button" class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200"
                    @click="moveWeek(1)">→
            </button>
            <span class="ml-3 text-sm text-gray-500" x-text="isCurrentWeek() ? 'This week' : ''"></span>
        </div>

        <div class="flex items-center gap-2 mb-2">
            <button @click="showCalendar"
                    x-bind:class="mode === 'schedule' ? 'bg-blue-600 text-white' : 'bg-gray-100'"
                    class="px-3 py-1 rounded"
            >
                <span class="ms-2">Calendar</span>
            </button>
            <button @click="showMap"
                    x-bind:class="mode === 'map' ? 'bg-blue-600 text-white' : 'bg-gray-100'"
                    class="px-3 py-1 rounded"
            >
                <span class="ms-2">Map</span>
            </button>
        </div>

        <!-- Диапазон дат недели -->
        <div class="text-sm font-medium text-gray-700"
             x-text="days.length ? (days[0].label + ' — ' + days[6].label) : ''">
        </div>
    </div>

    <div x-show="mode==='schedule'">
        <div class="overflow-x-auto pb-[10px]">
            <!-- Глобальный оверлей спиннера -->
            <div x-show="isLoading"
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="w-12 h-12 border-4 border-blue-500 border-dashed rounded-full animate-spin"
                     aria-label="Saving..."></div>
            </div>
            {{-- Заголовок --}}
            <div class="inline-flex items-center border-b border-b-gray-400">
                {{-- Первая узкая ячейка для таймзоны или иконки --}}
                <div class="w-32 flex-shrink-0 p-2 text-sm font-medium text-center">
                    GMT -04
                </div>
                {{-- Дни недели с часами --}}
                <div class="flex-1 inline-flex border-l-2 border-l-gray-400">
                    <template x-for="day in days" :key="day.date">
                        <div class="flex flex-col">
                            {{-- Дата --}}
                            <div
                                class="h-5 flex items-center justify-center font-semibold text-sm border-b border-b-gray-300">
                                <span x-text="day.label"></span>
                            </div>
                            {{-- Часы --}}
                            <div class="flex">
                                <template x-for="(slotLabel, idx) in defaultTimeSlots" :key="idx">
                                    <div
                                        class="w-[30px] h-8 flex-shrink-0 text-center text-[10px] border-r border-r-gray-300 last:border-r-0">
                                        <span x-text="slotLabel"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Строки сотрудников --}}
            <template x-for="employee in employees" :key="employee.id">
                <div class="inline-flex items-start border-b border-b-gray-300 group" :data-emp="employee.id">
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
                    <div class="flex-1 inline-flex relative" wire:ignore>
                        <template x-for="day in days" :key="`${day.date}-${lanesVersion}`">
                            <div class="relative flex-shrink-0"
                                 :style="`width:${wrapCols * slotWidthPx}px; height:${containerHeight(employee.id, day)}px`"
                                 :class="{ 'day-left-border': day !== 0 }" :data-day="day.date" :data-emp="employee.id">
                                {{-- Фоновые ячейки часов --}}
                                <div class="grid"
                                     :style="`grid-template-columns: repeat(${wrapCols}, ${slotWidthPx}px);`">
                                    <template x-for="(slotIdx, i) in flatSlots(employee.id, day)"
                                              :key="`${employee.id}-${day.date}-${i}-${slotIdx}-${lanesVersion}`">
                                        <div x-data="{ slotIdx: slotIdx }"
                                             :key="`${employee.id}-${day.date}-${i}-${slotIdx}-${lanesVersion}`"
                                             wire:key="cell-${employee.id}-${day.date}-${i}-${lanesVersion}"
                                             class="h-15 border-r border-r-gray-300"
                                             :class="{
                                                  'bg-blue-100': isSelected(employee.id, day, slotIdx),
                                                  'bg-gray-100 pointer-events-none': isPast(day, slotIdx),
                                                }"
                                             @mousedown.prevent="$event.button === 0
                                                  && !isPast(day, slotIdx)
                                                  && startSelection(employee.id, day, slotIdx)"
                                             @mouseenter.prevent="$event.buttons === 1
                                                  && !isPast(day, slotIdx)
                                                  && !dragSelection(employee.id, day, slotIdx)"
                                             @contextmenu.prevent="onContextMenu($event, employee, day, slotIdx)">
                                        </div>
                                    </template>
                                </div>

                                {{-- Задачи --}}
                                <template x-for="task in dayTasks(employee.id, day)" :key="task.id">
                                    <div
                                        class="absolute top-1 h-14 bg-green-500 text-white text-[11px] rounded shadow cursor-move px-1 flex items-center space-x-1"
                                        :class="{
                                                'pointer-events-none opacity-60 bg-[repeating-linear-gradient(45deg,#aeaeae00_0,#10182885_5px,#0000_5px,#0000_18px)]': isTaskPast(task),
                                                'cursor-pointer': !isTaskPast(task)
                                            }"
                                        @click.stop="onTaskClick(task, $event)"
                                        @mousedown.prevent="!isTaskPast(task) && startDrag(task, $event)"
                                        @contextmenu.prevent.stop="
                                            suppressTaskClick = true;
                                                contextMenu.x = $event.clientX;
                                                contextMenu.y = $event.clientY;
                                                contextMenu.task = task;

                                                const col = $event.currentTarget.closest('[data-day]');
                                                sel.day = col ? col.dataset.day : null;
                                                const row = $event.currentTarget.closest('[data-emp]');
                                                sel.emp = row ? +row.dataset.emp : null;

                                                contextMenu.visible = true;
                                        "
                                        :style="drag.task && drag.task.id === task.id
                                                ? `left:${drag.previewX}px; width:${drag.widthPx}px; opacity:.65; top:${drag.previewY}px; height:${rowHeightPx}px;`
                                                : taskStyle(task)"
                                    >
                                        <div class="flex flex-col">
                                            <span class="truncate" x-text="task.client.name"></span>
                                            <span class="whitespace-wrap"
                                                  x-text="`${to12Hour(task.start, task.day)} – ${to12Hour(task.end, task.day)}`"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="mode === 'map'" class="h-full rounded border overflow-hidden"
         x-init="$watch('mode', v => { if (v === 'map') $nextTick(() => window.dispatchEvent(new Event('map:shown'))) })" id="jobsMap">
        <div class="relative h-screen">
            {{-- Спиннер --}}
            <div x-show="isLoading"
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="w-12 h-12 border-4 border-blue-500 border-dashed rounded-full animate-spin"></div>
            </div>

            {{-- Карта --}}
            <div wire:ignore class="h-screen rounded border" id="jobsMap" x-transition></div>
        </div>
    </div>

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
    <div x-data="addCustomer()" x-on:customer-validation-error.window="onErrors($event.detail.errors)"
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
            <template x-if="customerError">
                <div class="mb-2 text-red-600 text-xs" x-text="customerError"></div>
            </template>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Name*</label>
                <input type="text" x-model="name" required
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" x-model="email" required
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Phone</label>
                <input type="text" x-model="phone" required
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Address</label>
                <div class="relative">
                    <input type="text"
                           class="w-full border rounded px-3 py-2 text-sm"
                           placeholder="Start typing address…"
                           x-model="query"
                           @input.debounce.400ms="findSuggestions"
                           @focus="open = true"
                           @keydown.escape="open = false; resetSelection()"
                           @blur="setTimeout(()=>open=false,150)">

                    <!-- dropdown -->
                    <template x-if="open && suggestions.length">
                        <ul class="absolute z-20 left-0 right-0 bg-white border mt-1 rounded shadow max-h-60 overflow-auto">
                            <template x-for="(s, i) in suggestions" :key="s.id">
                                <li @mousedown.prevent="selectSuggestion(s)"
                                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                                    x-text="s.label"></li>
                            </template>
                        </ul>
                    </template>
                </div>

                <!-- скрытые поля, которые пойдут в createCustomer -->
                <input type="hidden" x-model="selected.id">
                <input type="hidden" x-model="selected.name">
                <input type="hidden" x-model="selected.email">
                <input type="hidden" x-model="selected.phone">
                <input type="hidden" x-model="selected.lat">
                <input type="hidden" x-model="selected.lng">
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="showAddCustomerModal = false"
                        class="px-4 py-2 text-sm bg-gray-100 rounded hover:bg-gray-200">Cancel
                </button>
                <button type="button" @click="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                    Add
                </button>
            </div>
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
                                    openList(it){
                                        if (!it.search) it.search = { open: false, q: '', results: [], hi: -1, loading: false };
                                        this.sel.id = it.id;
                                        this.jobModalForm.items.forEach(x => { if (x.search) x.search.open = (x === it); });
                                        it.search.open = true;
                                    },

                                    hideList(it) {
                                        it.search.open = false;
                                        it.search.hi = -1;
                                    },

                                    deferClose(it) {
                                        clearTimeout(it.search._t);
                                        it.search._t = setTimeout(() => { it.search.open = false; }, 120);
                                    },

                                    async autocomplete(it){
                                        if (!it) it = this.getSelItem();
                                        if (!it) return;
                                        if (!it.search) it.search = { open: false, q: '', results: [], hi: -1, loading: false };
                                        const q = (it.name || '').trim();
                                        it.search.q = q;
                                        it.search.loading = true;
                                        it.search.open = true;
                                        it.search.hi = -1;

                                        if (q.length < 2) { it.search.results = []; it.search.loading = false; it.search.open = false; return; }
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

                                    choose(index){
                                        const p = this.jobModalForm.items[index].search.results[index];
                                        const it = this.jobModalForm.items[index];

                                        it.name       = p.name;
                                        it.part_id    = p.id;
                                        it.unit_price = Number(p.price) || 0;
                                        it.is_custom  = false;
                                        it.priceLocked = true;

                                        it.stock = {
                                            available: Number(p.available ?? 0),
                                            reserved:  Number(p.reserved ?? 0),
                                            quantity:  Number(p.quantity ?? 0),
                                        };

                                        if (it.qty > it.stock.available) {
                                            it.qty = Math.max(0, it.stock.available);
                                            it.warn.qty = `Only ${it.stock.available} available (reserved: ${it.stock.reserved}).`;
                                        } else {
                                            it.warn.qty = '';
                                        }

                                        if (typeof it.stock.available === 'number') {
                                            if (Number(it.qty) > it.stock.available) it.qty = it.stock.available;
                                        }

                                        this.onQtyInput(it);
                                        this.hideList(it);
                                        this.recalcItemsTotal();
                                    },

                                    onQtyInput(it) {
                                        const q = Math.max(0, Number.parseInt(it.qty ?? 0, 10) || 0);
                                        const max = Number.isFinite(it?.stock?.available) ? Number(it.stock.available) : Infinity;

                                        if (q > max) {
                                            it.qty = max;
                                            it.warn = `Only ${max} available.`;
                                        } else {
                                            it.qty = q;
                                            it.warn = '';
                                        }
                                        this.recalcItemsTotal();
                                    },

                                    selectPart(it, p){
                                        it.name       = p.name;
                                        it.part_id    = p.id;
                                        it.item_id    = p.id;
                                        it.is_custom  = false;
                                        it.unit_price = Number(p.price ?? 0.0);
                                        it.unit_cost  = Number(p.cost ?? 0.0);
                                        it.stock      = Number(p.quantity ?? 0.0);
                                        it.priceLocked = true;
                                        this.enforceQty(it, showMsg=true);
                                        this.recalcItemsTotal();
                                        it.search.open = false;
                                    },

                                    unlinkPart(it){
                                        if (!it.warn) it.warn = { qty: '' };
                                        it.part_id   = null;
                                        it.item_id   = null;
                                        it.is_custom = true;
                                        it.priceLocked = false;
                                        it.stock       = null;
                                        it.warn.qty    = '';
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
                                  :key="item.key">
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
                                                    <div class="relative flex flex-col" @click.outside="hideList(item)">
                                                        <div class="relative flex flex-row">
                                                            <input x-model="item.name" type="text"
                                                                   placeholder="Material name"
                                                                   @input.debounce.300ms="autocomplete(item)"
                                                                   @focus="openList(item)"
                                                                   @blur="deferClose(item)"
                                                                   @keydown.escape.prevent="hideList(item)"
                                                                   @keydown.arrow-down.prevent="move(item,1)"
                                                                   @keydown.arrow-up.prevent="move(item,-1)"
                                                                   @keydown.enter.prevent="choose(index)"
                                                                   class="w-full rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer text-sm pr-16"/>
                                                            <label
                                                                class="flex flex-col absolute right-3 top-1/2 -translate-y-1/2 items-center gap-0 text-[8px] uppercase text-gray-600 select-none cursor-pointer">
                                                                Tax
                                                                <input type="checkbox" x-model="item.tax"
                                                                       class="form-checkbox accent-blue-600 h-3 w-3 cursor-pointer"/>
                                                            </label>
                                                        </div>
                                                        <!-- Chip "Linked to part" -->
                                                        <div x-show="item.part_id"
                                                             class="mt-1 flex items-center gap-2 text-xs">
                                                                                    <span
                                                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                                                        <svg class="w-3.5 h-3.5"
                                                                                             viewBox="0 0 24 24"
                                                                                             fill="none"
                                                                                             stroke="currentColor">
                                                                                            <path
                                                                                                d="M10 13a5 5 0 0 0 7.07 0l2.12-2.12a5 5 0 1 0-7.07-7.07L10 5"
                                                                                                stroke-width="2"/>
                                                                                            <path
                                                                                                d="M14 11a5 5 0 0 0-7.07 0L4.8 13.12a5 5 0 1 0 7.07 7.07L14 19"
                                                                                                stroke-width="2"/>
                                                                                        </svg>
                                                                                        Linked to part #<span
                                                                                            x-text="item.part_id"></span>
                                                                                    </span>

                                                            <button type="button"
                                                                    @click="unlinkPart(item)"
                                                                    class="text-rose-600 hover:text-rose-700 underline underline-offset-2">
                                                                Unlink
                                                            </button>
                                                        </div>

                                                        <!-- выпадающий список -->
                                                        <div x-show="item.search.open" x-transition.opacity
                                                             @mousedown.prevent
                                                             class="absolute top-full left-0 z-30 w-full bg-white border rounded shadow mt-1 max-h-56 overflow-auto">
                                                            <template x-if="item.search.loading">
                                                                <div class="px-3 py-2 text-sm text-gray-500">
                                                                    Searching…
                                                                </div>
                                                            </template>

                                                            <template x-for="(p, idx) in item.search.results"
                                                                      :key="p.id">
                                                                <div @click="choose(idx)"
                                                                     :class="['px-3 py-2 cursor-pointer', idx===item.search.hi ? 'bg-blue-50' : 'hover:bg-gray-50']">
                                                                    <div class="flex items-center gap-2">
                                                                        <template x-if="p.image">
                                                                            <img
                                                                                :src="'{{ asset('storage') }}' + p.image"
                                                                                alt="p.name"
                                                                                class="w-9 h-9 rounded object-cover border">
                                                                        </template>
                                                                        <template
                                                                            x-if="!p.image && p.nomenclature.image">
                                                                            <img
                                                                                :src="'{{ asset('storage') }}' + p.nomenclature.image"
                                                                                :alt="p.name"
                                                                                class="w-9 h-9 rounded object-cover border">
                                                                        </template>
                                                                        <template
                                                                            x-if="!p.image && !p.nomenclature.image">
                                                                                    <span class="w-[50px] h-[50px]">
                                                                                        <div>
                                                                                            <svg
                                                                                                xmlns="http://www.w3.org/2000/svg"
                                                                                                xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                                                version="1.1" width="56"
                                                                                                height="56"
                                                                                                viewBox="0 0 256 256"
                                                                                                xml:space="preserve">
                                                                                                             <defs></defs>
                                                                                                <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;"
                                                                                                   transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                                                                                                    <path
                                                                                                        d="M 89 20.938 c -0.553 0 -1 0.448 -1 1 v 46.125 c 0 2.422 -1.135 4.581 -2.898 5.983 L 62.328 50.71 c -0.37 -0.379 -0.973 -0.404 -1.372 -0.057 L 45.058 64.479 l -2.862 -2.942 c -0.385 -0.396 -1.019 -0.405 -1.414 -0.02 c -0.396 0.385 -0.405 1.019 -0.02 1.414 l 3.521 3.62 c 0.37 0.38 0.972 0.405 1.373 0.058 l 15.899 -13.826 l 21.783 22.32 c -0.918 0.391 -1.928 0.608 -2.987 0.608 H 24.7 c -0.552 0 -1 0.447 -1 1 s 0.448 1 1 1 h 55.651 c 5.32 0 9.648 -4.328 9.648 -9.647 V 21.938 C 90 21.386 89.553 20.938 89 20.938 z"
                                                                                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                                                                                        transform=" matrix(1 0 0 1 0 0) "
                                                                                                        stroke-linecap="round"/>
                                                                                                    <path
                                                                                                        d="M 89.744 4.864 c -0.369 -0.411 -1.002 -0.444 -1.412 -0.077 l -8.363 7.502 H 9.648 C 4.328 12.29 0 16.618 0 21.938 v 46.125 c 0 4.528 3.141 8.328 7.356 9.361 l -7.024 6.3 c -0.411 0.368 -0.445 1.001 -0.077 1.412 c 0.198 0.22 0.471 0.332 0.745 0.332 c 0.238 0 0.476 -0.084 0.667 -0.256 l 88 -78.935 C 90.079 5.908 90.113 5.275 89.744 4.864 z M 9.648 14.29 h 68.091 L 34.215 53.33 L 23.428 42.239 c -0.374 -0.385 -0.985 -0.404 -1.385 -0.046 L 2 60.201 V 21.938 C 2 17.721 5.431 14.29 9.648 14.29 z M 2 68.063 v -5.172 l 20.665 -18.568 l 10.061 10.345 L 9.286 75.692 C 5.238 75.501 2 72.157 2 68.063 z"
                                                                                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                                                                                        transform=" matrix(1 0 0 1 0 0) "
                                                                                                        stroke-linecap="round"/>
                                                                                                    <path
                                                                                                        d="M 32.607 35.608 c -4.044 0 -7.335 -3.291 -7.335 -7.335 s 3.291 -7.335 7.335 -7.335 s 7.335 3.291 7.335 7.335 S 36.652 35.608 32.607 35.608 z M 32.607 22.938 c -2.942 0 -5.335 2.393 -5.335 5.335 s 2.393 5.335 5.335 5.335 s 5.335 -2.393 5.335 -5.335 S 35.549 22.938 32.607 22.938 z"
                                                                                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                                                                                        transform=" matrix(1 0 0 1 0 0) "
                                                                                                        stroke-linecap="round"/>
                                                                                                </g>
                                                                                            </svg>
                                                                                        </div>
                                                                                    </span>
                                                                        </template>
                                                                        <div class="min-w-0">
                                                                            <div class="flex items-center gap-2">
                                                                                        <span
                                                                                            class="font-medium truncate"
                                                                                            x-text="p.name"></span>
                                                                                <span class="text-xs text-gray-500"
                                                                                      x-text="p.sku ?? ''"></span>
                                                                            </div>
                                                                            <div
                                                                                class="flex items-center gap-2 text-xs">
                                                                                        <span class="text-gray-600"
                                                                                              x-text="money(p.price)"></span>
                                                                                <!-- бейдж наличия -->
                                                                                <span
                                                                                    :class="p.quantity>0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                                                                                    class="px-1.5 py-0.5 rounded">
                                                                                            <span
                                                                                                x-show="p.available>0">In stock: <span
                                                                                                    x-text="p.available"></span></span>
                                                                                            <span
                                                                                                x-show="p.available<=0">Out of stock</span>
                                                                                        </span>
                                                                                <template x-if="p.reserved > 0">
                                                                                            <span
                                                                                                class="inline-flex items-center rounded px-2 py-0.5 text-xs bg-orange-100 text-orange-700">
                                                                                                Reserved <span
                                                                                                    class="ml-1"
                                                                                                    x-text="p.reserved"></span>
                                                                                            </span>
                                                                                </template>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            <div
                                                                x-show="!item.search.loading && item.search.results.length===0"
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
                                                               placeholder=""
                                                               @input="onQtyInput(item); recalcItemsTotal()"
                                                               :min="(item.part_id && (item.stock ?? 0) <= 0) ? 0 : 1"
                                                               :max="item.part_id ? (item.stock ?? 0) : null"
                                                               @blur="onQtyInput(item); recalcItemsTotal()"
                                                               class="block px-2 py-2 w-full text-sm bg-white rounded-lg border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"/>
                                                        <p x-show="item.warn.qty" class="mt-1 text-xs text-rose-600"
                                                           x-text="item.warn.qty"></p>
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
                                                               step="0.01" min="0"
                                                               @input="recalcItemsTotal()"
                                                               :readonly="item.priceLocked"
                                                               :class="['w-full rounded-lg border', item.priceLocked ? 'bg-gray-100 cursor-not-allowed' : '']"
                                                               title="Цена берётся из выбранной запчасти"
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
                        :disabled="saving || !validateJobModalForm(jobModalForm) || hasQtyErrors"
                        :class="{'opacity-50 pointer-events-none': saving || !validateJobModalForm(jobModalForm) || hasQtyErrors}">
                    <span x-show="!saving">Save job</span>
                    <span x-show="saving">Saving…</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Overlay для клика вне -->
    <div
        x-show="popover.open"
        x-transition.opacity
        @click="closePopover()"
        class="fixed inset-0 z-40"
    ></div>

    <!-- Сам попап -->
    <div
        x-show="popover.open"
        x-transition
        @keydown.escape.window="closePopover()"
        @click.outside="closePopover()"
        class="fixed z-50 w-[360px] max-w-[90vw] bg-white rounded-xl shadow-2xl border border-gray-200"
        :style="`left:${popover.x}px; top:${popover.y}px`"
    >
        <div class="p-4 space-y-3">
            <!-- Заголовок -->
            <div class="flex items-start gap-2">
                <div class="shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                        <path d="M5 7h14M5 12h14M5 17h8" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="font-semibold text-gray-900 truncate" x-text="popover.job?.title"></div>
                    <div class="text-[10px] text-gray-500" x-text="dayjs(popover.job?.day).format('MMM D YYYY')+' '+formatTime(popover.job?.start, popover.job?.end)"></div>
                </div>
            </div>

            <!-- Описание -->
            <div class="flex items-start gap-2" x-show="popover.job?.description">
                <div class="shrink-0 mt-0.5" title="Job description">
                    <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                        <path d="M4 6h16M4 12h10M4 18h7" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="text-sm text-gray-700 whitespace-pre-wrap" x-text="popover.job?.description"></div>
            </div>

            <!-- Цена -->
            <div class="flex items-center gap-2">
                <div class="shrink-0" title="Price">
                    <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M12 3v18M8 7c0-1.657 1.79-3 4-3 1.657 0 3 .895 3 2s-1 2-3 2-4 1-4 3 1.79 3 4 3 3-.895 3-2"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="text-sm font-medium text-gray-900" x-text="formatMoney(popover.job?.price)"></div>
            </div>

            <!-- Клиент -->
            <div class="flex items-start gap-2">
                <div class="shrink-0 mt-0.5" title="Customer">
                    <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                        <path d="M15 7a3 3 0 11-6 0 3 3 0 016 0zM4 20a8 8 0 1116 0" stroke="currentColor"
                              stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="text-sm text-gray-700 leading-5">
                    <div class="font-medium text-gray-900" x-text="popover.job?.client?.name"></div>
                    <div class="text-gray-600" x-text="popover.job?.client?.address"></div>
                    <div class="text-gray-600" x-text="popover.job?.client?.phone"></div>
                </div>
            </div>

            <!-- Техник -->
            <div class="flex items-center gap-2">
                <div class="shrink-0" title="Technician">
                    <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                        <path d="M12 6a3 3 0 110 6 3 3 0 010-6zm0 7c-3.866 0-7 2.239-7 5v1h14v-1c0-2.761-3.134-5-7-5z"
                              stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </div>
                <div class="text-sm text-gray-700">
                    <span class="font-medium text-gray-900" x-text="popover.job?.technician?.name"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.GEOAPIFY_KEY = "{{ config('services.geoapify.key') }}";
    function validateJobModalForm(form) {
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
            jobModalType: '',
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
            message: '',
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
                this.invalidateLanes = () => {
                    this._lanesCache = {};
                };
                this.stopClock();
                this.startClock();
                this.setWeek(this.currentNow());
                this.$watch('tasks', () => this.invalidateLanes());
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

                window.addEventListener('tasks-refetch', () => {
                    const from = this.days[0].date;
                    const to = this.days[6].date;
                    this.fetchWeek(from, to);
                });
            },

            _onDragHandler: null,
            _onDropHandler: null,

            employees: @entangle('employees'),
            timeSlots: @entangle('timeSlots'),
            defaultTimeSlots: @entangle('defaultTimeSlots'),
            baseCount: @entangle('timeSlotsBaseCount'),
            tasks: @entangle('tasks'),
            now: new Date(),
            slotWidthPx: 30,
            rowHeightPx: 60,
            wrapCols: 32,
            sel: {idx: null, emp: null, day: null, startIdx: null, endIdx: null},
            menuX: 0,
            menuY: 0,
            suppressTaskClick: false,
            jobModalType: '',
            showAddCustomerModal: false,
            showCustomerModal: false,
            showEmployeesDropdown: false,
            jobModalOpen: false,
            confirmDeleteOpen: false,
            taskToDelete: null,
            menuVisible: false,
            saving: false,
            isLoading: false,
            _lanesCache: {},
            _empMaxLanesCache: {},
            lanesVersion: 0,
            customerError: '',
            contextMenu: {
                visible: false,
                x: 0,
                y: 0,
                task: null,
            },
            jobModalForm: {
                jobModalType: '',
                task_id: null,
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
                message: '',
                new_customer: {
                    name: '',
                    email: '',
                    phone: '',
                    address: '',
                },
            },

            weekStart: null,
            days: [],
            firstDay: 1,

            APP_TZ: dayjs.tz.guess(),
            nowTs: null,
            clockId: null,

            resetLaneCaches() {
                this._lanesCache = {};
                this._empMaxLanesCache = {};
                this.lanesVersion++;
                this.isLoading = false;
            },

            currentNow() {
                return dayjs.tz ? dayjs.tz(dayjs(), this.APP_TZ) : dayjs()
            },

            startClock() {
                this.bumpNow();
                this.clockId = setInterval(() => this.bumpNow(), 30_000);
            },

            stopClock() {
                if (this.clockId) clearInterval(this.clockId);
            },

            bumpNow() {
                this.nowTs = Date.now();
                // this.$dispatch('time-tick', { now: this.nowTs });
            },

            slotDateTime(dayObj, slotLabel) {
                const dateStr = typeof dayObj === 'string' ? dayObj : dayObj.date;
                const fmt = 'YYYY-MM-DD h:mm A';
                return dayjs.tz
                    ? dayjs.tz(`${dateStr} ${slotLabel}`, fmt, this.APP_TZ)
                    : dayjs(`${dateStr} ${slotLabel}`, fmt);
            },

            get currentDayIdx() {
                const today = this.currentNow().format('YYYY-MM-DD')
                return this.days.findIndex(d => (typeof d === 'string' ? d : d.date) === today)
            },

            currentSlotIdxFor(dayObj) {
                const gridStartLabel = this.timeSlots[0]
                const stepMinutes = 30
                const now = this.currentNow()
                const gridStart = this.slotDateTime(dayObj, gridStartLabel)
                let diff = now.diff(gridStart, 'minute')
                if (diff < 0) return -1
                const idx = Math.floor(diff / stepMinutes)
                return Math.min(idx, this.timeSlots.length - 1)
            },

            dayKey(d) {
                return typeof d === 'string' ? d : d?.date;
            },

            setWeek(d) {
                const base = dayjs.isDayjs(d)
                    ? (d.tz ? d.tz(this.APP_TZ) : d)
                    : (dayjs.tz ? dayjs.tz(d, 'YYYY-MM-DD', this.APP_TZ) : dayjs(d));

                let start = base.startOf('week');

                if (this.firstDay === 1) {
                    start = start.add(1, 'day');
                }

                this.weekStart = start.format('YYYY-MM-DD');

                this.days = Array.from({length: 7}, (_, i) => {
                    const d = start.add(i, 'day');
                    return {
                        date: d.format('YYYY-MM-DD'),
                        label: d.format('ddd, MMM D'),
                    };
                });

                if (this.fetchWeek) {
                    this.fetchWeek(this.days[0].date, this.days[6].date);
                }

                this.resetLaneCaches();
            },

            moveWeek(delta) {
                const start = dayjs.tz
                    ? dayjs.tz(this.weekStart, 'YYYY-MM-DD', this.APP_TZ)
                    : dayjs(this.weekStart, 'YYYY-MM-DD');
                this.setWeek(start.add(delta, 'week'));
            },

            goToday() {
                this.setWeek(this.currentNow());
                this.invalidateLanes()
            },

            isCurrentWeek() {
                const now = this.currentNow();
                const start = dayjs.tz
                    ? dayjs.tz(this.weekStart, 'YYYY-MM-DD', this.APP_TZ)
                    : dayjs(this.weekStart, 'YYYY-MM-DD');
                const end = start.add(6, 'day').endOf('day');
                return now.isAfter(start) && now.isBefore(end);
            },

            async fetchWeek(fromDate, toDate) {
                this.isLoading = true;
                //await this.$wire.call('loadTasksForRange', fromDate, toDate);
                await this.$wire.call('loadTasksForRange', fromDate, toDate).then(tasks => {
                    this.tasks = tasks;
                    this.resetLaneCaches();
                });
            },

            onContextMenu(event, emp, day, idx) {
                if (
                    this.sel.emp !== emp.id ||
                    this.sel.day !== day ||
                    idx < this.sel.startIdx ||
                    idx > this.sel.endIdx
                ) {
                    this.startSelection(emp.id, day, idx);
                    this.endSelection();
                }
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
                this.sel = {idx: null, emp: null, day: null, startIdx: null, endIdx: null};
            },

            isPast(dayObj, slotIdx) {
                if (slotIdx === this.timeSlots.length - 1) return true;
                if (this.baseCount > 0 && (slotIdx % this.baseCount) === (this.baseCount - 1)) {
                    return true;
                }
                const slotLabel = this.timeSlots[slotIdx];
                const cellMoment = this.slotDateTime(dayObj, slotLabel);
                const isEndOfDaySlot = slotIdx === this.timeSlots.length - 1;
                return isEndOfDaySlot || cellMoment.isBefore(this.currentNow());
            },

            isTaskPast(task) {
                const fmtIn = 'HH:mm:ss';
                const dayObj = {date: task.day};
                const start = dayjs.tz
                    ? dayjs.tz(`${task.day} ${task.start}`, `YYYY-MM-DD ${fmtIn}`, this.APP_TZ)
                    : dayjs(`${task.day} ${task.start}`, `YYYY-MM-DD ${fmtIn}`);
                const end = dayjs.tz
                    ? dayjs.tz(`${task.day} ${task.end}`, `YYYY-MM-DD ${fmtIn}`, this.APP_TZ)
                    : dayjs(`${task.day} ${task.end}`, `YYYY-MM-DD ${fmtIn}`);
                return end.isBefore(this.currentNow());
            },

            dayWidth: function () {
                return this.slotWidthPx * this.timeSlots.length;
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

            updateTime({field, value}) {
                if (field === 'from') {
                    this.jobModalForm.schedule_from_time12 = value;
                } else if (field === 'to') {
                    this.jobModalForm.schedule_to_time12 = value;
                }
            },

            parseDbTime(s) {
                return dayjs(s, [
                    'YYYY-MM-DD HH:mm:ss',
                    'YYYY-MM-DD HH:mm',
                    'HH:mm:ss',
                    'HH:mm'
                ], true);
            },

            to12Hour(time24, dayStr) {
                if (!time24 || !dayStr) return '';
                // time24: 'HH:mm:ss', dayStr: 'YYYY-MM-DD'
                return dayjs.tz(`${dayStr} ${time24}`, 'YYYY-MM-DD HH:mm:ss', this.APP_TZ)
                    .format('h:mmA');
            },

            // Получить задачи сотрудника на конкретный день
            dayTasks(empId, day) {
                const d = (typeof day === 'string') ? day : (day && day.date ? day.date : null);
                if (!d) return [];
                return this.tasks.filter(t => String(t.technician) === String(empId) && String(t.day) === String(d));
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

            popover: {open: false, job: null, x: 0, y: 0, placement: 'right',},
            onTaskClick(task, evt) {
                if (this.suppressTaskClick) {
                    this.suppressTaskClick = false;
                    return;
                }

                // защита: если только что был drag — не показываем
                if (this.drag?.moved) {
                    this.drag.moved = false;
                    return;
                }

                // Собираем расширенную информацию о задаче.
                //const client = this.clients?.find(c => c.id === task.customer_id) ?? {};
                const tech = this.employees?.find(e => e.id === task.technician) ?? {};
                const totalSum = task.items.reduce((sum, item) => sum + (item.total ?? 0.0), 0);
                const taskMessage = (task.message === '') ? '---' : task.message;

                this.popover.job = {
                    id: task.id,
                    title: `Job #${task.id}`,
                    description: taskMessage,
                    price: totalSum,
                    start: task.start,
                    end: task.end,
                    day: task.day,
                    client: {
                        name: task.client.name ?? '',
                        phone: task.client.phone ?? '',
                        address: [task.client.address].filter(Boolean).join(', ')
                    },
                    technician: {id: tech.id, name: tech.name ?? ''}
                };

                // Позиционируем у курсора
                const {clientX, clientY} = evt;
                this.placePopover(clientX, clientY);

                this.popover.open = true;
            },

            placePopover(x, y) {
                // Простейшее позиционирование с учётом края экрана
                const gap = 12;
                const vw = window.innerWidth, vh = window.innerHeight;
                const w = 360, h = 280;
                let px = x + gap, py = y + gap, placement = 'right';

                if (px + w > vw) {
                    px = x - w - gap;
                    placement = 'left';
                }
                if (py + h > vh) {
                    py = vh - h - gap;
                }
                if (py < 0) py = gap;

                this.popover.x = px;
                this.popover.y = py;
                this.popover.placement = placement;
            },

            closePopover() {
                this.popover.open = false;
                this.popover.job = null;
            },

            // Перетаскивание задач
            drag: {
                task: null,
                offsetX: 0,
                widthPx: 0,
                cell: null,
                previewX: 0,
                pressed: false,
                moved: false,
                startX: null,
                row: 0,
                previewY: 0,
                durationSlots: 0
            },
            startDrag(task, evt) {
                if (evt.button !== 0) return;
                this.drag.task = task;
                this.drag.offsetX = evt.offsetX;
                this.drag.moved = false;
                this.drag.startX = evt.clientX;
                this.drag.cell = evt.currentTarget.closest('div[data-day]');
                const start = dayjs(task.start, 'HH:mm:ss');
                const end = dayjs(task.end, 'HH:mm:ss');
                this.drag.durationSlots = Math.max(1, Math.round(end.diff(start, 'minute') / 30));
                this.drag.widthPx = this.drag.durationSlots * this.slotWidthPx;
                this.drag.previewX = start * this.slotWidthPx;

                const lane = this.laneOf(task) ?? 0;
                this.drag.row = lane;
                this.drag.previewY = lane * this.rowHeightPx;

                this._onDragHandler = this.onDrag.bind(this);
                this._onDropHandler = this.onDrop.bind(this);
                document.addEventListener('mousemove', this._onDragHandler);
                document.addEventListener('mouseup', this._onDropHandler);
            },
            onDrag(evt) {
                if (!this.drag.task) return;
                const cell = this.drag.cell;
                if (!cell) return;

                if (!this.drag.moved && Math.abs(evt.clientX - this.drag.startX) > 3) {
                    this.drag.moved = true;
                }
                if (!this.drag.moved) return;

                this.drag.moved = true;
                const day = cell.dataset.day;
                const emp = +cell.closest('[data-emp]').dataset.emp;
                if (day !== this.drag.task.day || emp !== this.drag.task.technician) return;
                const rect = cell.getBoundingClientRect();
                const sw = this.slotWidthPx;
                let slotCount = this.timeSlots.length;
                let slotWidthPx = this.slotWidthPx;

                // ---- X
                let x = evt.clientX - rect.left - this.drag.offsetX;
                const maxX = rect.width - this.drag.widthPx;
                x = Math.max(0, Math.min(maxX, x));
                let col = Math.floor(x / sw);
                const maxStartCol = this.wrapCols - this.drag.durationSlots;
                col = Math.max(0, Math.min(maxStartCol, col));
                this.drag.previewX = col * sw;

                // ---- Y
                const rows = this.rowsFor(emp, {date: day});
                let y = evt.clientY - rect.top;
                let row = Math.floor(y / this.rowHeightPx);
                row = Math.max(0, Math.min(rows - 1, row));
                this.drag.row = row;
                this.drag.previewY = row * this.rowHeightPx;

                let start = dayjs(this.drag.task.start, 'HH:mm:ss');
                let end = dayjs(this.drag.task.end, 'HH:mm:ss');
                let durationSlots = Math.max(1, Math.round((end.diff(start, 'minute')) / 30));
                this.drag.widthPx = durationSlots * slotWidthPx;

            },
            async onDrop(evt) {
                document.removeEventListener('mousemove', this._onDragHandler);
                document.removeEventListener('mouseup', this._onDropHandler);

                const t = this.drag.task;
                const moved = this.drag.moved;
                const cell = this.drag.cell || document.elementFromPoint(evt.clientX, evt.clientY)?.closest('div[data-day]');
                let previewX = this.drag.previewX;

                if (!t) {
                    return reset.call(this);
                }

                function reset() {
                    this.drag.task = null;
                    this.drag.cell = null;
                    this.drag.pressed = false;
                    this.drag.moved = false;
                    this.drag.previewX = 0;
                    this.drag.previewY = 0;
                }

                if (!moved) {
                    try {
                        this.onTaskClick(t, evt);
                    } finally {
                        reset.call(this);
                    }
                    return;
                }

                if (!(cell && this.drag.task && this.drag.moved)) {
                    this.drag.task = this.drag.cell = null;
                    previewX = 0;
                    return;
                }
                const to24 = (s) => dayjs(s, 'h:mm A').format('HH:mm:ss');

                try {
                    if (!cell) return;
                    this.isLoading = true;

                    const sw = this.slotWidthPx;
                    const dayStr = cell.getAttribute('data-day');
                    const empId = Number(cell.getAttribute('data-emp'));

                    let col = Math.floor(previewX / sw);
                    const maxStartCol = this.wrapCols - this.drag.durationSlots;
                    col = Math.max(0, Math.min(maxStartCol, col));

                    const row = this.drag.row;
                    const idx = row * this.wrapCols + col;

                    const slotLabel = this.getSlotLabelByIdx(idx);
                    if (!slotLabel) {
                        this.drag.task = this.drag.cell = null;
                        previewX = this.drag.previewY = 0;
                        return;
                    }
                    const slot = to24(slotLabel);

                    await this.$wire.call('moveTask', this.drag.task.id, dayStr, slot, empId, row);
                    await this.fetchWeek(this.days[0].date, this.days[6].date);
                    this.resetLaneCaches();
                    await this.$nextTick();
                } finally {
                    // сброс драг-состояния
                    this.isLoading = false;
                    this.drag.task = null;
                    this.drag.cell = null;
                    previewX = this.drag.previewY = 0;
                    this.drag.moved = false;
                    this.resetLaneCaches();
                }
            },

            taskStyle(task) {
                const s = this.parseDbTime(task.start);
                const e = this.parseDbTime(task.end);
                if (!s.isValid() || !e.isValid()) return '';

                const base = dayjs(this.timeSlots[0], 'h:mm A');
                const slotMin = this.slotWidthPx;

                const startIdx = Math.max(0, Math.round(s.diff(base, 'minute') / slotMin));
                const endIdx = Math.max(startIdx + 1,
                    Math.min(this.timeSlots.length,
                        Math.round(e.diff(base, 'minute') / slotMin)));

                const leftPx = startIdx * this.slotWidthPx;
                const widthPx = (endIdx - startIdx) * this.slotWidthPx;

                const lane = this.laneOf(task);
                const topPx = lane * this.rowHeightPx;

                return `left:${leftPx}px; width:${widthPx}px; top:${topPx}px;`;
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
            formatTime(from, to) {
                const f = dayjs(from, 'HH:mm:ss').format('h:mmA');
                const t = dayjs(to, 'HH:mm:ss').format('h:mmA');
                return `${f} – ${t}`;
            },

            recalcItemsTotal() {
                this.jobModalForm.items.forEach(item => {
                    const qty = Number(item.qty) || 0;
                    const price = Number(item.unit_price) || 0;
                    const base = qty * price;
                    const tax = item.tax ? base * 0.10 : 0;
                    item.total = +(base + tax).toFixed(2);
                    item.taxTotal = +tax.toFixed(2);
                });
            },

            addItem(type) {
                this.jobModalForm.items.push({
                    id: Date.now() + Math.random(),
                    key: Date.now() + Math.random(),
                    db_id: null,
                    name: '',
                    qty: 1,
                    unit_price: 0.00,
                    unit_cost: 0.00,
                    tax: false,
                    taxTotal: 0.00,
                    description: '',
                    type,
                    total: 0.00,
                    part_id: null,
                    stock: null,
                    priceLocked: false,
                    is_custom: false,
                    warn: {qty: ''},
                    search: {open: false, q: '', results: [], hi: -1, loading: false}
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
            @this.call('searchCustomers', this.jobModalForm.customer_query)
                ;
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
                this.$wire.call('deleteTask', task.id)
                    .then(() => {
                        this.confirmDeleteOpen = false;
                        this.invalidateLanes();
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

            toTime12String(timeStr) {
                let [h, m] = timeStr.split(':');
                h = parseInt(h, 10);
                const ampm = h >= 12 ? 'PM' : 'AM';
                const hour12 = h % 12 || 12;
                return `${hour12}:${m} ${ampm}`;
            },

            getStartIdx(t) {
                return this.timeSlots.indexOf(dayjs(t.start, 'HH:mm:ss').format('h:mm A'));
            },

            getEndIdx(t) {
                return this.timeSlots.indexOf(dayjs(t.end, 'HH:mm:ss').format('h:mm A'));
            },

            overlap(a, b) {
                const a1 = this.getStartIdx(a), a2 = this.getEndIdx(a);
                const b1 = this.getStartIdx(b), b2 = this.getEndIdx(b);
                // [a1, a2) vs [b1, b2)
                return a1 < b2 && b1 < a2;
            },

            buildLanes(empId, day) {
                const dayStr = this.dayKey(day);
                const key = `${empId}-${dayStr}`;
                // если уже строили — вернём из кэша
                if (this._lanesCache[key]) return this._lanesCache[key];

                const tasks = this.tasks
                    .filter(t => t.technician === empId && t.day === dayStr)
                    .sort((a, b) => a.start.localeCompare(b.start));

                const lanes = [];
                const map = {};
                for (const t of tasks) {
                    let placed = false;
                    for (let i = 0; i < lanes.length; i++) {
                        const lane = lanes[i];
                        const last = lane[lane.length - 1];
                        if (!this.overlap(last, t)) {
                            lane.push(t);
                            map[t.id] = i;
                            placed = true;
                            break;
                        }
                    }
                    if (!placed) {
                        lanes.push([t]);
                        map[t.id] = lanes.length - 1;
                    }
                }

                const info = {lanesCount: Math.max(1, lanes.length), map};
                this._lanesCache[key] = info;
                return info;
            },

            laneOf(task) {
                return (this.buildLanes(task.technician, task.day).map[task.id]) ?? 0;
            },

            // Сколько дорожек у (empId, day)
            lanesCount(empId, day) {
                return this.buildLanes(empId, day).lanesCount || 1;
            },

            invalidateLanes() {
                this._lanesCache = {};
                this._empMaxLanesCache = {};
                this.lanesVersion++;
            },

            qtyErrorsText() {
                const over = (this.jobModalForm.items || []).filter(it => it.type === 'material' && (Number(it.qty) || 0) > (it.stock?.available ?? Infinity));
                if (!over.length) return '';
                if (over.length === 1) {
                    const it = over[0];
                    const max = it.stock?.available ?? 0;
                    return `“${it.name || 'Material'}”: only ${max} available.`;
                }
                return `${over.length} item(s) exceed available stock.`;
            },

            get hasQtyErrors() {
                const items = this.jobModalForm.items || [];
                return items.some(it => {
                    if (it.type !== 'material') return false;

                    const q = Number.parseInt(it.qty ?? 0, 10);
                    if (!Number.isFinite(q) || q < 0) return true;

                    const max = Number.isFinite(it?.stock?.available)
                        ? Number(it.stock.available)
                        : Infinity;

                    return q > max;
                });
            },

            empMaxLanes(empId) {
                if (this._empMaxLanesCache[empId] != null) return this._empMaxLanesCache[empId];
                let maxLanes = 1;
                for (const d of this.days) {
                    const info = this.buildLanes(empId, d);
                    if (info.lanesCount > maxLanes) maxLanes = info.lanesCount;
                }
                this._empMaxLanesCache[empId] = maxLanes;
                return maxLanes;
            },
            range(n) {
                return Array.from({length: n}, (_, i) => i);
            },
            rowsFor(empId, day) {
                const perDay = this.buildLanes(empId, this.dayKey(day)).lanesCount || 1;
                const perEmp = this.empMaxLanes(empId);
                return Math.max(1, perDay, perEmp);
            },
            getSlotLabelByIdx(i) {
                const base = this.defaultTimeSlots;
                const idx = i % base.length;
                return base[idx];
            },
            rowsSlots(empId, day) {
                const need = this.rowsFor(empId, day);
                const base = Array.from({length: this.wrapCols}, (_, c) => c);
                const rows = [];
                for (let r = 0; r < need; r++) rows.push(base.map(c => r * this.wrapCols + c));
                return rows;
            },
            flatSlots(empId, day) {
                // rowsSlots(empId, day) уже возвращает массив рядов, где ряд = массив slotIdx
                // Делаем плоский список всех slotIdx по порядку
                const rows = this.rowsSlots(empId, day);
                const flat = [];
                for (let r = 0; r < rows.length; r++) {
                    const row = rows[r];
                    for (let k = 0; k < row.length; k++) {
                        flat.push(row[k]);
                    }
                }
                return flat;
            },
            containerHeight(empId, day) {
                return this.rowsFor(empId, day) * this.rowHeightPx;
            },
            /*rowsCount() {
                return Math.ceil(this.timeSlots.length / this.wrapCols);
            },*/

            routeControl: null,
            routeLayer: null,
            routeTech: null,
            routeLayers: [],
            mode: 'schedule',
            map: null, markers: null, inited: false,
            async showMap() {
                this.mode = 'map';
                if (!this.inited) {
                    await this.initMap();
                    this.inited = true;
                }
                await this.renderMarkers(this.tasks);
                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize(true);

                        if (this.bounds?.isValid?.()) {
                            this.map.fitBounds(this.bounds, { padding: [40, 40] });
                        }
                    }
                }, 50);
            },
            async initMap() {
                this.map = L.map('jobsMap', {zoomControl: true}).setView([40.73, -73.93], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(this.map);
                this.markers = L.markerClusterGroup(); // кластеризация
                this.map.addLayer(this.markers);
                this.addEmployeeRouteControl();
            },
            showCalendar() {
                this.mode = 'schedule';
            },

            addEmployeeRouteControl () {
                const self = this;

                const EmployeesControl = L.Control.extend({
                    options: { position: 'topleft' },

                    onAdd(map) {
                        const wrap = L.DomUtil.create('div', 'leaflet-bar leaflet-control');

                        // блокируем прокидывание событий на карту
                        L.DomEvent.disableClickPropagation(wrap);
                        L.DomEvent.disableScrollPropagation(wrap);

                        // Контейнер
                        wrap.style.background = '#fff';
                        wrap.style.padding = '8px';
                        wrap.style.minWidth = '260px';
                        wrap.style.boxShadow = '0 1px 3px rgba(0,0,0,.25)';
                        wrap.style.borderRadius = '8px';

                        // Верхняя строка — переключатель маршрутизации
                        const top = document.createElement('div');
                        top.className = 'flex items-center gap-2 mb-2';

                        const routeBtn = document.createElement('button');
                        routeBtn.className = 'px-2 py-1 rounded bg-blue-600 text-white text-sm';
                        routeBtn.textContent = 'Routing on';
                        routeBtn.dataset.enabled = '1'; // включено

                        top.appendChild(routeBtn);

                        // Кнопка очистки
                        const clearBtn = document.createElement('button');
                        clearBtn.className = 'px-2 py-1 rounded bg-gray-200 text-sm';
                        clearBtn.textContent = 'Clear';
                        top.appendChild(clearBtn);

                        wrap.appendChild(top);

                        // Поисковая строка
                        const search = document.createElement('input');
                        search.type = 'text';
                        search.placeholder = 'Filter by name or tag';
                        search.className =
                            'w-full border border-gray-300 rounded px-2 py-1 text-sm mb-2';
                        wrap.appendChild(search);

                        // Список сотрудников с чекбоксами
                        const list = document.createElement('div');
                        list.className = 'max-h-56 overflow-auto space-y-1';
                        wrap.appendChild(list);

                        // Локальное состояние
                        let selected = new Set(); // id выбранных сотрудников
                        let routingEnabled = true;

                        // Рендер списка
                        const renderList = () => {
                            const q = search.value.trim().toLowerCase();
                            list.innerHTML = '';

                            (self.employees || []).forEach(e => {
                                const hay = (e.name + ' ' + (e.tags || '')).toLowerCase();
                                if (q && !hay.includes(q)) return;

                                const row = document.createElement('label');
                                row.className = 'flex items-center gap-2 cursor-pointer text-sm';

                                const cb = document.createElement('input');
                                cb.type = 'checkbox';
                                cb.checked = selected.has(e.id);
                                cb.addEventListener('change', () => {
                                    cb.checked ? selected.add(e.id) : selected.delete(e.id);
                                    maybeBuildRoute();
                                });

                                const color = document.createElement('span');
                                color.style.display = 'inline-block';
                                color.style.width = '12px';
                                color.style.height = '12px';
                                color.style.borderRadius = '3px';
                                color.style.background = e.color || '#55b';
                                color.style.border = '1px solid #999';

                                const name = document.createElement('span');
                                name.textContent = e.name;

                                row.appendChild(cb);
                                row.appendChild(color);
                                row.appendChild(name);
                                list.appendChild(row);
                            });
                        };

                        renderList();

                        // События
                        search.addEventListener('input', renderList);

                        routeBtn.addEventListener('click', () => {
                            routingEnabled = !routingEnabled;
                            routeBtn.textContent = routingEnabled ? 'Routing on' : 'Routing off';
                            routeBtn.className =
                                'px-2 py-1 rounded text-sm ' +
                                (routingEnabled ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-800');
                            maybeBuildRoute();
                        });

                        clearBtn.addEventListener('click', () => {
                            selected.clear();
                            renderList();
                            self.clearRoute();
                        });

                        // Помощник: строим/обновляем маршрут только если включено
                        function maybeBuildRoute() {
                            if (!routingEnabled) {
                                self.clearRoute();
                                return;
                            }
                            // передаём массив id
                            const ids = Array.from(selected);
                            self.showTechRoute(ids, self.currentDayISO);
                        }

                        return wrap;
                    },
                });

                this.map.addControl(new EmployeesControl());
            },

            async renderMarkers(tasks) {
                this.markers.clearLayers();
                const bounds = [];
                for (const t of tasks) {
                    // ожидаем у каждой задачи t.client: {name, phone, address, lat, lng}
                    let {lat, lng} = t.client ?? {};
                    if ((lat == null || lng == null) && t.client?.address_formatted) {
                        // дернуть бэкенд-геокодер — он вернёт lat/lng и сохранит в БД
                        const res = await fetch(`/geocode?addr=${encodeURIComponent(t.client.address)}`);
                        if (res.ok) {
                            const p = await res.json();
                            lat = p.lat;
                            lng = p.lng;
                        }
                    }
                    if (lat == null || lng == null) continue;

                    const totalSum = t.items.reduce((sum, item) => sum + (item.total ?? 0.0), 0);
                    const techNames = Array.isArray(t.technician)
                        ? t.technician.map(x => (typeof x === 'object' ? x.name : this.employees.find(e => e.id === x)?.name))
                            .filter(Boolean).join(', ')
                        : this.employees?.find(e => e.id === t.technician)?.name || '';
                    const formatTime = this.formatTime(t.start, t.end);
                    const formatted = dayjs(t.day).format("MMM D YYYY");

                    function makePopupEl(t, formatted, formatTime, totalSum, techNames) {
                        const root = document.createElement('div'); root.className = 'text-xs';

                        const line = (html) => { const d = document.createElement('div'); d.innerHTML = html; root.appendChild(d); };
                        const txt  = (tag, text, cls) => { const d = document.createElement(tag); if (cls) d.className = cls; d.textContent = text; root.appendChild(d); };

                        txt('div', `Job #${t.id ?? ''}`, 'font-semibold');
                        txt('div', `${formatted} ${formatTime}`, 'mt-0 mb-2 text-gray-500 text-[10px]');
                        txt('div', t.message ?? '');
                        line(`<b>Price: </b>$${(totalSum ?? 0).toLocaleString()}`);
                        line(`<b>Client: </b>${t.client?.name ?? ''}`);
                        txt('div', t.client?.address ?? '');
                        line(`<b>Phone: </b>${t.client?.phone ?? ''}`);
                        line(`<b>Technician: </b>${techNames ?? ''}`);
                        return root;
                    }

                    const el = makePopupEl(t, formatted, formatTime, totalSum, techNames);
                    const m = L.marker([lat, lng]).bindPopup(el);
                    this.markers.addLayer(m);
                    bounds.push([lat, lng]);
                }
                if (bounds.length) this.map.fitBounds(bounds, {padding: [40, 40]});
            },
            makeStopIcon(n) {
                return L.divIcon({
                    className: 'stop-num',
                    html: `<div style="
                      width:28px;height:28px;border-radius:50%;
                      background:#1e90ff;color:#fff;display:flex;
                      align-items:center;justify-content:center;
                      font-weight:600">${n}</div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                });
            },

            clearRoute() {
                if (this.routeControl) { this.map.removeControl(this.routeControl); this.routeControl = null; }
                if (this.routeLayer)   { this.routeLayer.clearLayers(); }
            },
            async showTechRoute(selectedTechIds = [], dayISO) {
                this.clearRoute();

                // раскладываем цвет по технику (можно взять e.color из employees)
                const colorByTech = id => {
                    const e = (this.employees || []).find(x => x.id === id);
                    return e?.color || '#1e90ff';
                };

                for (const techId of selectedTechIds) {
                    // 1) точки задач этого техника за день (сортируем по start)
                    const points = (this.tasks || [])
                        .filter(t => t.technician === techId && t.day === dayISO && t.client && t.client.lat != null && t.client.lng != null)
                        .sort((a, b) => (a.start || '').localeCompare(b.start || ''))
                        .map(t => ({ lat: t.client.lat, lng: t.client.lng }));

                    if (points.length < 2) continue; // для 1 точки строить нечего

                    // 2) запрос в Geoapify
                    const waypoints = points.map(p => `${p.lat},${p.lng}`).join('|');
                    const apiKey = window.GEOAPIFY_KEY;
                    console.log(apiKey);
                    const url = `https://api.geoapify.com/v1/routing?waypoints=${encodeURIComponent(waypoints)}&mode=drive&apiKey=${apiKey}`;

                    let geojson;
                    try {
                        const res = await fetch(url);
                        if (!res.ok) {
                            console.warn('Routing error', techId, res.status, await res.text());
                            continue;
                        }
                        geojson = await res.json();
                    } catch (e) {
                        console.error('Routing fetch failed', e);
                        continue;
                    }

                    // 3) отрисовка (Geoapify возвращает GeoJSON)
                    const lineColor = colorByTech(techId);
                    const routeLayer = L.geoJSON(geojson, {
                        style: { color: lineColor, weight: 5, opacity: 0.9 }
                    }).addTo(this.map);

                    this.routeLayers.push(routeLayer);

                    // 4) необязательная маркировка точек порядком (1,2,3…)
                    points.forEach((p, idx) => {
                        const m = L.marker([p.lat, p.lng], {
                            icon: L.divIcon({
                                className: 'route-order',
                                html: `<div style="background:${lineColor};color:#fff;border-radius:12px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font:12px sans-serif">${idx+1}</div>`,
                                iconSize: [24, 24],
                                iconAnchor: [12, 12]
                            })
                        });
                        m.addTo(routeLayer);
                    });

                    // зум к маршруту
                    try { this.map.fitBounds(routeLayer.getBounds(), { padding: [40, 40] }); } catch {}
                }
            },

            addCustomer() {
                return {
                    // поля формы (минимум)
                    name: '', email: '', phone: '',
                    query: '',
                    suggestions: [],
                    open: false,
                    selected: { id: '', label: '', lat: null, lng: null },

                    resetSelection() {
                        this.selected = { id: '', label: '', lat: null, lng: null };
                    },

                    async findSuggestions() {
                        if (this.query.trim().length < 4) {
                            this.suggestions = [];
                            this.resetSelection();
                            return;
                        }
                        // дергаем Livewire
                        this.suggestions = await this.$wire.searchAddress(this.query);
                        this.open = true;
                    },

                    selectSuggestion(s) {
                        this.selected = { id: s.id, label: s.label, lat: s.lat, lng: s.lng };
                        this.query = s.label;     // показываем читаемый адрес
                        this.open = false;
                    },

                    async submit() {
                        // собираем данные:
                        const payload = {
                            name:  this.name,
                            email: this.email,
                            phone: this.phone,

                            // если адрес выбран из подсказки — кладём нормализованные поля
                            address_formatted: this.selected.label || null,
                            address_place_id:  this.selected.id    || null,
                            address_lat:       this.selected.lat   ?? null,
                            address_lng:       this.selected.lng   ?? null,
                        };

                        // если пользователь стирал поле руками после выбора — сбросим координаты
                        if (!this.selected.id || this.query !== this.selected.label) {
                            payload.address_formatted = null;
                            payload.address_place_id  = null;
                            payload.address_lat       = null;
                            payload.address_lng       = null;
                        }

                        await this.$wire.createCustomer(payload);
                        this.showAddCustomerModal = false;
                    },

                    onErrors(errors) {
                        // можете подсветить поля / показать тосты
                        console.warn(errors);
                    }
                }
            },

            async openJobModal(type = null, sel, contextMenu = null) {
                if (type) this.jobModalType = type;
                this.jobModalOpen = true;

                const SLOT_MIN = 30;
                const FIRST_HOUR = 6;
                const SLOTS_PER_DAY = 32;

                const norm = (i) => ((i % SLOTS_PER_DAY) + SLOTS_PER_DAY) % SLOTS_PER_DAY;

                if (type === 'edit' && contextMenu.task) {
                    const t = contextMenu.task;
                    deepAssign(this.jobModalForm, defaultJobModalForm());
                    deepAssign(this.jobModalForm, t);
                    for (customer in this.jobModalForm.results) {
                        this.jobModalForm.customer_id = customer;
                    }
                    this.selectCustomer(t.client);
                    this.jobModalForm.schedule_from_date = t.day || '';
                    this.jobModalForm.schedule_to_date = t.day || '';
                    this.jobModalForm.schedule_from_time12 = this.to12Hour(t.start, t.day || '');
                    this.jobModalForm.schedule_to_time12 = this.to12Hour(t.end, t.day || '');
                    this.jobModalForm.items = (t.items || []).map(item => ({
                        key: Date.now() + Math.random(),
                        db_id: item.db_id ?? null,
                        type: item.type,
                        name: item.name ?? '',
                        qty: Number(item.qty ?? 1),
                        unit_price: Number(item.unit_price ?? 0.0),
                        unit_cost: Number(item.unit_cost ?? 0.0),
                        description: item.description ?? '',
                        tax: Boolean(item.tax ?? false),
                        taxTotal: Number(item.taxTotal ?? 0.0),
                        total: Number(item.total ?? 0.0),
                        item_id: item.item_id ?? null,
                        part_id: item.part_id ?? null,
                        is_custom: item.is_custom ?? (!item.part_id),
                        stock: item.part ? Number(item.part.qty ?? 0) : null,
                        priceLocked: !!item.part_id,
                        warn: {qty: ''},
                        search: {open: false, q: '', results: [], hi: -1, loading: false}
                    }));

                    const ids = this.jobModalForm.items
                        .filter(x => x.part_id)
                        .map(x => x.part_id);

                    if (ids.length) {
                        try {
                            const stockMap = await this.$wire.call('partsStockByIds', ids);
                            this.jobModalForm.items.forEach(it => {
                                if (it.part_id && stockMap[it.part_id]) {
                                    it.stock = {
                                        available: Number(stockMap[it.part_id].available ?? 0),
                                        reserved: Number(stockMap[it.part_id].reserved ?? 0),
                                        quantity: Number(stockMap[it.part_id].quantity ?? 0),
                                    };
                                    // ограничим qty, если нужно
                                    if (typeof it.stock.available === 'number' && it.qty > it.stock.available) {
                                        it.qty = it.stock.available;
                                    }
                                }
                            });
                        } catch (e) {
                            console.error('partsStockByIds failed', e);
                        }
                    }

                    const tech = this.employees.find(e => e.id === this.sel.emp);
                    if (tech && !this.jobModalForm.employees.some(e => e.id === tech.id)) {
                        this.jobModalForm.employees.push(tech);
                    }

                    this.jobModalForm.message = t.message || '';
                    this.jobModalForm.jobModalType = 'edit';
                    this.jobModalForm.task_id = t.id;
                    this.recalcItemsTotal();
                    return;
                } else {
                    deepAssign(this.jobModalForm, defaultJobModalForm());
                    this.jobModalForm.jobModalType = 'new';

                    const day = dayjs(sel.day.date, 'YYYY-MM-DD');

                    const rawStart = sel.startIdx ?? 0;
                    const rawEnd = sel.endIdx ?? rawStart;
                    let startCol = norm(rawStart);
                    let endCol = norm(rawEnd);

                    if (endCol < startCol && (rawEnd - rawStart) < 0) {
                        [startCol, endCol] = [endCol, startCol];
                    }

                    let from = day.startOf('day').add(FIRST_HOUR, 'hour').add(startCol * SLOT_MIN, 'minute');
                    let to = day.startOf('day').add(FIRST_HOUR, 'hour').add((endCol + 1) * SLOT_MIN, 'minute');

                    const endOfDay = day.startOf('day').hour(22).minute(0).second(0).millisecond(0);
                    // Если «to» убежал на следующий день — режем по границе
                    if (to.isAfter(endOfDay)) {
                        // сдвигаем назад, чтобы длительность влезла в день
                        const durMin = Math.max(SLOT_MIN, to.diff(from, 'minute'));
                        to = endOfDay;
                        from = endOfDay.clone().subtract(durMin, 'minute');
                    }

                    this.jobModalForm.schedule_from_date = from.format('YYYY-MM-DD');
                    this.jobModalForm.schedule_to_date = to.format('YYYY-MM-DD');

                    this.jobModalForm.schedule_from_time12 = from.format('h:mm A');
                    this.jobModalForm.schedule_to_time12 = to.format('h:mm A');

                    this.jobModalForm.schedule_from_time = from.format('HH:mm');
                    this.jobModalForm.schedule_to_time = to.format('HH:mm');

                    const tech = this.employees.find(e => e.id === this.sel.emp);
                    if (tech && !this.jobModalForm.employees.some(e => e.id === tech.id)) {
                        this.jobModalForm.employees.push(tech);
                    }
                }
                this.jobModalForm.employees_query = '';
                this.menuVisible = false;
                this.invalidateLanes();
                this.clearSelection();
            },
        }
    }
</script>
