{{-- resources/views/livewire/job-scheduler.blade.php --}}
<div x-data="viewportPanel()" x-ref="panel"
     class="overflow-auto"
     :style="`height:${h}px`">

    <div
        x-data="scheduler()"
        @mouseup.window="$event.button === 0 && endSelection()"
        @unique-slot-error.window="alert($event.detail[0].message)"
        @interval-overlap-error.window="alert($event.detail[0].message)"
        class="bg-white text-gray-800 border"

    >
        <div class="sticky top-0 z-40 flex items-center justify-between px-3 py-2 bg-white shadow-md">
            <div class="flex items-center gap-2">
                <button type="button"
                        class="px-2 py-1 rounded-3xl bg-white hover:bg-[#e7fdef] border-2 border-brand-accent font-bold text-[12px] text-brand-accent"
                        @click="goToday; $dispatch('week:changed')">Today
                </button>
                <button type="button" class="px-2 py-1 rounded hover:shadow"
                        @click="prev; $dispatch('week:changed')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 4.5L7.5 12L15 19.5"
                              stroke="currentColor" stroke-width="2.25"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button type="button" class="px-2 py-1 rounded hover:shadow"
                        @click="next; $dispatch('week:changed')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M9 4.5L16.5 12L9 19.5"
                              stroke="currentColor" stroke-width="2.25"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <!-- Диапазон дат недели -->
                <div class="text-sm font-medium text-gray-700 font-[LufgaSemiBold]"
                     x-text="headerLabel">
                </div>
                <span class="ml-3 text-sm text-gray-500" x-text="isCurrentWeek() ? 'Current week' : ''"></span>
            </div>

            <div class="flex items-center gap-2">
                <button @click="showCalendar; destroyMap()"
                        x-bind:class="mode === 'schedule' ? 'bg-brand-accent text-white' : 'bg-gray-100'"
                        class="px-3 py-1 rounded"
                >
                    <span class="ms-2">Calendar</span>
                </button>
                <button @click="showMap"
                        x-bind:class="mode === 'map' ? 'bg-brand-accent text-white' : 'bg-gray-100'"
                        class="px-3 py-1 rounded"
                >
                    <span class="ms-2">Map</span>
                </button>
            </div>

            <!-- правый блок шапки, рядом с выводом диапазона недели -->
            <div class="relative" x-data="{ open:false }">
                <button
                    @click="open = !open"
                    class="px-3 py-1 rounded-2xl bg-white border-2 border-brand-accent font-bold text-[12px] text-brand-accent hover:bg-[#e7fdef]"
                    :class="{ 'bg-blue-600 text-white': mapView === 'day' }"
                >
                    <span class="text-brand-accent" x-text="mapView === 'day' ? 'Day' : 'Week'"></span>
                    <svg class="inline -mt-0.5 ml-1 h-4 w-4 opacity-70 text-brand-accent font-bold" viewBox="0 0 20 20"
                         fill="currentColor">
                        <path
                            d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"/>
                    </svg>
                </button>

                <div
                    x-show="open" x-transition
                    @click.outside="open=false" @keydown.escape.window="open=false"
                    class="absolute flex flex-col right-0 mt-1 w-40 rounded-lg bg-white shadow z-50"
                >
                    <button
                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 rounded-lg"
                        :class="mapView === 'week' ? 'bg-blue-50 text-blue-700' : ''"
                        @click="setMapView('week'); open=false"
                    >
                        Week
                    </button>
                    <button
                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 rounded-lg"
                        :class="mapView === 'day' ? 'bg-blue-50 text-blue-700' : ''"
                        @click="setMapView('day'); open=false"
                    >
                        Day
                    </button>
                </div>
            </div>

            <!-- Scheduler settings -->
            <div class="relative">
                <!-- Кнопка-шестерёнка -->
                <button type="button"
                        class="ml-3 inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-700 shadow hover:bg-gray-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600"
                        title="Calendar settings"
                        @click="openSettings">
                    <!-- иконка -->
                    <svg viewBox="0 0 20 20" class="h-5 w-5" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                              d="M11.983 1.58a1 1 0 0 0-1.966 0l-.16.8a7.968 7.968 0 0 0-1.44.594l-.74-.427a1 1 0 1 0-1 1.732l.692.4a8.054 8.054 0 0 0-1.03 1.03l-.4-.692a1 1 0 1 0-1.732 1l.427.74c-.233.46-.429.953-.594 1.44l-.8.16a1 1 0 1 0 0 1.966l.8.16c.165.487.361.98.594 1.44l-.427.74a1 1 0 1 0 1.732 1l.4-.692c.33.384.676.73 1.03 1.03l-.692.4a1 1 0 1 0 1 1.732l.74-.427c.46.233.953.429 1.44.594l.16.8a1 1 0 1 0 1.966 0l.16-.8c.487-.165.98-.361 1.44-.594l.74.427a1 1 0 1 0 1-1.732l-.692-.4c.384-.33.73-.676 1.03-1.03l.4.692a1 1 0 1 0 1.732-1l-.427-.74c.233-.46.429-.953.594-1.44l.8-.16a1 1 0 1 0 0-1.966l-.8-.16a7.968 7.968 0 0 0-.594-1.44l.427-.74a1 1 0 1 0-1.732-1l-.4.692a8.054 8.054 0 0 0-1.03-1.03l.692-.4a1 1 0 1 0-1-1.732l-.74.427a7.968 7.968 0 0 0-1.44-.594l-.16-.8ZM10 7a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z"
                              clip-rule="evenodd"/>
                    </svg>
                </button>

                <template x-teleport="body">
                    <div x-show="schedulerSettingsOpenState" x-transition.opacity class="fixed inset-0 z-[1000]"
                         aria-modal="true" role="dialog"
                         @keydown.escape.window="closeSettings">
                        <div class="absolute inset-0 bg-black/50" @click="close()"></div>

                        <div
                            class="relative mx-auto mt-20 w-full max-w-xl rounded-lg bg-white p-5 shadow-xl dark:bg-slate-900 dark:text-slate-100"
                            @click.stop>
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-lg font-semibold">Scheduler settings</h2>
                                <button class="rounded p-1 hover:bg-black/5 dark:hover:bg-white/10"
                                        @click="closeSettings" aria-label="Close">
                                    ✕
                                </button>
                            </div>

                            <div class="space-y-5">
                                <!-- Time zone -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium">Time zone</label>
                                    <select x-model="form.tz"
                                            class="w-full rounded border-gray-300 text-sm dark:border-slate-700 dark:bg-slate-800">
                                        <template x-for="z in timezones" :key="z.value">
                                            <option :value="z.value" x-text="z.label"
                                                    :selected="form.tz === z.value"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Toggles -->
                                <div class="space-y-2">
                                    <div class="flex gap-3">
                                        <label for="toggle-hours-settings"
                                               class="relative inline-flex h-6 w-10 cursor-pointer select-none items-center">
                                            <input
                                                id="toggle-hours-settings"
                                                type="checkbox"
                                                class="peer sr-only"
                                                aria-label="Toggle"
                                                x-model="form.onlyBusiness"
                                            >
                                            <!-- Трек -->
                                            <span
                                                class="absolute inset-0 rounded-full
                                             bg-slate-600 transition-colors duration-200
                                             peer-checked:bg-blue-600
                                             peer-focus-visible:outline peer-focus-visible:outline-2
                                             peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue-500
                                             peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></span>
                                            <!-- Бегунок -->
                                            <span
                                                class="relative ml-0.5 h-5 w-5 rounded-full bg-white shadow
                                             transition-transform duration-200
                                             translate-x-0 peer-checked:translate-x-4">
                                        </span>
                                        </label>
                                        <span>Only display business hours</span>
                                    </div>

                                    <div class="flex gap-3">
                                        <label for="toggle-holidays-settings"
                                               class="relative inline-flex h-6 w-10 cursor-pointer select-none items-center">
                                            <input
                                                id="toggle-holidays-settings"
                                                type="checkbox"
                                                class="peer sr-only"
                                                aria-label="Toggle"
                                                x-model="form.usHolidays"
                                            >
                                            <!-- Трек -->
                                            <span
                                                class="absolute inset-0 rounded-full
                                             bg-slate-600 transition-colors duration-200
                                             peer-checked:bg-blue-600
                                             peer-focus-visible:outline peer-focus-visible:outline-2
                                             peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue-500
                                             peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></span>
                                            <!-- Бегунок -->
                                            <span
                                                class="relative ml-0.5 h-5 w-5 rounded-full bg-white shadow
                                             transition-transform duration-200
                                             translate-x-0 peer-checked:translate-x-4">
                                        </span>
                                        </label>
                                        <span>Display US holidays</span>
                                    </div>
                                </div>

                                <!-- Scheduler items options -->
                                <div>
                                    <div class="mb-2 text-sm font-medium">Scheduler items options</div>
                                    <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                                        <template x-for="(label, key) in labels" :key="key">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" x-model="form.fields[key]"
                                                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                                                <span x-text="label"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50
                         dark:border-slate-700 dark:hover:bg-slate-800"
                                        @click="resetToDefaults">Reset
                                </button>
                                <button
                                    class="rounded bg-emerald-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-emerald-700"
                                    @click="await saveSettings">Done
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="mode==='schedule' && mapView==='week'">
            <div class="overflow-x-auto pb-[10px]">
                <!-- Глобальный оверлей спиннера -->

                {{-- Заголовок --}}
                <div class="inline-flex items-center border-b border-b-gray-400">
                    {{-- Первая узкая ячейка для таймзоны или иконки --}}
                    <div
                        class="flex justify-center items-center left-0 z-30 w-32 h-[61px] flex-shrink-0 p-0 text-sm font-medium text-center bg-gray-50 day-right-border">
                        GMT -04
                    </div>
                    {{-- Дни недели с часами --}}
                    <div class="flex-1 inline-flex">
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
                <template x-for="(employee, empIdx) in employees" :key="employee.id">
                    <div class="inline-flex items-start border-b border-b-gray-300 group" :data-emp="employee.id">
                        {{-- Колонка с аватаром и именем --}}
                        <div
                            class="sticky left-0 z-20 w-32 h-[61px] flex-shrink-0 flex items-center p-2 space-x-2 bg-gray-50 day-right-border">
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
                                     :class="{ 'day-left-border': days[0] !== day }" :data-day="day.date"
                                     :data-emp="employee.id">
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

                                    <!-- holiday overlay -->
                                    <template x-if="settings.usHolidays && holidays.has(day.date)">
                                        <div class="absolute inset-0 pointer-events-none">
                                            <div class="w-full h-full bg-orange-100/40"></div>
                                            <!-- тонкая полоска сверху с названием праздника -->
                                            <template x-if="empIdx === 0">
                                                <div class="absolute left-0 top-0 h-5 px-2 text-[11px] text-gray-700
                                                bg-orange-200/80 z-10 flex items-center">
                                                    <span x-text="holidayName(day.date)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Задачи --}}
                                    <template x-for="task in dayTasks(employee.id, day)"
                                              :key="`t-${task.id}-${employee.id}-${day}`">
                                        <div
                                            class="absolute top-1 h-14 bg-green-500 text-white text-[11px] rounded shadow cursor-move px-1 flex items-center space-x-1 z-30 border border-emerald-600"
                                            :class="{
                                                    'pointer-events-none opacity-60': isTaskPast(task),
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

        <!-- ДНЕВНАЯ СЕТКА -->
        <div x-show="mode==='schedule' && mapView==='day'">
            <template x-if="settings.usHolidays && holidays.has(currentDayISO)">
                <div
                    class="top-0 z-20 bg-orange-200/80 text-gray-800 text-xs px-2 py-1 border-b border-orange-300"
                    :title="holidayName(currentDayISO)">
                    <span x-text="holidayName(currentDayISO)"></span>
                </div>
            </template>

            <div
                class="flex items-center top-0 z-10 h-[20px] w-full border-b border-gray-300 day-grid-top-panel-border">
                <div class="flex w-[50px] h-full text-[9px] justify-center items-center px-0 border-r border-gray-300">
                    GMT-04
                </div>
                <div class="flex-1"></div>
            </div>

            <div id="dayGrid" class="relative bg-white pb-3"
                 :style="`height:${dayGridHeight}px`">
                <div class="relative" :style="`min-height:${dayGridHeight}px`">
                    <div
                        class="absolute left-[49px] top-0 bottom-0 border-r border-gray-300 cursor-not-allowed"
                        style="z-index: 5"
                        :style="{
                              top: 0,
                              height: `${(dayEndHour - dayStartHour) * 60 * pxPerMin}px`,
                            }"
                        @mousedown.stop
                        @click.stop
                        @touchstart.stop
                    ></div>

                    <!-- маска 12:00am – 12:30am -->
                    <template x-if="!settings.onlyBusiness">
                        <div
                            class="absolute left-[50px] right-0 bg-gray-200/60 cursor-not-allowed"
                            style="z-index: 5"
                            :style="`top: 0; height: ${30 * pxPerMin}px`"
                            @mousedown.stop @click.stop @touchstart.stop
                            title="Недоступно для создания задач">
                        </div>
                    </template>

                    <!-- маска 11:30pm – 12:00am -->
                    <template x-if="!settings.onlyBusiness">
                        <div
                            class="absolute left-[50px] right-0 bg-gray-200/60 cursor-not-allowed"
                            style="z-index: 5"
                            :style="`top: ${(dayEndHour - dayStartHour) * 60 * pxPerMin - 30 * pxPerMin}px;
                                height: ${30 * pxPerMin}px`"
                            @mousedown.stop @click.stop @touchstart.stop
                            title="Недоступно для создания задач">
                        </div>
                    </template>

                    <!-- маска 6:00–7:00 -->
                    <template x-if="settings.onlyBusiness">
                        <div
                            class="absolute left-[50px] right-0 bg-gray-200/60 cursor-not-allowed"
                            style="z-index: 5"
                            :style="{
                                  top: 0,
                                  height: `${(DAY_OPEN_HOUR - dayStartHour) * 60 * pxPerMin - 30}px`
                                }"
                            @mousedown.stop
                            @click.stop
                            @touchstart.stop
                            title="Недоступно для создания задач"
                        ></div>
                    </template>

                    <!-- маска 9:30PM – 10:00PM -->
                    <template x-if="settings.onlyBusiness">
                        <div
                            class="absolute left-[50px] right-0 bg-gray-200/60 cursor-not-allowed"
                            style="z-index: 5"
                            :style="{
                                  top: `${(dayEndHour - dayStartHour) * 60 * pxPerMin - 30}px`,
                                  height: `${30 * pxPerMin}px`,
                                }"
                            @mousedown.stop
                            @click.stop
                            @touchstart.stop
                            title="Недоступно для создания задач"
                        ></div>
                    </template>

                    <!-- ЧАСОВЫЕ линии + подпись -->
                    <template x-for="slot in hours" :key="slot.h">
                        <div>
                            <!-- толстая часовая линия -->
                            <div class="absolute left-10 right-0 border-t border-gray-300"
                                 :style="`top:${slot.top}px`"></div>

                            <!-- подпись по центру часа -->
                            <div class="absolute left-2 text-[11px] text-gray-500 select-none"
                                 :style="`top:${slot.center}px; transform:translateY(-50%);`"
                                 x-text="slot.label"></div>
                        </div>
                    </template>

                    <!-- ПОЛУЧАСОВЫЕ тонкие линии -->
                    <template x-for="hh in halfHours" :key="'h'+hh.top">
                        <div class="absolute left-10 right-0 border-t border-gray-200"
                             :style="`top:${hh.top}px`"></div>
                    </template>

                    <div class="absolute inset-x-0" :style="`top:0; bottom:0;height:100%;`"
                         @mouseleave="selecting && endSelectionDay()"
                    >
                        <template x-for="cell in dayCells" :key="cell.i">
                            <div
                                class="absolute left-12 right-0 border-b border-transparent select-none"
                                :style="`top:${cell.top}px; height:${cell.h}px;`"
                                :class="{
                                    'pointer-events-none':
                                      cell.min < DAY_OPEN_HOUR*60 ||
                                      cell.min >= dayEndHour*60
                                  }"
                            ></div>
                        </template>
                    </div>

                    <!-- Блоки задач -->
                    <template x-for="t in tasksForDay(currentDayISO)" :key="t.id">
                        <div
                            class="absolute left-14 right-3 rounded-md shadow-sm overflow-hidden bg-accent border:1px solid"
                            :class="{
                                'pointer-events-none opacity-60 bg-brand-accent': isTaskPast(t),
                                'cursor-pointer': !isTaskPast(t)
                            }"
                            :style="`top:${t._top}px;height:${t._height}px;`"
                            @click.stop="onTaskClick(t, $event)"
                        >
                            <div class="px-2 pt-1 text-xs font-medium truncate text-gray-50"
                                 x-text="t?.client?.name || t.message || 'Task'"></div>
                            <div class="px-2 pb-1 text-[11px] opacity-90 text-gray-50"
                                 x-text="`${to12Hour(t.start, t.day)} – ${to12Hour(t.end, t.day)}`"></div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        {{-- Спиннер --}}
        <div>
            <template x-if="isLoading">
                <div class="fixed inset-0 z-50 grid place-items-center bg-black/35 backdrop-blur-sm">
                    <div class="flex items-center gap-3 rounded-xl bg-white/90 px-5 py-3 shadow-xl">
                        <svg class="h-6 w-6 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Loading…</span>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="mode === 'map'" class="h-full rounded border overflow-hidden z-5"
             x-init="$watch('mode', v => { if (v === 'map') $nextTick(() => window.dispatchEvent(new Event('map:shown'))) })"
             id="jobsMap">
            <div class="relative h-[calc(100vh-140px)] min-h-[420px]">
                {{-- Карта --}}
                <div wire:ignore
                     x-init="
                        window.addEventListener('week:changed', async () => {
                            if (mode === 'map') {
                                await nextFrame();
                                await rerenderMap({ fit: true });
                            }
                        });
                     "
                     class="absolute inset-0 rounded border overflow-hidden z-10 h-full w-full" id="jobsMap"
                     x-ref="jobsMap"
                     x-transition></div>
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
             x-init="
                const pushSchedule = () => {
                  Livewire.dispatch('task-steps.schedule', {
                    from: jobModalForm.schedule_from_time12,
                    to:   jobModalForm.schedule_to_time12,
                  });
                };

                // 1) при открытии/первичной установке формы — вызови вручную там, где ты заполняешь jobModalForm
                pushSchedule();

                // 2) автообновление при изменении
                $watch('jobModalForm.schedule_from_time12', () => pushSchedule());
                $watch('jobModalForm.schedule_to_time12',   () => pushSchedule());
             "
             class="fixed inset-0 z-[45] flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[95vh]">
                <div class="flex justify-between items-center border-b pb-4 mb-6">
                    <h2 class="text-xl font-semibold" x-text="jobModalType === 'edit' ? 'Edit job' : 'New job'"></h2>
                    <button @click="jobModalOpen = false" class="text-gray-500 hover:text-red-500 text-2xl">&times;
                    </button>
                </div>

                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Left Column -->
                    <div class="w-full lg:w-1/3 space-y-4">
                        <!-- Customer -->
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
                            <button type="button" class="text-blue-600 text-xs mt-2"
                                    @click="showAddCustomerModal = true">
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
                                                                if(!val) return;

                                                                val = val.trim().toUpperCase();
                                                                val = val.replace(/(AM|PM)$/, ' $1');

                                                                const [time, ampm] = val.split(' ');
                                                                const [h, m] = time.split(':');

                                                                this.hour = parseInt(h, 10);
                                                                this.minute = m;
                                                                this.ampm = (ampm === 'PM' ? 'PM' : 'AM');
                                                            }
                                                        }"

                                            x-effect="setFromExternal(jobModalForm.schedule_from_time12)"
                                            @time-changed.window="updateTime($event.detail)"
                                            class="relative w-36"
                                        >
                                            <button type="button" @click="show = !show"
                                                    class="w-full px-2 py-1 border rounded focus:outline-none flex items-center justify-between"
                                            >
                                                <span x-text="value"></span>
                                                <svg class="w-4 h-4 ml-2 text-gray-400" fill="none"
                                                     stroke="currentColor"
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
                                                    if(!val) return;
                                                    val = val.trim().toUpperCase();
                                                    val = val.replace(/(AM|PM)$/, ' $1');
                                                    const [time, ampm] = val.split(' ');
                                                    const [h, m] = time.split(':');
                                                    this.hour = parseInt(h, 10);
                                                    this.minute = m;
                                                    this.ampm = (ampm === 'PM' ? 'PM' : 'AM');
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
                                                <svg class="w-4 h-4 ml-2 text-gray-400" fill="none"
                                                     stroke="currentColor"
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
                    <div class="w-full lg:w-2/3">
                        <div class="rounded space-y-4"
                             x-init="
                                $watch('jobModalForm.task_id', id => {
                                    if (id) { Livewire.dispatch('task-steps.switch', { id: Number(id) }) }
                                })
                             "
                        >
                            <livewire:task-steps :task-id="0" :key="'task-steps_key'"/>
                        </div>

                        <div class="space-y-4">
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
                                                              class="w-full rounded-lg h-[38px] text-sm border border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer overflow-y-auto">
                                                    </textarea>
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
                                                            <div class="relative flex flex-col"
                                                                 @click.outside="hideList(item)">
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
                                                                                                    version="1.1"
                                                                                                    width="56"
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
                                                                                    <div
                                                                                        class="flex items-center gap-2">
                                                                                            <span
                                                                                                class="font-medium truncate"
                                                                                                x-text="p.name"></span>
                                                                                        <span
                                                                                            class="text-xs text-gray-500"
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
                                                                <p x-show="item.warn.qty"
                                                                   class="mt-1 text-xs text-rose-600"
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
                                                                              placeholder="Description (optional)"
                                                                              rows="1"
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
                </div>

                <div class="flex justify-end mt-6">
                    <button @click="onSubmit(jobModalForm); jobModalOpen = false"
                            class="px-5 py-2 rounded-xl bg-brand-accent text-white"
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
                        <template x-if="settings?.fields?.job_number">
                            <div class="font-semibold text-gray-900 truncate" x-text="popover.job?.title"></div>
                        </template>
                        <template x-if="settings?.fields?.date">
                            <div class="text-[10px] text-gray-500"
                                 x-text="dayjs(popover.job?.day).format('MMM D YYYY')+' '+formatTime(popover.job?.start, popover.job?.end)"></div>
                        </template>
                    </div>
                </div>

                <!-- Описание -->
                <template x-if="settings?.fields?.description">
                    <div class="flex items-start gap-2" x-show="popover.job?.description">
                        <div class="shrink-0 mt-0.5" title="Job description">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                                <path d="M4 6h16M4 12h10M4 18h7" stroke="currentColor" stroke-width="1.5"
                                      stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="text-sm text-gray-700 whitespace-pre-wrap"
                             x-text="popover.job?.description  || '—'"></div>
                    </div>
                </template>

                <!-- Цена -->
                <template x-if="settings?.fields?.amount">
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
                </template>

                <!-- Клиент -->
                <template x-if="settings?.fields?.customer">
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
                </template>

                <!-- Техник -->
                <template x-if="settings?.fields?.technician">
                    <div class="flex items-center gap-2">
                        <div class="shrink-0" title="Technician">
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M12 6a3 3 0 110 6 3 3 0 010-6zm0 7c-3.866 0-7 2.239-7 5v1h14v-1c0-2.761-3.134-5-7-5z"
                                    stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <div class="text-sm text-gray-700">
                            <span class="font-medium text-gray-900" x-text="popover.job?.technician?.name"></span>
                        </div>
                    </div>
                </template>
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
            _onDragHandler: null,
            _onDropHandler: null,

            employees: @entangle('employees'),
            timeSlots: @entangle('timeSlots'),
            defaultTimeSlots: @entangle('defaultTimeSlots'),
            baseCount: @entangle('timeSlotsBaseCount'),
            dayStartHour: @entangle('dayStartHour'),
            dayEndHour: @entangle('dayEndHour'),
            tasks: @entangle('tasks'),
            settings: @entangle('settings'),
            now: new Date(),
            slotWidthPx: 30,
            rowHeightPx: 60,
            wrapCols: 0,
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
            pendingMapRefresh: false,
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

            weekStart: '',
            days: [],
            firstDay: 1,
            DAY_OPEN_HOUR: 7,
            DAY_HEADER_H: 0,
            HOLIDAY_H: 30,
            pxPerMin: 1,
            selecting: false,
            selStartIdx: null,
            selEndIdx: null,
            dayTimeSlots: [],

            APP_TZ: dayjs.tz.guess(),
            BUSINESS_START: 6,
            BUSINESS_END: 22,

            _reqId: 0,
            _fetching: false,

            holidays: new Map(),
            loadedHolidayYears: new Set(),

            schedulerSettingsOpenState: false,
            storageKey: 'scheduler.settings.v1',
            timezones: [
                {value: 'America/New_York', label: '(GMT-04:00) Eastern Time - New York'},
                {value: 'America/Chicago', label: '(GMT-05:00) Central Time - Chicago'},
                {value: 'America/Denver', label: '(GMT-06:00) Mountain Time - Denver'},
                {value: 'America/Los_Angeles', label: '(GMT-07:00) Pacific Time - Los Angeles'},
                {value: 'Asia/Vladivostok', label: '(GMT+10:00) Vladivostok Time'},
            ],
            labels: {
                job_number: 'Job number',
                description: 'Description',
                customer: 'Customer',
                street: 'Street',
                zip: 'Zip',
                team: 'Team',
                schedule: 'Schedule',
                amount: 'Amount',
                phone: 'Phone',
                city_state: 'City, State',
                arrival_window: 'Arrival window',
                job_tags: 'Job tags',
                date: 'Date',
                technician: 'Technician'
            },
            form: {},

            init() {
                this.invalidateLanes = () => {
                    this._lanesCache = {};
                };
                this.stopClock();

                this.setWeek(this.tz());

                this.loadSettings();
                this.applySettings(true);

                this.wrapCols = this.baseCount;

                this.$watch('settings.onlyBusiness', () => {
                    this.wrapCols = this.baseCount;
                    this.invalidateLanes();
                    this.queueFetch();
                });

                this.$watch('baseCount', v => this.wrapCols = v);

                this.$watch('settings', () => {
                });
                window.addEventListener('cal-settings:changed', (e) => {
                    this.settings = e.detail;
                    this.applySettings();
                });

                this.$watch('weekStart', () => this.queueFetch());
                this.$watch('currentDayISO', () => this.queueFetch());
                this.$watch('view', () => this.queueFetch());
                this.$watch('settings.usHolidays', () => this.queueFetch());

                if (!this.currentDayISO) this.currentDayISO = this.tz().format('YYYY-MM-DD');
                if (!this.weekStart) this.weekStart = this.startOfWeek(this.tz()).format('YYYY-MM-DD');

                this.$watch('tasks', () => this.invalidateLanes());

                /*this.$watch('mapView', () => {
                    if (this.mapView === 'day') this.$nextTick(() => this.renderDayGrid());
                });*/

                this.fetchForCurrentView().finally(() => {
                    this.startClock();
                });

                window.addEventListener('map:shown', () => {
                    try {
                        // когда вкладка «Map» показана — подровняем и перерисуем
                        window.Alpine.store?.manager?.dbg('event: map:shown');
                    } catch {
                    }
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

                window.addEventListener('tasks-refetch', () => {
                    const from = this.days[0].date;
                    const to = this.days[6].date;
                    this.fetchWeek(from, to);
                });
            },

            openSettings() {
                this.schedulerSettingsOpenState = true;
                this.$nextTick(() => document.querySelector('[role="dialog"] select')?.focus());
            },
            closeSettings() {
                this.schedulerSettingsOpenState = false;
            },
            loadSettings() {
                const defaults = {
                    tz: this.APP_TZ,
                    onlyBusiness: true,
                    usHolidays: false,
                    fields: {},
                };
                const raw = this.settings || {};
                this.form = {
                    ...defaults,
                    ...raw,
                    fields: {...defaults.fields, ...(raw.fields || {})},
                    onlyBusiness: !!raw.onlyBusiness,
                    usHolidays: !!raw.usHolidays,
                };
            },
            async saveSettings() {
                const self = this;
                try {
                    // шлём только нужные ключи
                    const payload = {
                        tz: self.form.tz,
                        onlyBusiness: !!self.form.onlyBusiness,
                        usHolidays: !!self.form.usHolidays,
                        fields: {...self.form.fields},
                    };

                    const saved = await this.$wire.call('saveSchedulerSettings', payload);

                    self.settings = JSON.parse(JSON.stringify(payload));
                    // обновляем локальные настройки тем, что пришло с бэка
                    self.settings = saved || self.settings;

                    this.closeSettings();
                    // применяем и перерисовываем
                    await this.applySettings(false);
                } catch (e) {
                    console.error('Failed to save settings', e);
                }
            },
            resetToDefaults() {
                localStorage.removeItem(this.storageKey);
                this.form = this.$data.form;
                this.loadSettings();
            },

            async applySettings(initial = false) {
                // 1) TZ
                this.APP_TZ = this.settings?.tz || this.APP_TZ;
                if (dayjs.tz && dayjs.tz.setDefault) {
                    dayjs.tz.setDefault(this.APP_TZ);
                }

                // 2) Диапазон видимых часов
                if (this.settings?.onlyBusiness) {
                    this.dayStartHour = this.BUSINESS_START;
                    this.dayEndHour = this.BUSINESS_END;
                    this.wrapCols = 32;
                } else {
                    this.dayStartHour = 0;
                    this.dayEndHour = 24;
                    this.wrapCols = 64;
                }

                await this.$nextTick();
                this.resetLaneCaches();

                // 4) Перерисовать / перезагрузить задачи в текущем виде
                if (!initial) {
                    await this.fetchForCurrentView?.();
                }
                await this.loadHolidaysForCurrentView();
            },

            async loadHolidaysForCurrentView() {
                if (!this.settings.usHolidays) {
                    this.holidays = new Map();
                    return;
                }

                // диапазон как вы уже делаете для задач
                let from, to;
                if (this.view === 'day') {
                    const d = this.currentDayISO || this.tz().format('YYYY-MM-DD');
                    from = d;
                    to = d;
                } else {
                    const s = this.weekStart ? this.tz(this.weekStart) : this.startOfWeek(this.tz());
                    from = s.startOf('day').format('YYYY-MM-DD');
                    to = s.add(6, 'day').endOf('day').format('YYYY-MM-DD');
                }

                const rows = await this.$wire.call('loadHolidaysForRange', from, to);
                const map = new Map();
                for (const h of (rows || [])) map.set(h.date, h.name);
                this.holidays = map;
            },

            holidayName(dayISO) {
                return this.holidays.get(dayISO) || 'Holiday';
            },

            get getTaskId() {
                return this.jobModalForm.task_id;
            },

            get topOffset() {
                const hasHoliday = this.settings.usHolidays && this.holidays.has(this.currentDayISO);
                return this.DAY_HEADER_H + (hasHoliday ? this.HOLIDAY_H : 0);
            },

            toPx(minutesFromMidnight) {
                return (minutesFromMidnight - this.dayStartHour * 60) * this.pxPerMin;
            },

            to12h(h) {
                const am = h < 12;
                let hh = h % 12;
                if (hh === 0) hh = 12;
                if (hh === 6) return '';
                return `${hh}${am ? 'am' : 'pm'}`;
            },
            parseTime(dayISO, timeStr) {
                if (!dayISO || !timeStr) return null;
                const dt = dayjs.tz(`${dayISO}T${timeStr}`, this.APP_TZ);
                return dt.isValid() ? dt : null;
            },
            mondayStart(d) {
                const m = this.tz(d).startOf('day');
                const offset = (m.day() + 6) % 7;
                return m.subtract(offset, 'day');
            },
            tz(d) {
                return d ? dayjs.tz(d, this.APP_TZ) : dayjs().tz(this.APP_TZ);
            },
            safeTz(d) {
                const m = d ? dayjs.tz(d, this.APP_TZ) : dayjs().tz(this.APP_TZ);
                return m.isValid() ? m : null;
            },
            getDayISO(d) {
                if (!d) return null;
                if (typeof d === 'string') return d.slice(0, 10);
                if (typeof d === 'object' && 'date' in d) return String(d.date).slice(0, 10);
                const m = this.safeTz(d);
                return m ? m.format('YYYY-MM-DD') : null;
            },
            startOfWeek(d) {
                return this.tz(d).startOf('isoWeek');
            },
            endOfWeek(d) {
                return this.tz(d).endOf('isoWeek');
            },
            get view() {
                return this.mapView || 'week';
            },
            nowTs: null,
            clockId: null,
            debugMap: true,

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
                this.stopClock();
                const tick = () => {
                    this.now = this.tz();
                };
                tick();
                this.clockId = setInterval(tick, 30000);
            },

            stopClock() {
                if (this.clockId) clearInterval(this.clockId);
                this.clockId = null;
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

            async setWeek(weekStartInput) {
                const start = this.mondayStart(weekStartInput);
                this.weekStart = start.format('YYYY-MM-DD');
                this.weekEnd = start.add(6, 'day').format('YYYY-MM-DD');

                const cur = this.safeTz(this.currentDayISO);
                if (!cur || !cur.isBetween(this.weekStart, this.weekEnd, null, '[]')) {
                    this.currentDayISO = start.format('YYYY-MM-DD');
                }

                this.days = Array.from({length: 7}, (_, i) => {
                    const d = start.add(i, 'day');
                    return {date: d.format('YYYY-MM-DD'), label: d.format('ddd, MMM D')};
                });
                this.ensureCurrentDayIsInWeek();

                if (this.fetchWeek) {
                    await this.fetchWeek(this.days[0].date, this.days[6].date);
                    this.pendingMapRefresh = true;
                }

                if (this.mode === 'map') {
                    await this.hardRefreshMap.call(this, {fit: true});
                    this.pendingMapRefresh = false;
                } else {
                    //this.dbg('mode:', this.mode, '→ map не перерисовываем сейчас');
                }

                this.resetLaneCaches();
            },

            queueFetch: Alpine.debounce(function () {
                this.fetchForCurrentView();
            }, 0),

            toDate(d) {
                const m = this.safeTz(d);
                return m ? new Date(m.valueOf()) : new Date();
            },

            get dayCells() {
                const out = [];
                const startMin = this.dayStartHour * 60;
                const endMin = this.dayEndHour * 60;
                let idx = 0;

                for (let m = startMin; m <= endMin; m += this.slotWidthPx, idx++) {
                    const top = this.DAY_HEADER_H + (m - startMin) * this.pxPerMin;
                    out.push({
                        i: idx,
                        min: m,
                        top,
                        h: 30 * this.pxPerMin
                    });
                }
                return out;
            },

            isDaySelected(i) {
                if (this.selStartIdx == null || this.selEndIdx == null) return false;
                const a = Math.min(this.selStartIdx, this.selEndIdx);
                const b = Math.max(this.selStartIdx, this.selEndIdx);
                return i >= a && i <= b;
            },

            async setDay(day) {
                const d = this.safeTz(day) || this.tz();
                this.currentDayISO = d.format('YYYY-MM-DD');

                const ws = this.startOfWeek(d);
                if (!this.weekStart || !this.safeTz(this.weekStart).isSame(ws, 'day')) {
                    await this.setWeek(ws);
                }
            },

            prev() {
                return this.view === 'day' ? this.moveDay(-1) : this.moveWeek(-1);
            },
            next() {
                return this.view === 'day' ? this.moveDay(1) : this.moveWeek(1);
            },

            get headerLabel() {
                if (this.view === 'day') {
                    return this.tz(this.currentDayISO).format('ddd, MMM D');
                }
                // week
                return this.days?.length === 7
                    ? `${this.days[0].label} — ${this.days[6].label}`
                    : '';
            },

            async moveWeek(delta) {
                const base = this.view === 'day'
                    ? this.mondayStart(this.weekStart || this.currentDayISO || this.tz())
                    : (this.safeTz(this.weekStart) || this.startOfWeek(this.tz()));

                await this.setWeek(this.startOfWeek(base.add(delta, 'week')));
                this.$dispatch('week:changed', {start: this.startOfWeek(this.tz())});
            },

            async goToday() {
                //this.currentDayISO = this.tz().format('YYYY-MM-DD');
                if (this.view === 'day') {
                    this.currentDayISO = this.tz().format('YYYY-MM-DD');
                } else {
                    await this.setWeek(this.mondayStart(this.tz()));
                }

                this.invalidateLanes()
                await this.fetchForCurrentView();
            },

            isCurrentWeek() {
                if (this.view === 'day') {
                    return this.tz(this.currentDayISO).isSame(this.tz(), 'day');
                }
                const now = this.tz();
                const start = this.tz(this.weekStart);
                const end = start.add(6, 'day').endOf('day');
                return now.isAfter(start) && now.isBefore(end);
            },

            isCurrentDay() {
                const cur = this.safeTz(this.currentDayISO);
                return !!cur && cur.isSame(this.tz(), 'day');
            },

            async fetchForCurrentView() {
                if (this._fetching) return;
                const req = ++this._reqId;
                this._fetching = true;
                this.isLoading = true;

                try {
                    if (this.view === 'day') {
                        const d = this.currentDayISO ? this.tz(this.currentDayISO) : this.tz();
                        await this.fetchWeek(d.startOf('day'), d.endOf('day'));
                    } else {
                        const s = this.weekStart ? this.tz(this.weekStart) : this.startOfWeek(this.tz());
                        await this.fetchWeek(s.startOf('day'), s.add(6, 'day').endOf('day'));
                    }

                    // если за время ожидания стартовал еще один fetch — прерываем пост-обработку
                    if (req !== this._reqId) return;

                    if (this.mode === 'map') {
                        await this.hardRefreshMap(true);
                        if (this.routingEnabled && this.selectedTechIds.size) {
                            await this.showTechRoute([...this.selectedTechIds], this.currentDayISO);
                        }
                    }

                    await this.$nextTick();
                    this.resetLaneCaches();
                    await this.loadHolidaysForCurrentView();
                } finally {
                    if (req === this._reqId) this.isLoading = false;
                    this._fetching = false;
                    await this.$nextTick();
                }
            },

            toISO(d) {
                const m = this.safeTz(d);   // ваш dayjs.tz + проверка isValid
                return m ? m.format('YYYY-MM-DD') : null;
            },

            async fetchWeek(fromDate, toDate) {
                this.isLoading = true;
                try {
                    const from = this.toISO(fromDate);
                    const to = this.toISO(toDate);

                    const tasks = await this.$wire.call('loadTasksForRange', from, to);
                    this.tasks = tasks ?? [];
                } finally {
                    this.isLoading = false;
                    await this.$nextTick();

                    if (this.mode === 'map') {
                        await this.refreshMap(true);
                        if (this.routingEnabled && this.selectedTechIds.size) {
                            await this.showTechRoute(Array.from(this.selectedTechIds), this.currentDayISO);
                        }
                    }
                }
            },

            async setView(v) {
                if (this.view === v) return;
                this.view = v;

                // обеспечим валидную опорную дату
                if (v === 'day' && !this.currentDayISO) {
                    this.currentDayISO = this.tz().format('YYYY-MM-DD');
                }
                if (v === 'week' && !this.weekStart) {
                    await this.setWeek(this.mondayStart(this.tz()));
                }

                await this.fetchForCurrentView();
            },

            moveDay(delta) {
                const cur = this.currentDayISO ? this.tz(this.currentDayISO) : this.tz();
                this.currentDayISO = cur.add(delta, 'day').format('YYYY-MM-DD');
                //this.fetchForCurrentView();
            },

            get daySlots() {
                const out = [];
                for (let h = this.dayStartHour; h <= this.dayEndHour; h++) {
                    const top = (h - this.dayStartHour) * 60 * this.pxPerMin;
                    let yHour = (h * 60) * this.pxPerMin;
                    out.push({
                        h,
                        label: this.to12h(h % 24),
                        top,
                        yHour,
                        yHalf: yHour + 30 * this.pxPerMin,
                    });
                }
                return out;
            },

            // высота всей области дня
            get dayGridHeight() {
                if (this.settings?.onlyBusiness)
                    return (this.dayEndHour - this.dayStartHour) * 60 * this.pxPerMin + 35;
                else
                    return (this.dayEndHour - this.dayStartHour) * 60 * this.pxPerMin + 20;
            },

            get hours() {
                const out = [];
                for (let h = this.dayStartHour; h <= this.dayEndHour; h++) {
                    const top = (h - this.dayStartHour) * 60 * this.pxPerMin;
                    out.push({
                        h,
                        label: this.to12h(h % 24),
                        top: top,
                        center: top
                    });
                }
                return out;
            },

            get halfHours() {
                const out = [];
                for (let h = this.dayStartHour; h < this.dayEndHour; h++) {
                    const top = ((h - this.dayStartHour) * 60 + 30) * this.pxPerMin;
                    out.push({top});
                }
                return out;
            },

            buildDayTimeSlots() {
                const base = this.currentDayISO
                    ? this.tz(this.currentDayISO).startOf('day')
                    : this.tz().startOf('day');

                const slots = [];
                for (let h = this.dayStartHour; h <= this.dayEndHour; h++) {
                    const t = base.hour(h).minute(0).second(0);
                    slots.push({
                        label: t.format('hA'),
                        startISO: t.toISOString(),
                        endISO: t.add(1, 'hour').toISOString(),
                        hour: h
                    });
                }
                this.dayTimeSlots = slots;
            },

            hmToMin(hm) {
                if (!hm) return 0;
                const [h, m] = hm.split(':').map(Number);
                return h * 60 + (m || 0);
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

            to12Hour(timeStr, dayISO = this.currentDayISO) {
                const dt = this.parseTime(dayISO, timeStr);
                return dt ? dt.format('h:mmA') : '';
            },

            // Получить задачи сотрудника на конкретный день
            dayTasks(empId, dayLike) {
                const iso = this.getDayISO(dayLike) ?? this.currentDayISO;
                if (!iso) return [];

                const emp = String(empId);

                return this.tasks.filter(t =>
                    String(t.technician) === emp &&        // <-- привели обе стороны
                    String(t.day).slice(0, 10) === String(iso) // на всякий случай «срезали» время
                );
            },

            get dayModeTasks() {
                const dISO = this.currentDayISO;                 // 'YYYY-MM-DD'
                const selected = this.selectedTechIds?.size
                    ? Array.from(this.selectedTechIds).map(String)
                    : (this.employees || []).map(e => String(e.id)); // если ничего не выбрано — все

                return (this.tasks || [])
                    .filter(t => String(t.day) === String(dISO))
                    .filter(t => {
                        const ids = Array.isArray(t.technician) ? t.technician.map(String) : [String(t.technician)];
                        return ids.some(id => selected.includes(id));
                    })
                    .map(t => {
                        // координаты времени
                        const s = this.hmToMin(t.start_time || t.start || '00:00');
                        const e = this.hmToMin(t.end_time || t.end || '00:00');

                        const startMin = (s ?? (this.dayStartHour * 60));
                        const endMin = Math.max((e ?? startMin + 30), startMin + 30); // не меньше 30 минут

                        const top = (startMin - this.dayStartHour * 60) * this.pxPerMin;
                        const height = (endMin - startMin) * this.pxPerMin;

                        return {
                            ...t,
                            _top: Math.max(0, top),
                            _height: Math.max(6, height),
                            _color: this.colorOfTech(String(t.technician))
                        };
                    })
                    .sort((a, b) => a._top - b._top);
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
                this.mouseDown = false;
            },
            isSelected(emp, day, idx) {
                if (this.sel.emp !== emp || this.sel.day !== day) return false;
                const [min, max] = [this.sel.startIdx, this.sel.endIdx].sort((a, b) => a - b);
                return idx >= min && idx < max + 1;
            },

            startSelectionDay(i) {
                this.selecting = true;
                this.selStartIdx = i;
                this.selEndIdx = i;
            },
            dragSelectionDay(i) {
                if (!this.selecting) return;
                this.selEndIdx = i;
            },
            endSelectionDay() {
                if (!this.selecting) return;

                const a = Math.min(this.selStartIdx, this.selEndIdx);
                const b = Math.max(this.selStartIdx, this.selEndIdx);

                const startMinutesFromOpen = a * 30;
                const endMinutesFromOpen = (b + 1) * 30;

                // абсолютные минуты от полуночи
                const absStartMin = this.dayStartHour * 60 + startMinutesFromOpen;
                const absEndMin = this.dayStartHour * 60 + endMinutesFromOpen;

                // перерасчёт в «часы:мин» текущего дня (dayjs)
                const d = this.tz(this.currentDayISO ?? this.tz());
                const start = d.hour(0).minute(0).second(0).add(absStartMin, 'minute');
                const end = d.hour(0).minute(0).second(0).add(absEndMin, 'minute');

                // здесь делайте то же, что делаете в неделе (создание задачи / модалка)
                // this.createTaskFromSelection(start, end) ...
                // или вызов уже существующего endSelection(...)

                this.selecting = false;
                this.selStartIdx = this.selEndIdx = null;
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
                    //this.isLoading = true;

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
                    //this.isLoading = false;
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
                const topPx = 2;//lane * this.rowHeightPx;

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
                    this.$wire.call('saveJob', jobModalForm);
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

                this.$wire.call('createCustomer', customer);
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

            // ===== FILTER HELPERS =====
            todayISO() {
                return dayjs().tz(this.APP_TZ).format('YYYY-MM-DD');
            },
            isInCurrentWeek(dayISO) {
                return (this.days || []).some(d => d.date === String(dayISO));
            },
            // константы
            FMT: 'YYYY-MM-DD HH:mm:ss',

            prepTaskForDay(t, dayISO) {
                // день начала сетки (например, 06:00)
                const dayStart = dayjs.tz(`${dayISO} ${String(this.dayStartHour).padStart(2, '0')}:00:00`, this.FMT, this.APP_TZ);
                const dayEnd = dayStart.clone().add((this.dayEndHour - this.dayStartHour), 'hour');

                // реальные время старта/финиша
                const start = dayjs.tz(`${t.day} ${t.start}`, this.FMT, this.APP_TZ);
                const end = dayjs.tz(`${t.day} ${t.end}`, this.FMT, this.APP_TZ);

                // обрезаем по границам рабочего дня
                const s = start.isBefore(dayStart) ? dayStart : start;
                const e = end.isAfter(dayEnd) ? dayEnd : end;

                // минуты от начала дня -> px
                const topMin = Math.max(0, s.diff(dayStart, 'minute'));
                const hMin = Math.max(0, e.diff(s, 'minute'));

                return {
                    ...t,
                    _top: topMin * this.pxPerMin,   // pxPerMin = HOUR_PX / 60
                    _height: Math.max(1, hMin * this.pxPerMin),
                };
            },

            tasksForDay(dayISO) {
                return (this.tasks || [])
                    .filter(t => String(t.day) === String(dayISO))
                    .map(t => this.prepTaskForDay(t, dayISO))
                    .sort((a, b) => dayjs(`2000-01-01 ${a.start}`).diff(dayjs(`2000-01-01 ${b.start}`)));
            },
            groupByTech(tasks) {
                const by = new Map();
                for (const t of tasks) {
                    const tIds = Array.isArray(t.technician) ? t.technician.map(String) : [String(t.technician)];
                    for (const id of tIds) {
                        if (!by.has(id)) by.set(id, []);
                        by.get(id).push(t);
                    }
                }
                return by;
            },
            // ===== /FILTER HELPERS =====

            // ===== LOG HELPERS =====
            dbg(...args) {
                console.log('%c[MAP]', 'color:#0af;font-weight:600', ...args);
            },
            gstart(label) {
                console.groupCollapsed('%c[MAP] ' + label, 'color:#0af');
            },
            gend() {
                console.groupEnd();
            },

            getMapEl() {
                return document.getElementById('jobsMap');
            },
            logMapEl(where = '') {
                const el = this.getMapEl();
                if (!el) {
                    this.dbg(where, 'mapEl: null');
                    return;
                }
                const cs = getComputedStyle(el);
                this.dbg(where, 'mapEl:', {
                    display: cs.display, visibility: cs.visibility,
                    w: el.offsetWidth, h: el.offsetHeight
                });
            },
            // ===== /LOG HELPERS =====

            routeControl: null,
            routeLayer: null,
            routeTech: null,
            routeLayers: {},
            routeCache: new Map(),
            mode: 'schedule',
            map: null,
            markers: null,
            inited: false,
            _initializingMap: false,
            mapView: 'week',
            colorMap: [],
            selectedTechIds: new Set(),
            routingEnabled: true,
            currentDayISO: null,
            weekStartISO: null,
            routeCtlWrap: null,
            _employeesControlWrap: null,
            _employeesControl: null,
            _toolbarWrap: null,
            employeeFilter: null,
            weekBtn: document.createElement('button'),
            dayBtn: document.createElement('button'),
            GEOAPIFY_KEY: window.GEOAPIFY_KEY,
            DEFAULT_CENTER: [40.73, -73.93],
            DEFAULT_ZOOM: 10,

            icons: {
                dotBlue: L.divIcon({
                    className: 'job-dot',
                    html: '<div style="width:14px;height:14px;border-radius:50%;background:#2563eb;box-shadow:0 0 0 2px #fff"></div>',
                    iconSize: [10, 10],
                    iconAnchor: [5, 5]
                }),
                dotBlueBig: L.divIcon({
                    className: 'job-dot-big',
                    html: '<div style="width:22px;height:22px;border-radius:50%;background:#2563eb;border:2px solid #fff;box-shadow:0 0 0 2px #2563eb;"></div>',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                }),
                wrench: L.divIcon({
                    className: 'job-wrench',
                    html: `
                    <div style="width:18px;height:18px;border-radius:9px;background:#fff;border:1px solid #cbd5e1;display:flex;align-items:center;justify-content:center">
                      <svg viewBox="0 0 24 24" width="12" height="12" fill="#475569"><path d="M22 19.59 19.59 22l-7.13-7.13a6.5 6.5 0 0 1-8.36-8.36l3.17 3.17 2.12-2.12L6.22 4.39A6.5 6.5 0 0 1 14.4 12.55z"/></svg>
                    </div>`,
                    iconSize: [18, 18],
                    iconAnchor: [9, 9]
                }),
            },

            async showMap() {
                this.mode = 'map';

                await this.$nextTick();

                if (this._initializingMap) return;
                this._initializingMap = true;

                if (!this.map) {
                    await this.waitMapContainerReady();
                    await this.initMap();
                    this.inited = true;
                }
                this.ensureCurrentDayIsInWeek();
                if (this.pendingMapRefresh) {
                    await this.hardRefreshMap({fit: true});
                    this.pendingMapRefresh = false;
                } else {
                    await this.refreshMap(true);
                }
                this.updateRouteControlVisibility();
                this._initializingMap = false;
            },

            async waitMapContainerReady(selector = '#jobsMap', maxTries = 20) {
                const el = document.querySelector(selector);
                for (let i = 0; i < maxTries; i++) {
                    const vis = el && getComputedStyle(el).display !== 'none';
                    const ok = vis && el.offsetWidth > 0 && el.offsetHeight > 0;
                    if (ok) return true;
                    await this.nextFrame();
                }
                return false;
            },

            async tilesFallbackRedraw(map) {
                const pane = map.getPanes()?.tilePane;
                const loaded = pane?.querySelectorAll('img.leaflet-tile-loaded')?.length ?? 0;
                if (!loaded) {
                    map.invalidateSize(true);
                    map.setZoom(map.getZoom());
                }
            },

            rebindMapIfContainerChanged() {
                if (!this.map) return;
                const current = this.map.getContainer ? this.map.getContainer() : null;
                const el = this.getMapEl();
                if (el && current && current !== el) {
                    // контейнер заменился — корректно пересоздаём карту
                    const center = this.map.getCenter ? this.map.getCenter() : this.DEFAULT_CENTER;
                    const zoom = this.map.getZoom ? this.map.getZoom() : this.DEFAULT_ZOOM;

                    this.map.remove();         // уничтожаем старый экземпляр
                    this.map = L.map(el, {zoomControl: true}).setView(center, zoom);

                    // заново вешаем тайлы и слои
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(this.map);

                    this.markers = L.markerClusterGroup();
                    this.map.addLayer(this.markers);
                }
            },

            async refreshMap(fit = false) {
                if (!this.map) return;
                await this.waitMapContainerReady('#jobsMap');
                await this.nextFrame();
                this.map.invalidateSize(true);
                await this.rerenderMap({fit});
            },

            async hardRefreshMap({fit = true} = {}) {
                const prev = this.map ? {center: this.map.getCenter(), zoom: this.map.getZoom()} : null;

                try {
                    this.map?.off();
                    this.map?.remove();
                } catch (_) {
                }
                this.map = null;
                this.markers = null;

                await this.$nextTick();
                await this.waitMapContainerReady('#jobsMap');
                await this.nextFrame();

                await this.initMap();
                await this.rerenderMap({fit});

                if (!fit && prev) this.map.setView(prev.center, prev.zoom);

                if (this.routingEnabled && this.selectedTechIds.size) {
                    await this.showTechRoute(Array.from(this.selectedTechIds), this.currentDayISO);
                }
            },

            async initMap() {
                const el = this.$refs.jobsMap;

                if (el._leaflet_id) {
                    try {
                        el._leaflet_id = null;
                    } catch (_) {
                    }
                    el.innerHTML = '';
                }

                if (this.map) return;

                this.map = L.map('jobsMap', {zoomControl: false})
                    .setView(this.DEFAULT_CENTER, this.DEFAULT_ZOOM);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(this.map);

                this.markers = L.markerClusterGroup();
                this.map.addLayer(this.markers);

                this.addDayToolbar();

                setTimeout(() => {
                    this.map?.invalidateSize(true);
                }, 0);
                this.updateRouteControlVisibility();
            },

            destroyMap() {
                if (!this.map) return;
                try {
                    this.map.off();
                    this.map.remove();
                } catch (_) {
                }
                this.map = null;
                this.inited = false;

                const el = this.$refs.jobsMap;
                if (el) {
                    try {
                        el._leaflet_id = null;
                    } catch (_) {
                    }
                    el.innerHTML = '';
                }
            },

            nextFrame() {
                return new Promise(r => requestAnimationFrame(r));
            },

            async rerenderMap({fit = true} = {}) {
                if (this.isLoading) return;
                if (!this.map || !this.markers) {
                    return;
                }

                this.markers.clearLayers();
                this.clearRoute();

                if (this.mapView === 'week') {
                    await this.renderWeekMarkers({fit});
                } else {
                    await this.renderDayView({fit});
                }

                await this.nextFrame();
                this.map.invalidateSize(true);
            },

            async renderWeekMarkers({fit = true} = {}) {
                const points = [];
                const today = this.todayISO();

                for (const t of (this.tasks || [])) {
                    // только задания недели, которую сейчас смотрим
                    if (!this.isInCurrentWeek(t.day)) continue;

                    const c = t.client || {};
                    const lat = c.lat ?? c.address_lat ?? null;
                    const lng = c.lng ?? c.address_lng ?? null;
                    if (lat == null || lng == null) continue;

                    const isToday = String(t.day || '') === today;
                    const icon = isToday ? this.icons.dotBlue : this.icons.wrench;

                    const m = L.marker([lat, lng], {icon}).bindPopup(this.makePopupEl(t));
                    this.markers.addLayer(m);
                    points.push([lat, lng]);
                }

                if (fit && points.length) {
                    const b = L.latLngBounds(points);
                    this.map.fitBounds(b, {padding: [40, 40]});
                } else {
                    this.map.setView(this.DEFAULT_CENTER, this.DEFAULT_ZOOM);
                }
            },

            async renderDayView({fit = true} = {}) {
                const dayISO = this.currentDayISO || this.todayISO();

                // 1) маркеры дня
                const dayTasks = this.tasksForDay(dayISO);
                const points = [];
                for (const t of dayTasks) {
                    const c = t.client || {};
                    const lat = c.lat ?? c.address_lat ?? null;
                    const lng = c.lng ?? c.address_lng ?? null;
                    if (lat == null || lng == null) continue;

                    // текущее задание выделяем большим кругом
                    const isCurrent = String(t.status || '').toLowerCase() === 'in_progress';
                    const icon = isCurrent ? this.icons.dotBlueBig : this.icons.dotBlue;

                    const m = L.marker([lat, lng], {icon}).bindPopup(this.makePopupEl(t));
                    this.markers.addLayer(m);
                    points.push([lat, lng]);
                }

                // 2) кого показываем: по умолчанию — все техники
                const selected = this.selectedTechIds.size
                    ? Array.from(this.selectedTechIds)
                    : Array.from(this.groupByTech(dayTasks).keys());

                // 3) строим маршруты (цвет берём из цвета сотрудника — внутри showTechRoute он у вас уже считает)
                if (selected.length) {
                    await this.showTechRoute(selected, dayISO);
                }

                if (fit && points.length) {
                    const b = L.latLngBounds(points);
                    try {
                        this.map.fitBounds(b, {padding: [40, 40]});
                    } catch {
                    }
                } else {
                    this.map.setView(this.DEFAULT_CENTER, this.DEFAULT_ZOOM);
                }
            },

            updateRouteControlVisibility() {
                if (!this._employeesControlWrap) return;
                const wrap = this._employeesControlWrap;
                this._employeesControlWrap.style.display = (this.mapView === 'day') ? '' : 'none';
            },

            colorOfTech(id) {
                const key = String(id);
                if (this.colorMap[key]) return this.colorMap[key];

                // детерминированный "seed" из id
                let seed = 0;
                for (let i = 0; i < key.length; i++) seed = (seed * 31 + key.charCodeAt(i)) >>> 0;

                // распределяем оттенок по кругу (golden angle)
                const hue = (seed * 137.508) % 360;
                const sat = 70;   // насыщенность
                const lig = 50;   // светлота
                const color = `hsl(${hue}, ${sat}%, ${lig}%)`;

                this.colorMap[key] = color;
                return color;
            },

            paintModeButtons() {
                this.weekBtn.className = 'px-2 py-1 rounded ' + (this.mapView === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800');
                this.dayBtn.className = 'px-2 py-1 rounded ' + (this.mapView === 'day' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800');
            },

            async setMapView(view) {
                this.mapView = view;
                if (view === 'day' && !this.currentDayISO) this.currentDayISO = this.todayISO();
                if (view === 'week' && !this.weekStart) {
                    await this.setWeek(this.mondayStart(this.tz()));
                    if (this.currentDayISO) this.tasksForDay(this.currentDayISO);
                }
                this.paintModeButtons?.();
                this.updateRouteControlVisibility();
                await this.hardRefreshMap(true);
            },

            showCalendar() {
                this.mode = 'schedule';
            },

            addDayToolbar() {
                const self = this;

                // состояние
                if (!self.selectedTechIds) self.selectedTechIds = new Set(); // по умолчанию выберем всех ниже
                if (self.routingEnabled == null) self.routingEnabled = true;
                self.employeeFilter = '';

                // Leaflet control
                const Toolbar = L.Control.extend({
                    options: {position: 'topleft'},
                    onAdd() {
                        const wrap = L.DomUtil.create('div', 'leaflet-control route-toolbar');
                        wrap.style.zIndex = 1000;                       // выше тайл-слоя
                        wrap.style.display = 'flex';
                        wrap.style.gap = '10px';

                        /* -------- Кнопка: Routing -------- */
                        const routing = document.createElement('div');
                        routing.className = 'rt-btn';
                        routing.style.position = 'relative';

                        const routingBtn = document.createElement('button');
                        routingBtn.className = 'rt-pill';
                        routingBtn.type = 'button';
                        routingBtn.innerHTML = `<span class="rt-pill__text">Routing ${self.routingEnabled ? 'on' : 'off'}</span> ▾`;
                        routing.appendChild(routingBtn);

                        const routingMenu = document.createElement('div');
                        routingMenu.className = 'rt-menu';
                        routingMenu.innerHTML = `
                            <button data-v="on"  class="rt-item">On</button>
                            <button data-v="off" class="rt-item">Off</button>
                          `;
                        routing.appendChild(routingMenu);

                        routingBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            closeAllMenus();
                            routingMenu.classList.toggle('open');
                        });
                        routingMenu.addEventListener('click', (e) => {
                            const v = e.target?.dataset?.v;
                            if (!v) return;
                            self.routingEnabled = (v === 'on');
                            routingBtn.querySelector('.rt-pill__text').textContent =
                                `Routing ${self.routingEnabled ? 'on' : 'off'}`;
                            routingMenu.classList.remove('open');
                            maybeBuildRoute();
                        });

                        /* -------- Кнопка: Employees -------- */
                        const empl = document.createElement('div');
                        empl.className = 'rt-btn';
                        empl.style.position = 'relative';

                        const emplBtn = document.createElement('button');
                        emplBtn.className = 'rt-pill';
                        emplBtn.type = 'button';
                        emplBtn.innerHTML = `<span class="rt-pill__text">All employees</span> ▾`;
                        empl.appendChild(emplBtn);

                        const emplMenu = document.createElement('div');
                        emplMenu.className = 'rt-menu';
                        // фильтр
                        const filterWrap = document.createElement('div');
                        filterWrap.style.padding = '8px';
                        filterWrap.innerHTML = `
                            <input class="rt-input" type="text" placeholder="Filter by name or tag">
                          `;
                        emplMenu.appendChild(filterWrap);
                        const filterInput = filterWrap.querySelector('input');

                        // список чекбоксов
                        const list = document.createElement('div');
                        list.style.maxHeight = '260px';
                        list.style.overflow = 'auto';
                        emplMenu.appendChild(list);

                        empl.appendChild(emplMenu);

                        // отрисовка списка
                        const renderList = () => {
                            const q = (self.employeeFilter || '').trim().toLowerCase();
                            list.innerHTML = '';
                            (self.employees || []).forEach(e => {
                                const hay = (e.name + ' ' + (e.tags || '')).toLowerCase();
                                if (q && !hay.includes(q)) return;

                                e.color = self.colorOfTech(e.id);

                                const row = document.createElement('label');
                                row.className = 'rt-row';

                                const cb = document.createElement('input');
                                cb.type = 'checkbox';
                                cb.checked = self.selectedTechIds.size === 0
                                    ? true
                                    : self.selectedTechIds.has(String(e.id));
                                cb.style.color = e.color;

                                const swatch = document.createElement('span');
                                swatch.className = 'rt-color';
                                swatch.style.background = e.color;

                                const name = document.createElement('span');
                                name.textContent = e.name;

                                row.appendChild(cb);
                                //row.appendChild(color);
                                row.appendChild(name);
                                list.appendChild(row);

                                cb.addEventListener('change', () => {
                                    const id = String(e.id);
                                    if (cb.checked) self.selectedTechIds.add(id);
                                    else self.selectedTechIds.delete(id);
                                    updateEmployeesBtnTitle();
                                    maybeBuildRoute();
                                });
                            });
                        };

                        filterInput.addEventListener('mousedown', e => e.stopPropagation());
                        filterInput.addEventListener('click', e => e.stopPropagation());
                        filterInput.addEventListener('pointerdown', e => e.stopPropagation());

                        list.addEventListener('mousedown', e => e.stopPropagation());
                        list.addEventListener('click', e => e.stopPropagation());
                        list.addEventListener('pointerdown', e => e.stopPropagation());

                        // фильтр
                        filterInput.addEventListener('input', () => {
                            self.employeeFilter = filterInput.value;
                            renderList();
                        });

                        // раскрытие/закрытие меню
                        emplBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            closeAllMenus();
                            emplMenu.classList.toggle('open');
                            // ленивая инициализация: при первом открытии выберем всех
                            if (self.selectedTechIds.size === 0 && (self.employees || []).length) {
                                (self.employees || []).forEach(e => self.selectedTechIds.add(String(e.id)));
                                updateEmployeesBtnTitle();
                            }
                            renderList();
                        });

                        // Добавляем оба блока в обёртку
                        wrap.appendChild(routing);
                        wrap.appendChild(empl);

                        // задания помощников
                        function updateEmployeesBtnTitle() {
                            const total = (self.employees || []).length;
                            const n = self.selectedTechIds.size;
                            const txt = (n === 0 || n === total) ? 'All employees' : `${n} employee${n > 1 ? 's' : ''}`;
                            emplBtn.querySelector('.rt-pill__text').textContent = txt;
                        }

                        function maybeBuildRoute() {
                            // строим/чистим только в режиме "day"
                            if (self.mapView !== 'day') return self.clearRoute();

                            if (!self.routingEnabled) {
                                self.clearRoute();
                                return;
                            }
                            const ids = Array.from(self.selectedTechIds);
                            self.showTechRoute(ids, self.currentDayISO ?? self.todayISO);
                        }

                        function closeAllMenus() {
                            wrap.querySelectorAll('.rt-menu.open').forEach(m => m.classList.remove('open'));
                        }

                        // закрытие по клику вне
                        document.addEventListener('click', () => closeAllMenus());

                        // сохраним ссылку, чтобы управлять видимостью
                        self._toolbarWrap = wrap;

                        // текст на старте
                        updateEmployeesBtnTitle();

                        return wrap;
                    }
                });

                this.map.addControl(new Toolbar());
                this.updateToolbarVisibility();    // сразу прячем в неделе
            },

            totalForItems(items = []) {
                return items.reduce((sum, i) => {
                    const qty = Number(i.qty ?? 0);
                    const p = Number(i.unit_price ?? i.price ?? 0);
                    const tot = Number(i.total ?? (qty * p));
                    return sum + (isFinite(tot) ? tot : 0);
                }, 0);
            },
            formatTimeRange(t) {
                // t.day = 'YYYY-MM-DD', t.start/end = 'HH:mm:ss'
                // Используйте ваши dayjs/this.tz/... если нужно TZ
                const start = `${t.day} ${t.start ?? ''}`.trim();
                const end = `${t.day} ${t.end ?? ''}`.trim();
                // Если есть ваши функции форматирования — используйте их
                const s12 = this.to12Hour ? this.to12Hour(t.start, t.day) : (t.start ?? '');
                const e12 = this.to12Hour ? this.to12Hour(t.end, t.day) : (t.end ?? '');
                return {formatted: `${s12} – ${e12}`, start12: s12, end12: e12, start, end};
            },
            techNamesOf(t) {
                const list = this.employees || [];
                const tech = t?.technician;

                // если массив id
                if (Array.isArray(tech)) {
                    const ids = new Set(tech.map(x => String(x)));
                    return list
                        .filter(e => ids.has(String(e.id)))
                        .map(e => e?.name)
                        .filter(Boolean)
                        .join(', ');
                }

                // одиночный id (число/строка) или объект с id
                const id = typeof tech === 'object' && tech !== null ? tech.id : tech;
                const emp = list.find(e => String(e.id) === String(id));
                return emp?.name ?? '';
            },
            showField(key) {
                return !!(this.settings?.fields?.[key]);
            },

            updateToolbarVisibility() {
                const wrap = this._toolbarWrap;
                if (!wrap) return;
                wrap.style.display = (this.mapView === 'day') ? 'flex' : 'none';
            },

            maybeBuildRoute() {
                if (!this.routingEnabled) {
                    this.clearRoute();
                    return;
                }
                // передаём массив id
                const ids = Array.from(this.selectedTechIds);
                const dayISO = this.currentDayISO || this.days?.[0]?.date || '';
                this.dbg('[ROUTE] ids=', ids, 'day=', dayISO);
                this.showTechRoute(ids, this.currentDayISO);
            },

            getClientCoords(c) {
                const lat = c?.lat ?? c?.address_lat ?? null;
                const lng = c?.lng ?? c?.address_lng ?? null;
                return (lat == null || lng == null) ? null : {lat, lng};
            },

            waypointsKey(points) {
                // key для кэша маршрута
                return points.map(p => `${p.lat},${p.lng}`).join('|');
            },

            makePopupEl(t) {
                console.log(t);
                const {formatted} = this.formatTimeRange(t);
                const totalSum = this.totalForItems(t.items ?? []);
                const techNames = this.techNamesOf(t);

                const root = document.createElement('div');
                root.className = 'text-xs';

                const line = (html) => {
                    const d = document.createElement('div');
                    d.innerHTML = html;
                    root.appendChild(d);
                };
                const txt = (tag, text, cls) => {
                    const d = document.createElement(tag);
                    if (cls) d.className = cls;
                    d.textContent = text;
                    root.appendChild(d);
                };

                if (this.showField('job_number')) {
                    txt('div', `Job #${t.id ?? ''}`, 'font-semibold');
                }
                if (this.showField('date')) {
                    txt('div', `${formatted}`, 'mt-0 mb-2 text-gray-500 text-[10px]');
                }
                if (t.message) txt('div', t.message, '');
                if (this.showField('price')) {
                    line(`<b>Price: </b>$${(totalSum ?? 0).toLocaleString()}`);
                }
                if (this.showField('customer')) {
                    line(`<b>Client: </b>${t.client?.name ?? ''}`);
                    txt('div', t.client?.address ?? '');
                    line(`<b>Phone: </b>${t.client?.phone ?? ''}`);
                }
                if (this.showField('technician')) {
                    line(`<b>Technician: </b>${techNames ?? ''}`);
                }
                return root;
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

            clearRoute(techIds = null) {
                const ids = techIds ?? Object.keys(this.routeLayers || {});
                ids.forEach(id => {
                    const g = this.routeLayers[id];
                    if (g) {
                        g.clearLayers();
                        if (this.map && this.map.hasLayer(g)) this.map.removeLayer(g);
                        delete this.routeLayers[id];
                    }
                });
            },

            // === helpers =====================================================
            normalizeId(x) {
                // '1' / 1 / {id:1} / {value:'1'} → '1'
                return x == null ? null : String(x.id ?? x.value ?? x);
            },
            taskHasTech(t, techId) {
                const v = t.technician ?? t.technicians ?? t.tech ?? null;
                if (Array.isArray(v)) return v.some(u => this.normalizeId(u) === techId);
                return this.normalizeId(v) === techId;
            },
            taskDayISO(t) {
                // поддерживаем разные имена полей: day / date / start
                const raw =
                    t.day ?? t.date ?? (typeof t.start === 'string' ? t.start : '') ?? '';
                return String(raw).slice(0, 10); // 'YYYY-MM-DD'
            },
            clientLatLng(t) {
                const c = t.client ?? {};
                // сначала берём сохранённые в БД координаты, потом «живые»
                const lat = c.address_lat ?? c.lat ?? null;
                const lng = c.address_lng ?? c.lng ?? null;
                return {lat, lng};
            },
            // цвет линии для техника
            colorByTech(id) {
                const e = (this.employees ?? []).find(x => String(x.id) === String(id)) ?? {};
                return e.color || '#1e90ff';
            },
            ensureCurrentDayIsInWeek() {
                const dates = (this.days || []).map(d => d.date);
                if (!this.currentDayISO || !dates.includes(this.currentDayISO)) {
                    this.currentDayISO = dates[0];
                }
            },
            // ================================================================
            async showTechRoute(selectedTechIds = [], dayISO) {
                if (!this.map) return;

                if (!dayISO) return;
                const day = dayISO || this.currentDayISO || (this.days?.[0]?.date ?? '');
                console.debug('[ROUTE] ids=', selectedTechIds, 'day=', day);

                if (!Array.isArray(selectedTechIds) || selectedTechIds.length === 0) {
                    this.clearRoute();
                    return;
                }

                const apiKey = window.GEOAPIFY_KEY;
                if (!apiKey) {
                    console.warn('[ROUTE] no GEOAPIFY_KEY');
                    return;
                }

                const ids = selectedTechIds.map(String);
                this.dbg('[ROUTE] ids=', ids, 'dayISO=', dayISO);

                for (const techId of ids) {
                    function hasId(ids, v) {
                        return ids?.has ? ids.has(v) : ids?.includes?.(v);
                    }

                    // задача попадает в выбранный диапазон (день/неделя)
                    function inScope(t, dayISO) {
                        // если явно просим день — сравниваем именно день
                        if (this.mapView === 'day') {
                            const d = dayISO || this.currentDayISO || this.todayISO();
                            return String(t.day || '') === String(d);
                        }

                        // режим "неделя": проверяем попадание в границы недели
                        const start = (this.days?.[0]?.date ?? this.weekStart);
                        const end = (this.days?.[6]?.date ?? dayjs(start).add(6, 'day').format('YYYY-MM-DD'));
                        const td = String(t.day || '');
                        return td >= String(start) && td <= String(end);
                    }

                    const selectedIds =
                        (this.selectedTechIds && this.selectedTechIds.size)
                            ? this.selectedTechIds
                            : new Set((this.employees || []).map(e => String(e.id))); // все техники по умолчанию

                    // ---- сбор задач для маршрута ----
                    const techTasks = (this.tasks || [])
                        .filter(t => {
                            // у задачи может быть один техник или массив
                            const tIds = Array.isArray(t.technician)
                                ? t.technician.map(x => String(x))
                                : [String(t.technician)];

                            const assigned = tIds.some(x => hasId(selectedIds, String(x)));
                            const c = t.client ?? {};
                            const hasCoords = (c.lat ?? c.address_lat) != null && (c.lng ?? c.address_lng) != null;

                            return assigned && inScope.call(this, t, dayISO) && hasCoords;
                        })
                        .sort((a, b) => (a.start || '').localeCompare(b.start || ''));

                    // для дебага
                    //this.dbg?.('[ROUTE] week', this.weekStart, '->', this.days?.[6]?.date, 'dayISO=', dayISO, 'tasks=', techTasks.length);

                    const points = techTasks.sort((a, b) => (a.start || '').localeCompare(b.start || ''))
                        .map(t => ({
                            lat: t.client.lat ?? t.client.address_lat,
                            lng: t.client.lng ?? t.client.address_lng
                        }));

                    this.dbg('[ROUTE] tech', techId, 'points:', points.length);

                    if (points.length < 2) continue;

                    // URL для Geoapify
                    const waypoints = points.map(p => `${p.lat},${p.lng}`).join('|');
                    const cacheKey = `${techId}:${dayISO}:${this.waypointsKey(points)}`;
                    let geojson = this.routeCache.get(cacheKey);

                    if (!geojson) {
                        const url = `https://api.geoapify.com/v1/routing?waypoints=${encodeURIComponent(waypoints)}&mode=drive&apiKey=${apiKey}`;
                        try {
                            const res = await fetch(url);
                            if (!res.ok) {
                                console.warn('Routing error', techId, res.status, await res.text());
                                continue;
                            }
                            geojson = await res.json();
                            this.routeCache.set(cacheKey, geojson);
                        } catch (e) {
                            console.error('Routing fetch failed', e);
                            continue;
                        }
                    }

                    // цвет линии (или дефолт)
                    const color = (this.employees || []).find(e => e.id === techId)?.color || '#1e90ff';
                    const lineColor = this.colorByTech(techId);

                    // группа слоёв для этого техника
                    /*if (!this.routeLayers[techId]) this.routeLayers[techId] = L.layerGroup().addTo(this.map);
                    const group = this.routeLayers[techId];
                    group.clearLayers();*/

                    // линия маршрута
                    const routeLayer = L.geoJSON(geojson, {
                        style: {
                            color: lineColor,
                            weight: 5,
                            opacity: 0.9
                        },
                    }).addTo(this.map);
                    this.routeLayers.push(routeLayer);

                    // номеруем точки
                    points.forEach((p, idx) => {
                        L.marker([p.lat, p.lng], {
                            icon: L.divIcon({
                                className: 'route-order',
                                html:
                                    `<div style="background:${lineColor};color:#fff;border-radius:12px;` +
                                    `width:24px;height:24px;display:flex;align-items:center;` +
                                    `justify-content:center;font:12px sans-serif">${idx + 1}</div>`,
                                iconSize: [24, 24],
                                iconAnchor: [12, 12],
                            }),
                        }).addTo(routeLayer);
                    });

                    // подвинем карту к маршруту
                    try {
                        this.map.fitBounds(routeLayer.getBounds(), {padding: [40, 40]});
                    } catch {
                    }
                }
            },

            addCustomer() {
                return {
                    // поля формы (минимум)
                    name: '', email: '', phone: '',
                    query: '',
                    suggestions: [],
                    open: false,
                    selected: {id: '', label: '', lat: null, lng: null},

                    resetSelection() {
                        this.selected = {id: '', label: '', lat: null, lng: null};
                    },

                    async findSuggestions() {
                        if (this.query.trim().length < 4) {
                            this.suggestions = [];
                            this.resetSelection();
                            return;
                        }
                        // дергаем Livewire
                        this.suggestions = await this.$wire.call('searchAddress', this.query);
                        this.open = true;
                    },

                    selectSuggestion(s) {
                        this.selected = {id: s.id, label: s.label, lat: s.lat, lng: s.lng};
                        this.query = s.label;     // показываем читаемый адрес
                        this.open = false;
                    },

                    async submit() {
                        // собираем данные:
                        const payload = {
                            name: this.name,
                            email: this.email,
                            phone: this.phone,

                            // если адрес выбран из подсказки — кладём нормализованные поля
                            address_formatted: this.selected.label || null,
                            address_place_id: this.selected.id || null,
                            address_lat: this.selected.lat ?? null,
                            address_lng: this.selected.lng ?? null,
                        };

                        // если пользователь стирал поле руками после выбора — сбросим координаты
                        if (!this.selected.id || this.query !== this.selected.label) {
                            payload.address_formatted = null;
                            payload.address_place_id = null;
                            payload.address_lat = null;
                            payload.address_lng = null;
                        }

                        await this.$wire.call('createCustomer', payload);
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
