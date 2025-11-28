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
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');

            // Transaction Type: credit = add money, debit = spend money
            $table->enum('type', ['credit', 'debit']);

            // Payment method for credit additions
            $table->enum('method', ['cash', 'stripe', 'paypal'])->nullable();

            // Amount credited or debited
            $table->decimal('amount', 10, 2);

            // Balance after this transaction (very useful)
            $table->decimal('balance_after', 10, 2)->default(0);

            // Optional: transaction reference (Stripe ID, PayPal ID, Invoice No.)
            $table->string('transaction_id', 100)->nullable(); 
            $table->string('currency', 10)->nullable();

            // Optional: any notes (e.g. "Used for breakfast meal", "Admin adjustment")
            $table->text('description')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};

