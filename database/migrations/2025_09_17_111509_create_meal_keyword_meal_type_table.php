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
        Schema::create('meal_keyword_meal_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            $table->foreignId('meal_keyword_id')->constrained('meal_keywords')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['meal_type_id', 'meal_keyword_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_keyword_meal_type');
    }
};
