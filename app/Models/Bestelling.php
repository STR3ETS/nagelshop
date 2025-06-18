<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bestelling extends Model
{
    protected $table = 'bestellingen';

    protected $fillable = [
        'transactie_id',
        'naam',
        'email',
        'adres',
        'postcode',
        'plaats',
        'betaalmethode',
        'totaalprijs',
        'track_trace',
        'status',
    ];

    public function producten()
    {
        return $this->belongsToMany(Product::class, 'bestelling_product')->withPivot('aantal');
    }

}
