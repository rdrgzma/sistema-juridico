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
            // Criamos "Dicionários" mapeando o ID para o Nome 
            // Ex: [ 1 => 'A fazer', 2 => 'Em andamento' ]
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
                        <strong>{{ $activity->description }}</strong> 
                        
                        @if($activity->event === 'updated' && isset($activity->properties['attributes']))
                            <div class="mt-2 text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded space-y-1">
                                @foreach($activity->properties['attributes'] as $key => $value)
                                    
                                    {{-- IGNORA CAMPOS TÉCNICOS --}}
                                    @if(in_array($key, ['updated_at', 'created_at', 'process_id', 'id'])) 
                                        @continue 
                                    @endif

                                    @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $value)
                                        @php
                                            // Configuração Padrão
                                            $label = ucfirst($key);
                                            $oldVal = $activity->properties['old'][$key];
                                            $newVal = $value;

                                            // TRADUÇÕES AMIGÁVEIS
                                            if ($key === 'column_id') {
                                                $label = 'Fase/Etapa';
                                                $oldVal = $columnsMap[$oldVal] ?? $oldVal;
                                                $newVal = $columnsMap[$newVal] ?? $newVal;
                                            } elseif ($key === 'assigned_to') {
                                                $label = 'Responsável';
                                                $oldVal = $usersMap[$oldVal] ?? 'Ninguém';
                                                $newVal = $usersMap[$newVal] ?? 'Ninguém';
                                            } elseif ($key === 'title') {
                                                $label = 'Título';
                                            } elseif ($key === 'description') {
                                                $label = 'Descrição';
                                                
                                                // Remove as tags HTML (ex: <p>, <strong>, <br>)
                                                $oldVal = strip_tags((string) $oldVal);
                                                $newVal = strip_tags((string) $newVal);
                                                
                                                // Se ficar apenas espaço em branco após remover as tags, mostra "Vazio"
                                                if (trim($oldVal) === '') $oldVal = 'Vazio';
                                                if (trim($newVal) === '') $newVal = 'Vazio';
                                                
                                            } elseif ($key === 'status') {
                                                $label = 'Situação';
                                            }
                                        @endphp

                                        <div class="flex gap-2">
                                            <span class="text-gray-500 font-medium">{{ $label }}:</span>
                                            <span class="line-through text-red-500 opacity-80">
                                                {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                                            </span>
                                            <span class="text-green-500 font-medium">
                                                &rarr; {{ is_array($newVal) ? json_encode($newVal) : $newVal }}
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if(isset($activity->properties['comment']))
                            <p class="mt-2 italic border-l-2 border-gray-300 pl-2 text-gray-500">
                                "{{ $activity->properties['comment'] }}"
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500 italic ml-6">
                Nenhuma movimentação registrada neste processo ainda.
            </div>
        @endforelse
    </div>
</div>