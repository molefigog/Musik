<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadTaskFileRequest;
use App\Http\Resources\Admin\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    // GET /api/admin/tasks
    public function index()
    {
        $tasks = Task::with('user')->latest()->get();

        return TaskResource::collection($tasks);
    }

    // GET /api/admin/tasks/{task}
    public function show(Task $task)
    {
        return new TaskResource($task->load('user'));
    }

    // PUT /api/admin/tasks/{task} — edit title/details before or after upload
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'details' => ['sometimes', 'nullable', 'string'],
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0.01'],
            'is_paid' => ['sometimes', 'boolean'],
        ]);

        $task->update($data);

        return new TaskResource($task->load('user'));
    }

    // POST /api/admin/tasks/{task}/upload
    // Handles beat / recording / artwork uploads and flips status to true.
    public function upload(UploadTaskFileRequest $request, Task $task)
    {
        $disk = config('filesystems.default');
        $folder = "tasks/{$task->service_type}/{$task->id}";

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store($folder, $disk);
            $task->file_path = $path;
        }

        if ($request->hasFile('preview')) {
            $previewPath = $request->file('preview')->store("{$folder}/preview", $disk);
            $task->preview_path = $previewPath;
        }

        $task->status = true;
        $task->save();

        return new TaskResource($task->load('user'));
    }
}
