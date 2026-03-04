<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProcessProbability: string implements HasLabel
{
    case Alta = 'alta';
    case Media = 'media';
    case Baixa = 'baixa';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Alta => 'Alta',
            self::Media => 'Média',
            self::Baixa => 'Baixa',
        };
    }
}
