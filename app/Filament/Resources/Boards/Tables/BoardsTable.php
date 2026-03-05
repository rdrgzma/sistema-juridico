<?php

namespace App\Filament\Resources\Boards\Tables;

use App\Models\Board;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Pages\BoardKanban;
// No v5, para ações dentro de Tabelas, use o namespace Tables\Actions para manter o contexto do registro
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;

class BoardsTable
{
    public static function make(Table $table): Table
    {
        // Retornamos o objeto $table vindo do Resource após configurá-lo
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('client.name')
                        ->disabled()
                        ->weight('bold')
                        ->size('lg'),
                    Tables\Columns\TextColumn::make('process.number')
                        ->disabled()
                        ->weight('bold')
                        ->size('lg'),
                    Tables\Columns\TextColumn::make('name')
                        ->label('Quadro')
                        ->weight('bold')
                        ->size('lg'),
                    Tables\Columns\TextColumn::make('unit.name')
                        ->color('gray'),
                ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('open')
                    ->label('Ver Quadro')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->color('success')
                    ->url(fn (Board $record) => BoardKanban::getUrl(['board' => $record->id])),
            ])
            ->recordUrl(fn (Board $record) => BoardKanban::getUrl(['board' => $record->id]));
    }
}
