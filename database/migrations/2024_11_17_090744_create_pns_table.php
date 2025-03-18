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
        Schema::create('pns', function (Blueprint $table) {
            $table->id();
            $table->string('number',191)->unique(); // Уникальный номер
            $table->foreignId('part_id')->constrained('parts')->onDelete('cascade'); // Связь с таблицей parts
            $table->foreignId('nomenclature_id')->constrained('nomenclatures')->onDelete('cascade'); // Связь с таблицей nomenclatures
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pns');
    }
};
