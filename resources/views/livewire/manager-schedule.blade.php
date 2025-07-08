<div x-data="{
    employees: [],
    selection: null,
    jobModalOpen: false,
    showAddCustomerModal: false,
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
        customer_query: '',
        employees_query: '',
        results: [],
        employees: [],
        employees_results: [],
        selectedCustomer: null,
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
            address: '',
        },
    },
    showCustomerModal: false,
    showEmployeesDropdown: false,
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
                this.scrollToToday();
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
            startSelect(event, technician_id, dayIdx, date, slotIdx, isTouch = false) {
                this.selection = {
                    technician_id,
                    dayIdx,
                    date,
                    startSlot: slotIdx,
                    endSlot: slotIdx,
                };
                if (isTouch) {
                    this.menuX = event.pageX;
                    this.menuY = event.pageY;
                }
                this.menuVisible = false;
            },
            dragSelect(dayIdx, slotIdx) {
                if (!this.selection || this.selection.dayIdx !== dayIdx) return;
                this.selection.endSlot = slotIdx;
            },
            endSelect() {
                if (!this.selection) return;
                this.mouseDown = false;
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
            isSelected(dayIdx, slotIdx) {
                if (!this.selection || dayIdx !== this.selection.dayIdx) return false;
                let [from, to] = [this.selection.start, this.selection.end].sort((a,b)=>a-b);
                return slotIdx >= from && slotIdx <= to;
            },
            showContextMenu($event, dayIdx, slotIdx, technician_id) {
                $event.preventDefault();
                if (!this.selection || this.selection.technician_id !== technician_id || !this.selectionSlots.includes(slotIdx)) {
                    this.clearSelection();
                    this.selection = { technician_id, start: slotIdx, end: slotIdx };
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
        if (type) this.jobModalType = type;
        this.jobModalOpen = true;

        const slotDuration = 30;   // 30 минут
        const minHour = 6;         // 6 утра
        const baseDate = dayjs(this.selection.date, 'DD.MM.YYYY').startOf('day').toDate();

        const startSlot = Math.min(this.selection.start, this.slotsPerDay - 1);
        const endSlot   = Math.min(this.selection.end, this.slotsPerDay - 1);

        const startMinutes = minHour * 60 + startSlot * slotDuration;
        const endMinutes   = minHour * 60 + (endSlot + 1) * slotDuration;

        const from = new Date(baseDate);
        from.setMinutes(startMinutes);
        const to = new Date(baseDate);
        to.setMinutes(endMinutes);

        // Если to.getDate() !== from.getDate(), ограничь время
        if (to.getDate() !== from.getDate()) {
            to.setDate(from.getDate());
            to.setHours(23, 59, 0, 0); // или до 22:00, если у тебя последний слот — 22:00
        }

        function toInputDate(d) {
            return `${d.getFullYear()}-${(d.getMonth() + 1).toString().padStart(2, '0')}-${d.getDate().toString().padStart(2, '0')}`;
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

        // Остальное — как раньше
        const technicianName = this.employees.find(e => e.id === this.selection.technician_id)?.name || '';
        this.jobModalForm.dispatch = technicianName;
        this.menuVisible = false;

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
    formatTime(slotIdx) {
        let h = 6 + Math.floor(slotIdx / 2);
        let m = slotIdx % 2 === 0 ? '00' : '30';
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${h}:${m} ${ampm}`;
    },
    formatTime12(time) {
        if (!time) return '';
        let [h, m] = time.split(':');
        h = parseInt(h);
        let suffix = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${h}:${m}`;
    },
    buildDatetime(date, time, ampm) {
        let [h, m] = time.split(':');
        h = parseInt(h);
        if (ampm === 'PM' && h < 12) h += 12;
        if (ampm === 'AM' && h === 12) h = 0;
        h = h.toString().padStart(2, '0');
        return `${date}T${h}:${m}:00`;
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

                $wire.createCustomer(customer);
                this.showAddCustomerModal = false;
                this.jobModalForm.new_customer = { name: '', email: '', phone: '', address: '' };
            },
            prefill(data) {
                this.jobModalForm.schedule_from = data.schedule_from;
                this.jobModalForm.schedule_to = data.schedule_to;
                this.jobModalForm.dispatch = data.dispatch;
                this.open = true;
            },
            addItem(type) {
                this.jobModalForm.items.push({
                    id: Date.now() + Math.random(),
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
            },
            formatMoney(val) {
                return '$' + Number(val).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            },
            removeItem(id) {
                this.jobModalForm.items = this.jobModalForm.items.filter(item => item.id !== id);
            },
            scrollToToday() {
                this.$nextTick(() => {
                    const todayCol = document.getElementById('today-column');
                    const grid = this.$refs.mainGrid;
                    if (todayCol && grid) {
                        // todayCol.offsetLeft - положение столбца
                        grid.scrollLeft = todayCol.offsetLeft - 0;
                    }
                });
            },
            searchCustomers() {
                if (this.jobModalForm.customer_query.length < 2) {
                    this.jobModalForm.results = [];
                    return;
                }
                $wire.call('searchCustomers', this.jobModalForm.customer_query);
            },
            selectCustomer(customer) {
                if (!customer || !customer.id) {
                    this.selectedCustomer = null;
                    this.jobModalForm.customer_id = null;
                    this.jobModalForm.customer_query = '';
                    this.showCustomerModal = false;
                    return;
                }
                this.jobModalForm.customer_query = customer.name + (customer.email ? ' ('+customer.email+')' : '');
                this.jobModalForm.customer_id = customer.id;
                this.selectedCustomer = customer;
                this.showCustomerModal = false;
                $dispatch('customer-selected', customer);
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
}" x-init="init(@js($employees)); scrollToToday();"
>
    <div class="select-none bg-white text-sm text-gray-900">
        <div class="flex items-center gap-2 bg-white px-4 py-2 sticky top-0 z-30">
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
        <!-- Sticky колонка -->
        <div
            class="absolute left-[10px] z-30 bg-white w-[60px] flex-shrink-0 flex flex-col border-y border-gray-400">
            <!-- GMT и техники -->
            <div class="w-[60px] h-[85px] flex-shrink-0 flex items-end justify-center bg-gray-50 text-[11px] font-thin">
                GMT-04
            </div>
            <template x-for="employee in employees" :key="employee.id">
                <div
                    class="w-[60px] h-[64px] sticky left-0 z-10 flex-shrink-0 flex flex-col justify-center items-center gap-1 px-2 border-t border-gray-400 bg-white"
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
            </template>
        </div>
        <div class="select-none bg-white text-sm text-gray-900 relative overflow-x-auto pl-15 pb-2" x-ref="mainGrid">
            <!-- Временная шкала -->
            <div class="sticky top-0 z-10 bg-white">
                <div class="flex">
                    <template x-for="(d, dayIdx) in currentWeek" :key="d.format('YYYY-MM-DD')">
                        <div :id="d.isSame(dayjs(), 'day') ? 'today-column' : null"
                             class="text-start border-y border-gray-400 day-right-border w-[640px] day-column" :data-date="d.format('YYYY-MM-DD')"
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
                                        <div
                                            class="h-12 w-[40px] text-[11px] border-b border-gray-100 font-thin flex items-center justify-center">
                                            <span x-text="formatHour(i + 5)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <template x-for="employee in employees" :key="employee.id">
                                <div class="flex w-[640px] flex-col relative min-h-[64px] border-gray-100">
                                    <!-- Правая часть — сетка и задачи -->
                                    <div id="mainGrid" class="relative">
                                        <template x-if="selection && selection.technician_id === employee.id">
                                            <div
                                                class="absolute bg-blue-200 bg-opacity-70 rounded z-10 pointer-events-none"
                                                :style="selectionHighlightStyle(employee)"
                                            >
                                                <!-- Время с - по -->
                                                <div
                                                    class="absolute left-1 top-1 text-[8px] text-shadow-2xs text-shadow-white text-black font-thin drop-shadow"
                                                    x-text="selectionTimeRange"></div>
                                            </div>
                                        </template>
                                        <!-- Сетка 30-минутных слотов -->
                                        <div class="flex relative w-[640px]">
                                            <!-- 48 слотов по 30 минут -->
                                            <template x-for="slotIdx in 32" :key="slotIdx">
                                                <div
                                                    :class="{
                                                        'bg-blue-100': selection && selection.dayIdx === dayIdx && slotIdx-1 >= selection.startSlot && slotIdx-1 <= selection.endSlot
                                                    }"
                                                    class="flex left-0 border-r border-gray-100 cursor-pointer"
                                                    :style="'top: ' + ((slotIdx-1)*16) + 'px; height: 64px; width: 20px;'"
                                                    @mousedown.prevent="startSelect($event, employee.id, dayIdx, d.format('YYYY-MM-DD'), slotIdx-1)"
                                                    @mouseenter="dragSelect(dayIdx, slotIdx-1)"
                                                    @mouseup.window="endSelect()"
                                                    @contextmenu.prevent="showContextMenu($event, dayIdx, employee.id)"
                                                    @touchstart.passive="startLongPress($event, dayIdx, slotIdx-1)"
                                                    @touchend="cancelLongPress()"
                                                ></div>
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
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5"
                     viewBox="0 0 24 24">
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

        <div x-show="jobModalOpen"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[95vh]">
                <div class="flex justify-between items-center border-b pb-4 mb-6">
                    <h2 class="text-xl font-semibold">New job</h2>
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
                                <div x-show="showCustomerModal && jobModalForm.results.length" class="absolute top-full min-w-full max-w-full bg-white z-30 border rounded shadow mt-1">
                                    <template x-for="customer in jobModalForm.results">
                                        <div
                                            @click="selectCustomer(customer)"
                                            class="block px-2 py-1 hover:bg-gray-100 cursor-pointer"
                                            x-text="customer.name + ' ' + (customer.email || '')"
                                        >
                                        </div>
                                    </template>
                                </div>
                                <div x-show="showCustomerModal && jobModalForm.customer_query.length > 1 && jobModalForm.results.length === 0"
                                     class="block hover:bg-gray-100 px-2 py-1 text-gray-400 bg-white z-30 border shadow rounded mt-1">Ничего не найдено</div>

                            </div>
                            <button type="button" class="text-blue-600 text-xs mt-2" @click="showAddCustomerModal = true">+
                                New customer
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
                                                    // Если нужна двусторонняя связь с jobModalForm:
                                                    $dispatch('time-changed', { value: this.value });
                                                },
                                                setFromExternal(val) {
                                                    // Позволяет задавать значение извне, например при открытии модалки
                                                    if (!val) return;
                                                    let [time, ampm] = val.split(' ');
                                                    let [h, m] = time.split(':');
                                                    this.hour = parseInt(h);
                                                    this.minute = m;
                                                    this.ampm = ampm || 'AM';
                                                }
                                            }"
                                            x-init="setFromExternal(jobModalForm.schedule_from_time12)"
                                            @time-changed.window="jobModalForm.schedule_from_time12 = $event.detail.value"
                                            class="relative w-36"
                                        >
                                            <button type="button"
                                                    @click="show = !show"
                                                    class="w-full px-3 py-2 border rounded focus:outline-none flex items-center justify-between"
                                            >
                                                <span x-text="value"></span>
                                                <svg class="w-4 h-4 ml-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <div x-show="show"
                                                 @click.away="show = false"
                                                 class="absolute z-10 bg-white rounded shadow-md mt-1 p-2 flex gap-2"
                                                 style="min-width: 210px"
                                            >
                                                <!-- Часы -->
                                                <select x-model="hour"
                                                        class="border rounded p-1">
                                                    <template x-for="h in 12" :key="h">
                                                        <option :value="h" x-text="h"></option>
                                                    </template>
                                                </select>
                                                <!-- Минуты -->
                                                <select x-model="minute"
                                                        class="border rounded p-1">
                                                    <template x-for="m in [0, 15, 30, 45]" :key="m">
                                                        <option :value="m.toString().padStart(2, '0')" x-text="m.toString().padStart(2, '0')"></option>
                                                    </template>
                                                </select>
                                                <!-- AM/PM -->
                                                <select x-model="ampm" class="border rounded p-1">
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

                                        <input type="hidden" x-model="jobModalForm.schedule_from_time" name="schedule_from_time12">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <label class="block text-xs text-gray-500 mb-1 w-10">To</label>
                                    <div class="flex gap-2 items-center">
                                        <input
                                            type="date"
                                            :value="jobModalForm.schedule_to_date"
                                            @input="jobModalForm.schedule_to_date = $event.target.value"
                                            class="border rounded px-2 py-1"
                                        >
                                        <input
                                            type="time"
                                            :value="jobModalForm.schedule_to_time"
                                            @input="jobModalForm.schedule_to_time = $event.target.value"
                                            class="border rounded px-2 py-1"
                                            step="900"
                                        >
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
                        <div class="border p-4 rounded">
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
                                                                   placeholder=" "
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
                                                                   min="0"
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
                                                               min="0"
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
                                                        <span
                                                            x-text="formatMoney(item.qty * item.unit_price || 0)"></span>
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

                                                    <!-- Service Name + Tax -->
                                                    <div class="flex-1 flex flex-col w-3/5">
                                                        <label class="sr-only">Material name</label>
                                                        <div class="relative flex items-center">
                                                            <input x-model="item.name" type="text"
                                                                   placeholder="Material name"
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
                                                                   placeholder=" "
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
                                                                   min="0"
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
                                                               min="0"
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
                                                        <span
                                                            x-text="formatMoney(item.qty * item.unit_price || 0)"></span>
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
                    <button @click="$wire.call('saveJob', jobModalForm); jobModalOpen = false"
                            class="bg-blue-600 text-white px-6 py-2 rounded">Save job
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
                            class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>
                <form @submit.prevent="saveNewCustomer">
                    <template x-if="customerError">
                        <div class="mb-2 text-red-600 text-xs" x-text="customerError"></div>
                    </template>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Name*</label>
                        <input type="text" x-model="jobModalForm.new_customer.name" required class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" x-model="jobModalForm.new_customer.email" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Phone</label>
                        <input type="text" x-model="jobModalForm.new_customer.phone" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Address</label>
                        <input type="text" x-model="jobModalForm.new_customer.address" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="showAddCustomerModal = false" class="px-4 py-2 text-sm bg-gray-100 rounded hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Add</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
