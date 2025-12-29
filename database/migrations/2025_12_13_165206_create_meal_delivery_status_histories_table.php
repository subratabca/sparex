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
        Schema::create('meal_delivery_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_delivery_id')->constrained()->onDelete('cascade');
            $table->enum('delivery_status', ['pending','preparing','ready_for_pickup','picked_up','on_the_way','arrived','delivered','failed','cancelled'
            ]);
            $table->text('notes')->nullable();
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_lng', 11, 8)->nullable();
            $table->unsignedBigInteger('updated_by_id')->nullable();
            $table->string('updated_by_type')->nullable(); // 'client', 'delivery_person', 'system', 'customer'
            $table->timestamps();

            $table->index(['updated_by_id', 'updated_by_type']);
            $table->index('delivery_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void 
    {
        Schema::dropIfExists('meal_delivery_status_histories');
    }
};
