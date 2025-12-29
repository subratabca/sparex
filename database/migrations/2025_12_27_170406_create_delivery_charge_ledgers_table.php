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
            
            // Order information
            $table->foreignId('meal_order_id')->constrained('meal_orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            // Client information (who provides the meal)
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            
            // Delivery person information (will be assigned later)
            $table->foreignId('delivery_person_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Meal information
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            $table->date('delivery_date'); // The actual delivery date
            
            // Delivery charge calculation
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('distance_km', 8, 2)->nullable(); // Distance from client to customer
            $table->string('distance_category')->nullable(); // inside_city_2km, inside_city_5km, etc
            
            // Payment tracking for delivery person
            $table->enum('payment_status', ['due', 'paid', 'cancelled'])->default('due');
            $table->timestamp('payment_date')->nullable();
            $table->text('payment_notes')->nullable();
            
            // Flags to ensure unique delivery charge per client per meal type per day
            $table->boolean('is_charge_counted')->default(true);
            $table->string('charge_key')->unique(); // Composite key for uniqueness
            
            $table->timestamps();
            
            // Indexes for performance with shorter names
            $table->index(['client_id', 'delivery_date', 'meal_type_id'], 'idx_dcl_client_date_type');
            $table->index(['delivery_person_id', 'payment_status'], 'idx_dcl_delivery_payment');
            $table->index('charge_key', 'idx_dcl_charge_key');
            $table->index('meal_order_id', 'idx_dcl_meal_order');
            $table->index('customer_id', 'idx_dcl_customer');
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