<?php

namespace App\Models;

enum ServiceType: string
{
    case Beat = 'beat';
    case Recording = 'recording';
    case Artwork = 'artwork';
}
