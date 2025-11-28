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
        Schema::create('meal_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            $table->string('name', 50);
            $table->timestamps();
            $table->unique(['meal_type_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_keywords');
    }
};

