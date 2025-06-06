<div
    x-data="{
        days: ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'],
        sales:   [95000, 75000, 88000, 95000, 76000, 82000, 84000],
        returns: [30000, 23000, 66000, 48000, 21000, 8000, 2000],
        max: 100000,
        get steps() { return [100000, 75000, 50000, 25000, 0]; }
    }"
    class="bg-[#18242D] rounded-2xl p-6 w-full flex flex-col"
>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-white text-lg font-bold">Sales order summary</h2>
        <span class="text-[#00DD7F] text-xs">| Week 1: 12 Jan – 19 Jan</span>
    </div>
    <div class="flex flex-row">
        <!-- Y-Axis -->
        <div class="flex flex-col justify-between items-end h-56 pr-3 select-none">
            <template x-for="s in steps" :key="s">
                <span class="text-[#7A8FA6] text-xs font-semibold" x-text="s >= 1000 ? (s/1000)+'K' : s"></span>
            </template>
        </div>
        <!-- Bar Chart -->
        <div class="relative w-full flex items-end justify-between gap-8 h-56">
            <template x-for="(day, idx) in days" :key="day">
                <div class="flex flex-col items-center w-12">
                    <!-- Столбики: два бара в одном baseline -->
                    <div class="flex justify-between gap-2 h-48 relative">
                        <!-- BG Bars (серый фон) -->
                        <div class="w-5 h-full bg-[#354153] bg-opacity-30 rounded-t-xl absolute left-0 z-0"></div>
                        <div class="w-5 h-full bg-[#354153] bg-opacity-30 rounded-t-xl absolute left-6 z-0"></div>
                        <!-- Sales order (фиолетовый) -->
                        <div
                            class="w-5 rounded-t-xl bg-[#A259FF] absolute left-0 z-10 transition-all"
                            :style="'height: ' + Math.round(sales[idx] / max * 192) + 'px; bottom: 0;'"
                            :title="'Sales order: ' + sales[idx]"
                        ></div>
                        <!-- Sales returned (голубой) -->
                        <div
                            class="w-5 rounded-t-xl bg-[#2CD9FF] absolute left-6 z-10 transition-all"
                            :style="'height: ' + Math.round(returns[idx] / max * 192) + 'px; bottom: 0;'"
                            :title="'Sales returned: ' + returns[idx]"
                        ></div>
                    </div>
                    <span class="text-[#6E7C8C] text-xs text-center mt-2" x-text="day"></span>
                </div>
            </template>
        </div>
    </div>
    <!-- Легенда -->
    <div class="flex items-center gap-4 mt-5 justify-center text-xs">
        <span class="flex items-center gap-2">
            <span class="text-[#7A8FA6] block w-4 h-3 rounded bg-[#A259FF]"></span> Sales order
        </span>
        <span class="flex items-center gap-2">
            <span class="text-[#7A8FA6] block w-4 h-3 rounded bg-[#2CD9FF]"></span> Sales returned
        </span>
    </div>
</div>
