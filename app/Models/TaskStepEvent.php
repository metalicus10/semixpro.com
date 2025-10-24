<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskStepEvent extends Model
{
    use HasFactory;

    protected $table = 'task_step_events';

    protected $fillable = [
        'task_id',
        'step',         // SCHEDULE | OMW | START | FINISH | INVOICE | PAY
        'happened_at',
        'user_id',
        'reverted',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
        'reverted'    => 'boolean',
    ];

    /** Порядок шагов по бизнес-логике */
    public const ORDER = ['SCHEDULE','OMW','START','FINISH','INVOICE','PAY'];

    /* ======================= Relations ======================= */

    public function task(): BelongsTo
    {
        // Если у вас модель называется иначе — поправьте класс здесь
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ========================= Scopes ======================== */

    /** События по конкретной задаче */
    public function scopeForTask($q, int $taskId)
    {
        return $q->where('task_id', $taskId);
    }

    /** Неоткаченные события */
    public function scopeActive($q)
    {
        return $q->where('reverted', false);
    }

    /** Последние активные (удобно для текущего шага) */
    public function scopeLatestActive($q)
    {
        return $q->active()->orderByDesc('happened_at')->orderByDesc('id');
    }

    /* ====================== Helpers ========================== */

    /** Возвращает следующий допустимый шаг для переданного текущего */
    public static function nextAllowed(?string $current): string
    {
        $idx = array_search($current, self::ORDER, true);
        $nextIndex = min(($idx === false ? -1 : $idx) + 1, count(self::ORDER) - 1);
        return self::ORDER[$nextIndex];
    }

    /** Проверка порядка: можно ли поставить $target после $current */
    public static function isNext(string $target, ?string $current): bool
    {
        return $target === self::nextAllowed($current);
    }
}
