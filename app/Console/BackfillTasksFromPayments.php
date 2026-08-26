<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\TaskProvisioningService;
use Illuminate\Console\Command;

class BackfillTasksFromPayments extends Command
{
    protected $signature = 'tasks:backfill {--dry-run : List what would happen without writing anything}';

    protected $description = 'Create tasks for completed payments that never got one (pre-dates the task system)';

    public function handle(TaskProvisioningService $provisioning): int
    {
        $dryRun = $this->option('dry-run');

        $orphaned = Payment::where('status', 'completed')
            ->whereDoesntHave('task')
            ->orderBy('created_at')
            ->get();

        if ($orphaned->isEmpty()) {
            $this->info('No completed payments are missing a task. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$orphaned->count()} completed payment(s) with no task.");

        $autoCreated = 0;
        $needsReview = [];

        foreach ($orphaned as $payment) {
            if ($payment->service_type) {
                if (! $dryRun) {
                    $provisioning->createFromPayment($payment);
                }
                $autoCreated++;
                continue;
            }

            $needsReview[] = [
                $payment->id,
                $payment->txn_id,
                $payment->user_id,
                $payment->amount,
                $payment->type,
                $payment->description,
                $payment->created_at->toDateString(),
            ];
        }

        $this->info(($dryRun ? '[dry-run] would create' : 'Created') . " {$autoCreated} task(s) automatically.");

        if (! empty($needsReview)) {
            $this->warn(count($needsReview) . ' payment(s) have no service_type and need manual review:');
            $this->table(
                ['Payment ID', 'Txn ID', 'User ID', 'Amount', 'Type', 'Description', 'Date'],
                $needsReview
            );
            $this->line('');
            $this->line('For each of these, set service_type + title directly, e.g.:');
            $this->line('  Payment::find($id)->update([\'service_type\' => \'beat\', \'title\' => \'...\']);');
            $this->line('Then re-run this command to create their tasks.');
        }

        return self::SUCCESS;
    }
}
