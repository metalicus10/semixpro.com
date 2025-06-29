<div x-data="{
    employees: [],
    selection: null,
    jobModalOpen: false,
    showAddCustomerModal: false,
    jobModalForm: {
        customer_query: '',
        schedule_from: '',
        schedule_to: '',
        dispatch: '',
        notify_customer: false,
        items: [],
        private_notes: '',
        tags: '',
        attachments: [],
        message: null,
        new_customer: {
            name: '',
            email: '',
            phone: '',
            address: ''
        }
    },
    showCustomerModal: false,
    customerError: '',
    menuVisible: false,
    menuX: 0,
    menuY: 0,
    selectionSlots: [],
    longpressTimeout: null,
    weekStart: dayjs().startOf('week').add(1, 'day'),
    currentWeek: [],
    viewDate: dayjs().startOf('day'),
    slotsPerDay: 32,
    baseHour: 6,

    init(data) {
        this.employees = data;
        this.setWeek(this.viewDate);
        window.addEventListener('customer-validation-error', event => {
            this.customerError = Object.values(event.detail.errors)[0][0] ?? 'Validation error';
        });

        window.addEventListener('customer-created', event => {
            this.form.customer_query = event.detail.name;
                this.showCustomerModal = false;
                this.customerError = '';
        });
    },
        setWeek(date) {
            this.weekStart = dayjs(date).startOf('week').add(1, 'day'); // Monday as start
            this.currentWeek = Array.from({length: 7}, (_, i) => this.weekStart.add(i, 'day'));
        },
            prevWeek() {
                this.setWeek(this.weekStart.subtract(7, 'day'));
            },
            nextWeek() {
                this.setWeek(this.weekStart.add(7, 'day'));
            },
            goToday() {
                this.setWeek(dayjs());
                this.viewDate = dayjs().startOf('day');
            },
            isToday(d) {
                return d.isSame(dayjs(), 'day');
            },
            formatHour(h) {
                const hour = h % 12 || 12;
                const suffix = h < 12 ? 'AM' : 'PM';
                return `${hour} ${suffix}`;
            },
            formatRange(start, end) {
                const s = new Date(start);
                const e = new Date(end);
                return `${s.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })} - ${e.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}`;
            },
            initials(name) {
                return name.split(' ').map(p => p[0]).join('').slice(0, 2).toUpperCase();
            },
            taskPosition(task) {
                const s = new Date(task.start_time);
                const e = new Date(task.end_time);
                const minutesStart = (s.getHours() - 6) * 60 + s.getMinutes();
                const duration = (e - s) / (1000 * 60);
                const left = (minutesStart / 30) * 80;
                const width = (duration / 30) * 80;
                return `left: ${left}px; width: ${width}px; top: 8px; height: 48px;`;
            },
            selectionBoxStyle(sel) {
                const left = Math.min(sel.startX, sel.endX);
                const width = Math.abs(sel.endX - sel.startX);
                return `top: 0px; left: ${left}px; width: ${width}px; height: 100%;`;
            },
            startSelect(event, technician_id, isTouch = false) {
                this.selection = {
                    technician_id,
                    startX: event.offsetX || event.clientX,
                    endX: event.offsetX || event.clientX
                };
                if (isTouch) {
                    this.menuX = event.pageX;
                    this.menuY = event.pageY;
                }
            },
            dragSelect(event) {
                if (this.selection) {
                    this.selection.endX = event.offsetX || event.clientX;
                }
            },
            endSelect(event, isTouch = false) {
                if (!this.selection) return;
                this.selection.endX = event.offsetX || event.clientX;
            },
            startLongpress(e, index, technician_id) {
                this.longpressTimeout = setTimeout(() => {
                    this.showContextMenu(e, index, technician_id);
                }, 600);
            },
            cancelLongpress() {
                clearTimeout(this.longpressTimeout);
            },
            clearSelection() {
                this.selection = null;
                this.selectionSlots = [];
                this.menuVisible = false;
            },
            closeMenu() {
                this.menuVisible = false;
                this.selectedTechnician = null;
            },
            selectSlot(index, technician_id) {
                if (!this.selection || this.selection.technician_id !== technician_id) {
                    this.selection = { technician_id, start: index, end: index };
                } else {
                    this.selection.end = index;
                }
                this.updateSelectionSlots();
            },
            updateSelectionSlots() {
                const start = Math.min(this.selection.start, this.selection.end);
                const end = Math.max(this.selection.start, this.selection.end);
                this.selectionSlots = [];
                for (let i = start; i <= end; i++) {
                    this.selectionSlots.push(i);
                }
            },
            slotClass(index, employee) {
                if (this.selection && employee.id === this.selection.technician_id && this.selectionSlots.includes(index)) {
                    return 'bg-blue-400 bg-opacity-60 border border-blue-500';
                }
                return '';
            },
            showContextMenu(event, index, technician_id) {
                event.preventDefault();
                if (!this.selection || this.selection.technician_id !== technician_id || !this.selectionSlots.includes(index)) {
                    this.clearSelection();
                    this.selection = { technician_id, start: index, end: index };
                    this.updateSelectionSlots();
                }
                const grid = document.getElementById('mainGrid');
                const rect = grid.getBoundingClientRect();
                this.menuX = event.clientX;
                this.menuY = event.clientY;
                this.selectedTechnician = technician_id;
                this.menuVisible = true;
            },
    openJobModal(type = null) {
        // Если нужен type — сохраняем его
        if (type) this.jobModalType = type;

        this.jobModalOpen = true;
        // Вычисления для времени по selection
        const startSlot = Math.min(this.selection.start, this.selection.end);
        const endSlot = Math.max(this.selection.start, this.selection.end);

        const minutesStart = startSlot * 30;
        const minutesEnd = (endSlot + 1) * 30;

        const start = new Date();
        start.setHours(6, 0, 0, 0);
        const end = new Date(start);

        start.setMinutes(start.getMinutes() + minutesStart);
        end.setMinutes(end.getMinutes() + minutesEnd);

        const technicianName = this.employees.find(e => e.id === this.selection.technician_id)?.name || '';

        this.menuVisible = false;

        // Заполняем форму
        this.jobModalForm.schedule_from = start.toISOString().slice(0, 16);
        this.jobModalForm.schedule_to   = end.toISOString().slice(0, 16);
        this.jobModalForm.dispatch      = technicianName;

        // Можно добавить другие автозаполняемые поля, если нужно

        this.clearSelection();
    },
    get selectionTimeRange() {
        if (!this.selection) return '';

        const startIdx = Math.min(this.selection.start, this.selection.end ?? this.selection.start);
        const endIdx   = Math.max(this.selection.start, this.selection.end ?? this.selection.start);

        // День недели (0...6)
        const startDay = Math.floor(startIdx / this.slotsPerDay);
        const endDay = Math.floor(endIdx / this.slotsPerDay);

        // Слот в дне (0...31)
        const startSlotInDay = startIdx % this.slotsPerDay;
        const endSlotInDay = endIdx % this.slotsPerDay;

        // Рассчитываем дату (от понедельника недели)
        const weekStart = dayjs(this.currentWeek[0].format('YYYY-MM-DD'));

        const startDate = weekStart.add(startDay, 'day').toDate();
        startDate.setHours(this.baseHour, 0, 0, 0);
        startDate.setMinutes(startDate.getMinutes() + startSlotInDay * 30);

        const endDate = weekStart.add(endDay, 'day').toDate();
        endDate.setHours(this.baseHour, 0, 0, 0);
        endDate.setMinutes(endDate.getMinutes() + (endSlotInDay + 1) * 30);

        // Выводим время и если нужно — дату
        const startString = `${this.formatTime(startDate)}${startDay !== endDay ? ' ' + weekStart.add(startDay, 'day').format('ddd') : ''}`;
        const endString   = `${this.formatTime(endDate)}${startDay !== endDay ? ' ' + weekStart.add(endDay, 'day').format('ddd') : ''}`;

        return `${startString} - ${endString}`;
    },
    formatTime(date) {
        // 12-часовой формат, AM/PM
        let h = date.getHours();
        let m = date.getMinutes();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        m = m.toString().padStart(2, '0');
        return `${h}:${m} ${ampm}`;
    },
            registerDraggable(el, task) {
                if (window.interact) {
                    interact(el).draggable({
                        onend(event) {
                            Livewire.dispatch('updateTaskPosition', task.id, event.pageX);
                        }
                    });
                }
            },
            selectionHighlightStyle(employee) {
                if (!this.selection || this.selection.technician_id !== employee.id) return '';
                const from = Math.min(this.selection.start, this.selection.end ?? this.selection.start);
                const to = Math.max(this.selection.start, this.selection.end ?? this.selection.start);
                const left = from * 20;
                const width = (to - from + 1) * 20;
                return `left: ${left}px; width: ${width}px; top: 0; height: ${this.slotHeight}px;`;
            },
            totalSlots() {
                console.log((this.currentWeek.length * 32)*2);
                return (this.currentWeek.length * 32)*2;
            },
            addLineItem() {
                this.jobModalForm.items.push({ name: '', qty: 1, unit_price: 0, unit_cost: 0, description: '' });
            },
            handleFiles(e) {
                this.jobModalForm.attachments = Array.from(e.target.files);
            },
            closeJobModal() {
                this.jobModalOpen = false;
            },
            saveJob() {
                // Livewire.emit или AJAX — сохранить задание
                // ...ваша логика...
                this.closeJobModal();
            },
            saveNewCustomer() {
                this.customerError = '';
                const customer = this.jobModalForm.new_customer;
                if (!customer.name || (!customer.email && !customer.phone)) {
                    this.customerError = 'Name and either email or phone are required.';
                    return;
                }

                Livewire.dispatch('createCustomer', customer);
            },
            prefill(data) {
                this.jobModalForm.schedule_from = data.schedule_from;
                this.jobModalForm.schedule_to = data.schedule_to;
                this.jobModalForm.dispatch = data.dispatch;
                this.open = true;
            },
            addItem(type) {
                this.jobModalForm.items.push({
                    name: '', qty: 1, unit_price: 0, unit_cost: 0, tax: false, description: '', type
                });
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
                return '$' + (sub + tax).toFixed(2);
            }

}" x-init="init(@js($employees))">
    <div x-ref="mainGrid"
         class="select-none bg-white text-sm text-gray-900 relative overflow-x-auto">

        <div class="flex items-center gap-2 bg-white px-4 py-2 sticky top-0 z-20">
            <button @click="prevWeek" class="px-2 py-1 rounded hover:bg-gray-100 text-xl">&larr;</button>
            <button @click="goToday" class="border px-3 py-1 rounded bg-white hover:bg-gray-50 font-medium">Today
            </button>
            <button @click="nextWeek" class="px-2 py-1 rounded hover:bg-gray-100 text-xl">&rarr;</button>
            <div class="mx-4 font-semibold text-base">
                <span x-text="weekStart.format('MMMM DD')"></span>
                –
                <span x-text="weekStart.add(6, 'day').format('MMMM DD, YYYY')"></span>
            </div>
        </div>

        <!-- Временная шкала -->
        <div class="sticky top-0 z-10 bg-white">
            <div class="flex">
                <div class="w-[60px] h-[85px] flex-shrink-0 flex flex-col">
                    <!-- Первая строка: для выравнивания по высоте с датой -->
                    <div class="w-[60px] h-[37px] bg-gray-50"></div>
                    <!-- Вторая строка: временной пояс, прижат вниз -->
                    <div class="w-[60px] h-[48px] bg-gray-50 flex items-end justify-center text-[11px] font-thin">
                        GMT-04
                    </div>
                </div>
                <template x-for="(d, dayIdx) in currentWeek" :key="d.format('YYYY-MM-DD')">
                    <div class="text-start border-y border-gray-400 day-right-border w-[640px]"
                         :class="{
                            'day-left-border': dayIdx !== 0,
                            'border-r border-gray-200': dayIdx % 1 !== 0,
                         }"
                    >
                        <div class="w-[640px] flex gap-1 items-baseline px-3 py-1 border-b border-gray-400">
                            <div class="text-xl font-medium" x-text="d.format('D')"></div>
                            <div class="text-xs font-thin" x-text="d.format('ddd')"></div>
                        </div>
                        <div class="flex w-[640px]">
                            <div class="grid grid-cols-16 w-[640px] divide-x divide-gray-200">
                                <template x-for="i in 16" :key="i">
                                    <div class="h-12 w-[40px] text-[11px] font-thin flex items-center justify-center">
                                        <span x-text="formatHour(i + 5)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Основная сетка -->
        <div x-data="{
                slotHeight: 0,
                setSlotHeight() {
                    this.$nextTick(() => {
                        this.slotHeight = this.$refs.techRow?.offsetHeight || 24;
                    });
                }
            }" x-init="setSlotHeight()" @resize.window="setSlotHeight" class="flex w-[4540px]">
            <!-- Сетка и задачи -->
            <div class="flex flex-col relative w-[4540px]">
                <template x-for="employee in employees" :key="employee.id">
                    <div class="flex flex-row relative min-h-[68px] border-gray-100">
                        <!-- Техники -->
                        <!-- Левый столбец — имя/аватар -->
                        <div
                            class="w-[60px] flex-shrink-0 flex flex-col justify-center items-center gap-1 px-2 border-r border-gray-100 bg-white"
                            x-ref="techRow">
                            <span x-text="employee.name" class="text-gray-800 text-[11px]"></span>
                            <template x-if="employee.avatar">
                                <img :src="employee.avatar" class="w-8 h-8 rounded-full" alt=""/>
                            </template>
                            <template x-if="!employee.avatar">
                                <div
                                    class="w-8 h-8 rounded-full bg-gray-300 text-xs font-semibold flex items-center justify-center">
                                    <span x-text="initials(employee.name)"></span>
                                </div>
                            </template>
                        </div>
                        <!-- Правая часть — сетка и задачи -->
                        <div id="mainGrid" class="relative">
                            <template x-if="selection && selection.technician_id === employee.id">
                                <div
                                    class="absolute bg-blue-200 bg-opacity-70 rounded z-10 pointer-events-none"
                                    :style="selectionHighlightStyle(employee)"
                                >
                                    <!-- Время с - по -->
                                    <div class="absolute left-1 top-1 text-[8px] text-shadow-2xs text-shadow-white text-black font-thin text-gray-600 drop-shadow"
                                         x-text="selectionTimeRange"></div>
                                </div>
                            </template>
                            <!-- Сетка 30-минутных слотов -->
                            <div :style="`width: 640px; height: 100%; position: relative;`">
                                <template x-for="(col, index) in 224" :key="index">
                                    <div class="absolute top-0 bottom-0 border-r border-gray-100 w-[20px]"
                                         :class="{
                                            [slotClass(index, employee)]: true,
                                            'border-l-2 border-gray-100': index % 32 === 0,
                                            'border-l-2 border-gray-700': index % 32 === 0,
                                            'border-r border-gray-100': index % 32 !== 0,
                                         }"
                                         :style="`left: ${index * 20}px; width: 20px; height: ${slotHeight}px;`, selectionHighlightStyle(employee)"
                                         @click="
                                            if (!selection || selection.technician_id !== employee.id || !selectionSlots.includes(index)) {
                                                clearSelection();
                                                selectSlot(index, employee.id);
                                            }"
                                         @mousedown.prevent="
                                            if ($event.button === 0) {
                                                clearSelection();
                                                selectSlot(index, employee.id);
                                            }"
                                         @mouseover="if ($event.buttons === 1) selectSlot(index, employee.id)"
                                         @contextmenu.prevent="showContextMenu($event, index, employee.id)"
                                         @touchstart.passive="startLongpress($event, index, employee.id)"
                                         @touchend="cancelLongpress()"
                                    >
                                    </div>
                                </template>
                            </div>
                            <!-- Задачи -->
                            <template x-for="task in employee.tasks" :key="task.id">
                                <div
                                    class="absolute bg-blue-600 text-white text-xs rounded shadow px-2 py-1 flex flex-col justify-center"
                                    :style="taskPosition(task)"
                                    x-init="$nextTick(() => registerDraggable($el, task))"
                                >
                                    <div class="font-semibold truncate" x-text="task.title"></div>
                                    <div class="text-[11px] opacity-80"
                                         x-text="formatRange(task.start_time, task.end_time)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <!-- Контекстное меню -->
    <div
        x-show="menuVisible"
        @click.away="closeMenu()"
        class="absolute z-[9999] bg-white shadow border rounded w-40"
        :style="`top: ${menuY}px; left: ${menuX}px;`"
    >
        <button
            @click="openJobModal('job'); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M10 17v-3m4 3v-3m2-4h-8m4-6v2m0 0V3m0 2.5l.91 2.27m0 0a5.75 5.75 0 1 1-5.82 0"/>
            </svg>
            <span class="font-medium">+Job</span>
        </button>
        <button
            @click="openJobModal('estimate'); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 9h8M8 13h6" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span class="font-medium">+Estimate</span>
        </button>
        <button
            @click="openJobModal('event'); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="3" stroke-width="1.5" stroke="currentColor"/>
                <path d="M7 8h10M12 13v5" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span class="font-medium">+Event</span>
        </button>
    </div>

    <div>
        <div x-show="jobModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[95vh]">
                <div class="flex justify-between items-center border-b pb-4 mb-6">
                    <h2 class="text-xl font-semibold">New job</h2>
                    <button @click="jobModalOpen = false" class="text-gray-500 hover:text-red-500 text-2xl">&times;
                    </button>
                </div>

                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Left Column -->
                    <div class="w-full lg:w-1/3 space-y-6">
                        <div class="bg-gray-50 rounded-lg border p-4">
                            <div class="font-medium text-sm mb-1 flex items-center gap-1">
                                <svg class="w-4 h-4"/>
                                Customer
                            </div>
                            <input type="text" x-model="jobModalForm.customer_query"
                                   class="w-full rounded px-2 py-1 text-sm border"
                                   placeholder="Name, email, phone, or address"/>
                            <button type="button" class="text-blue-600 text-xs mt-2"
                                    @click="showAddCustomerModal = true">+
                                New customer
                            </button>
                        </div>

                        <!-- Schedule -->
                        <div class="border p-4 rounded space-y-4">
                            <label class="block text-sm font-medium">Schedule</label>
                            <div class="flex flex-col gap-2">
                                <div>
                                    <label class="text-xs text-gray-500">From</label>
                                    <input type="datetime-local" x-model="jobModalForm.schedule_from"
                                           class="w-full border rounded px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">To</label>
                                    <input type="datetime-local" x-model="jobModalForm.schedule_to"
                                           class="w-full border rounded px-3 py-2 text-sm">
                                </div>
                                <div class="text-xs text-gray-500">Timezone: EDT</div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">Dispatch</label>
                                <input type="text" x-model="jobModalForm.dispatch" placeholder="Dispatch by name or tag"
                                       class="w-full border rounded px-3 py-2 text-sm">
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="jobModalForm.notify_customer" class="form-checkbox">
                                <label class="text-sm">Notify customer</label>
                            </div>
                        </div>
                    </div>

                    <!-- Center Column -->
                    <div class="w-full lg:w-2/3 space-y-6">
                        <div class="border p-4 rounded">
                            <label class="block text-sm font-medium mb-2">Line items</label>
                            <template x-for="(item, index) in jobModalForm.items" :key="index">
                                <div class="mb-4 space-y-2 border-b pb-2">
                                    <div class="flex flex-wrap gap-2">
                                        <input x-model="item.name" type="text" placeholder="Item name"
                                               class="flex-1 border rounded px-3 py-2 text-sm">
                                        <input x-model="item.qty" type="number" step="0.01" min="0"
                                               class="w-20 border rounded px-2 py-1 text-sm" placeholder="Qty">
                                        <input x-model="item.unit_price" type="number" step="0.01" min="0"
                                               class="w-24 border rounded px-2 py-1 text-sm" placeholder="Unit price">
                                        <input x-model="item.unit_cost" type="number" step="0.01" min="0"
                                               class="w-24 border rounded px-2 py-1 text-sm" placeholder="Unit cost">
                                        <label class="flex items-center gap-1 text-sm">
                                            <input type="checkbox" x-model="item.tax" class="form-checkbox">
                                            Tax
                                        </label>
                                    </div>
                                    <textarea x-model="item.description" placeholder="Description (optional)"
                                              class="w-full border rounded px-3 py-2 text-sm"></textarea>
                                </div>
                            </template>
                            <div class="flex gap-4">
                                <button @click="addItem('service')" class="text-blue-600 text-sm">+ Services item
                                </button>
                                <button @click="addItem('material')" class="text-blue-600 text-sm">+ Materials item
                                </button>
                            </div>
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
                            <button @click="jobModalForm.message = ''" class="text-blue-600 text-sm">+ Message</button>
                            <template x-if="jobModalForm.message !== null">
                            <textarea x-model="jobModalForm.message" class="w-full border rounded mt-2 px-3 py-2 text-sm"
                                      placeholder="Add a message..."></textarea>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button @click="$wire.call('saveJob', jobModalForm); jobModalOpen = false"
                            class="bg-blue-600 text-white px-6 py-2 rounded">Save job
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
