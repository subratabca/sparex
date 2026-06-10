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
        Schema::create('meal_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('delivery_type', ['self_pickup','courier'])->default('self_pickup');
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('client_platform_fee', 10, 2)->default(0);
            $table->decimal('rider_platform_fee', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);  
            $table->decimal('payable_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0); 
            $table->string('payment_type', 50)->nullable(); 
            $table->string('payment_method', 50)->nullable(); 
            $table->string('transaction_id', 100)->nullable(); 
            $table->string('currency', 10)->nullable();  
            $table->string('order_number', 50)->unique(); 
            $table->string('invoice_no', 50)->unique(); 
            $table->enum('payment_status', ['due','paid','partially_paid'])->default('due');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_orders');
    }
};


