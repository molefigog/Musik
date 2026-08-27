<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_id',
        'service_type',
        'title',
        'details',
        'amount',
        'is_paid',
        'status',
        'file_path',
        'file_name',
        'preview_path',
    ];

 protected $casts = [

    'amount' => 'decimal:2',
    'is_paid' => 'boolean',
    'status' => 'boolean',
];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Same pattern as the movie playback accessor — swap 'b2' for whatever
    // disk name you registered for the gw-ent bucket if it differs.
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path
            ? Storage::disk(config('filesystems.default'))->url($this->file_path)
            : null;
    }

    public function getPreviewUrlAttribute(): ?string
    {
        return $this->preview_path
            ? Storage::disk(config('filesystems.default'))->url($this->preview_path)
            : null;
    }

    public function markCompleted(): void
    {
        $this->update(['status' => true]);
    }
}
