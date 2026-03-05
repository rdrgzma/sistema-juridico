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
                    ->label('Nome') 
                    ->required(),
                Select::make('type')
                    ->label('Tipo')
                    ->options(ClientType::class)
                    ->required(),
                TextInput::make('document')
                    ->label('Documento'),
                Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->required(),
                Repeater::make('contacts')
                    ->label('Contatos')
                    ->relationship()
                    ->schema([
                        TextInput::make('type')
                            ->label('Tipo')
                            ->required(),
                        TextInput::make('value')
                            ->label('Valor')
                            ->required(),
                        Toggle::make('is_primary')
                            ->label('Principal'),
                    ])
                    ->columns(['md' => 3]),
                Repeater::make('addresses')
                    ->label('Endereços')
                    ->relationship()
                    ->schema([
                        TextInput::make('zip_code')
                            ->label('CEP'),
                        TextInput::make('street')
                            ->label('Rua'),
                        TextInput::make('number')
                            ->label('Número'),
                        TextInput::make('complement')
                            ->label('Complemento'),
                        TextInput::make('neighborhood')
                            ->label('Bairro'),
                        TextInput::make('city')
                            ->label('Cidade'),
                        TextInput::make('state')
                            ->label('Estado'),
                        Toggle::make('is_primary')
                            ->label('Principal'),
                    ])
                    ->columns(['md' => 2]),
            ]);
    }
}
