{{-- resources/views/livewire/task-steps.blade.php --}}
<div
    x-data="taskSteps()"
    x-init="init(@js($steps), @js($current), @js($timeline), @js($stepMeta))"
    class="bg-transparent text-white rounded-2xl py-3"
    :class="!hasTask() ? 'opacity-30 pointer-events-none' : ''"
>
    <!-- Прогресс -->
    <div class="relative flex items-center justify-between w-full" x-ref="trackWrap">
        <!-- СЕРАЯ ЛИНИЯ -->
        <div class="absolute h-[1px] bg-gray-300 z-0"
             :style="`left:${rail.left}px; width:${rail.width}px;top:${rail.top}px`"></div>

        <!-- ШАГИ -->
        <template x-for="(step, i) in state.steps" :key="step">
            <div class="relative z-10 flex flex-col items-center w-full text-center min-h-[107px]">
                <button
                    data-dot
                    class="flex items-center justify-center w-6 h-6 rounded-full ring-2 transition focus:outline-none"
                    :class="isDone(step)?'bg-emerald-500 ring-emerald-300':isCurrent(step)?'bg-emerald-400 ring-emerald-200':'bg-slate-800 ring-slate-600'"
                    @click="set(step)"
                    :disabled="!canClick(step);!hasTask() || !canClick(step)"
                >
                    <span class="font-bold text-white">
                        <template x-if="step === 'SCHEDULE'">
                            @include('icons.calendar')
                        </template>
                        <template x-if="step === 'OMW'">
                            @include('icons.omw')
                        </template>
                        <template x-if="step === 'START'">
                            @include('icons.start')
                        </template>
                        <template x-if="step === 'FINISH'">
                            @include('icons.finish')
                        </template>
                        <template x-if="step === 'INVOICE'">
                            @include('icons.invoice')
                        </template>
                        <template x-if="step === 'PAY'">
                            @include('icons.pay')
                        </template>
                    </span>
                </button>
                <div class="mt-2 w-24 text-xs uppercase tracking-wide"
                     :class="isDone(step)||isCurrent(step)?'text-emerald-300':'text-slate-400'">
                    <span x-text="label(step)"></span>
                    <!-- ДОП. ИНФО ПОД ШАГОМ -->
                    <template x-for="(line, idx) in metaLines(step)" :key="step+'_'+idx">
                        <div class="text-[10px] normal-case opacity-80 leading-4" x-text="line"></div>
                    </template>
                </div>
            </div>
        </template>

        <!-- ЗЕЛЁНЫЙ ПРОГРЕСС -->
        <div class="absolute h-[1px] bg-emerald-400 z-0 transition-all duration-500"
             :style="`left:${rail.left}px; width:${rail.width * progressFrac()}px; top:${rail.top}px`"></div>
    </div>

    <!-- Кнопки действий -->
    <!--<div class="mt-5 flex items-center gap-3">
        <button @click="set(nextAllowed())"
                class="btn btn-primary bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg disabled:opacity-50"
                :disabled="!nextAllowed() || !canClick(nextAllowed())">
            Next: <span class="ml-1" x-text="label(nextAllowed())"></span>
        </button>
        <button @click="$wire.undoLast()" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700">
            Undo last
        </button>
        <div class="ml-auto text-sm text-slate-300">
            Current: <span class="font-semibold" x-text="label(state.current ?? 'SCHEDULE')"></span>
        </div>
    </div>-->

    <!-- Таймлайн (flex, не таблица) -->
    <!--<div class="mt-6 space-y-2">
        <div class="text-xs uppercase tracking-wide text-slate-400">History</div>
        <template x-for="item in state.timeline" :key="item.step+item.at">
            <div class="flex items-center justify-between rounded-lg bg-slate-800 px-3 py-2">
                <div class="flex items-center gap-3">
                    <div class="h-2 w-2 rounded-full" :class="item.step===state.current?'bg-emerald-400':'bg-slate-500'"></div>
                    <div class="text-sm"><span class="font-semibold" x-text="label(item.step)"></span></div>
                </div>
                <div class="text-xs text-slate-400" x-text="item.at + ' · ' + item.user"></div>
            </div>
        </template>
    </div>-->
</div>

<script>
    function taskSteps(){
        return {
            rail:{left:0, width:0, top:0},
            state:{steps:[], current:null, timeline:[], stepMeta:{}},
            ro: null,
            labels:{
                SCHEDULE:'Schedule', OMW:'OMW', START:'Start', FINISH:'Finish', INVOICE:'Invoice', PAY:'Pay'
            },
            init(steps,current,timeline,stepMeta){
                this.state.steps = steps;
                this.state.current = current;
                this.state.timeline = timeline;
                this.state.stepMeta = stepMeta || {};

                // Считаем строго после рендера DOM + layout
                this.$nextTick(() => requestAnimationFrame(() => this.recalcRail()));

                // Пересчёт при ресайзе контейнера
                this.ro = new ResizeObserver(() => this.recalcRail());
                this.ro.observe(this.$refs.trackWrap);

                // На всякий случай — шрифты/события из Livewire
                if (document.fonts?.ready) document.fonts.ready.then(() => this.recalcRail());
                window.addEventListener('jobStepChanged', () => this.$nextTick(() => this.recalcRail()));
                window.addEventListener('alpine:taskStepsSwitch', () => this.$nextTick(() => this.recalcRail()));
            },
            recalcRail(){
                const wrap = this.$refs.trackWrap;
                if (!wrap) return;

                const dots = wrap.querySelectorAll('[data-dot]');
                if (dots.length < 2) { this.rail.left = 0; this.rail.width = 0; this.rail.top = 0; return; }

                const rWrap = wrap.getBoundingClientRect();
                const r1 = dots[0].getBoundingClientRect();
                const r2 = dots[dots.length - 1].getBoundingClientRect();

                // центры кружков в координатах wrap
                const x1 = (r1.left + r1.width / 2) - rWrap.left;
                const x2 = (r2.left + r2.width / 2) - rWrap.left;
                const y = (r1.top + r1.height / 2) - rWrap.top;

                // DEBUG: один раз посмотри значения
                // console.log({wrap:rWrap, first:r1, last:r2, x1, x2});

                this.rail.left  = Math.round(x1);
                this.rail.width = Math.max(0, Math.round(x2 - x1));
                this.rail.top   = Math.round(y);
            },
            progressFrac(){
                if (!this.state.current) return 0;
                const idx  = this.state.steps.indexOf(this.state.current);
                const segs = this.state.steps.length - 1;
                return Math.max(0, Math.min(1, idx / segs));
            },
            label(s){ return this.labels[s] ?? s; },
            isCurrent(s){ return this.state.current===s; },
            isDone(s){ const idxCur=this.state.steps.indexOf(this.state.current); return idxCur>=this.state.steps.indexOf(s); },
            nextAllowed(){
                const i=this.state.steps.indexOf(this.state.current);
                return this.state.steps[Math.min((i<0?-1:i)+1, this.state.steps.length-1)];
            },
            timeOf(step){
                const it=this.state.timeline.find(t=>t.step===step);
                return it ? it.at : '';
            },
            canClick(step){
                return step===this.nextAllowed();
            },
            set(step){ if(!step) return; this.$wire.setStep(step).then(()=>{ this.$dispatch('refreshTaskSteps'); }); },

            hasTask(){
                return Number(this.$wire.taskId || 0) > 0;
            },
            metaLines(step){
                const lines = this.state.stepMeta?.[step] || [];
                return Array.isArray(lines) ? lines : [];
            },
        }
    }
</script>
