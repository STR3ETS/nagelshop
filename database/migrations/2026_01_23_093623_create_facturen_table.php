<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturen', function (Blueprint $table) {
            $table->id();
            $table->string('factuurnummer')->unique();
            $table->date('datum');

            $table->string('naam');
            $table->string('email')->nullable();
            $table->string('adres')->nullable();
            $table->string('postcode')->nullable();
            $table->string('plaats')->nullable();

            $table->unsignedTinyInteger('btw_percentage')->default(21);

            $table->decimal('subtotaal_ex', 10, 2)->default(0);
            $table->decimal('btw_bedrag', 10, 2)->default(0);
            $table->decimal('totaal_incl', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturen');
    }
};
