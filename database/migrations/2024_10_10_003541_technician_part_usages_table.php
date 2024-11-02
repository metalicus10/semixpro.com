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
        Schema::create('technician_part_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade'); // Связь с таблицей пользователей
            $table->foreignId('part_id')->constrained('parts')->onDelete('cascade'); // Связь с таблицей запчастей
            $table->integer('quantity_used')->default(0); // Количество запчастей у техника
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_part_usages');
    }
};
