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
        Schema::create('bulk_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action_type');
            $table->string('target_type');
            $table->unsignedBigInteger('user_id');
            $table->json('items'); // все изменения
            $table->text('summary'); // описание
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_logs');
    }
};
