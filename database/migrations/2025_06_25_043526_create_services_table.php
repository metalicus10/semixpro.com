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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название услуги
            $table->string('slug')->unique(); // slug для URL, если нужно
            $table->string('category')->nullable(); // Категория услуги
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0); // Цена
            $table->unsignedBigInteger('manager_id')->nullable(); // если услуга принадлежит менеджеру
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Если нужна связь с менеджером
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
