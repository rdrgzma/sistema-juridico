<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'state',
        'responsible_name',
        'status',
        'is_matriz',
    ];

    protected function casts(): array
    {
        return [
            'is_matriz' => 'boolean',
        ];
    }

    public function clients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function processes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Process::class);
    }
}
