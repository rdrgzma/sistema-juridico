<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ClientType: string implements HasLabel
{
    case PF = 'pf';
    case PJ = 'pj';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PF => 'Pessoa Física (PF)',
            self::PJ => 'Pessoa Jurídica (PJ)',
        };
    }
}
