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
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict')->onUpdate('cascade');
            $table->string('name');
            $table->string('email', 50)->nullable();
            $table->string('phone');
            $table->string('address1', 255);
            $table->string('address2', 255)->nullable();
            $table->string('zip_code', 50);
            $table->foreignId('country_id')->constrained('countries')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('county_id')->constrained('counties')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('city_id')->constrained('cities')->onDelete('restrict')->onUpdate('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
