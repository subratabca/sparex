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
        Schema::create('client_meal_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_order_id')->constrained('meal_orders')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0); 
            $table->decimal('payable_amount', 10, 2);
            $table->enum('payment_status', ['due', 'paid'])->default('due');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_meal_orders');
    }
};
