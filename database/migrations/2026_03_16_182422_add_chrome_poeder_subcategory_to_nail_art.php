<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryId = DB::table('categories')->where('naam', 'Nail Art')->value('id');

        if ($categoryId) {
            DB::table('subcategories')->insertOrIgnore([
                'category_id' => $categoryId,
                'naam'        => 'Chrome Poeder',
                'slug'        => 'chrome-poeder',
            ]);
        }
    }

    public function down(): void
    {
        $categoryId = DB::table('categories')->where('naam', 'Nail Art')->value('id');

        if ($categoryId) {
            DB::table('subcategories')
                ->where('category_id', $categoryId)
                ->where('naam', 'Chrome Poeder')
                ->delete();
        }
    }
};
