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
                    ->required(),
                TextInput::make('court'),
                TextInput::make('value')
                    ->numeric(),
                Select::make('probability')
                    ->options(ProcessProbability::class),
                Select::make('risk_level')
                    ->options(ProcessRiskLevel::class),
                Select::make('status')
                    ->options(ProcessStatus::class),
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->required(),
                Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->required(),
            ]);
    }
}
