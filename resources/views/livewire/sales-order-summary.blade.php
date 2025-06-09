<div
    x-data="{
        open: false,
        today: new Date(),
        month: (new Date()).getMonth(),
        year: (new Date()).getFullYear(),
        get firstDay() {
            return new Date(this.year, this.month, 1)
        },
        get lastDay() {
            return new Date(this.year, this.month + 1, 0)
        },
        get weeks() {
            let weeks = []
            let weekStart = new Date(this.firstDay)
            let weekNum = 1
            while (weekStart <= this.lastDay) {
                let weekEnd = new Date(weekStart)
                weekEnd.setDate(weekEnd.getDate() + 6)
                if (weekEnd > this.lastDay) weekEnd = new Date(this.lastDay)
                weeks.push({
                    number: weekNum,
                    start: new Date(weekStart),
                    end: new Date(weekEnd),
                })
                weekNum++
                weekStart.setDate(weekStart.getDate() + 7)
            }
            return weeks
        },
        get availableWeeks() {
            return this.weeks.filter(w => w.end <= this.today)
        },
        selectedWeekIndex: -1,
        selectedWeek: null,
        weekLabel(week) {
            const ruMonths = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек']
            return `Неделя ${week.number}: ${week.start.getDate()} ${ruMonths[week.start.getMonth()]} - ${week.end.getDate()} ${ruMonths[week.end.getMonth()]}`
        },
        salesFromServer: @entangle('sales').defer,
        returnsFromServer: @entangle('returns').defer,
        sales: [],
        returns: [],
        animatedSales: [],
        animatedReturns: [],
        animateBars() {
            this.animatedSales = this.sales.map(() => 0)
            this.animatedReturns = this.returns.map(() => 0)
            setTimeout(() => {
                const max = Math.max(...this.sales, 1)
                this.animatedSales = this.sales.map(s => Math.round(s / max * 100))
                this.animatedReturns = this.returns.map(r => Math.round(r / max * 100))
            }, 50)
        },
        selectWeek(idx) {
            this.selectedWeek = this.availableWeeks[idx]
            this.selectedWeekIndex = idx
            this.open = false
            // передаём диапазон дат в Livewire (формат: ISO-строка)
            $wire.changeWeek(
                this.selectedWeek.start.toISOString().slice(0, 10),
                this.selectedWeek.end.toISOString().slice(0, 10)
            )
        },
        init() {
            this.selectedWeekIndex = this.availableWeeks.length - 1
            this.selectedWeek = this.availableWeeks[this.selectedWeekIndex] ?? null
            // Инициируем первый fetch данных
            if (this.selectedWeek) {
                $wire.changeWeek(
                    this.selectedWeek.start.toISOString().slice(0, 10),
                    this.selectedWeek.end.toISOString().slice(0, 10)
                )
            }
        }
    }" x-init="init()"
    x-effect="
        sales = salesFromServer;
        returns = returnsFromServer;
        animateBars();
    "
    class="bg-[#18242D] rounded-xl p-6 w-full h-full flex flex-col"
>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-white text-lg font-bold">Sales order summary</h2>
        <div class="relative">
            <!-- Селект -->
            <button
                @click="open = !open"
                class="flex items-start justify-between w-60 px-4 py-2 rounded-xl bg-[#202C3A] text-[#D3DAE6] text-sm font-medium shadow border border-[#3A4553] focus:outline-none transition"
                type="button"
            >
                <span x-text="selectedWeek ? weekLabel(selectedWeek) : 'Выберите неделю'"></span>
                <svg class="w-4 h-4 ml-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <!-- Выпадающий список -->
            <div
                x-show="open"
                @click.away="open = false"
                class="absolute left-0 mt-2 w-60 rounded-lg bg-[#202C3A] shadow-lg border border-[#3A4553] z-50"
                style="display: none;"
            >
                <template x-for="(week, idx) in availableWeeks" :key="week.start">
                    <div
                        @click="selectWeek(idx)"
                        class="flex items-start text-sm px-4 py-2 cursor-pointer hover:bg-[#29374B] transition text-[#D3DAE6]"
                        :class="selectedWeekIndex === idx ? 'bg-[#232B37] font-semibold' : ''"
                    >
                        <svg x-show="selectedWeekIndex === idx" class="w-4 h-4 text-[#85F0B7] mr-2" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="weekLabel(week)"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <div class="flex flex-row w-full h-full">
        <!-- Y-Axis -->
        <div class="flex flex-col justify-between items-end h-[90%] pr-3 w-12 select-none">
            <template x-for="(s, idx) in steps" :key="idx">
            <span class="text-[#7A8FA6] text-xs font-semibold"
                  :style="idx == 0 ? 'transform: translateY(-8px)' : idx == steps.length-1 ? 'transform: translateY(8px)' : ''"
                  x-text="s >= 1000 ? (s/1000)+'K' : s"></span>
            </template>
        </div>
        <!-- Bar Chart + Days -->
        <div class="flex flex-col w-full h-full">
            <!-- Bar Chart -->
            <div class="relative flex items-end justify-between gap-8 h-[90%]">
                <!-- Горизонтальные линии -->
                <template x-for="(s, idx) in steps" :key="idx">
                    <div
                        class="absolute left-0 w-full border-t border-dashed border-[#526079] opacity-80 z-10"
                        :style="{
                        top: ((1 - (s / max)) * 100) + '%'
                    }"
                    ></div>
                </template>
                <template x-for="(day, idx) in days" :key="day">
                    <div class="flex flex-col items-center w-12 h-full">
                        <div class="relative flex gap-2 items-end h-full w-full">
                            <!-- BG Bars (серый фон) -->
                            <div class="w-6 h-full bg-[#354153] bg-opacity-30 rounded-t-xl absolute left-0 z-0"></div>
                            <div class="w-6 h-full bg-[#354153] bg-opacity-30 rounded-t-xl absolute left-7 z-0"></div>
                            <!-- Бары -->
                            <div class="w-6 rounded-t-xl bg-[#A259FF] absolute left-0 bottom-0 z-10 "
                                 :style="'height: ' + animatedSales[idx] + '%; transition: height 2.0s cubic-bezier(0.22,1,0.36,1);'"
                            ></div>
                            <div class="w-6 rounded-t-xl bg-[#2CD9FF] absolute left-7 bottom-0 z-10 "
                                 :style="'height: ' + animatedSales[idx] + '%; transition: height 2.0s cubic-bezier(0.22,1,0.36,1);'"
                            ></div>
                        </div>
                    </div>
                </template>
            </div>
            <!-- Дни недели (строго под графиком) -->
            <div class="flex items-end justify-between gap-8 h-[10%]">
                <template x-for="(day, idx) in days" :key="day">
                    <span class="text-[#6E7C8C] text-xs text-center w-12" x-text="day"></span>
                </template>
            </div>
        </div>
    </div>
    <!-- Легенда -->
    <div class="flex items-center gap-4 mt-5 text-gray-400 justify-center text-xs">
        <span class="flex items-center gap-2">
            <span class="block w-4 h-3 rounded bg-[#A259FF]"></span> Sales order
        </span>
        <span class="flex items-center gap-2">
            <span class="block w-4 h-3 rounded bg-[#2CD9FF]"></span> Sales returned
        </span>
    </div>
</div>
