<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $appends = ['item_id', 'item_name'];

    protected $fillable = [
        'amount',
        'status',
        'txn_id',
        'msisdn',
        'conversation_id',
        'type',
        'raw_response',
        'description',
        'user_id',
        'seller_id',
        'music_id',
        'service_id',
        'service_type',
        'title',
    ];
    public function task(): HasOne
    {
        return $this->hasOne(Task::class, 'payment_id');
    }

    public function music(): BelongsTo
    {
        return $this->belongsTo(Music::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getItemIdAttribute(): ?int
    {
        return $this->music_id ?? $this->service_id;
    }

    public function getItemNameAttribute(): string
    {
        return (string) ($this->music?->title ?? $this->title ?? $this->description ?? 'Purchased item');
    }
}
