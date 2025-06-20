<div x-data="{
            employees: [],
            selection: null,
            menuVisible: false,
            menuX: 0,
            menuY: 0,
            selectionSlots: [],
            longpressTimeout: null,
            weekStart: dayjs().startOf('week').add(1, 'day'),
            currentWeek: [],
            viewDate: dayjs().startOf('day'),

            init(data) {
                this.employees = data;
                this.setWeek(this.viewDate);
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
                }, 600); // 600ms удержания
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
            openModal(type) {
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

                if (window.createJobModalInstance) {
                    window.createJobModalInstance.prefill({
                        schedule_from: start.toISOString().slice(0, 16),
                        schedule_to: end.toISOString().slice(0, 16),
                        dispatch: technicianName
                    });
                    window.createJobModalInstance.open = true;
                }

                this.clearSelection();
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
        }"
     x-init="init(@js($employees))">
    <div x-ref="mainGrid"
         class="w-full select-none bg-white text-sm text-gray-900 relative overflow-x-auto">

        <div class="flex items-center gap-2 bg-white border-b px-4 py-2 sticky top-0 z-20">
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
        <div class="sticky top-0 z-10 bg-white border-b border-gray-200">
            <div class="flex border-b">
                <div class="w-[60px] h-[100px] flex-shrink-0 flex flex-col border-r border-gray-200">
                    <!-- Первая строка: для выравнивания по высоте с датой -->
                    <div class="h-12 bg-gray-50"></div>
                    <!-- Вторая строка: временной пояс, прижат вниз -->
                    <div class="h-20 bg-gray-50 flex items-end justify-center text-[11px] font-thin">
                        GMT-04
                    </div>
                </div>
                <template x-for="(d, dayIdx) in currentWeek" :key="d.format('YYYY-MM-DD')">
                    <div class="text-start ">
                        <div class="flex gap-1 items-baseline p-3">
                            <div class="text-xl font-medium" x-text="d.format('D')"></div>
                            <div class="text-xs font-thin" x-text="d.format('ddd')"></div>
                        </div>
                        <div class="flex">
                            <div class="grid grid-cols-[repeat(16,minmax(40px,1fr))] divide-x divide-gray-200">
                                <template x-for="i in 16" :key="i">
                                    <div class="h-12 w-10 text-[11px] font-thin flex items-center justify-center">
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
            }" x-init="setSlotHeight()" @resize.window="setSlotHeight" class="flex">
            <!-- Сетка и задачи -->
            <div class="flex flex-col flex-1 relative">
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
                        <div id="mainGrid" class="relative flex-1">
                            <template x-if="selection && selection.technician_id === employee.id">
                                <div
                                    class="absolute bg-blue-200 bg-opacity-70 rounded z-10 pointer-events-none"
                                    :style="selectionHighlightStyle(employee)"
                                ></div>
                            </template>
                            <!-- Сетка 30-минутных слотов -->
                            <div :style="`width: ${currentWeek.length * 32 * 20}px; height: 100%; position: relative;`">
                                <template x-for="(col, index) in 224" :key="index">
                                    <div class="absolute top-0 bottom-0 border-r border-gray-100"
                                         :class="slotClass(index, employee)"
                                         :style="`left: ${index * 20}px; width: 20px;height: ${slotHeight}px;`, selectionHighlightStyle(employee)"
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
            @click="openModal('job'); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M10 17v-3m4 3v-3m2-4h-8m4-6v2m0 0V3m0 2.5l.91 2.27m0 0a5.75 5.75 0 1 1-5.82 0"/>
            </svg>
            <span class="font-medium">+Job</span>
        </button>
        <button
            @click="openModal('estimate'); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 9h8M8 13h6" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span class="font-medium">+Estimate</span>
        </button>
        <button
            @click="openModal('event'); closeMenu()"
            class="flex items-center gap-2 w-full px-4 py-1 text-left hover:bg-blue-50 rounded transition"
        >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="3" stroke-width="1.5" stroke="currentColor"/>
                <path d="M7 8h10M12 13v5" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span class="font-medium">+Event</span>
        </button>
    </div>
</div>
