<?php

use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use App\Models\Process;

new class extends Component
{
    // O Filament passa automaticamente o Processo aberto para esta variável
    public ?Process $record = null;

    // O método with() expõe os dados para o HTML abaixo
    public function with(): array
    {
        if (! $this->record) {
            return ['activities' => collect()];
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
                        <time class="text-xs font-normal text-gray-500 dark:text-gray-400">
                            {{ $activity->created_at->diffForHumans() }}
                        </time>
                    </div>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        <strong>{{ $activity->description }}</strong> 
                        
                        @if($activity->event === 'updated' && isset($activity->properties['attributes']))
                            <div class="mt-2 text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded">
                                @foreach($activity->properties['attributes'] as $key => $value)
                                    @if(isset($activity->properties['old'][$key]))
                                        <div class="flex gap-2">
                                            <span class="text-gray-500 font-medium">{{ ucfirst($key) }}:</span>
                                            <span class="line-through text-red-500 opacity-80">
                                                {{ is_array($activity->properties['old'][$key]) ? json_encode($activity->properties['old'][$key]) : $activity->properties['old'][$key] }}
                                            </span>
                                            <span class="text-green-500 font-medium">
                                                &rarr; {{ is_array($value) ? json_encode($value) : $value }}
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