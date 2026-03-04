<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProcessStatus: string implements HasLabel
{
    case Ativo = 'ativo';
    case Arquivado = 'arquivado';
    case Suspenso = 'suspenso';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Arquivado => 'Arquivado',
            self::Suspenso => 'Suspenso',
        };
    }
}
