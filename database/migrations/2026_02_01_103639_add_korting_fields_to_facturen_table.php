<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('facturen', function (Blueprint $table) {
            $table->string('korting_type')->nullable()->after('verzendkosten_incl');     // none|percent|amount
            $table->decimal('korting_waarde', 10, 2)->default(0)->after('korting_type'); // 10.00 of 10.00%
            $table->decimal('korting_bedrag', 10, 2)->default(0)->after('korting_waarde'); // berekend €
        });
    }

    public function down(): void
    {
        Schema::table('facturen', function (Blueprint $table) {
            $table->dropColumn(['korting_type', 'korting_waarde', 'korting_bedrag']);
        });
    }
};
