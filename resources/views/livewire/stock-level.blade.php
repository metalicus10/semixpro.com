<div
    x-data="{
        radius: 90,
        items: @entangle('items'),
        get total() {
            return this.items.reduce((sum, i) => sum + i.value, 0);
        },
        get circumference() {
            return 2 * Math.PI * this.radius;
        },
        get chartCircles() {
            let offset = 0;
            return this.items.map(item => {
                const length = (item.value / this.total) * this.circumference
                const c = `<circle cx='100' cy='100' r='90' fill='none'
                    stroke='${item.color}' stroke-width='20'
                    stroke-dasharray='${length} ${this.circumference}'
                    stroke-dashoffset='${offset}'
                    stroke-linecap='round'
                    style='transition: stroke-dasharray 0.7s, stroke-dashoffset 0.7s;'/>`
                offset += length
                return c
            }).join('');
        },
        numberFormat(num) {
            return num.toLocaleString('ru-RU');
        }
    }"
    class="bg-[#1A232F] rounded-xl border border-slate-700 p-6 flex gap-10 w-full"
>
    <!-- Левая часть: Кольцевая диаграмма -->
    <div class="flex flex-col items-center justify-center min-w-[240px]">
        <div class="relative w-[200px] h-[200px] flex items-center justify-center">
            <svg class="absolute inset-0" width="200" height="200" viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="90" stroke="#252E3B" stroke-width="20" fill="none" />
                <g x-html="chartCircles"></g>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-white text-4xl font-extrabold" x-text="numberFormat(total)"></span>
                <span class="uppercase tracking-widest text-[#5A7184] text-sm mt-2">Active Part</span>
            </div>
        </div>
    </div>
    <!-- Правая часть: Легенда + прогрессбары -->
    <div class="flex flex-col justify-center w-full gap-6">
        <template x-for="(item, idx) in items" :key="idx">
            <div>
                <span class="block font-semibold text-sm mb-1"
                      :class="{
                        'text-white': idx === 0,
                        'text-[#E6F700]': idx === 1,
                        'text-[#FFE658]': idx === 2,
                        'text-[#E65075]': idx === 3
                      }"
                      x-text="item.label"
                ></span>
                <div class="flex items-center gap-3">
                    <span class="text-white text-lg font-semibold" x-text="numberFormat(item.value)"></span>
                    <span class="text-[#5A7184] text-sm">parts</span>
                    <div class="flex-1 h-2 rounded bg-[#252E3B] ml-4 overflow-hidden">
                        <div class="h-2 transition-all duration-500"
                             :class="item.bar"
                             :style="'width: ' + Math.round(item.value / total * 100) + '%'"
                        ></div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
