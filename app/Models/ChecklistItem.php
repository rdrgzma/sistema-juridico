<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

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

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
