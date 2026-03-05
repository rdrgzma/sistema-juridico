<?php

namespace App\Models;

use App\Enums\ProcessProbability;
use App\Enums\ProcessRiskLevel;
use App\Enums\ProcessStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
// IMPORTAÇÕES DO SPATIE MEDIA LIBRARY
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Process extends Model implements HasMedia
{
    // Adicionada a trait InteractsWithMedia
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'number',
        'court',
        'value',
        'probability',
        'risk_level',
        'status',
        'client_id',
        'unit_id',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'probability' => ProcessProbability::class,
            'risk_level' => ProcessRiskLevel::class,
            'status' => ProcessStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function checklists(): MorphMany
    {
        return $this->morphMany(Checklist::class, 'checklistable');
    }

    /**
     * Relacionamento com as Tasks para a Timeline
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}