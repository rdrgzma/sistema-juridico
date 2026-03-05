<?php

namespace App\Filament\Resources\Processes\Schemas;

use App\Enums\ProcessProbability;
use App\Enums\ProcessRiskLevel;
use App\Enums\ProcessStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProcessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Número')
                    ->required(),
                TextInput::make('court')
                    ->label('Tribunal'),
                TextInput::make('value')
                    ->label('Valor')
                    ->numeric(),
                Select::make('probability')
                    ->label('Probabilidade')
                    ->options(ProcessProbability::class),
                Select::make('risk_level')
                    ->label('Risco')
                    ->options(ProcessRiskLevel::class),
                Select::make('status')
                    ->label('Status')
                    ->options(ProcessStatus::class),
                Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->required(),
                Select::make('unit_id')
                    ->label('Unidade')
                    ->relationship('unit', 'name')
                    ->required(),
            ]);
    }
}
