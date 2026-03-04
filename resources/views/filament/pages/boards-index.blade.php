<x-filament-panels::page>
    <div class="w-full">
        <div class="mb-6 border-b border-gray-800 pb-4">
            <h2 class="text-lg font-medium text-gray-400">Seus Quadros Operacionais</h2>
        </div>

        {{-- Flex-wrap força os itens a ficarem lado a lado e pularem linha quando não houver espaço --}}
        <div class="flex flex-wrap gap-6">
            @foreach($boards as $board)
                {{-- w-72 define uma largura fixa para o card não esticar --}}
                <div class="flex flex-col justify-between p-5 bg-[#18181b] border border-gray-800 rounded-xl hover:border-orange-500 transition-all h-48 w-72 shadow-xl">
                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="p-1.5 bg-orange-500/10 rounded-md">
                                <x-filament::icon icon="heroicon-m-squares-2x2" class="h-5 w-5 text-orange-500" />
                            </div>
                            <h3 class="font-bold text-base text-white truncate" title="{{ $board->name }}">
                                {{ $board->name }}
                            </h3>
                        </div>
                        
                        <div>
                            <span class="px-2 py-1 text-[10px] font-bold bg-gray-800 text-gray-300 rounded-md uppercase">
                                {{ $board->unit?->name ?? 'Geral' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-800/50 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Capacidade</span>
                            <span class="text-sm font-semibold text-gray-300">{{ $board->tasks_count ?? 0 }} Tarefas</span>
                        </div>
                        
                        <a href="{{ App\Filament\Pages\ViewBoard::getUrl(['board' => $board->id]) }}" 
                           class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black text-xs font-bold rounded-lg transition-colors shadow-lg">
                            Abrir
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if($boards->isEmpty())
            <div class="text-center py-12 border-2 border-dashed border-gray-800 rounded-xl">
                <p class="text-gray-500 text-sm">Nenhum quadro operacional cadastrado.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>