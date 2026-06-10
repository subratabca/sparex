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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstName', 50);
            $table->string('lastName', 50)->nullable();
            $table->string('email', 50)->unique();
            $table->string('mobile', 50)->nullable();
            $table->string('image', 255)->nullable();
            $table->enum('role', ['admin', 'client', 'customer','delivery'])->default('customer');
            $table->string('password', 255); 
            $table->boolean('accept_registration_tnc')->default(0);
            $table->string('otp', 6); 
            $table->boolean('status')->default(0);
            $table->boolean('is_email_verified')->default(0);
            $table->string('address1', 255)->nullable();
            $table->string('address2', 255)->nullable();
            $table->string('zip_code', 50)->nullable();
            $table->string('doc_image1', 255)->nullable();
            $table->string('doc_image2', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('county_id')->nullable()->constrained('counties')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete(); // Fixed foreign key
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};


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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->integer('max_request_by_customer')->default(1);
            $table->string('image', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};


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
        Schema::create('meal_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_types'); 
    }
};


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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('restrict')->onUpdate('cascade');
            $table->string('image', 255);
            $table->string('name', 50);
            $table->decimal('weight', 10, 2)->default(0)->comment('Weight in kilograms (kg)');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->default(0);
            $table->integer('current_stock')->default(0);
            $table->string('address1', 255); 
            $table->string('address2', 255)->nullable(); 
            $table->foreignId('country_id')->constrained('countries')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('county_id')->constrained('counties')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('city_id')->constrained('cities') ->onDelete('restrict')->onUpdate('cascade');
            $table->string('zip_code', 50);
            $table->text('description'); 
            $table->date('expire_date')->nullable(); 
            $table->date('collection_date')->nullable();
            $table->time('start_collection_time')->nullable();
            $table->time('end_collection_time')->nullable(); 
            $table->decimal('latitude', 10, 8)->nullable(); 
            $table->decimal('longitude', 11, 8)->nullable(); 
            $table->boolean('accept_tnc')->default(0); 
            $table->enum('status', ['pending', 'published', 'processing', 'completed'])->default('pending'); 
            $table->boolean('has_availability')->default(0);
            $table->boolean('has_variants')->default(0);
            $table->boolean('has_brand')->default(0);
            $table->boolean('is_free')->default(false); 
            $table->boolean('has_discount_price')->default(0);
            $table->unique(['client_id', 'name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};


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
        Schema::create('product_nutrients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Macronutrients
            $table->decimal('calories', 8, 2)->nullable()->comment('Energy value');
            $table->string('calories_unit', 10)->default('kcal');

            $table->decimal('protein', 8, 2)->nullable()->comment('Protein content');
            $table->string('protein_unit', 10)->default('g');

            $table->decimal('fat', 8, 2)->nullable()->comment('Fat content');
            $table->string('fat_unit', 10)->default('g');

            $table->decimal('carbohydrates', 8, 2)->nullable()->comment('Carbohydrate content');
            $table->string('carbohydrates_unit', 10)->default('g');

            $table->decimal('fiber', 8, 2)->nullable()->comment('Dietary fiber');
            $table->string('fiber_unit', 10)->default('g');

            $table->decimal('sugar', 8, 2)->nullable()->comment('Sugar content');
            $table->string('sugar_unit', 10)->default('g');

            $table->decimal('cholesterol', 8, 2)->nullable()->comment('Cholesterol amount');
            $table->string('cholesterol_unit', 10)->default('mg');

            $table->decimal('sodium', 8, 2)->nullable()->comment('Sodium amount');
            $table->string('sodium_unit', 10)->default('mg');

            // Micronutrients
            $table->decimal('vitamin_a', 8, 2)->nullable()->comment('Vitamin A content');
            $table->string('vitamin_a_unit', 10)->default('µg');

            $table->decimal('vitamin_c', 8, 2)->nullable()->comment('Vitamin C content');
            $table->string('vitamin_c_unit', 10)->default('mg');

            $table->decimal('calcium', 8, 2)->nullable()->comment('Calcium content');
            $table->string('calcium_unit', 10)->default('mg');

            $table->decimal('iron', 8, 2)->nullable()->comment('Iron content');
            $table->string('iron_unit', 10)->default('mg');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_nutrients');
    }
};


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
        Schema::create('meal_type_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('meal_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_type_products');
    }
};

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
        Schema::create('meal_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            $table->string('name', 50);
            $table->timestamps();
            $table->unique(['meal_type_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_keywords');
    }
};


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
        Schema::create('meal_delivery_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            // Inside city delivery charges
            $table->decimal('inside_city_2km', 10, 2)->default(0);
            $table->decimal('inside_city_5km', 10, 2)->default(0);
            $table->decimal('inside_city_10km', 10, 2)->default(0);
            $table->decimal('inside_city_above_10km', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.2025_09_17_111503_create_meal_delivery_charges_table.php
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_delivery_charges');
    }
};


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
        Schema::create('meal_carts', function (Blueprint $table) {
            $table->id();
            $table->date('meal_date');
            $table->time('meal_time')->nullable();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade'); 
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->unsigned()->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_carts');
    }
};


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
        Schema::create('meal_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('delivery_type', ['self_pickup','courier'])->default('self_pickup');
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('client_platform_fee', 10, 2)->default(0);
            $table->decimal('rider_platform_fee', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);  
            $table->decimal('payable_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0); 
            $table->string('payment_type', 50)->nullable(); 
            $table->string('payment_method', 50)->nullable(); 
            $table->string('transaction_id', 100)->nullable(); 
            $table->string('currency', 10)->nullable();  
            $table->string('order_number', 50)->unique(); 
            $table->string('invoice_no', 50)->unique(); 
            $table->enum('payment_status', ['due','paid','partially_paid'])->default('due');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_orders');
    }
};


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
        Schema::create('meal_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_order_id')->constrained('meal_orders')->onDelete('cascade'); 
            $table->date('meal_date');
            $table->time('meal_time')->nullable();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade'); 
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->unsigned()->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * 
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_order_items');
    }
};


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
        Schema::create('client_meal_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_order_id')->constrained('meal_orders')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0); 
            $table->decimal('payable_amount', 10, 2);
            $table->enum('payment_status', ['due', 'paid'])->default('due');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_meal_orders');
    }
};




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
        Schema::create('meal_shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_order_id')->constrained('meal_orders')->onDelete('cascade');
            $table->string('name');
            $table->string('email', 50)->nullable();
            $table->string('phone');
            $table->string('address1', 255);
            $table->string('address2', 255)->nullable();
            $table->string('zip_code', 50);
            $table->foreignId('country_id')->constrained('countries')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('county_id')->constrained('counties')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('city_id')->constrained('cities')->onDelete('restrict')->onUpdate('cascade'); 
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_shipping_addresses');
    }
};

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
        Schema::create('delivery_charge_ledgers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('meal_order_id')->constrained('meal_orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            
            $table->foreignId('delivery_person_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            $table->date('delivery_date');

            $table->string('order_tracking')->unique()->nullable();

            $table->enum('delivery_status', ['pending','accept_order','preparing','ready_for_pickup','picked_up','on_the_way','arrived','delivered','cancelled'])->default('pending');
            
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('distance_km', 8, 2)->nullable(); 
            $table->string('distance_category')->nullable(); 
            
            $table->enum('payment_status', ['due', 'paid', 'cancelled'])->default('due');
            
            $table->boolean('is_charge_counted')->default(true);
            $table->string('charge_key')->unique(); 
            
            $table->timestamps();
            
            // Indexes for performance with shorter names
            $table->index(['client_id', 'delivery_date', 'meal_type_id'], 'idx_dcl_client_date_type');
            $table->index(['delivery_person_id', 'payment_status'], 'idx_dcl_delivery_payment');
            $table->index('charge_key', 'idx_dcl_charge_key');
            $table->index('meal_order_id', 'idx_dcl_meal_order');
            $table->index('customer_id', 'idx_dcl_customer');
            
            // indexes for tracking
            $table->index('order_tracking', 'idx_dcl_order_tracking');
            $table->index(['customer_id', 'delivery_status'], 'idx_dcl_customer_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_charge_ledgers');
    }
};


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
            $table->timestamp('pick_up_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('updated_by_id')->nullable();
            $table->string('updated_by_type')->nullable(); // 'client', 'delivery_person', 'system', 'customer'
            $table->timestamps();

            $table->index(['updated_by_id', 'updated_by_type'], 'idx_mdsh_updated_by');
            $table->index('delivery_status', 'idx_mdsh_status');
            $table->index('created_at', 'idx_mdsh_created_at');
            $table->index('delivery_charge_ledger_id', 'idx_mdsh_delivery_charge_ledger');
            $table->index(['delivery_status', 'pick_up_at'], 'idx_mdsh_status_pickup_at');
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





above is my table migration.