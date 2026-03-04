<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Enums\ClientType;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(ClientType::class)
                    ->required(),
                TextInput::make('document'),
                Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->required(),
                Repeater::make('contacts')
                    ->relationship()
                    ->schema([
                        TextInput::make('type')->required(),
                        TextInput::make('value')->required(),
                        Toggle::make('is_primary'),
                    ])
                    ->columns(['md' => 3]),
                Repeater::make('addresses')
                    ->relationship()
                    ->schema([
                        TextInput::make('zip_code'),
                        TextInput::make('street'),
                        TextInput::make('number'),
                        TextInput::make('complement'),
                        TextInput::make('neighborhood'),
                        TextInput::make('city'),
                        TextInput::make('state'),
                        Toggle::make('is_primary'),
                    ])
                    ->columns(['md' => 2]),
            ]);
    }
}
