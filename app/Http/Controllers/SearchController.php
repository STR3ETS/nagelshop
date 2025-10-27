<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function products(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') return response()->json([]);

        $term = mb_strtolower($q, 'UTF-8');

        // GEEN 'slug' selecteren als die kolom niet bestaat
        $items = Product::query()
            ->select(['products.id', 'products.naam', 'products.foto', 'products.category_id'])
            // optioneel: categorie naam erbij zonder relatie:
            // ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            // ->addSelect('categories.naam as categorie_naam')
            ->whereRaw('LOWER(products.naam) LIKE ?', ['%'.$term.'%'])
            ->orderByRaw('CASE WHEN LOWER(products.naam) LIKE ? THEN 0 ELSE 1 END', [$term.'%'])
            ->orderBy('products.naam')
            ->limit(12)
            ->get();

        $out = $items->map(function ($p) {
            return [
                'id'        => $p->id,
                'naam'      => $p->naam,
                // URL naar de foto in storage
                'foto'      => $p->foto ? asset('storage/producten/'.$p->foto) : null,
                // Gebruik dit als je de join aanzet:
                // 'categorie' => $p->categorie_naam ?? null,
                // of met relatie (als je die hebt gedefinieerd op Product):
                // 'categorie' => optional($p->category)->naam,
            ];
        });

        return response()->json($out);
    }
}
