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
        'category_id',
        'subcategory_id',
        'uitverkoop',
    ];

    protected $casts = [
        'uitverkoop' => 'boolean',
    ];

    public function bestellingen()
    {
        return $this->belongsToMany(Bestelling::class, 'bestelling_product')->withPivot('aantal');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
