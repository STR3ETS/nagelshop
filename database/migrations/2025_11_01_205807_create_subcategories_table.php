<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('naam');
            $table->string('slug')->nullable();
            $table->timestamps();
            $table->unique(['category_id','naam']);
        });

        // Optioneel: kolom op producten voor subcategorie
        if (Schema::hasTable('producten')) {
            Schema::table('producten', function (Blueprint $table) {
                $table->foreignId('subcategory_id')->nullable()
                    ->constrained('subcategories')->nullOnDelete()->after('category_id');
            });
        }
    }

    public function down(): void {
        if (Schema::hasTable('producten')) {
            Schema::table('producten', function (Blueprint $table) {
                $table->dropConstrainedForeignId('subcategory_id');
            });
        }
        Schema::dropIfExists('subcategories');
    }
};
