Route::prefix('delivery')->middleware([DeliveryTokenVerificationMiddleware::class])->group(function () {
	Route::controller(DeliveryNotificationController::class)->group(function () {
        Route::get('/notification/list', 'index')->name('delivery.notifications');
        Route::get('/get/notification/list', 'getNotificationList');
        Route::get('/limited/notification/list', 'limitedNotificationList');
	    Route::get('/view/notification/{notificationId}', 'view');
	    Route::get('/get/notification/details/{notificationId}', 'getNotificationDetails')->name('delivery.notification.details');
	});
});

...............................................

frontend.components.meal-cart.cart.blade.php


Check AdminProfileController all method and its corresponding blade.php  pages & its functionalities.
Map 422 validation errors to the inline fields, otherwise use the global function showLoader(),hideLoader(),successToast(msg),errorToast(msg),handleError(error) in config.js in above pages
............................................................................

Check AdminAuthController all method and its corresponding blade.php  pages & its functionalities.
Check ClientAuthController all method and its corresponding blade.php  pages & its functionalities.

we start here: customer also nedd to update


Earlier you provide below  2 template using bootstrap5 for spare-meal-system
1.meal-customer-dashboard-preview.html
2.meal-home-preview.html

Now provide Login and register page html for spare-meal-system