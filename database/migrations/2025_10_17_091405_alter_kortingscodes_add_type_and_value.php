<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kortingscodes', function (Blueprint $table) {
            $table->string('type')->default('percent'); // 'percent' | 'amount'
            $table->decimal('value', 10, 2)->default(0);
        });

        // Migreer bestaande data: 'korting' (int %) -> type=percent, value=korting
        if (Schema::hasColumn('kortingscodes', 'korting')) {
            DB::table('kortingscodes')->update([
                'type'  => 'percent',
                'value' => DB::raw('korting'),
            ]);

            Schema::table('kortingscodes', function (Blueprint $table) {
                $table->dropColumn('korting');
            });
        }

        // Zorg dat code case-insensitive uniek is door alles uppercase op te slaan (app-logica)
        // DB-kant laten zoals hij is (unique op 'code' blijft gelden).
    }

    public function down(): void
    {
        Schema::table('kortingscodes', function (Blueprint $table) {
            $table->unsignedTinyInteger('korting')->nullable();
        });

        // Zet value terug naar int % alleen voor percent types
        DB::table('kortingscodes')->where('type', 'percent')->update([
            'korting' => DB::raw('LEAST(100, GREATEST(0, ROUND(value)))'),
        ]);

        Schema::table('kortingscodes', function (Blueprint $table) {
            $table->dropColumn(['type', 'value']);
        });
    }
};
