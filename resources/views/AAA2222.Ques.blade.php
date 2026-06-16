Route::prefix('admin')->middleware('jwt.auth:admin')->group(function () {
    Route::controller(MealDeliveryChargeController::class)->group(function () {
        Route::get('/meal-delivery/charge','index')->name('meal.delivery.charges');
        Route::get('/get/meal-delivery/charges','getList');
        Route::get('/create/meal-delivery/charge','create')->name('create.meal.delivery.charge');
        Route::post('/store/meal-delivery/charge','store');
        Route::get('/get/meal-delivery/charge/details/{id}','show');
        Route::get('/edit/meal-delivery/charge/{id}','edit');
        Route::post('/update/meal-delivery/charge','update');
        Route::post('/delete/meal-delivery/charge/{id}','delete');
    });
 });



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



when i create new delivery charge for meal type dinner it showing Validation failed in toastmessage.i want to show error message under every input field not toast error message.if diner is created for all distance charge then it will show alredy created or some informative good message under input field. but i can edit it.

Map 422 validation errors to the inline fields, otherwise use the global function showLoader(),hideLoader(),successToast(msg),errorToast(msg),handleError(error) in config.js for create,edit,index,delete page.