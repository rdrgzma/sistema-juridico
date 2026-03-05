<?php

namespace App\Filament\Pages;

use App\Models\Board;
use App\Models\Task;
use App\Models\Client;
use App\Models\Process;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas; // NOVIDADE v5
use Filament\Schemas\Contracts\HasSchemas;         // NOVIDADE v5
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BoardKanban extends Page implements HasSchemas, HasActions
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.board-kanban';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $maxWidth = 'full';

    public Board $board;

    // Propriedades para filtros reativos
    public ?string $search = '';
    public ?string $filterPriority = '';

    public function mount(): void
    {
        $boardId = request()->query('board');
        $this->loadBoard($boardId);
    }

// Método disparado automaticamente quando as propriedades mudam no Livewire
    public function updatedSearch(): void { $this->loadBoard($this->board->id); }
    public function updatedFilterPriority(): void { $this->loadBoard($this->board->id); }

    protected function loadBoard($id): void
    {
        $this->board = Board::with([
            'columns.tasks' => function ($query) {
                // Aplica filtro de busca por título
                $query->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
                      // Aplica filtro por prioridade
                      ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority));
            },
            'columns.tasks.checklists.items',
            'columns.tasks.comments'
        ])->findOrFail($id);
    }

    /**
     * Lógica de Movimentação (Invocada pelo Drop no Blade)
     */
    #[On('update-task-status')]
    public function updateTaskStatus($taskId, $newColumnId): void
    {
        $task = Task::find($taskId);
        
        if ($task) {
            $task->column_id = $newColumnId;
            $task->save(); // O save() dispara os eventos e o Spatie Activity Log escuta
        }

        $this->loadBoard($this->board->id);
        Notification::make()->title('Tarefa movida!')->success()->send();
    }

/**
     * Action para CRIAR tarefa
     */
    public function createTaskAction(): Action
    {
        return Action::make('createTask')
            ->form([
                TextInput::make('title')->label('Título')->required(),
                Select::make('client_id')
                    ->label('Cliente')
                    ->options(\App\Models\Client::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live() // <-- ATIVA A REATIVIDADE NO FORMULÁRIO
                    ->afterStateUpdated(fn (Set $set) => $set('process_id', null)), // <-- LIMPA O PROCESSO SE TROCAR O CLIENTE
                    
                Select::make('process_id')
                    ->label('Processo')
                    ->options(function (Get $get) {
                        $clientId = $get('client_id'); // Pega o ID do cliente selecionado
                        
                        // Se não tem cliente selecionado, não mostra nenhum processo
                        if (! $clientId) {
                            return []; 
                        }
                        
                        // Se tem cliente, busca apenas os processos daquele cliente
                        // NOTA: Troque o primeiro 'id' pela coluna que quer exibir (ex: 'numero')
                        return \App\Models\Process::where('client_id', $clientId)->pluck('number', 'id');
                    })
                    ->searchable()
                    ->preload(),

                Hidden::make('column_id'),
            ])
            ->mountUsing(fn ($schema, array $arguments) => $schema->fill([
                'column_id' => $arguments['column_id'] ?? null,
            ]))
            ->action(function (array $data) {
                Task::create($data);
                $this->loadBoard($this->board->id);
            });
    }

    /**
     * Action para EDITAR tarefa completa
     */
    public function editTaskAction(): Action
    {
        return Action::make('editTask')
            ->record(fn (array $arguments) => Task::find($arguments['record'] ?? null))
            ->fillForm(fn (Task $record): array => $record->attributesToArray())
            ->modalHeading(fn (Task $record) => "Editar Tarefa: {$record->title}")
            ->form([
                Tabs::make('Detalhes')
                    ->tabs([
                        Tab::make('Geral')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required(),                                
                                Select::make('client_id')
                                    ->label('Cliente')
                                    ->options(\App\Models\Client::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->disabled(), // <--- ADICIONADO AQUI: Desabilita a edição
                                Select::make('process_id')
                                    ->label('Processo')
                                    ->options(function (Get $get) {
                                        $clientId = $get('client_id');                                        
                                        if (! $clientId) {
                                            return [];
                                        }                                        
                                        return \App\Models\Process::where('client_id', $clientId)->pluck('number', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(),
                                    Select::make('assigned_to')
                                    ->label('Atribuir a (Responsável)')
                                    ->options(\App\Models\User::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                                RichEditor::make('description')
                                    ->label('Descrição'),
                                
                                Select::make('priority')
                                    ->options(['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'])
                                    ->required(),
                                
                                DatePicker::make('due_date')
                                    ->label('Prazo'),
                            ]),

                        Tab::make('Checklist')
                            ->schema([
                                Repeater::make('checklists')
                                    ->relationship()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Grupo')
                                            ->required(),
                                        
                                        Repeater::make('items')
                                            ->relationship('items')
                                            ->schema([
                                                Checkbox::make('is_completed')->label('OK'),
                                                TextInput::make('label')->required(),
                                            ])->columns(2)
                                    ])
                            ]),
                        
                        Tab::make('Mídia e Comentários')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('attachments')
                                    ->collection('tasks')
                                    ->multiple(),
                                
                                Repeater::make('comments')
                                    ->relationship()
                                    ->schema([
                                        Textarea::make('content')->required(),
                                        Hidden::make('user_id')->default(auth()->id()),
                                    ])
                            ]),
                    ])
            ])
            ->extraModalFooterActions([
                // ... action de deletar mantida
                Action::make('deleteTask')
                    ->label('Excluir Tarefa')
                    ->color('danger')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Excluir Tarefa')
                    ->modalDescription('Tem certeza que deseja apagar esta tarefa?')
                    ->modalSubmitActionLabel('Sim, excluir permanentemente')
                    ->action(function (Task $record) {
                        $record->delete();
                        $this->loadBoard($this->board->id);
                        Notification::make()
                            ->title('Tarefa removida com sucesso')
                            ->success()
                            ->send();
                    })
            ])
            ->action(function (Task $record, array $data) {
                $record->update($data);
                $this->loadBoard($this->board->id);
                Notification::make()->title('Tarefa salva!')->success()->send();
            });
    }
}