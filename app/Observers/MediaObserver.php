<?php

namespace App\Observers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Task;
use App\Models\Process;
use Illuminate\Support\Facades\Auth;

class MediaObserver
{
    /**
     * Disparado sempre que um novo registro de mídia é criado no banco.
     */
    public function created(Media $media): void
    {
        $user = Auth::user();
        
        // Propriedades básicas do log
        $properties = [
            'file_name'   => $media->file_name,
            'file_size'   => $media->human_readable_size,
            'mime_type'   => $media->mime_type,
            'custom_type' => 'document_upload', // Identificador para o Blade da Timeline
        ];

        // CENÁRIO 1: O arquivo foi anexado a uma TAREFA
        if ($media->model_type === Task::class) {
            $task = Task::find($media->model_id);

            if ($task && $task->process_id) {
                $properties['process_id'] = $task->process_id;
                $properties['task_title'] = $task->title;

                activity()
                    ->performedOn($task)
                    ->causedBy($user)
                    ->withProperties($properties)
                    ->log("Anexou o documento: {$media->file_name} na tarefa");
            }
        }

        // CENÁRIO 2: O arquivo foi anexado diretamente ao PROCESSO
        if ($media->model_type === Process::class) {
            $process = Process::find($media->model_id);

            if ($process) {
                $properties['process_id'] = $process->id;

                activity()
                    ->performedOn($process)
                    ->causedBy($user)
                    ->withProperties($properties)
                    ->log("Anexou o documento: {$media->file_name} no processo");
            }
        }
    }
}