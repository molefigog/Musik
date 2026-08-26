<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
