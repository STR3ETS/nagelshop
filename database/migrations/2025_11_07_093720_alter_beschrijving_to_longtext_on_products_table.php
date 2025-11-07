<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vereist doctrine/dbal als je een bestaande kolomtype wijzigt:
        // composer require doctrine/dbal
        Schema::table('products', function (Blueprint $table) {
            $table->longText('beschrijving')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('beschrijving', 65535)->nullable()->change();
        });
    }
};
