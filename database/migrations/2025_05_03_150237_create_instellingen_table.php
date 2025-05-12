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
        Schema::create('instellingen', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('telefoon')->nullable();
            $table->string('btw_nummer')->nullable();
            $table->string('kvk_nummer')->nullable();
            $table->string('openingstijden')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instellingen');
    }
};
