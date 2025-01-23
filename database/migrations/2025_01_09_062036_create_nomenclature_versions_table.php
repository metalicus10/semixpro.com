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
        Schema::create('nomenclature_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomenclature_id')->constrained()->onDelete('cascade');
            $table->text('changes'); // JSON-объект с изменениями
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Кто изменил
            $table->timestamps(); // Дата изменения
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomenclature_versions');
    }
};
