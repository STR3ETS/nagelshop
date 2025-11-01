<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // nullable, omdat sommige categorieën geen subcategorieën hebben
            if (!Schema::hasColumn('products', 'subcategory_id')) {
                $table->foreignId('subcategory_id')
                      ->nullable()
                      ->constrained('subcategories')
                      ->nullOnDelete(); // zet NULL als subcategory wordt verwijderd
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'subcategory_id')) {
                $table->dropConstrainedForeignId('subcategory_id');
            }
        });
    }
};
