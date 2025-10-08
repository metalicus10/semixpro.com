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
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'calendar_settings')) {
                $table->json('scheduler_settings')->nullable()->after('delete_after_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'scheduler_settings')) {
                $table->dropColumn('scheduler_settings');
            }
        });
    }
};
