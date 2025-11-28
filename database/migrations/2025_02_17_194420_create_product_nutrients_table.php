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
        Schema::create('product_nutrients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Macronutrients
            $table->decimal('calories', 8, 2)->nullable()->comment('Energy value');
            $table->string('calories_unit', 10)->default('kcal');

            $table->decimal('protein', 8, 2)->nullable()->comment('Protein content');
            $table->string('protein_unit', 10)->default('g');

            $table->decimal('fat', 8, 2)->nullable()->comment('Fat content');
            $table->string('fat_unit', 10)->default('g');

            $table->decimal('carbohydrates', 8, 2)->nullable()->comment('Carbohydrate content');
            $table->string('carbohydrates_unit', 10)->default('g');

            $table->decimal('fiber', 8, 2)->nullable()->comment('Dietary fiber');
            $table->string('fiber_unit', 10)->default('g');

            $table->decimal('sugar', 8, 2)->nullable()->comment('Sugar content');
            $table->string('sugar_unit', 10)->default('g');

            $table->decimal('cholesterol', 8, 2)->nullable()->comment('Cholesterol amount');
            $table->string('cholesterol_unit', 10)->default('mg');

            $table->decimal('sodium', 8, 2)->nullable()->comment('Sodium amount');
            $table->string('sodium_unit', 10)->default('mg');

            // Micronutrients
            $table->decimal('vitamin_a', 8, 2)->nullable()->comment('Vitamin A content');
            $table->string('vitamin_a_unit', 10)->default('µg');

            $table->decimal('vitamin_c', 8, 2)->nullable()->comment('Vitamin C content');
            $table->string('vitamin_c_unit', 10)->default('mg');

            $table->decimal('calcium', 8, 2)->nullable()->comment('Calcium content');
            $table->string('calcium_unit', 10)->default('mg');

            $table->decimal('iron', 8, 2)->nullable()->comment('Iron content');
            $table->string('iron_unit', 10)->default('mg');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_nutrients');
    }
};
