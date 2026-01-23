<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactuurRegel extends Model
{
    protected $table = 'factuur_regels';

    protected $fillable = [
        'factuur_id','product_id','artikel','aantal','prijs_incl','totaal_incl'
    ];

    public function factuur(): BelongsTo
    {
        return $this->belongsTo(Factuur::class, 'factuur_id');
    }
}
