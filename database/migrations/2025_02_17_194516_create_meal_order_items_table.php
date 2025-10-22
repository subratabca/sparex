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
        Schema::create('meal_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_order_id')->constrained('meal_orders')->onDelete('cascade'); 
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade'); 
            $table->foreignId('menu_id')->constrained('customer_menus')->onDelete('cascade');
            $table->integer('quantity')->unsigned()->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending','approved','delivered','canceled','returned'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_order_items');
    }
};
