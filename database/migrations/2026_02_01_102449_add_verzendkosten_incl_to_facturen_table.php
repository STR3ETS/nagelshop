<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('facturen', function (Blueprint $table) {
            $table->decimal('verzendkosten_incl', 10, 2)->default(0)->after('btw_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('facturen', function (Blueprint $table) {
            $table->dropColumn('verzendkosten_incl');
        });
    }
};
