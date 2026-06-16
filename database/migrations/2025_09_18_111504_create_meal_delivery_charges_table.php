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
        Schema::create('meal_delivery_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            // Inside city delivery charges
            $table->decimal('inside_city_2km', 10, 2)->default(0);
            $table->decimal('inside_city_5km', 10, 2)->default(0);
            $table->decimal('inside_city_10km', 10, 2)->default(0);
            $table->decimal('inside_city_above_10km', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.2025_09_17_111503_create_meal_delivery_charges_table.php
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_delivery_charges');
    }
};
