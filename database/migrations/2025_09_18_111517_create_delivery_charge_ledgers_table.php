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
        Schema::create('delivery_charge_ledgers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('meal_order_id')->constrained('meal_orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            
            $table->foreignId('delivery_person_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            $table->date('delivery_date');

            $table->string('order_tracking')->unique()->nullable();

            $table->enum('delivery_status', ['pending','accept_order','accept_delivery','preparing','ready_for_pickup','picked_up','on_the_way','arrived','delivered','cancelled'])->default('pending');
            
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('rider_platform_fee', 10, 2)->default(0);
            $table->decimal('payable_amount', 10, 2)->default(0);
            $table->decimal('distance_km', 8, 2)->nullable(); 
            $table->string('distance_category')->nullable(); 
            
            $table->enum('payment_status', ['due', 'paid', 'cancelled'])->default('due');
            
            $table->boolean('is_charge_counted')->default(true);
            $table->string('charge_key')->unique(); 
            
            $table->timestamps();
            
            // Indexes for performance with shorter names
            $table->index(['client_id', 'delivery_date', 'meal_type_id'], 'idx_dcl_client_date_type');
            $table->index(['delivery_person_id', 'payment_status'], 'idx_dcl_delivery_payment');
            $table->index('charge_key', 'idx_dcl_charge_key');
            $table->index('meal_order_id', 'idx_dcl_meal_order');
            $table->index('customer_id', 'idx_dcl_customer');
            
            // indexes for tracking
            $table->index('order_tracking', 'idx_dcl_order_tracking');
            $table->index(['customer_id', 'delivery_status'], 'idx_dcl_customer_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_charge_ledgers');
    }
};