<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Music extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'file_src',
        'size',//nulllable
        'price',
        'duration',//nulllable
        'release_id',
        'genre_id',
        'is_sold',//default false
        'extension',//nulllable
        'file_name',//nulllable
        'waveform',//nulllable
        'is_published',//nulllable
    ];

    protected $casts = [
        'is_sold' => 'boolean',
        'is_published' => 'boolean',

    ];
    public function release()
    {
        return $this->belongsTo(Release::class);
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }
    protected $appends = [
        'genre_title',
    ];
    public function getGenreTitleAttribute()
    {
        return $this->genre?->title;
    }
}
