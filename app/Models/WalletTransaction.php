<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'payment_id', 'type', 'amount', 'description'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

/*
|--------------------------------------------------------------------
| Add these relations to your existing User model:
|--------------------------------------------------------------------

public function walletTransactions()
{
    return $this->hasMany(WalletTransaction::class);
}

public function purchases()
{
    return $this->hasMany(Payment::class, 'user_id');
}

public function sales()
{
    return $this->hasMany(Payment::class, 'seller_id');
}
*/
