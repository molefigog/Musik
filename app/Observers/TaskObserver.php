<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\UserNotification;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    public function updated(Task $task): void
    {
        if (! $task->wasChanged('status') || ! $task->status) {
            return;
        }

        $user = $task->user;

        if (! $user) {
            return;
        }

        $tokens = $user->fcmTokens()
            ->pluck('token')
            ->filter()
            ->values()
            ->all();

        if (empty($tokens)) {
            return;
        }

        $title = 'Task Completed';
        $body = sprintf('Your task "%s" is now ready.', $task->title);

        UserNotification::create([
            'user_id' => $user->id,
            'source' => 'task',
            'title' => $title,
            'body' => $body,
            'data' => [
                'task_id' => $task->id,
                'service_type' => $task->service_type,
                'status' => $task->status,
            ],
        ]);

        if (!empty($tokens)) {
            try {
                app(FirebaseService::class)->sendToAll($tokens, $title, $body);
            } catch (\Throwable $exception) {
                Log::error('Failed to send task completion FCM notification', [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}