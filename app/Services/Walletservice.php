<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;


class WalletService
{
    public function creditSeller(Payment $payment): ?WalletTransaction
    {
        if (! $payment->seller_id || $payment->credited_at || $payment->status !== 'completed') {
            return null;
        }

        return DB::transaction(function () use ($payment) {
            // re-fetch payment locked, in case of concurrent observer firing
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if (! $locked || $locked->credited_at) {
                return null;
            }

            $seller = User::whereKey($locked->seller_id)->lockForUpdate()->first();
            if (! $seller) {
                return null;
            }

            $seller->increment('balance', $locked->amount);

            $txn = WalletTransaction::create([
                'user_id'     => $seller->id,
                'payment_id'  => $locked->id,
                'type'        => 'sale_credit',
                'amount'      => $locked->amount,
                'description' => "Sale of {$locked->item_name}",
            ]);

            $locked->forceFill(['credited_at' => now()])->save();

            return $txn;
        });
    }


    public function reverseSaleCredit(Payment $payment, ?string $reason = null): ?WalletTransaction
    {
        if (! $payment->seller_id || ! $payment->credited_at) {
            return null;
        }

        return DB::transaction(function () use ($payment, $reason) {
            $seller = User::whereKey($payment->seller_id)->lockForUpdate()->first();
            if (! $seller) {
                return null;
            }

            $seller->decrement('balance', $payment->amount);

            $txn = WalletTransaction::create([
                'user_id'     => $seller->id,
                'payment_id'  => $payment->id,
                'type'        => 'sale_reversal',
                'amount'      => -$payment->amount,
                'description' => $reason ?? "Reversal for {$payment->item_name}",
            ]);

            $payment->forceFill(['credited_at' => null])->save();

            return $txn;
        });
    }

    public function topUp(User $user, float $amount): WalletTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();

            if ($locked->balance < $amount) {
                throw new \RuntimeException('Insufficient balance to top up wallet.');
            }

            $locked->decrement('balance', $amount);
            $locked->increment('wallet', $amount);

            return WalletTransaction::create([
                'user_id'     => $locked->id,
                'type'        => 'top_up',
                'amount'      => $amount,
                'description' => 'Balance moved to wallet',
            ]);
        });
    }

    public function cashOut(User $user, float $amount): WalletTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();

            if ($locked->wallet < $amount) {
                throw new \RuntimeException('Insufficient wallet funds.');
            }

            $locked->decrement('wallet', $amount);
            $locked->increment('balance', $amount);

            return WalletTransaction::create([
                'user_id'     => $locked->id,
                'type'        => 'cash_out',
                'amount'      => $amount,
                'description' => 'Wallet moved to balance',
            ]);
        });
    }

    public function spendFromWallet(User $buyer, float $amount, string $description = 'Item purchase'): WalletTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($buyer, $amount, $description) {
            $locked = User::whereKey($buyer->id)->lockForUpdate()->first();

            if ($locked->wallet < $amount) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }

            $locked->decrement('wallet', $amount);

            return WalletTransaction::create([
                'user_id'     => $locked->id,
                'type'        => 'wallet_spend',
                'amount'      => -$amount,
                'description' => $description,
            ]);
        });
    }

    public function purchases(User $user, Carbon $from, Carbon $to): Collection
    {
        return Payment::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->with('music')
            ->latest()
            ->get();
    }

    public function sales(User $user, Carbon $from, Carbon $to): Collection
    {
        return Payment::query()
            ->where('seller_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->with('music')
            ->latest()
            ->get();
    }
    public function ledger(User $user, Carbon $from, Carbon $to): Collection
    {
        return WalletTransaction::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest()
            ->get();
    }

    /** Convenience summary object for dashboards/widgets. */
    public function summaryTotals(User $user, Carbon $from, Carbon $to): array
    {
        $purchases = $this->purchases($user, $from, $to);
        $sales     = $this->sales($user, $from, $to);

        return [
            'purchases_count' => $purchases->count(),
            'purchases_total' => $purchases->sum('amount'),
            'sales_count'      => $sales->count(),
            'sales_total'      => $sales->sum('amount'),
            'balance'          => $user->balance,
            'wallet'           => $user->wallet,
        ];
    }

    public function summaryPdf(User $user, Carbon $from, Carbon $to, string $type = 'purchases'): DomPdf
    {
        $rows = $type === 'sales'
            ? $this->sales($user, $from, $to)
            : $this->purchases($user, $from, $to);

        return Pdf::loadView('reports.wallet-summary', [
            'user'  => $user,
            'rows'  => $rows,
            'type'  => $type,
            'from'  => $from,
            'to'    => $to,
            'total' => $rows->sum('amount'),
        ]);
    }
}