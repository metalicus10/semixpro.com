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
        Schema::create('brand_nomenclature', function (Blueprint $table) {
            $table->id();

            // Колонки для связи с nomenclatures и brands
            $table->unsignedBigInteger('nomenclature_id');
            $table->unsignedBigInteger('brand_id');

            // Внешний ключ для таблицы nomenclatures
            $table->foreign('nomenclature_id')
                ->references('id')
                ->on('nomenclatures')
                ->onDelete('cascade');

            // Внешний ключ для таблицы brands
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_nomenclature');
    }
};
