
Client can change delivery_status from pending->accept_order->preparing->ready_for_pickup

Delivery Man can change delivery_status from ready_for_pickup->picked_up->on_the_way->arrived','delivered'

when delivery_status is pending it will show accept_order and cancelled,when delivery_status is accept_order it will show preparing only,when delivery_status is preparing it will show ready_for_pickup only,  in your provide function openDeliveryStatusModalForGroup() modal in view.blade.php.when delivery_status is preparing in modal it will show a input field to give pick up time manually by client.So give me full updated part only.






select status in dropdown will be disabled in your provide function openDeliveryStatusModalForGroup() modal.Give me this part only.