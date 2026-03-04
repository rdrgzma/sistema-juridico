<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaskPriority: string implements HasLabel
{
    case Baixa = 'baixa';
    case Media = 'media';
    case Alta = 'alta';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Baixa => 'Baixa',
            self::Media => 'Média',
            self::Alta => 'Alta',
        };
    }
}
