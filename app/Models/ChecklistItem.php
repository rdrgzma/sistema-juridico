<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Importações necessárias para o Log
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;

class ChecklistItem extends Model
{
    use HasFactory, LogsActivity; // Adicionada a trait LogsActivity

    protected $fillable = [
        'checklist_id',
        'label',
        'is_completed',
        'sort_order',
        'responsible_id',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'due_date' => 'date',
        ];
    }

    // Configura o log para acompanhar o label e o status de conclusão
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['label', 'is_completed', 'due_date', 'responsible_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Item do Checklist {$eventName}");
    }

    // Navega: Item -> Checklist -> Tarefa -> Processo (para injetar o process_id)
    public function tapActivity(Activity $activity, string $eventName)
    {
        if ($this->checklist && $this->checklist->checklistable_type === \App\Models\Task::class && $this->checklist->checklistable) {
            $processId = $this->checklist->checklistable->process_id;
            
            if ($processId) {
                $activity->properties = $activity->properties->merge([
                    'process_id' => $processId,
                ]);
            }
        }
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}