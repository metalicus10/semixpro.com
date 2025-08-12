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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('item_title', 191);
            $table->string('item_type', 191);
            $table->string('item_description', 191);
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('part_id')->nullable()->after('item_id');
            $table->foreign('part_id')->references('id')->on('parts')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->boolean('is_custom')->default(1);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['part_id']);
            $table->dropColumn('part_id');
        });
        Schema::dropIfExists('order_items');
    }
};
