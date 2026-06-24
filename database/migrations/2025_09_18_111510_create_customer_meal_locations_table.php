<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_meal_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('label');                       // Home / Office / Gym
            $table->string('name')->nullable();            // contact name (optional)
            $table->string('phone', 30)->nullable();
            $table->string('address1', 255);
            $table->string('address2', 255)->nullable();
            $table->string('zip_code', 50);
            $table->foreignId('country_id')->constrained('countries')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('county_id')->constrained('counties')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('city_id')->constrained('cities')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void   
    {
        Schema::dropIfExists('customer_meal_locations');
    }
};
