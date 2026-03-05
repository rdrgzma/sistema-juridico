<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Importações necessárias para o Log
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;

class Comment extends Model
{
    use HasFactory, LogsActivity; // Adicionada a trait LogsActivity

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
    ];

    // Configura o log para acompanhar o conteúdo do comentário
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['content'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Comentário {$eventName}");
    }

    // Navega: Comentário -> Tarefa -> Processo (para injetar o process_id)
    public function tapActivity(Activity $activity, string $eventName)
    {
        if ($this->task && $this->task->process_id) {
            $activity->properties = $activity->properties->merge([
                'process_id' => $this->task->process_id,
            ]);
        }
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
