<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            $table->string('adres')->nullable()->change();
            $table->string('postcode')->nullable()->change();
            $table->string('plaats')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bestellingen', function (Blueprint $table) {
            $table->string('adres')->nullable(false)->change();
            $table->string('postcode')->nullable(false)->change();
            $table->string('plaats')->nullable(false)->change();
        });
    }
};
