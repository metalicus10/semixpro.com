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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->charset('utf8mb4')->collation('utf8mb4_unicode_ci'); // Наименование запчасти
            $table->string('sku')->unique()->charset('utf8mb4')->collation('utf8mb4_unicode_ci'); // Артикул
            $table->integer('quantity')->default(0); // Количество на складе
            $table->string('image')->nullable(); // Путь к изображению
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // Связь с таблицей категорий
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
