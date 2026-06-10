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
        Schema::create('client_meal_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_meal_order_id')->nullable()->constrained('client_meal_orders')->onDelete('cascade');

            $table->enum('type', ['credit', 'debit']);
            $table->enum('payment_method', ['cash','stripe','paypal'])->nullable();
            $table->decimal('paid_amount', 10, 2);
            $table->string('transaction_id', 100)->nullable(); 
            $table->string('currency', 10)->nullable();
            $table->text('payment_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_meal_payment_histories');
    }
};
