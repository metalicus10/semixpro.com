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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name',191)->unique();
            $table->string('contact_name',191);
            $table->string('email',191);
            $table->string('phone',191);
            $table->decimal('receivables', 10, 2)->default(0);
            $table->decimal('used_credits', 10, 2)->default(0);
            $table->string('address',191);
            $table->boolean('is_active')->default(true);
            $table->foreignId('manager_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
