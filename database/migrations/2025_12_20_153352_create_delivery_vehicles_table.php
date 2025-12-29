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
        Schema::create('delivery_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('users')->onDelete('cascade');
            $table->string('image', 255)->nullable();
            $table->enum('vehicle_type', ['bike','car','scooter','bicycle','walking']);
            $table->string('registration_number', 50)->nullable(); 
            $table->string('vehicle_color', 30)->nullable();
            $table->string('vehicle_brand', 50)->nullable(); 
            $table->string('vehicle_model', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['delivery_id', 'registration_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_vehicles');
    }
};