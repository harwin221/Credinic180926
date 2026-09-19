<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arqueo', function (Blueprint $table) {
            $table->decimal('desembolsos', 10, 2)->default(0.00)->nullable()->after('total_dolar');
        });
    }

    public function down(): void
    {
        Schema::table('arqueo', function (Blueprint $table) {
            $table->dropColumn('desembolsos');
        });
    }
};
