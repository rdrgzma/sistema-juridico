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
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use UnitEnum;

class ProcessResource extends Resource
{
    protected static ?string $model = Process::class;
    protected static string|UnitEnum|null $navigationGroup = 'Jurídico';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;

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
                        Grid::make(2)
                            ->components([
                                TextEntry::make('number')
                                    ->label('Número')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->columnSpanFull(),
                                    
                                TextEntry::make('client.name')
                                    ->label('Cliente')
                                    ->size('lg')
                                    ->color('primary') // Dá um destaque azul/primário ao nome do cliente
                                    ->columnSpanFull(),
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
                    ])
                    ->columnSpanFull(),

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
                                RepeatableEntry::make('all_documents') // Nome fictício para o estado customizado
                                    ->label('')
                                    ->state(function ($record) {
                                        // Buscamos as mídias do processo
                                        $processMedia = $record->getMedia(); 
                                        
                                        // Buscamos as mídias de todas as tasks desse processo
                                        // Usamos o model Media do Spatie para buscar onde o model_type é Task e os IDs batem
                                        $tasksIds = $record->tasks()->pluck('id')->toArray();
                                        $tasksMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Task::class)
                                            ->whereIn('model_id', $tasksIds)
                                            ->get();

                                        // Unimos as duas coleções
                                        return $processMedia->concat($tasksMedia);
                                    })
                                    ->schema([
                                        TextEntry::make('file_name')
                                            ->label('Nome do Arquivo')
                                            ->icon('heroicon-m-paper-clip')
                                            ->weight('medium'),

                                        TextEntry::make('model_type')
                                            ->label('Origem')
                                            ->formatStateUsing(fn ($state) => str_contains($state, 'Task') ? 'Tarefa' : 'Processo')
                                            ->badge()
                                            ->color(fn ($state) => str_contains($state, 'Task') ? 'warning' : 'success'),

                                        TextEntry::make('created_at')
                                            ->label('Data de Upload')
                                            ->dateTime('d/m/Y H:i'),

                                        TextEntry::make('download')
                                            ->label('Ação')
                                            ->default('Baixar')
                                            ->color('primary')
                                            ->icon('heroicon-m-arrow-down-tray')
                                            // IMPORTANTE: Como os arquivos são PRIVADOS, usamos o link temporário do Spatie
                                            ->url(fn ($record) => $record->getTemporaryUrl(now()->addMinutes(10)), shouldOpenInNewTab: true),
                                    ])
                                    ->columns(4)
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
