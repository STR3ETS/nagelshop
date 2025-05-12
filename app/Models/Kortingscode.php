<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kortingscode extends Model
{
    protected $table = 'kortingscodes';

    protected $fillable = [
        'code',
        'korting',
        'vervalt_op',
    ];

    protected $dates = ['vervalt_op'];
}
