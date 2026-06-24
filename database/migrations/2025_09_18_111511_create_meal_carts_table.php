<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_carts', function (Blueprint $table) {
            $table->id();
            $table->date('meal_date');
            $table->time('meal_time')->nullable();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('customer_meal_locations')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->unsigned()->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_carts');
    }
};