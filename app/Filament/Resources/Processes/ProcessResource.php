<?php

namespace App\Filament\Resources\Processes;

use App\Filament\Resources\Processes\Pages\CreateProcess;
use App\Filament\Resources\Processes\Pages\EditProcess;
use App\Filament\Resources\Processes\Pages\ListProcesses;
use App\Filament\Resources\Processes\Schemas\ProcessForm;
use App\Filament\Resources\Processes\Tables\ProcessesTable;
use App\Models\Process;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Processes\Pages;
use Filament\Schemas\Schema; // Adaptação para a versão que você está utilizando
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Tables\Actions\ViewAction;

class ProcessResource extends Resource
{
    protected static ?string $model = Process::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProcessForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProcessesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // CABEÇALHO FIXO
                Section::make('Resumo do Processo')
                    ->components([
                        Grid::make(4)
                            ->components([
                                TextEntry::make('number')
                                    ->label('Número / Título')
                                    ->weight('bold'),
                                    
                                TextEntry::make('client.name')
                                    ->label('Cliente'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                                        'ativo' => 'success',
                                        'arquivado' => 'gray',
                                        'suspenso' => 'warning',
                                        default => 'primary',
                                    }),
                                                                    
                                TextEntry::make('risk_level')
                                    ->label('Risco'),
                            ])
                    ]),

                // SISTEMA DE ABAS (TIMELINE E DOCUMENTOS)
                Tabs::make('NavegacaoProcesso')
                    ->tabs([
                        
                        Tabs\Tab::make('Histórico e Movimentações')
                            ->icon('heroicon-m-clock')
                            ->components([
                                Livewire::make('⚡process-timeline')
                            ]),

                        Tabs\Tab::make('Documentos Anexados')
                            ->icon('heroicon-m-document-duplicate')
                            ->components([
                                // Verifique se o relacionamento no model Process se chama 'media'
                                RepeatableEntry::make('media')
                                    ->label('') 
                                    ->schema([
                                        TextEntry::make('file_name')
                                            ->label('Nome do Arquivo')
                                            ->icon('heroicon-m-paper-clip')
                                            ->weight('medium'),
                                            
                                        TextEntry::make('created_at')
                                            ->label('Data de Upload')
                                            ->dateTime('d/m/Y H:i'),
                                            
                                        TextEntry::make('download')
                                            ->label('Ação')
                                            ->default('Baixar')
                                            ->color('primary')
                                            ->icon('heroicon-m-arrow-down-tray')
                                            // Ajuste o path conforme o seu sistema de arquivos
                                            ->url(fn ($record) => asset('storage/' . $record->id . '/' . $record->file_name), shouldOpenInNewTab: true), 
                                    ])
                                    ->columns(3)
                                    ->contained(true)
                            ]),
                    ])
                    ->columnSpanFull()
                    ->activeTab(1),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProcesses::route('/'),
            'create' => CreateProcess::route('/create'),
            'edit' => EditProcess::route('/{record}/edit'),
        ];
    }
}
