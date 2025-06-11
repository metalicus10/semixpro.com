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
        Schema::table('technician_parts', function (Blueprint $table) {
            $table->json('daily_transfers')->nullable()->after('total_transferred');
            $table->json('daily_returns')->nullable()->after('weekly_transfers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_parts', function (Blueprint $table) {
            $table->dropColumn('daily_transfers');
            $table->dropColumn('daily_returns');
        });
    }
};
