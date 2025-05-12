<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            // Voeg de transactie_id kolom toe
            $table->string('transactie_id', 20)->unique()->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            // Verwijder de transactie_id kolom
            $table->dropColumn('transactie_id');
        });
    }
};
