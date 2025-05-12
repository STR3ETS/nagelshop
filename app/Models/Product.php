<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'naam',
        'beschrijving',
        'prijs',
        'voorraad',
        'foto',
    ];

    public function bestellingen()
    {
        return $this->belongsToMany(Bestelling::class, 'bestelling_product')->withPivot('aantal');
    }

}
