<?php

namespace App\Livewire;

use App\Models\TaskStepEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TaskSteps extends Component
{
    public int $taskId;
    public array $steps = ['SCHEDULE','OMW','START','FINISH','INVOICE','PAY'];
    public ?string $current = null;           // текущее имя шага
    public array $timeline = [];              // события для вывода

    protected $listeners = ['refreshTaskSteps' => '$refresh'];

    public function mount(int $taskId) {
        $this->taskId = $taskId;
        $this->reload();
    }

    public function canSet(string $step): bool
    {
        // технику запрещаем INVOICE/PAY
        if (auth()->user()->hasRole('technician') && in_array($step, ['INVOICE','PAY'])) return false;
        return true;
    }

    public function setStep(string $step): void
    {
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

    private function reload(): void
    {
        $events = TaskStepEvent::where('task_id',$this->taskId)->where('reverted',false)->orderBy('happened_at')->get();
        $this->timeline = $events->map(fn($e)=>[
            'step'=>$e->step,
            'at'=>$e->happened_at->format('M d, Y g:i a'),
            'user'=>$e->user->name ?? '—',
        ])->toArray();

        $this->current = $events->last()->step ?? null;
    }

    public function render()
    {
        return view('livewire.task-steps');
    }
}
