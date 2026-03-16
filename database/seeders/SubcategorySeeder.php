<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'Gel' => [
                'Builder Gel',
                'Builder Love Story Gel',
                'Builder Dream Gel',
                'Jelly Gelly Gel',
                'Liquid Gel',
            ],
            'Liquids' => [
                'Prep',
                'Cuticle Oil',
                'Cuticle Remover',
                '3 in 1 Nail Prep & Cleanser',
                'Remover',
            ],
            'Base Coat' => [
                'Rubber Base',
                'Cold Base',
            ],
            'Gelpolish' => [
                'Flash',
                'Color',
            ],
            'Nail Art' => [
                'Metalic Gel',
                'Gypsum',
                'Ombre Spray',
            ],
            'Werkmateriaal' => [
                'Top Nail Forms',
                'Gel Tips',
                'Penselen',
                'Schort',
            ],
        ];

        foreach ($map as $catName => $subs) {
            $cat = Category::where('naam', $catName)->first();
            if (!$cat) continue;

            foreach ($subs as $naam) {
                Subcategory::firstOrCreate(
                    ['category_id' => $cat->id, 'naam' => $naam],
                    ['slug' => Str::slug($naam)]
                );
            }
        }
    }
}

