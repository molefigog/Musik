<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'art_cover'];

    public function music()
    {
        return $this->hasMany(Music::class, 'genre_id');
    }
}
