<div>
    <x-filament-panels::page>
        <div class="flex w-full gap-6 overflow-x-auto pb-8 items-start"
            x-data="{
                draggingCard: null,
                dragStartColumn: null,
                onDragStart(event, taskId, columnId) {
                    this.draggingCard = taskId;
                    this.dragStartColumn = columnId;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', taskId);
                    event.target.classList.add('opacity-50', 'scale-95');
                },
                onDragEnd(event) {
                    event.target.classList.remove('opacity-50', 'scale-95');
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
            }">
            @foreach($columns as $column)
                <div class="flex flex-col w-80 min-w-[20rem] shrink-0 gap-4"
                    x-on:dragover="onDragOver($event)"
                    x-on:drop="onDrop($event, {{ $column->id }})">
                    
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 px-4 py-3 rounded-xl shadow-sm">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-800 dark:text-gray-100 uppercase text-xs tracking-wider">{{ $column->name }}</h3>
                            <x-filament::badge size="sm" color="gray">{{ $column->tasks->count() }}</x-filament::badge>
                        </div>
                        <button type="button" 
                                wire:click="mountAction('createTask', { column: {{ $column->id }} })"
                                class="text-primary-600 dark:text-primary-400 hover:text-white p-1 hover:bg-primary-600 rounded transition duration-200" title="Nova Tarefa nesta Coluna">
                            <x-filament::icon icon="heroicon-o-plus" class="w-5 h-5"/>
                        </button>
                    </div>

                    {{-- Cards List --}}
                    <div class="flex flex-col gap-3 min-h-[150px] rounded-lg">
                        @foreach($column->tasks as $task)
                            <div draggable="true"
                                x-on:dragstart="onDragStart($event, {{ $task->id }}, {{ $column->id }})"
                                x-on:dragend="onDragEnd($event)"
                                wire:click="mountAction('editTask', { record: {{ $task->id }} })"
                                class="group relative flex cursor-grab active:cursor-grabbing flex-col gap-3 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 shadow-sm hover:shadow hover:ring-2 hover:ring-primary-500 transition duration-200">
                                
                                {{-- Priority and Process --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-semibold text-gray-400 font-mono tracking-wide">{{ $task->process?->number ?? 'NENHUM PROCESSO' }}</span>
                                    
                                    @php
                                        $priorityVal = $task->priority->value ?? 'baixa';
                                        $badgeColor = match($priorityVal) {
                                            'baixa' => 'success',
                                            'media' => 'warning',
                                            'alta' => 'danger',
                                            default => 'gray',
                                        };
                                        $priorityLabel = Str::upper($task->priority->getLabel());
                                    @endphp
                                    <x-filament::badge size="sm" :color="$badgeColor">
                                        {{ $priorityLabel }}
                                    </x-filament::badge>
                                </div>
                                
                                {{-- Title --}}
                                <h4 class="font-bold text-gray-900 dark:text-white leading-tight">
                                    {{ $task->title }}
                                </h4>

                                {{-- Client --}}
                                @if($task->client)
                                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                                    <x-filament::icon icon="heroicon-o-user" class="w-3.5 h-3.5"/>
                                    <span class="truncate">{{ $task->client->name }}</span>
                                </div>
                                @endif

                                {{-- Footer Details --}}
                                <div class="flex items-center justify-between mt-1 pt-3 border-t border-gray-100 dark:border-gray-800">
                                    
                                    {{-- Icons Box --}}
                                    <div class="flex items-center gap-3 text-xs font-medium text-gray-400">
                                        {{-- Attachments --}}
                                        @if($task->getMedia('anexos')->count() > 0)
                                        <div class="flex items-center gap-1 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                            <x-filament::icon icon="heroicon-o-paper-clip" class="w-4 h-4"/>
                                            <span>{{ $task->getMedia('anexos')->count() }}</span>
                                        </div>
                                        @endif
                                        
                                        {{-- Comments --}}
                                        @if($task->comments()->count() > 0)
                                        <div class="flex items-center gap-1 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                            <x-filament::icon icon="heroicon-o-chat-bubble-left-ellipsis" class="w-4 h-4"/>
                                            <span>{{ $task->comments()->count() }}</span>
                                        </div>
                                        @endif
                                        
                                        {{-- Checklists --}}
                                        @if($task->checklists()->count() > 0)
                                            @php
                                                $totalItems = $task->checklists->flatMap->items->count();
                                                $completedItems = $task->checklists->flatMap->items->where('is_completed', true)->count();
                                                $isAllDone = $totalItems > 0 && $completedItems === $totalItems;
                                            @endphp
                                            <div class="flex items-center gap-1 transition {{ $isAllDone ? 'text-success-600 dark:text-success-500 font-bold' : 'hover:text-gray-600 dark:hover:text-gray-200' }}">
                                                <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4"/>
                                                <span>{{ $completedItems }}/{{ $totalItems }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Assigned User Avatar --}}
                                    @if($task->assignedUser)
                                        <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900 border border-primary-200 dark:border-primary-700 flex items-center justify-center text-[9px] font-bold text-primary-700 dark:text-primary-300 shadow-sm" title="{{ $task->assignedUser->name }}">
                                            {{ Str::upper(substr($task->assignedUser->name, 0, 2)) }}
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
</div>
