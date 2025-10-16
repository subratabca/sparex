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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnUpdate()->nullOnDelete();
            $table->integer('quantity');
            $table->enum('movement_type', ['upload','sale','cancel','adjustment','return','expired','damaged']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['product_id', 'variant_id']);
            $table->index(['client_id', 'movement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
