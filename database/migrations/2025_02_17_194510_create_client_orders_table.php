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
        Schema::create('client_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('client_id')->constrained('users');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2);
            $table->decimal('items_weight', 10, 2)->default(0)->comment('Weight in kilograms (kg)'); 
            $table->decimal('delivery_fee', 10, 2)->default(0);  
            $table->decimal('payable_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->enum('payment_status', ['due', 'paid'])->default('due');
            $table->enum('status', ['pending','approved','delivered','canceled','partially_approved','partially_delivered'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_orders');
    }
};
