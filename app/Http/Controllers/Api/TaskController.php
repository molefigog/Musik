<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    // GET /api/tasks — only the authenticated user's own tasks
    public function index(Request $request)
    {
        $tasks = $request->user()
            ->tasks()
            ->latest()
            ->get();

        return TaskResource::collection($tasks);
    }

    // POST /api/tasks — create a task for the authenticated user
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type' => ['required', 'in:beat,recording,artwork'],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::create([
            'user_id' => $request->user()->id,
            'service_type' => $request->service_type,
            'title' => $request->title,
            'details' => $request->details,
            'is_paid' => false,
        ]);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    // GET /api/tasks/{task} — single task, scoped to owner
    public function show(Request $request, Task $task)
    {
        abort_if($task->user_id !== $request->user()->id, 403);

        return new TaskResource($task);
    }
}
