<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // bijv. 'INV'
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        // Init: zet last_number minimaal op max(bestellingen.id) zodat je niet teruggaat
        // (sluit aan op je oude logica INV- + bestelling_id)
        $maxBestellingId = (int) (DB::table('bestellingen')->max('id') ?? 0);

        DB::table('invoice_sequences')->insert([
            'key' => 'INV',
            'last_number' => $maxBestellingId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
