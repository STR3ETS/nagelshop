<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            // factuurnummer zoals: INV-000001
            $table->string('factuurnummer', 32)->nullable()->unique()->after('status');

            // optioneel maar vaak handig:
            $table->timestamp('factuur_datum')->nullable()->after('factuurnummer');
        });
    }

    public function down(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            $table->dropUnique(['factuurnummer']);
            $table->dropColumn(['factuurnummer', 'factuur_datum']);
        });
    }
};
