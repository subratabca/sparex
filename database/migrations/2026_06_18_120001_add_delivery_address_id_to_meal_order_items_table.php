<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_order_items', function (Blueprint $table) {
            $table->foreignId('delivery_address_id')->nullable()->after('meal_type_id')
                  ->constrained('meal_shipping_addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('meal_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_address_id');
        });
    }
};
