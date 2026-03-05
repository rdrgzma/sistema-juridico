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
    use HasFactory, LogsActivity; // <-- Trait adicionada aqui!

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
    ];

    // 1. Configura o que será salvo no log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['content'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Comentário {$eventName}");
    }

    // 2. Injeta o process_id no log
    public function tapActivity(Activity $activity, string $eventName)
    {
        // Busca a tarefa para pegar o process_id
        $task = $this->task ?? \App\Models\Task::find($this->task_id);
        
        if ($task && $task->process_id) {
            $activity->properties = $activity->properties->merge([
                'process_id' => $task->process_id,
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