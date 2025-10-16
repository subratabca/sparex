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
        Schema::create('delivery_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');

            // Inside city delivery charges
            $table->decimal('inside_city_2kg', 10, 2)->default(0);
            $table->decimal('inside_city_5kg', 10, 2)->default(0);
            $table->decimal('inside_city_10kg', 10, 2)->default(0);
            $table->decimal('inside_city_above_10kg', 10, 2)->default(0);

            // Outside city delivery charges
            $table->decimal('outside_city_2kg', 10, 2)->default(0);
            $table->decimal('outside_city_5kg', 10, 2)->default(0);
            $table->decimal('outside_city_10kg', 10, 2)->default(0);
            $table->decimal('outside_city_above_10kg', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_charges');
    }
};


