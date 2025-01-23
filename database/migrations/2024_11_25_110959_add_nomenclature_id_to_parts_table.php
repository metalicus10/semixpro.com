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
        Schema::table('parts', function (Blueprint $table) {
            $table->unsignedBigInteger('nomenclature_id')->nullable()->after('id')->references('id')->on('nomenclatures')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropForeign(['nomenclature_id']); // Удаляем внешний ключ
            $table->dropColumn('nomenclature_id'); // Удаляем колонку
        });
    }
};
