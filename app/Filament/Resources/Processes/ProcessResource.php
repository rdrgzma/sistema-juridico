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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

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
