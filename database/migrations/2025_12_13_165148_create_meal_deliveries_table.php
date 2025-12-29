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
        Schema::create('meal_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_person_id')->nullable()->constrained('users');
            $table->enum('delivery_status', ['pending','preparing','ready_for_pickup','picked_up','on_the_way','arrived','delivered','failed','cancelled'
            ])->default('pending');
            $table->timestamp('estimated_delivery_time')->nullable();
            $table->timestamp('actual_delivery_time')->nullable();
            $table->timestamp('pickup_time')->nullable();
            $table->timestamp('handover_time')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('tracking_code')->unique()->nullable();
            $table->decimal('current_location_lat', 10, 8)->nullable();
            $table->decimal('current_location_lng', 11, 8)->nullable();
            $table->string('proof_of_delivery_image')->nullable();
            $table->timestamps();

            $table->index('delivery_status');
            $table->index('tracking_code');
            $table->index('delivery_person_id');
                });
    }

    /**
     * Reverse the migrations. 2025_12_13_165148
     */
    public function down(): void 
    {
        Schema::dropIfExists('meal_deliveries');
    }
};
