<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factuur_regels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factuur_id')->constrained('facturen')->cascadeOnDelete();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('artikel');
            $table->integer('aantal')->default(1);
            $table->decimal('prijs_incl', 10, 2)->default(0);   // prijs incl btw
            $table->decimal('totaal_incl', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factuur_regels');
    }
};
