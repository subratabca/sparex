<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_shipping_addresses', function (Blueprint $table) {
            // NULL for single-address (/meal-plans) rows; set for multi-address (/meal-plan-setup) groups
            $table->date('meal_date')->nullable()->after('meal_order_id');
            $table->foreignId('meal_type_id')->nullable()->after('meal_date')
                  ->constrained('meal_types')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->after('meal_type_id')
                  ->constrained('customer_meal_locations')->nullOnDelete();
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('meal_shipping_addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meal_type_id');
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn(['meal_date', 'delivery_fee']);
        });
    }
};
