<x-filament-panels::page>
    <div
        class="flex w-full gap-4 overflow-x-auto pb-4"
        x-data="{
            draggingCard: null,
            dragStartColumn: null,
            onDragStart(event, taskId, columnId) {
                this.draggingCard = taskId;
                this.dragStartColumn = columnId;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', taskId);
                event.target.classList.add('opacity-50');
            },
            onDragEnd(event) {
                event.target.classList.remove('opacity-50');
                this.draggingCard = null;
                this.dragStartColumn = null;
            },
            onDragOver(event) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            },
            onDrop(event, columnId) {
                event.preventDefault();
                const taskId = event.dataTransfer.getData('text/plain');
                if (taskId && this.dragStartColumn !== columnId) {
                    $wire.updateTaskColumn(taskId, columnId);
                }
            }
        }"
    >
        @foreach($this->getColumns() as $column)
            <div
                class="flex w-80 min-w-80 flex-col gap-3 rounded-xl bg-gray-50/50 p-4 dark:bg-gray-900/50"
                x-on:dragover="onDragOver($event)"
                x-on:drop="onDrop($event, {{ $column->id }})"
            >
                <!-- Header da Coluna -->
                <div class="flex items-center justify-between font-semibold text-gray-900 dark:text-white">
                    <h3 class="text-sm uppercase tracking-wider">{{ $column->name }}</h3>
                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        {{ $column->tasks->count() }}
                    </span>
                </div>

                <!-- Lista de Cards -->
                <div class="flex min-h-[150px] flex-col gap-3">
                    @foreach($column->tasks as $task)
                        <div
                            draggable="true"
                            x-on:dragstart="onDragStart($event, {{ $task->id }}, {{ $column->id }})"
                            x-on:dragend="onDragEnd($event)"
                            wire:click="mountAction('editTask', { record: {{ $task->id }} })"
                            class="group relative flex cursor-grab flex-col gap-2 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-950/5 hover:ring-gray-950/20 active:cursor-grabbing dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-white/20"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    {{ $task->process?->number ?? 'N/A' }}
                                </span>
                                @php
                                    $priorityColors = [
                                        'baixa' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                        'media' => 'bg-warning-100 text-warning-700 dark:bg-warning-500/10 dark:text-warning-500',
                                        'alta' => 'bg-danger-100 text-danger-700 dark:bg-danger-500/10 dark:text-danger-500',
                                    ];
                                    $color = $priorityColors[$task->priority->value ?? 'baixa'] ?? $priorityColors['baixa'];
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-medium tracking-wide {{ $color }}">
                                    {{ Str::upper($task->priority->getLabel()) }}
                                </span>
                            </div>

                            <p class="text-sm font-medium text-gray-950 dark:text-white">
                                {{ $task->title }}
                            </p>

                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <div>{{ $task->client?->name ?? 'Sem cliente' }}</div>
                                <div><span class="font-medium">Resp:</span> {{ $task->assignedUser?->name ?? 'Nenhum' }}</div>
                            </div>

                            <!-- Footer Stats -->
                            <div class="mt-1 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-2 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                @if($task->comments()->count() > 0)
                                    <div class="flex items-center gap-1" title="Comentários">
                                        <x-heroicon-o-chat-bubble-left class="h-4 w-4" />
                                        <span>{{ $task->comments()->count() }}</span>
                                    </div>
                                @endif

                                @if($task->getMedia('anexos')->count() > 0)
                                    <div class="flex items-center gap-1" title="Anexos">
                                        <x-heroicon-o-paper-clip class="h-4 w-4" />
                                        <span>{{ $task->getMedia('anexos')->count() }}</span>
                                    </div>
                                @endif
                                
                                @if($task->checklists()->count() > 0)
                                    @php
                                        $totalItems = $task->checklists->flatMap->items->count();
                                        $completedItems = $task->checklists->flatMap->items->where('is_completed', true)->count();
                                        $progressColor = $totalItems > 0 && $completedItems === $totalItems ? 'text-success-600 dark:text-success-500' : '';
                                    @endphp
                                    <div class="flex items-center gap-1 {{ $progressColor }}" title="Checklists">
                                        <x-heroicon-o-check-circle class="h-4 w-4" />
                                        <span>{{ $completedItems }}/{{ $totalItems }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
