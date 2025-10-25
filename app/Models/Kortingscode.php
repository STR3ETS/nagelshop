<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Kortingscode extends Model
{
    protected $table = 'kortingscodes';

    // Nieuwe kolommen: type ('percent' | 'amount') en value (decimal)
    protected $fillable = [
        'code',
        'type',
        'value',
        'vervalt_op',
    ];

    protected $casts = [
        'vervalt_op' => 'datetime',
        'value'      => 'decimal:2',
    ];

    /**
     * Normaliseer code naar uppercase en trim spaties
     * (zorgt voor case-insensitive unieke codes).
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = mb_strtoupper(trim((string) $value));
    }

    /**
     * Handige weergave voor in tabellen: "20%" of "€ 10,00".
     */
    public function getDisplayValueAttribute(): string
    {
        if ($this->type === 'amount') {
            // NL notatie: komma als decimaal, punt als duizendtallen
            return '€ ' . number_format((float) $this->value, 2, ',', '.');
        }

        // Percentage zonder onnodige decimalen
        $pct = (float) $this->value;
        $pctStr = rtrim(rtrim(number_format($pct, 2, ',', '.'), '0'), ',');
        return $pctStr . '%';
    }

    /**
     * Is de kortingscode verlopen?
     */
    public function isExpired(): bool
    {
        return $this->vervalt_op instanceof Carbon
            ? now()->greaterThan($this->vervalt_op)
            : now()->greaterThan(Carbon::parse($this->vervalt_op));
    }

    /**
     * Scope: alleen (nog) geldige codes.
     */
    public function scopeActief($query)
    {
        return $query->where('vervalt_op', '>', now());
    }

    /**
     * Pas korting toe op een subtotaal.
     * Retourneert nieuw totaalbedrag (nooit < 0).
     */
    public function applyTo(float $subtotal): float
    {
        if ($this->isExpired()) {
            return round($subtotal, 2);
        }

        $discount = $this->type === 'amount'
            ? (float) $this->value
            : ($subtotal * ((float) $this->value / 100));

        return max(0, round($subtotal - $discount, 2));
    }
}
