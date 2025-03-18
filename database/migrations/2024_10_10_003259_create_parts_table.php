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
            $table->string('sku',191)->unique();
            $table->string('name',191);
            $table->integer('quantity')->default(0);
            $table->string('image',191)->nullable();
            $table->foreignId('nomenclature_id')->constrained('nomenclatures');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->json('url')->nullable();
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
