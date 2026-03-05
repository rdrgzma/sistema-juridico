<?php

namespace App\Models;

use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;

class Task extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'column_id',
        'assigned_to',
        'priority',
        'due_date',
        'client_id',
        'process_id',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'due_date' => 'date',
        ];
    }
    // No arquivo app/Models/Task.php
    protected static function booted()
    {
        static::deleting(function ($task) {
            $task->checklists()->delete();
            $task->comments()->delete();
        });
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function checklists(): MorphMany
    {
        return $this->morphMany(Checklist::class, 'checklistable');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Tarefa {$eventName}")
            ->tap(function (Activity $activity) {
                // Injeta o ID do processo pai no JSON do log
                // Ajuste '$this->process_id' conforme o seu relacionamento real
                $activity->properties = $activity->properties->merge([
                    'process_id' => $this->process_id, 
                ]);
            });
    }
}
