<div style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; min-width:1000px; overflow:auto; line-height:1.6; color: #333;">
    <div style="margin: 50px auto; width: 80%; max-width: 600px; padding: 30px 0;">
        @php
            $link = $recipientType === 'customer' 
                ? 'http://127.0.0.1:8000/user/complaint/details/'.$complaint->id 
                : 'http://127.0.0.1:8000/client/complaint/details/'.$complaint->id;
        @endphp
        <!-- Header -->
        <div style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 30px; text-align: center;">
            <a href="{{ url('/') }}" style="font-size: 1.8em; color: #00466a; text-decoration: none; font-weight: 700;">
                SpareX
            </a>
            <p style="font-size: 1.2em; color: #666; margin-top: 5px;">Product Complaint Status Update</p>
        </div>


        <!-- Complaint Alert -->
        <div style="background: #fff8e1; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
            <p style="margin: 0 0 12px 0; color: #2e7d32; font-weight: 500; line-height: 1.5;">
                The complaint for <strong>Order #{{ $complaint->order->order_number }}</strong> has been successfully resolved.
            </p>

            <p style="margin: 0 0 16px 0; color: #2e7d32; font-weight: 500; line-height: 1.5;">
                You can review the resolution details by clicking the link below:
            </p>

            <a href="{{ $link }}" 
               style="display: inline-block; padding: 10px 20px; background-color: #2e7d32; color: #fff; 
                      text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500; margin: 8px 0;">
               View Resolution Details
            </a>
            <p style="margin: 10px 0 0 0; color: #856404; font-weight: 500;">
                Current Status: <strong style="text-transform: capitalize;">{{ $complaint->status }}</strong>
            </p>
        </div>

        <!-- Main Content - Horizontal Layout -->
        <div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                @if($complaint->product->image)
                <div style="flex: 0 0 120px;">
                    <img src="{{ asset('upload/product/small/' . $complaint->product->image) }}" 
                         alt="{{ $complaint->product->name }}"
                         style="width: 100%; height: auto; border-radius: 6px; border: 1px solid #ddd;">
                </div>
                @endif
                
                <div style="flex: 1;">
                    <h2 style="color: #00466a; margin: 0 0 10px 0; font-size: 1.5em;">{{ $complaint->product->name }}</h2>
                    
                    @if($complaint->product->category)
                    <div style="margin-bottom: 8px; color: #666;">
                        <strong>Category:</strong> {{ $complaint->product->category->name }}
                    </div>
                    @endif
                    
                    <div style="margin-bottom: 8px; color: #28a745; font-weight: bold;">
                        ${{ number_format($complaint->product->price, 2) }} × {{ $complaint->order->orderItems->first()->quantity ?? 1 }}
                    </div>
                    
                    @if($complaint->variant)
                    <div style="margin-bottom: 8px; color: #666;">
                        <strong>Variant:</strong> 
                        @if($complaint->variant->color)
                        <span style="margin-right: 10px;">Color: {{ $complaint->variant->color }}</span>
                        @endif
                        @if($complaint->variant->size)
                        <span>Size: {{ $complaint->variant->size }}</span>
                        @endif
                    </div>
                    @endif
                    
                    <div style="color: #666;">
                        <strong>Sold by:</strong> 
                        {{ $complaint->product->client->firstName ?? '' }} {{ $complaint->product->client->lastName ?? '' }}
                    </div>
                    
                    <div style="margin-top: 10px; font-weight: bold; text-align: right;">
                        Total: ${{ number_format($complaint->product->price * ($complaint->order->orderItems->first()->quantity ?? 1), 2) }}
                    </div>
                </div>
            </div>

            <!-- Complaint Message -->
            <div style="padding: 15px; background: #f1f8ff; border-radius: 6px; margin-top: 25px; border-left: 4px solid #1976d2;">
                <h3 style="color: #00466a; margin-top: 0; margin-bottom: 10px; font-size: 1.1em;">Customer Complaint</h3>
                <div style="margin: 0; line-height: 1.6; color: #555;">
                    {!! $complaint->message !!}
                </div>
                <div style="margin-top: 10px; font-size: 0.9em; color: #666;">
                    <strong>Submitted on:</strong> {{ \Carbon\Carbon::parse($complaint->cmp_date)->format('M d, Y') }} at {{ $complaint->cmp_time }}
                </div>
                <div style="margin-top: 8px; font-size: 0.9em; color: #666;">
                    <strong>Complaint By:</strong> {{ $complaint->customer->firstName ?? '' }} {{ $complaint->customer->lastName ?? '' }}
                </div>
                <div style="margin-top: 5px; font-size: 0.9em; color: #666;">
                    <strong>Mobile:</strong> {{ $complaint->customer->mobile ?? 'N/A' }}
                </div>
                <div style="margin-top: 5px; font-size: 0.9em; color: #666;">
                    <strong>Email:</strong> {{ $complaint->customer->email ?? 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 0.9em;">
            <p style="color: #666; margin: 5px 0;">
                © {{ date('Y') }} SpareX. All rights reserved.
            </p>
            <div style="color: #999; margin-top: 10px;">
                <p style="margin: 3px 0;">Need help? Contact us at support@sparex.com</p>
            </div>
        </div>
    </div>
</div>