<?php

namespace App\Filament\Pages;

use App\Enums\TaskPriority;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class ViewBoard extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Detalhes do Quadro';

    protected string $view = 'filament.pages.view-board';

    public ?Board $board = null;

    public function mount(): void
    {
        $boardId = request()->query('board');
        if (! $boardId) {
            abort(404, 'ID do quadro não informado.');
        }

        $this->board = Board::findOrFail($boardId);
    }
    
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->board ? 'Quadro: ' . $this->board->name : 'Quadro Kanban';
    }

    public function getColumns(): Collection
    {
        if (! $this->board) {
            return collect();
        }

        return Column::with([
            'tasks.assignedUser',
            'tasks.client',
            'tasks.process',
            'tasks.checklists.items',
            'tasks.media',
            'tasks.comments',
        ])
        ->where('board_id', $this->board->id)
        ->orderBy('sort_order')
        ->get();
    }

    protected function getViewData(): array
    {
        return [
            'board' => $this->board,
            'columns' => $this->getColumns(),
        ];
    }

    public function updateTaskColumn(int $taskId, int $destinationColumnId): void
    {
        $task = Task::with('checklists.items')->find($taskId);
        $destination = Column::find($destinationColumnId);

        if (! $task || ! $destination) {
            return;
        }

        if (str_contains(strtolower($destination->name), 'concluído') || str_contains(strtolower($destination->name), 'done')) {
            $totalItems = $task->checklists->flatMap->items->count();
            $completedItems = $task->checklists->flatMap->items->where('is_completed', true)->count();

            if ($totalItems > 0 && $completedItems < $totalItems) {
                Notification::make()
                    ->title('Ação Bloqueada')
                    ->body('É necessário concluir todos os itens do Checklist para mover o cartão para Concluído.')
                    ->danger()
                    ->send();

                return;
            }
        }

        $task->column_id = $destinationColumnId;
        $task->save();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('voltar')
                ->label('Voltar aos Quadros')
                ->url(BoardsIndex::getUrl())
                ->color('gray')
                ->icon('heroicon-m-arrow-left'),
        ];
    }

    public function createTaskAction(): Action
    {
        return Action::make('createTask')
            ->label('Nova Tarefa')
            ->model(Task::class)
            ->mountUsing(function (\Filament\Forms\Form $form, array $arguments) {
                $form->fill([
                    'column_id' => $arguments['column'] ?? null,
                ]);
            })
            ->form($this->getTaskFormSchema())
            ->slideOver()
            ->action(function (array $data) {
                Task::create($data);
                Notification::make()
                    ->title('Tarefa criada com sucesso')
                    ->success()
                    ->send();
            });
    }

    public function editTaskAction(): Action
    {
        return Action::make('editTask')
            ->record(fn (array $arguments) => Task::find($arguments['record']))
            ->form($this->getTaskFormSchema())
            ->fillForm(function (Task $record): array {
                return $record->toArray();
            })
            ->action(function (array $data, Task $record): void {
                $record->update($data);
                Notification::make()
                    ->title('Tarefa atualizada com sucesso')
                    ->success()
                    ->send();
            })
            ->slideOver();
    }

    protected function getTaskFormSchema(?int $defaultColumnId = null): array
    {
        return [
            TextInput::make('title')->required()->columnSpanFull(),
            RichEditor::make('description')->columnSpanFull(),
            Select::make('column_id')
                ->label('Coluna')
                ->options(Column::where('board_id', $this->board?->id)->pluck('name', 'id')->toArray())
                ->default($defaultColumnId)
                ->required(),
            Select::make('priority')
                ->options(TaskPriority::class)
                ->default(TaskPriority::Baixa)
                ->required(),
            Select::make('assigned_to')
                ->label('Responsável')
                ->relationship('assignedUser', 'name'),
            DatePicker::make('due_date'),
            Select::make('client_id')
                ->relationship('client', 'name'),
            Select::make('process_id')
                ->relationship('process', 'number'),

            // Upload de anexo SpatieMediaLibrary
            SpatieMediaLibraryFileUpload::make('anexos')
                ->collection('anexos')
                ->multiple()
                ->panelLayout('grid')
                ->columnSpanFull(),

            // Checklists
            Repeater::make('checklists')
                ->relationship()
                ->schema([
                    TextInput::make('name')->required()->label('Nome do Checklist'),
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Toggle::make('is_completed')->label('Feito'),
                            TextInput::make('label')->required()->label('Item'),
                            Select::make('responsible_id')
                                ->label('Responsável')
                                ->relationship('responsible', 'name'),
                            DatePicker::make('due_date')->label('Prazo'),
                        ])->columns(['md' => 4]),
                ])->columnSpanFull(),

            // Comentários
            Repeater::make('comments')
                ->relationship()
                ->label('Comentários e Timeline')
                ->schema([
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->default(fn () => auth()->id())
                        ->required()
                        ->label('Usuário'),
                    TextInput::make('content')
                        ->required()
                        ->columnSpanFull()
                        ->label('Comentário'),
                ])->columns(['md' => 2])->columnSpanFull(),
        ];
    }
}
