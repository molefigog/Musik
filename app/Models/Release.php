<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;


class Release extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'art_cover',
        'published',
        'user_id',
        'slug',
    ];
    protected $casts = [
        'published' => 'boolean',
    ];
    protected static function booted()
    {
        static::creating(function ($release) {
            $release->slug = static::generateSlug($release->title);
        });

        static::updating(function ($release) {
            if ($release->isDirty('title')) {
                $release->slug = static::generateSlug($release->title);
            }
        });
    }

    protected static function generateSlug($title)
    {
        return Str::slug($title) . '-' . uniqid();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function music()
    {
        return $this->hasMany(Music::class);
    }

    public function allMusic()
    {
        return $this->music();
    }
}
