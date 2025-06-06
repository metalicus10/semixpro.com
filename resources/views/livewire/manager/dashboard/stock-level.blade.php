<div
    x-data="{
        radius: 90,
        items: [
            { label: 'HIGH STOCK PRODUCT', value: 4000, color: '#00DD7F', bar: 'bg-[#00DD7F]' },
            { label: 'NEAR-LOW STOCK PRODUCT', value: 2400, color: '#E6F700', bar: 'bg-[#E6F700]' },
            { label: 'LOW STOCK PRODUCT', value: 1800, color: '#FFE658', bar: 'bg-[#FFE658]' },
            { label: 'OUT OF STOCK PRODUCT', value: 372, color: '#E65075', bar: 'bg-[#E65075]' },
        ],
        get total() {
            return this.items.reduce((sum, i) => sum + i.value, 0);
        },
        get circumference() {
            return 2 * Math.PI * this.radius;
        },
        chartData() {
            let offset = 0
            return this.items.map(item => {
                const length = (item.value / this.total) * this.circumference;
                const data = {
                    color: item.color,
                    length: length,
                    offset: offset,
                }
                offset += length;
                return data;
            })
        },
        numberFormat(num) {
            return num.toLocaleString('ru-RU')
        }
    }"
    class="bg-[#1A232F] rounded-2xl p-8 flex gap-10 max-w-3xl w-full"
>
    <!-- Левая часть: Кольцевая диаграмма -->
    <div class="flex flex-col items-center justify-center min-w-[240px]">
        <div class="relative w-[200px] h-[200px] flex items-center justify-center">
            <svg class="absolute inset-0" width="200" height="200" viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="90" stroke="#252E3B" stroke-width="20" fill="none" />
                <g x-for="(item, idx) in chartData" :key="idx">
                    <circle
                        cx="100" cy="100" r="90"
                        fill="none"
                        :stroke="item.color"
                        stroke-width="20"
                        :stroke-dasharray="item.length + ' ' + circumference"
                        :stroke-dashoffset="item.offset"
                        stroke-linecap="round"
                        style="transition: stroke-dasharray 0.7s, stroke-dashoffset 0.7s;"
                    />
                </g>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-white text-4xl font-extrabold" x-text="numberFormat(total)"></span>
                <span class="uppercase tracking-widest text-[#5A7184] text-sm mt-2">Active Product</span>
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
                    <span class="text-[#5A7184] text-sm">products</span>
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
