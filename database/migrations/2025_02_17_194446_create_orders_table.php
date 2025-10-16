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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('status', ['pending','approved','delivered','canceled','partially_approved','partially_delivered'])->default('pending');
            $table->enum('delivery_type', ['myself','courier'])->default('myself');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);  
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->decimal('payable_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('payment_type', 50)->nullable(); 
            $table->string('payment_method', 50)->nullable(); 
            $table->string('transaction_id', 100)->nullable(); 
            $table->string('currency', 10)->nullable();  
            $table->string('order_number', 50)->unique(); 
            $table->string('invoice_no', 50)->unique(); 
            $table->boolean('accept_order_request_tnc')->default(0);
            $table->boolean('accept_product_delivery_tnc')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

