<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class TaskProvisioningService
{
    /**
     * Turn a completed Payment into a Task. Safe to call more than once for
     * the same payment — returns the existing task instead of duplicating it.
     */
    public function createFromPayment(Payment $payment): ?Task
    {
        if ($payment->status !== 'completed') {
            return null;
        }

        if ($payment->task) {
            return $payment->task;
        }

        if ($payment->service_id) {
            $task = Task::query()
                ->whereKey($payment->service_id)
                ->where('user_id', $payment->user_id)
                ->whereNull('payment_id')
                ->first();

            if ($task) {
                $task->update([
                    'payment_id' => $payment->id,
                    'is_paid' => true,
                ]);

                return $task;
            }
        }

        if (! $payment->service_type) {
            // This means whatever initiated the payment didn't tell us what
            // was being purchased. Log it loudly — a completed payment with
            // no task is a customer who paid and got nothing.
            Log::warning('Payment completed with no service_type — cannot create task', [
                'payment_id' => $payment->id,
                'txn_id' => $payment->txn_id,
            ]);

            return null;
        }

        return Task::create([
            'user_id' => $payment->user_id,
            'payment_id' => $payment->id,
            'service_type' => $payment->service_type,
            'title' => $payment->title ?: ucfirst($payment->service_type),
            'details' => $payment->description,
            'amount' => $payment->amount,
            'status' => false,
        ]);
    }
}
