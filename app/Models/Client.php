<?php

namespace App\Models;

use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'document',
        'unit_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => ClientType::class,
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }
}
