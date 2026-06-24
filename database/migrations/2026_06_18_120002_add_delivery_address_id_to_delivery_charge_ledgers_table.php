<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_charge_ledgers', function (Blueprint $table) {
            // the rider's drop-off address for this leg (multi-address orders); NULL for single-address
            $table->foreignId('delivery_address_id')->nullable()->after('meal_type_id')
                  ->constrained('meal_shipping_addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_charge_ledgers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_address_id');
        });
    }
};
