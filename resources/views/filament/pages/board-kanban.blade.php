<x-filament-panels::page>
    {{-- BARRA DE FILTROS --}}
    <div style="margin-bottom: 2rem; background: white; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; display: flex; gap: 1rem;">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar tarefa..." style="flex: 1; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.4rem;">
        <select wire:model.live="filterPriority" style="padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.4rem;">
            <option value="">Prioridades</option>
            <option value="baixa">Baixa</option>
            <option value="media">Média</option>
            <option value="alta">Alta</option>
        </select>
    </div>

    {{-- QUADRO KANBAN --}}
    <div style="width: 100%; overflow-x: auto; padding-bottom: 2rem;" class="custom-scrollbar">
        <div style="display: flex !important; flex-direction: row !important; align-items: flex-start !important; gap: 1.5rem !important; min-width: max-content;">
            
            @foreach($board->columns as $column)
                {{-- COLUNA --}}
                <div style="width: 300px !important; flex-shrink: 0 !important; background: #f3f4f6; border-radius: 0.75rem; padding: 1rem;"
                     x-on:dragover.prevent
                     x-on:drop="const taskId = event.dataTransfer.getData('task_id'); $wire.dispatch('update-task-status', { taskId: taskId, newColumnId: {{ $column->id }} })">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #4b5563;">
                            {{ $column->name }}
                        </h3>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="background: #d1d5db; padding: 0.1rem 0.4rem; border-radius: 1rem; font-size: 0.7rem;">{{ $column->tasks->count() }}</span>
                            <x-filament::icon-button icon="heroicon-o-plus" size="sm" wire:click="mountAction('createTask', { column_id: {{ $column->id }} })" />
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem; min-height: 200px;">
                        @foreach($column->tasks as $task)
                            {{-- CARD --}}
                            <div style="background: white; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; cursor: grab;"
                                 draggable="true"
                                 x-on:dragstart="event.dataTransfer.setData('task_id', {{ $task->id }})"
                                 wire:click="mountAction('editTask', { record: {{ $task->id }} })">
                                
                                <p style="font-size: 0.85rem; font-weight: 600; color: #1f2937;">{{ $task->title }}</p>
                                <span style="font-size: 0.65rem; font-weight: 800; color: #3b82f6; text-transform: uppercase;">{{ $task->priority }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    <x-filament-actions::modals />

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .fi-main { overflow: visible !important; }
    </style>
</x-filament-panels::page>
