<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            // Verleng alleen de kolom; raak de unique index niet aan
            $table->string('transactie_id', 64)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            // Zet terug naar de oude lengte indien nodig (pas 20 aan naar jouw vorige lengte)
            $table->string('transactie_id', 20)->change();
        });
    }
};
