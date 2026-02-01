<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factuur extends Model
{
    protected $table = 'facturen';

    protected $fillable = [
        'factuurnummer','datum',
        'naam','email','adres','postcode','plaats',
        'btw_percentage','subtotaal_ex','btw_bedrag','totaal_incl','verzendkosten_incl'
    ];

    protected $casts = [
        'datum' => 'date',
    ];

    public function regels(): HasMany
    {
        return $this->hasMany(FactuurRegel::class, 'factuur_id');
    }
}
