<!DOCTYPE html>
<html>
<head>
    <title>New Delivery Assignment</title>
</head>
<body>
    <h2>New Delivery Assignment</h2>
    
    <p><strong>Order Number:</strong> #{{ $mealOrder->order_number ?? $data['order_number'] }}</p>
    <p><strong>Client:</strong> {{ $client->name ?? $data['client_name'] }}</p>
    <p><strong>Pickup Address:</strong> {{ $data['client_address'] }}</p>
    <p><strong>Delivery Address:</strong> {{ $data['shipping_address'] }}</p>
    <p><strong>Delivery Charge:</strong> ${{ number_format($data['delivery_charge'], 2) }}</p>
    <p><strong>Meal Date:</strong> {{ \Carbon\Carbon::parse($data['meal_date'])->format('M d, Y') }}</p>
    <p><strong>Items:</strong> {{ $data['item_count'] }} item(s)</p>
    <p><strong>Meal Type:</strong> {{ $data['meal_type'] }}</p>
    
    @if(!empty($data['notes']))
    <p><strong>Notes:</strong> {{ $data['notes'] }}</p>
    @endif
    
    <p>
        <a href="{{ url('/rider/accept/' . ($data['delivery_ledger_id'] ?? '')) }}" 
           style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Accept Delivery Assignment
        </a>
    </p>
    
    <p><em>First come, first served!</em></p>
</body>
</html>