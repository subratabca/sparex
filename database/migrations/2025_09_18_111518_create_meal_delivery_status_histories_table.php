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
        Schema::create('meal_delivery_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_charge_ledger_id')->nullable()->constrained('delivery_charge_ledgers')->onDelete('cascade');
            $table->enum('delivery_status', ['pending','accept_order','preparing','ready_for_pickup','picked_up','on_the_way','arrived','delivered','cancelled'
            ]);
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('updated_by_id')->nullable();
            $table->string('updated_by_type')->nullable(); // 'client', 'delivery_person', 'system', 'customer'
            $table->timestamps();

            $table->index(['updated_by_id', 'updated_by_type'], 'idx_mdsh_updated_by');
            $table->index('delivery_status', 'idx_mdsh_status');
            $table->index('created_at', 'idx_mdsh_created_at');
            $table->index('delivery_charge_ledger_id', 'idx_mdsh_delivery_charge_ledger');
            $table->index(['delivery_status', 'picked_up_at'], 'idx_mdsh_status_picked_at');
            $table->index(['delivery_status', 'delivered_at'], 'idx_mdsh_status_delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void 
    {
        Schema::dropIfExists('meal_delivery_status_histories');
    }
};


