<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'checklistable_id',
        'checklistable_type',
    ];

    public function checklistable(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }
}
