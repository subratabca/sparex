In this project customer can order multiple meal from multiple clients(client means who will provide meal) for future booking.Meal type include breakfast,lunch,snacks,dinner. each meal type may have multiple items like breakfast have 3 items from 3 different clients. For meal delivery to customer there is a delivery charge which will calculate on distance wise from client location to customer meal shipping address.suppose customer-A order for breakfast for 3 items.1 item will come from client-1 and 2 items will come from client-2 then it will count 2 delivery charge one for clent-1 and 2nd for client-2 for same meal type for same day.if same customer may have order for dinner then delivery charge will be calculate same as breakfast this way.want to save delivery amount and payment_status as due or paid for delivery person for every delivery for same order same meal type on same day from same client delivery charge will count 1 time.Update below migration to fullfill this condition

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
        Schema::create('meal_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_person_id')->nullable()->constrained('users');
            $table->enum('delivery_status', ['pending','preparing','ready_for_pickup','picked_up','on_the_way','arrived','delivered','failed','cancelled'
            ])->default('pending');
            $table->timestamp('estimated_delivery_time')->nullable();
            $table->timestamp('actual_delivery_time')->nullable();
            $table->timestamp('pickup_time')->nullable();
            $table->timestamp('handover_time')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('tracking_code')->unique()->nullable();
            $table->decimal('current_location_lat', 10, 8)->nullable();
            $table->decimal('current_location_lng', 11, 8)->nullable();
            $table->string('proof_of_delivery_image')->nullable();
            $table->timestamps();

            $table->index('delivery_status');
            $table->index('tracking_code');
            $table->index('delivery_person_id');
                });
    }

    /**
     * Reverse the migrations. 2025_12_13_165148
     */
    public function down(): void 
    {
        Schema::dropIfExists('meal_deliveries');
    }
};
