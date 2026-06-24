<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_carts', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('meal_type_id')
                  ->constrained('customer_meal_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('meal_carts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
