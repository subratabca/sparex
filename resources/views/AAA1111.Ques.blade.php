Route::prefix('delivery')->middleware([DeliveryTokenVerificationMiddleware::class])->group(function () {
	Route::controller(DeliveryNotificationController::class)->group(function () {
        Route::get('/notification/list', 'index')->name('delivery.notifications');
        Route::get('/get/notification/list', 'getNotificationList');
        Route::get('/limited/notification/list', 'limitedNotificationList');
	    Route::get('/view/notification/{notificationId}', 'view');
	    Route::get('/get/notification/details/{notificationId}', 'getNotificationDetails')->name('delivery.notification.details');
	});
});


In this page have to correction below things:

1. rider name showing delivery one it should be Delivery One. all name like meal type name,delivery man name,product/food name should be Title Case.

2. Delivery Date  2026-06-05T18:00:00.000000Z should be Delivery Date  05-june-2026 8:00 am/pm

3. in order item section  must show product image and product name should be Title Case.

4. Delivery Actions and below of this sectin need to merge and make it one section with attractive looking & move this section at the after 1 no image.


Route::prefix('delivery')->middleware([DeliveryTokenVerificationMiddleware::class])->group(function () {
	Route::controller(DeliveryNotificationController::class)->group(function () {
        Route::get('/notification/list', 'index')->name('delivery.notifications');
        Route::get('/get/notification/list', 'getNotificationList');
	});
});

update views->user.components.meal-plan.index.blade.php

Country->England
County-> Greater London
City-> Barnet
Address->12 High Street
Postcode-> EN5 5XQ

Based on Country->England,County-> Greater London,City-> Barnet give me 3 address & postcode for 3 different clients and 1 shipping address which have 1 to 2 km distance from client 1 , 2 to 5 km from client 2 and 5 to 10 km from client 3.