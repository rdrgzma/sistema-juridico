<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
// Importações necessárias para o Log
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;

class Checklist extends Model
{
    use HasFactory, LogsActivity; // Adicionada a trait LogsActivity

    protected $fillable = [
        'name',
        'checklistable_id',
        'checklistable_type',
    ];

    // Configuração do que deve ser logado
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name']) // Loga quando o nome do checklist for criado/alterado
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Lista de Tarefas {$eventName}");
    }

    // Injeta o ID do processo pai para aparecer na timeline do processo
    public function tapActivity(Activity $activity, string $eventName)
    {
        // Verifica se o checklist pertence a uma Task
        if ($this->checklistable_type === \App\Models\Task::class && $this->checklistable) {
            $processId = $this->checklistable->process_id;
            
            if ($processId) {
                $activity->properties = $activity->properties->merge([
                    'process_id' => $processId,
                ]);
            }
        }
    }

    public function checklistable(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }
}