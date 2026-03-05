<?php

use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use App\Models\Process;
use App\Models\Column;
use App\Models\User;

new class extends Component
{
    public ?Process $record = null;

    public function with(): array
    {
        if (! $this->record) {
            return ['activities' => collect(), 'columnsMap' => [], 'usersMap' => []];
        }

        $activities = Activity::query()
            ->where(function ($query) {
                $query->where('subject_type', Process::class)
                      ->where('subject_id', $this->record->id);
            })
            ->orWhere('properties->process_id', $this->record->id)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        return [
            'activities' => $activities,
            'columnsMap' => Column::pluck('name', 'id')->toArray(),
            'usersMap'   => User::pluck('name', 'id')->toArray(),
        ];
    }
};
?>

<div class="space-y-4">
    <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-3">
        @forelse($activities as $activity)
            <div class="mb-6 ml-6">
                <span class="absolute flex items-center justify-center w-8 h-8 bg-primary-100 rounded-full -left-4 ring-4 ring-white dark:ring-gray-900 dark:bg-primary-900">
                    <x-filament::avatar :user="$activity->causer" size="sm" />
                </span>
                
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-1">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $activity->causer->name ?? 'Sistema' }}
                        </div>
                        <time class="flex items-center gap-1 text-xs font-normal text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-m-clock" class="w-3 h-3" />
                            {{ $activity->created_at->format('d/m/Y - H:i') }}
                        </time>
                    </div>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        <strong class="block mb-1">{{ $activity->description }}</strong> 

                        {{-- 1. UPLOAD DE DOCUMENTOS --}}
                        @if(isset($activity->properties['custom_type']) && $activity->properties['custom_type'] === 'document_upload')
                            <div class="mt-2 flex items-center gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                                <div class="p-2 bg-blue-500 rounded-lg">
                                    <x-filament::icon icon="heroicon-m-document-text" class="w-5 h-5 text-white" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-blue-900 dark:text-blue-300">
                                        {{ $activity->properties['file_name'] ?? 'Arquivo anexado' }}
                                    </span>
                                    <span class="text-xs text-blue-700 dark:text-blue-400 font-medium">Novo arquivo disponível na aba de Documentos</span>
                                </div>
                            </div>

                        {{-- 2. COMENTÁRIOS --}}
                        @elseif($activity->subject_type === 'App\Models\Comment' || (isset($activity->properties['attributes']['content']) && $activity->subject_type === 'App\Models\Comment'))
                            @php
                                $comentarioRaw = $activity->properties['attributes']['content'] ?? ($activity->properties['old']['content'] ?? '');
                                $comentarioLimpo = strip_tags((string) $comentarioRaw);
                            @endphp
                            @if(!empty(trim($comentarioLimpo)))
                                <div class="mt-2 p-3 bg-primary-50 border-l-4 border-primary-500 rounded shadow-sm dark:bg-gray-900 dark:border-primary-400">
                                    <p class="text-gray-800 dark:text-gray-200 font-medium italic">"{{ $comentarioLimpo }}"</p>
                                </div>
                            @endif

                        {{-- 3. ATUALIZAÇÕES GERAIS (Updated) --}}
                        @elseif($activity->event === 'updated' && isset($activity->properties['attributes']))
                            <div class="mt-2 text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded space-y-1">
                                @foreach($activity->properties['attributes'] as $key => $value)
                                    @if(in_array($key, ['updated_at', 'created_at', 'process_id', 'id', 'task_id', 'checklist_id', 'checklistable_id', 'checklistable_type'])) 
                                        @continue 
                                    @endif

                                    @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $value)
                                        @php
                                            $label = ucfirst($key);
                                            $oldVal = $activity->properties['old'][$key];
                                            $newVal = $value;

                                            if ($key === 'column_id') {
                                                $label = 'Fase/Etapa';
                                                $oldVal = $columnsMap[$oldVal] ?? $oldVal;
                                                $newVal = $columnsMap[$newVal] ?? $newVal;
                                            } elseif ($key === 'description') {
                                                $label = 'Descrição';
                                                $oldVal = strip_tags((string) $oldVal) ?: 'Vazio';
                                                $newVal = strip_tags((string) $newVal) ?: 'Vazio';
                                            } elseif ($key === 'is_completed') {
                                                $itemName = $activity->subject ? $activity->subject->label : 'Item';
                                                $listName = ($activity->subject && $activity->subject->checklist) ? $activity->subject->checklist->name : 'Lista';
                                                $label = "Checklist ({$listName}) - {$itemName}";
                                                $oldVal = $oldVal ? 'Concluído' : 'Pendente';
                                                $newVal = $newVal ? 'Concluído' : 'Pendente';
                                            }
                                        @endphp
                                        <div class="flex gap-2">
                                            <span class="text-gray-500 font-medium">{{ $label }}:</span>
                                            <span class="line-through text-red-500 opacity-80">{{ $oldVal }}</span>
                                            <span class="text-green-500 font-medium">&rarr; {{ $newVal }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500 italic ml-6">Nenhuma movimentação registrada ainda.</div>
        @endforelse
    </div>
</div>