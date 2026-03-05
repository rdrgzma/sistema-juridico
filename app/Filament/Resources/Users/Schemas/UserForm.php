<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload; // Importação para o upload
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação e Acesso')
                    ->schema([
                        // UPLOAD DA FOTO (Substituindo a URL)
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Foto de Perfil')
                            ->collection('avatars')
                            ->avatar() // Deixa o campo circular e otimizado para perfil
                            ->imageEditor()
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Nome Completo')
                            ->required(),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),

                Section::make('Atribuições')
                    ->schema([
                        Select::make('role_id')
                            ->label('Cargo / Função')
                            ->relationship('role', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            // PERMITE CRIAR UM CARGO NO MOMENTO DO CADASTRO
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nome do Cargo')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug())),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique('roles', 'slug'),
                            ]),

                        Select::make('unit_id')
                            ->label('Unidade / Filial')
                            ->relationship('unit', 'name')
                            ->preload()
                            ->searchable(),
                    ])->columns(2),

                Section::make('Preferências')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('locale')
                                    ->label('Idioma')
                                    ->default('pt_BR'),
                                TextInput::make('theme_color')
                                    ->label('Cor do Tema'),
                            ]),
                        Textarea::make('custom_fields')
                            ->label('Observações / Campos Extras')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}