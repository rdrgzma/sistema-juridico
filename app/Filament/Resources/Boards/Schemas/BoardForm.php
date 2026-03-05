<?php

namespace App\Filament\Resources\Boards\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms;
use Filament\Schemas\Schema;

class BoardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('unit_id')
                ->searchable()
                    ->relationship('unit', 'name')
                    ->required(),
                    Repeater::make('columns')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('name')->required(),
                ])->orderColumn('sort_order')
            ]);
    }
}
