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
        Schema::create('part_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts'); // ID запчасти
            $table->foreignId('from_warehouse_id')->constrained('warehouses'); // Откуда
            $table->foreignId('to_warehouse_id')->constrained('warehouses'); // Куда
            $table->integer('quantity'); // Количество
            $table->foreignId('technician_id')->constrained('users');
            $table->foreignId('manager_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_movements');
    }
};
