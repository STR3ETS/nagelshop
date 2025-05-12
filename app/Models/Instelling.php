<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instelling extends Model
{
    protected $table = 'instellingen';

    protected $fillable = [
        'email',
        'telefoon',
        'btw_nummer',
        'kvk_nummer',
        'openingstijden',
    ];
}
