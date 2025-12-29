<?php

namespace App\Livewire;

use App\Models\TaskStepEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskSteps extends Component
{
    public ?int $taskId = null;
    public array $steps = ['SCHEDULE','OMW','START','FINISH','INVOICE','PAY'];
    public ?string $current = null;
    public array $timeline = [];
    public array $stepMeta = [];
    public ?string $scheduleFrom12 = null;
    public ?string $scheduleTo12   = null;

    protected $listeners = ['refreshTaskSteps' => '$refresh'];

    public function mount(int $taskId) {
        $this->taskId = $taskId;
        $this->reload();
    }

    #[On('task-steps.switch')]
    public function switchTask($id): void
    {
        $this->taskId = (int) $id;
        $this->reload();
    }

    #[On('task-steps.schedule')]
    public function setSchedule($from = null, $to = null): void
    {
        $this->scheduleFrom12 = $from;
        $this->scheduleTo12   = $to;

        $this->rebuildStepMeta();
    }

    public function canSet(string $step): bool
    {
        // технику запрещаем INVOICE/PAY
        if (auth()->user()->inRole('technician') && in_array($step, ['INVOICE','PAY'])) return false;
        return true;
    }

    public function setStep(string $step): void
    {
        if (empty($this->taskId) || $this->taskId <= 0) {
            $this->dispatch('global-notify', type: 'warning', message: 'Сначала выберите задачу.');
            return;
        }

        if (!$this->canSet($step)) {
            $this->dispatch('global-notify', type:'warning', message:'Недостаточно прав для этого шага.');
            return;
        }
        // разрешаем только следующий по порядку
        $next = $this->nextAllowed();
        if ($step !== $next) {
            $this->dispatch('global-notify', type:'info', message:'Можно поставить только следующий шаг: '.$next);
            return;
        }
        TaskStepEvent::create([
            'task_id'=>$this->taskId,
            'step'=>$step,
            'user_id'=>Auth::id(),
        ]);
        $this->reload();
        $this->dispatch('global-notify', type:'success', message:'Шаг установлен: '.$step);
        $this->dispatch('jobStepChanged', step:$step, jobId:$this->taskId);
    }

    public function undoLast(): void
    {
        $last = TaskStepEvent::where('task_id',$this->taskId)->where('reverted',false)->latest()->first();
        if (!$last) return;
        // нельзя откатывать если уже PAY
        $last->update(['reverted'=>true]);
        $this->reload();
        $this->dispatch('global-notify', type:'success', message:'Отменён шаг: '.$last->step);
        $this->dispatch('jobStepChanged', step:$this->current, jobId:$this->taskId);
    }

    public function nextAllowed(): string
    {
        $idx = array_search($this->current, $this->steps, true);
        return $this->steps[min(($idx===false?-1:$idx)+1, count($this->steps)-1)];
    }

    private function rebuildStepMeta(): void
    {
        $meta = $this->stepMeta ?? [];

        $from = $this->compactAmPm($this->scheduleFrom12 ?? null);
        $to   = $this->compactAmPm($this->scheduleTo12 ?? null);

        $meta['SCHEDULE'] = [];

        if ($from && $to) {
            $meta['SCHEDULE'][] = "{$from} - {$to}";
        }

        $this->stepMeta = $meta;
    }

    private function reload(): void
    {
        if (empty($this->taskId) || $this->taskId <= 0) {
            $this->current = null;
            $this->timeline = [];
            $this->stepMeta = [];
            return;
        }

        $events = TaskStepEvent::where('task_id',$this->taskId)->where('reverted',false)->orderBy('happened_at')->get();
        $this->timeline = $events->map(fn($e)=>[
            'step'=>$e->step,
            'at'=>$e->happened_at->format('M d, Y g:i a'),
            'user'=>$e->user->name ?? '—',
        ])->toArray();

        $this->current = $events->last()->step ?? null;

        $byStep = $events->groupBy('step')->map(fn($g) => $g->last());

        $meta = [];
        $task = \App\Models\Task::query()->find($this->taskId);

        // 1) SCHEDULE — дата/время создания задачи (как у тебя сейчас)
        if (!empty($this->schedule_from_date ?? null)) {
            $meta['SCHEDULE'][] = \Carbon\Carbon::parse($this->schedule_from_date)->format("D, M d 'y");
        }

        $from = $this->schedule_from_time12 ?? null;
        $to   = $this->schedule_to_time12 ?? null;

        if ($from && $to) {
            $meta['SCHEDULE'][] = $this->compactAmPm($from).' - '.$this->compactAmPm($to);
        }

        // 2..6 — дата/время выставления соответствующего шага
        foreach (['FINISH', 'PAY'] as $s) {
            if (isset($byStep[$s])) {
                $meta[$s] = [
                    $byStep[$s]->happened_at->format('D, M d \'y'),
                    $byStep[$s]->happened_at->format('g:i a'),
                ];
            } else {
                $meta[$s] = []; // пока нет — ничего не показываем
            }
        }

        $this->stepMeta = $meta;
    }

    private function compactAmPm(?string $t): ?string
    {
        if ($t === null) return null;

        $t = trim($t);
        if ($t === '') return null;

        return strtolower(str_replace(' ', '', $t));
    }

    public function render()
    {
        return view('livewire.task-steps');
    }
}
