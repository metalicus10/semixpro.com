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
        Schema::create('technician_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users');
            $table->foreignId('manager_id')->constrained('users');
            $table->foreignId('part_id')->constrained('parts');
            $table->foreignId('nomenclature_id')->constrained('nomenclatures');
            $table->foreignId('warehouse_id')->constrained('warehouses');

            $table->integer('quantity');
            $table->integer('total_transferred')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_parts');
    }
};
