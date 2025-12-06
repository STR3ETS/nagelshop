<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            $table->string('levermethode', 20)
                ->default('verzenden')
                ->after('telefoon');
        });
    }

    public function down(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            $table->dropColumn('levermethode');
        });
    }
};
