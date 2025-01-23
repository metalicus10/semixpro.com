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
        Schema::create('transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->foreignId('source_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('target_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->integer('quantity');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Кто переместил
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_logs');
    }
};
