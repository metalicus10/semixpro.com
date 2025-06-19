<div
    x-data="scheduler"
    x-init="init(@js($employees))"
    class="w-full overflow-auto select-none bg-white text-sm text-gray-900 relative">

    <!-- Временная шкала -->
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200">
        <div class="flex">
            <div class="w-[140px] bg-gray-50 border-r border-gray-200 py-3 px-2 font-medium">GMT-04</div>
            <div class="grid grid-cols-[repeat(16,minmax(40px,1fr))] divide-x divide-gray-200">
                <template x-for="i in 16" :key="i">
                    <div class="h-12 w-10 text-xs flex items-center justify-center">
                        <span x-text="formatHour(i + 5)"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Основная сетка -->
    <div class="flex">
        <!-- Техники -->
        <div class="w-[140px] bg-white border-r border-gray-200">
            <template x-for="employee in employees" :key="employee.id">
                <div class="h-24 flex items-center gap-2 px-2 border-b border-gray-100">
                    <template x-if="employee.avatar">
                        <img :src="employee.avatar" class="w-8 h-8 rounded-full" alt="" />
                    </template>
                    <template x-if="!employee.avatar">
                        <div class="w-8 h-8 rounded-full bg-gray-300 text-xs font-semibold flex items-center justify-center">
                            <span x-text="initials(employee.name)"></span>
                        </div>
                    </template>
                    <span x-text="employee.name" class="text-gray-800 text-sm"></span>
                </div>
            </template>
        </div>

        <!-- Сетка и задачи -->
        <div class="flex-1 overflow-x-auto">
            <template x-for="employee in employees" :key="employee.id">
                <div class="relative h-24 border-b border-gray-100">

                    <!-- Сетка 30-минутных слотов: 32 слота на день (16 часов × 2) -->
                    <template x-for="(col, index) in 32" :key="index">
                        <div class="absolute top-0 bottom-0 border-r border-gray-100"
                             :style="`left: ${index * 20}px; width: 20px;`"
                             @click="
                                if (!selection || selection.technician_id !== employee.id || !selectionSlots.includes(index)) {
                                    clearSelection();
                                    selectSlot(index, employee.id);
                                }
                             "
                             @mousedown.prevent="
                                if ($event.button === 0) {
                                    clearSelection();
                                    selectSlot(index, employee.id);
                                }
                             "
                             @mouseover="if ($event.buttons === 1) selectSlot(index, employee.id)"
                             @contextmenu.prevent="showContextMenu($event, index, employee.id)"
                             @touchstart.passive="startLongpress($event, index, employee.id)"
                             @touchend="cancelLongpress()"
                             :class="slotClass(index, employee)">
                        </div>
                    </template>

                    <!-- Задачи -->
                    <template x-for="task in employee.tasks" :key="task.id">
                        <div class="absolute bg-blue-600 text-white text-xs rounded shadow px-2 py-1 flex flex-col justify-center"
                             :style="taskPosition(task)"
                             x-init="$nextTick(() => registerDraggable($el, task))">
                            <div class="font-semibold truncate" x-text="task.title"></div>
                            <div class="text-[11px] opacity-80" x-text="formatRange(task.start_time, task.end_time)"></div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
        <!-- Контекстное меню -->
        <div
            x-show="menuVisible"
            class="absolute z-50 bg-white shadow border rounded w-40"
            :style="`top: ${menuY}px; left: ${menuX}px`"
        >
            <button @click="openModal('job')" class="w-full px-4 py-2 hover:bg-gray-100 text-left">+ Job</button>
        </div>
    </div>
</div>

<script>
    const scheduler = {
        employees: [],
        selection: null,
        menuVisible: false,
        menuX: 0,
        menuY: 0,
        selectionSlots: [],
        longpressTimeout: null,

        init(data) {
            this.employees = data;
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
            // Сброс только если клик не по текущему выделению
            if (!this.selection || this.selection.technician_id !== technician_id || !this.selectionSlots.includes(index)) {
                this.clearSelection();
                this.selection = { technician_id, start: index, end: index };
                this.updateSelectionSlots();
            }
            // Если клик по выделению — просто показываем меню, ничего не сбрасываем!
            // (этот блок вызывается всегда)
            const grid = this.$root.querySelector('.overflow-x-auto');
            const rect = grid.getBoundingClientRect();
            this.menuX = event.clientX - rect.left + grid.scrollLeft;
            this.menuY = event.clientY - rect.top + grid.scrollTop;
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
                        Livewire.emit('updateTaskPosition', task.id, event.pageX);
                    }
                });
            }
        }
    }
</script>
