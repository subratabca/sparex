<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_health_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Core profile
            $table->enum('gender', ['male', 'female', 'other']);
            $table->unsignedSmallInteger('age');
            $table->decimal('weight', 6, 2);   // kg
            $table->decimal('height', 6, 2);   // cm
            $table->text('description')->nullable();

            // Health details
            $table->json('conditions')->nullable();                         // multi-select diseases
            $table->string('activity_level', 20)->default('light');         // sedentary|light|moderate|active|very_active
            $table->string('goal', 20)->nullable();                         // lose|maintain|gain
            $table->json('allergies')->nullable();                          // optional free list
            $table->string('dietary_preference', 30)->nullable();           // none|vegetarian|vegan|halal|...

            // History comparison window
            $table->string('period', 20)->default('last_week');             // last_week | last_month

            $table->timestamps();

            $table->unique('user_id'); // one health profile per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_health_profiles');
    }
};