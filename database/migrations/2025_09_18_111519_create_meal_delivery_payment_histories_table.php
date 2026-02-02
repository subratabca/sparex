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
        Schema::create('meal_delivery_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_charge_ledger_id')->nullable()->constrained('delivery_charge_ledgers')->onDelete('cascade');

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
        Schema::dropIfExists('meal_delivery_payment_histories');
    }
};
