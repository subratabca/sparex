<div style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; min-width:1000px; overflow:auto; line-height:1.6; color: #333;">
    <div style="margin: 50px auto; width: 80%; max-width: 600px; padding: 30px 0;">
        <!-- Header -->
@php
    $sender = $mailSender->role === 'client' ? 'Client' : 'Customer';
    $senderRole = $mailSender->role === 'client' ? 'seller' : 'customer';
    $link = $recipientType === 'admin' 
        ? url('admin/complaint/details/'.$complaint->id) 
        : ($senderRole === 'seller' 
            ? url('user/complaint/details/'.$complaint->id) 
            : url('client/complaint/details/'.$complaint->id));
    $latestConversation = $complaint->conversations->sortByDesc('created_at')->first();
    $orderItem = optional($complaint->orderItem);
    $product = optional($orderItem->product);
    $category = optional($product->category);
    $variant = optional($orderItem->variant);
    $clientUser = optional($product->client);
@endphp
        <div style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 30px; text-align: center;">
            <a href="{{ url('/') }}" style="font-size: 1.8em; color: #00466a; text-decoration: none; font-weight: 700;">
                SpareX
            </a>
            <p style="font-size: 1.2em; color: #666; margin-top: 5px;">
                Product Complaint Status Update From {{ $sender }}
            </p>
        </div>

        <!-- Complaint Alert -->
        <div style="background: #fff8e1; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
            @if($recipientType === 'admin')
                <p style="margin: 0; color: #2e7d32; font-weight: 500;">
                    The {{ $senderRole }} has responded to the complaint for <strong>Order #{{ optional($orderItem->order)->order_number ?? 'N/A' }}</strong>.
                </p>
                
                <a href="{{ $link }}" 
                   style="display: inline-block; margin: 2px 0; padding: 8px 16px; background-color: #17a2b8; color: #fff; 
                          text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
                   Review Complaint Details
                </a>

                <p style="margin: 0; color: #2e7d32; font-weight: 500;">
                   Please monitor this complaint until resolution.
                </p>
            @else
                @if($senderRole === 'seller')
                    <p style="margin: 0; color: #2e7d32; font-weight: 500;">
                        The seller has provided a response regarding your complaint for <strong>Order #{{ optional($orderItem->order)->order_number ?? 'N/A' }}</strong>.
                        If this response resolves your concern, no further action is needed.
                    </p>
                    
                    <p style="margin: 0; color: #2e7d32; font-weight: 500;">
                        If you have additional questions, please reply to this message by clicking below link.
                    </p>
                @else
                    <p style="margin: 0 0 10px 0; color: #2e7d32; font-weight: 500;">
                       The customer has provided the response regarding their complaint for <strong>Order #{{ optional($orderItem->order)->order_number ?? 'N/A' }}</strong>. By clicking below link you can see complaint details.
                    </p>
                @endif
            
                <a href="{{ $link }}" 
                   style="display: inline-block; margin: 2px 0; padding: 8px 16px; background-color: #17a2b8; color: #fff; 
                          text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
                   Review Complaint Details
                </a>

                <p style="margin: 0; color: #2e7d32; font-weight: 500; line-height: 1.5;">
                    Please respond within 24 hours if you have additional concerns.
                </p>
            @endif
        
            <p style="margin: 10px 0 0 0; color: #856404; font-weight: 500;">
                Current Status: <strong style="text-transform: capitalize;">{{ $complaint->status ?? 'N/A' }}</strong>
            </p>
        </div>

        <!-- Main Content - Horizontal Layout -->
        <div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                @if($product->image)
                <div style="flex: 0 0 120px;">
                    <img src="{{ asset('upload/product/small/' . $product->image) }}" 
                         alt="{{ $product->name ?? 'Product' }}"
                         style="width: 100%; height: auto; border-radius: 6px; border: 1px solid #ddd;">
                </div>
                @endif
                
                <div style="flex: 1;">
                    <h2 style="color: #00466a; margin: 0 0 10px 0; font-size: 1.5em;">{{ $product->name ?? 'N/A' }}</h2>
                    
                    @if($category->name ?? false)
                    <div style="margin-bottom: 8px; color: #666;">
                        <strong>Category:</strong> {{ $category->name }}
                    </div>
                    @endif
                    
                    <div style="margin-bottom: 8px; color: #28a745; font-weight: bold;">
                        ${{ number_format($product->price ?? 0, 2) }} × {{ $orderItem->quantity ?? 1 }}
                    </div>
                    
                    @if($variant)
                    <div style="margin-bottom: 8px; color: #666;">
                        <strong>Variant:</strong> 
                        @if($variant->color)
                        <span style="margin-right: 10px;">Color: {{ $variant->color }}</span>
                        @endif
                        @if($variant->size)
                        <span>Size: {{ $variant->size }}</span>
                        @endif
                    </div>
                    @endif
                    
                    <div style="color: #666;">
                        <strong>Sold by:</strong> 
                        {{ $clientUser->firstName ?? '' }} {{ $clientUser->lastName ?? '' }}
                    </div>
                    
                    <div style="margin-top: 10px; font-weight: bold; text-align: right;">
                        Total: ${{ number_format(($product->price ?? 0) * ($orderItem->quantity ?? 1), 2) }}
                    </div>
                </div>
            </div>

            <!-- Customer Complaint -->
            <div style="padding: 15px; background: #f1f8ff; border-radius: 6px; margin-top: 25px; border-left: 4px solid #1976d2;">
                <h3 style="color: #00466a; margin-top: 0; margin-bottom: 10px; font-size: 1.1em;">Customer Complaint</h3>
                <div style="margin: 0; line-height: 1.6; color: #555;">
                    {!! $complaint->message ?? '' !!}
                </div>
                <div style="margin-top: 10px; font-size: 0.9em; color: #666;">
                    <strong>Submitted on:</strong> {{ optional($complaint->cmp_date) ? \Carbon\Carbon::parse($complaint->cmp_date)->format('M d, Y') : 'N/A' }} at {{ $complaint->cmp_time ?? 'N/A' }}
                </div>
                <div style="margin-top: 8px; font-size: 0.9em; color: #666;">
                    <strong>Complaint By:</strong> {{ optional($complaint->customer)->firstName ?? '' }} {{ optional($complaint->customer)->lastName ?? '' }}
                </div>
            </div>

            <!-- 🔹 Latest Reply (Client or Customer) -->
            @if($latestConversation)
            <div style="padding: 15px; background: #fff3cd; border-radius: 6px; margin-top: 25px; border-left: 4px solid #ff9800;">
                <h3 style="color: #856404; margin-top: 0; margin-bottom: 10px; font-size: 1.1em;">
                    Latest Reply from {{ ucfirst($latestConversation->sender_role) }}
                </h3>
                <div style="margin: 0; line-height: 1.6; color: #555;">
                    {!! nl2br(e($latestConversation->reply_message)) !!}
                </div>
                <div style="margin-top: 10px; font-size: 0.9em; color: #666;">
                    <strong>Sent on:</strong> {{ \Carbon\Carbon::parse($latestConversation->created_at)->format('M d, Y h:i A') }}
                </div>
            </div>
            @endif
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
