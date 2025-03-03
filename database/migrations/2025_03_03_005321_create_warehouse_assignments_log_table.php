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
        Schema::create('warehouse_assignments_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade'); // Кто назначил
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade'); // Кому назначили
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade'); // Какой склад
            $table->timestamp('assigned_at')->default(now()); // Время назначения
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_assignments_log');
    }
};
