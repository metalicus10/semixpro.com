<div
    x-data="{
        radius: 90,
        items: @entangle('items'),
        animatedValues: [],
        animationDuration: 800,
        get total() {
            return this.items.reduce((sum, i) => sum + Number(i.value), 0);
        },
        getCircumference() {
            return 2 * Math.PI * this.radius;
        },
        get chartCircles() {
            let offset = 0;
            const circumference = this.getCircumference();
            return this.items.map((item, idx) => {
                console.log(item);
                const value = item.animatedValue ?? 0;
                if (value === 0) return '';
                const length = (value / this.total) * circumference;
                const circle = `
                    <circle
                        cx='100' cy='100' r='90' fill='none'
                        stroke='${item.color}' stroke-width='20'
                        stroke-dasharray='${length} ${circumference}'
                        stroke-dashoffset='${-offset}'
                        stroke-linecap='round'
                        style='transition: stroke-dasharray 0.7s, stroke-dashoffset 0.7s;'
                    />`;
                offset += length;
                return circle;
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
            <script>
                window.stockChartLabels = @json($labels);
                window.stockChartData = @json($data);
                window.stockChartColors = @json($colors);
            </script>
            <canvas id="stockDoughnut" width="300" height="300"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center z-50">
                <span class="text-white text-4xl font-extrabold" x-text="numberFormat(total)"></span>
                <span class="uppercase tracking-widest text-[#5A7184] text-sm mt-0">Active Part</span>
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
