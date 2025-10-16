@php
    $status = $clientOrder->status ?? 'pending';
    $clientName = $clientOrder->client?->firstName . ' ' . $clientOrder->client?->lastName;
@endphp

<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 800px; margin: 0 auto; color: #333; line-height: 1.6;">
    <!-- Header -->
    <div style="text-align: center; padding: 20px 0; border-bottom: 1px solid #eee;">
        <h1 style="color: #00466a; margin: 0; font-size: 24px;">{{ config('app.name') }}</h1>
        <p style="color: #666; margin: 5px 0 0; font-size: 16px;">Order Status Update</p>
    </div>

    <!-- Order Info -->
    <div style="background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px;">
        <h2 style="color: #00466a; margin: 0 0 15px 0; text-align: center; font-size: 20px;">
            Order <span style="background: #FF4D49; color: white; padding: 3px 10px; border-radius: 20px; font-size: 16px;">#{{ $order->order_number }}</span>
        </h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <p style="margin: 8px 0;"><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p style="margin: 8px 0;"><strong>Invoice No:</strong> {{ $order->invoice_no }}</p>
                <p style="margin: 8px 0;"><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y h:i A') }}</p>
                <p style="margin: 8px 0;"><strong>Seller:</strong> {{ $clientName }}</p>
            </div>
            <div>
                <p style="margin: 8px 0;"><strong>Status:</strong> 
                    <span style="background:
                        @if($status === 'approved') #28a745
                        @elseif($status === 'pending') #ffc107
                        @elseif($status === 'cancelled') #dc3545
                        @elseif($status === 'delivered') #0d6efd
                        @else #6c757d
                        @endif;
                        color:white; padding: 3px 10px; border-radius: 20px; font-size: 12px;">
                        {{ ucfirst($status) }}
                    </span>
                </p>
                <p style="margin: 8px 0;"><strong>Customer:</strong> {{ $order->customer->firstName }} {{ $order->customer->lastName }}</p>
                <p style="margin: 8px 0;"><strong>Contact:</strong> {{ $order->customer->mobile }}</p>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div style="margin-bottom: 25px;">
        <h3 style="color: #00466a; border-bottom: 2px solid #eee; padding-bottom: 8px; margin-bottom: 15px; font-size: 18px;">
            Your Products
        </h3>
        
        @foreach($order->orderItems as $item)
            @if($item->client_id === $clientOrder->client_id) {{-- Only this seller’s products --}}
                @php 
                    $product = $item->product;
                    $variant = $item->variant;
                @endphp
                <div style="padding: 15px; background: white; border-radius: 8px; margin: 15px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="display: flex; gap: 15px;">
                        <div style="flex: 0 0 80px;">
                            <img src="{{ $product->image ? asset('upload/product/small/' . $product->image) : asset('upload/no_image.jpg') }}" 
                                 alt="{{ $product->name }}"
                                 style="width: 100%; height: auto; border-radius: 6px; border: 1px solid #eee;">
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 8px 0; color: #00466a; font-size: 16px;">{{ $product->name }}</h4>
                            
                            <div style="margin-bottom: 8px;">
                                <span style="font-size: 14px; color: #666;">Category: {{ $product->category->name ?? 'N/A' }}</span>
                                @if($product->brand)
                                <span style="font-size: 14px; color: #666; margin-left: 15px;">Brand: {{ $product->brand->name }}</span>
                                @endif
                            </div>
                            
                            @if($variant)
                            <div style="margin-bottom: 8px; font-size: 14px; color: #666;">
                                <span>Variant:</span>
                                @if($variant->color)<span style="margin-left: 5px;">Color: {{ $variant->color }}</span>@endif
                                @if($variant->size)<span style="margin-left: 5px;">Size: {{ $variant->size }}</span>@endif
                            </div>
                            @endif
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-size: 14px;">
                                    <span style="color: #00466a; font-weight: 500;">
                                        @if($product->discount_price > 0)
                                            <span style="text-decoration: line-through; color: #999;">£{{ number_format($product->price, 2) }}</span>
                                            <span style="color: #28a745; margin-left: 5px;">£{{ number_format($product->discount_price, 2) }}</span>
                                        @else
                                            £{{ number_format($product->price, 2) }}
                                        @endif
                                    </span>
                                    <span style="color: #666; margin-left: 5px;">× {{ $item->quantity }}</span>
                                </div>
                                <div style="font-weight: 600; color: #00466a;">
                                    Total: £{{ number_format($item->total_price, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Payment Summary -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <h3 style="color: #00466a; margin: 0 0 15px 0; font-size: 18px; border-bottom: 2px solid #eee; padding-bottom: 8px;">
            Payment Summary
        </h3>
        
        <div style="max-width: 400px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                <span>Subtotal:</span>
                <span style="font-weight: 500;">£{{ number_format($clientOrder->subtotal, 2) }}</span>
            </div>
            
            @if($clientOrder->coupon_discount > 0)
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                <span>Coupon Discount:</span>
                <span style="color: #dc3545; font-weight: 500;">-£{{ number_format($clientOrder->coupon_discount, 2) }}</span>
            </div>
            @endif
            
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                <span>Tax:</span>
                <span style="font-weight: 500;">£{{ number_format($clientOrder->tax, 2) }}</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; padding: 10px 0; margin-top: 10px;">
                <span style="font-weight: 600;">Total:</span>
                <span style="font-weight: 700; color: #28a745;">£{{ number_format($clientOrder->payable_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 0.9em;">
        <p style="color: #666; margin: 5px 0;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
        <div style="color: #999; margin-top: 10px;">
            <p style="margin: 3px 0;">Need help? Contact us at {{ config('mail.from.address') }}</p>
        </div>
    </div>
</div>
