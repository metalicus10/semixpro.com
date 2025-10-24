{{-- resources/views/livewire/task-steps.blade.php --}}
<div
    x-data="taskSteps()"
    x-init="init(@js($steps), @js($current), @js($timeline))"
    class="bg-slate-900 text-white rounded-2xl p-5 shadow-lg"
>
    <!-- Прогресс -->
    <div class="flex items-center gap-6">
        <template x-for="(step, i) in state.steps" :key="step">
            <div class="flex items-center gap-4">
                <button
                    class="flex h-12 w-12 items-center justify-center rounded-full ring-2 transition
                           focus:outline-none"
                    :class="isDone(step) ? 'bg-emerald-500 ring-emerald-300' :
                             isCurrent(step) ? 'bg-emerald-400 ring-emerald-200' :
                                               'bg-slate-800 ring-slate-700'"
                    @click="set(step)"
                    :disabled="!canClick(step)"
                >
                    <span class="font-bold" x-text="i+1"></span>
                </button>
                <div class="w-24 text-xs uppercase tracking-wide"
                     :class="isDone(step)||isCurrent(step)?'text-emerald-300':'text-slate-400'">
                    <span x-text="label(step)"></span>
                    <div class="text-[10px] opacity-80" x-show="isDone(step)">
                        <span x-text="timeOf(step)"></span>
                    </div>
                </div>
                <div class="h-1 w-12 rounded"
                     :class="i < state.steps.length-1 ? (isDone(state.steps[i+1]) ? 'bg-emerald-400' : 'bg-slate-700') : 'bg-transparent'"></div>
            </div>
        </template>
    </div>

    <!-- Кнопки действий -->
    <div class="mt-5 flex items-center gap-3">
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
    </div>

    <!-- Таймлайн (flex, не таблица) -->
    <div class="mt-6 space-y-2">
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
    </div>
</div>

<script>
    function taskSteps(){
        return {
            state:{steps:[], current:null, timeline:[]},
            labels:{
                SCHEDULE:'Schedule', OMW:'OMW', START:'Start', FINISH:'Finish', INVOICE:'Invoice', PAY:'Pay'
            },
            init(steps,current,timeline){ this.state.steps=steps; this.state.current=current; this.state.timeline=timeline; },
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
                // кликабелен только следующий по порядку
                return step===this.nextAllowed();
            },
            set(step){ if(!step) return; this.$wire.setStep(step).then(()=>{ this.$dispatch('refreshTaskSteps'); }); },
        }
    }
</script>
