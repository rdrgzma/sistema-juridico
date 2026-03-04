<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProcessRiskLevel: string implements HasLabel
{
    case Alto = 'alto';
    case Medio = 'medio';
    case Baixo = 'baixo';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Alto => 'Alto',
            self::Medio => 'Médio',
            self::Baixo => 'Baixo',
        };
    }
}
