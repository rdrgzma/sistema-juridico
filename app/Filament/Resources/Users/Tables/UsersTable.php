<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                // EXIBIÇÃO DO CARGO / FUNÇÃO
                TextColumn::make('role.name')
                    ->label('Cargo')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->placeholder('Sem cargo'),

                // EXIBIÇÃO DA UNIDADE
                TextColumn::make('unit.name')
                    ->label('Unidade/Filial')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),

                // DATAS (Organizadas)
                TextColumn::make('created_at')
                    ->label('Cadastro em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // CAMPOS DE PERFIL (Escondidos por padrão para não poluir)
                TextColumn::make('locale')
                    ->label('Idioma')
                    ->toggleable(isToggledHiddenByDefault: true),

                ColorColumn::make('theme_color')
                    ->label('Cor do Tema')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro rápido por Unidade
                SelectFilter::make('unit')
                    ->relationship('unit', 'name')
                    ->label('Filtrar por Unidade'),
                
                // Filtro rápido por Cargo
                SelectFilter::make('role')
                    ->relationship('role', 'name')
                    ->label('Filtrar por Cargo'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
