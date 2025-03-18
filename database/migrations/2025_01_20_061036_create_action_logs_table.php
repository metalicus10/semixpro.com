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
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action_type',191); // Тип действия (add, update, delete)
            $table->string('target_type',191); // Тип объекта (например, category, brand, warehouse)
            $table->unsignedBigInteger('target_id'); // ID объекта
            $table->text('description')->nullable(); // Описание действия
            $table->unsignedBigInteger('user_id')->constrained()->onDelete('cascade'); // ID пользователя, который совершил действие
            $table->timestamps(); // Время действия
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
