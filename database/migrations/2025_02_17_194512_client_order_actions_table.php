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
        Schema::create('client_order_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total_refund', 10, 2)->default(0);
            $table->enum('payment_status', ['due', 'paid'])->default('due');
            $table->enum('action_type', ['canceled', 'returned', 'exchanged'])->nullable();
            $table->text('action_reason')->nullable();
            $table->unsignedBigInteger('action_by_id')->nullable();
            $table->enum('action_by_role', ['client', 'customer'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_order_actions');
    }
};
