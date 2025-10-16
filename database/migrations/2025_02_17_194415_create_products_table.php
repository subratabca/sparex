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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('restrict')->onUpdate('cascade');
            $table->string('image', 255);
            $table->string('name', 50)->unique();
            $table->decimal('weight', 10, 2)->default(0)->comment('Weight in kilograms (kg)');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->default(0);
            $table->integer('current_stock')->default(0);
            $table->string('address1', 255); 
            $table->string('address2', 255)->nullable(); 
            $table->foreignId('country_id')->constrained('countries')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('county_id')->constrained('counties')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('city_id')->constrained('cities') ->onDelete('restrict')->onUpdate('cascade');
            $table->string('zip_code', 50);
            $table->text('description'); 
            $table->date('expire_date')->nullable(); 
            $table->date('collection_date')->nullable();
            $table->time('start_collection_time')->nullable();
            $table->time('end_collection_time')->nullable(); 
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable(); 
            $table->boolean('accept_tnc')->default(0); 
            $table->enum('status', ['pending', 'published', 'processing', 'completed'])->default('pending'); 
            $table->boolean('has_variants')->default(0);
            $table->boolean('has_brand')->default(0);
            $table->boolean('is_free')->default(false); 
            $table->boolean('has_discount_price')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

