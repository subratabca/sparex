<div style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; min-width:1000px; overflow:auto; line-height:1.6; color:#333;">
    <div style="margin:50px auto; width:80%; max-width:600px; padding:30px 0;">
        
        <!-- Header -->
        <div style="border-bottom:1px solid #eee; padding-bottom:20px; margin-bottom:30px; text-align:center;">
            <a href="{{ url('/') }}" style="font-size:1.8em; color:#00466a; text-decoration:none; font-weight:700;">
                SpareX
            </a>
            @if($recipientType === 'client')
                <p style="font-size:1.2em; color:#666; margin-top:5px;">New Product Complaint</p>
            @else
                <p style="font-size:1.2em; color:#666; margin-top:5px;">Review Product Complaint</p>
            @endif
        </div>

        <!-- Complaint Alert -->
        <div style="background:#fff8e1; border-left:4px solid #ffc107; padding:15px; margin-bottom:25px; border-radius:4px;">
            @if($recipientType === 'client')
                <p style="margin:0; color:#2e7d32; font-weight:500;">
                    A customer has filed a complaint regarding your product from 
                    <strong>Order #{{ $complaint->orderItem->order->order_number ?? 'N/A' }}</strong>.
                    The complaint is currently under review.
                </p>
                <p style="margin:0; color:#2e7d32; font-weight:500;">
                    Please review the details and respond promptly to resolve this matter.
                </p>
                <a href="{{ url('client/complaint/details/' . $complaint->id) }}" 
                   style="display:inline-block; margin:8px 0; padding:8px 16px; background-color:#17a2b8; color:#fff; 
                          text-decoration:none; border-radius:4px; font-size:14px; font-weight:500;">
                   View Complaint Details
                </a>
                <p style="margin:0; color:#2e7d32; font-weight:500;">
                    Please respond within 24 hours to ensure timely resolution.
                </p>
            @else
                <p style="margin:0; color:#2e7d32; font-weight:500;">
                    Your complaint regarding 
                    <strong>Order #{{ $complaint->orderItem->order->order_number ?? 'N/A' }}</strong> 
                    is now under official review. We're currently investigating the matter and will update you soon.
                </p>
                <p style="margin:0; color:#2e7d32; font-weight:500;">
                    You can view your complaint details using the link below.
                </p>
                <a href="{{ url('user/complaint/details/' . $complaint->id) }}" 
                   style="display:inline-block; margin:8px 0; padding:8px 16px; background-color:#17a2b8; color:#fff; 
                          text-decoration:none; border-radius:4px; font-size:14px; font-weight:500;">
                   View Complaint Details
                </a>
            @endif
            <p style="margin:10px 0 0 0; color:#856404; font-weight:500;">
                Current Status: <strong style="text-transform:capitalize;">{{ $complaint->status }}</strong>
            </p>
        </div>

        <!-- Main Content -->
        <div style="padding:20px; background:#f8f9fa; border-radius:8px;">
            <div style="display:flex; gap:20px; margin-bottom:20px;">
                
                @if($complaint->orderItem->product->image)
                    <div style="flex:0 0 120px;">
                        <img src="{{ asset('upload/product/small/' . $complaint->orderItem->product->image) }}" 
                             alt="{{ $complaint->orderItem->product->name }}"
                             style="width:100%; height:auto; border-radius:6px; border:1px solid #ddd;">
                    </div>
                @endif

                <div style="flex:1;">
                    <h2 style="color:#00466a; margin:0 0 10px 0; font-size:1.5em;">
                        {{ $complaint->orderItem->product->name ?? 'N/A' }}
                    </h2>

                    @if($complaint->orderItem->product->category)
                        <div style="margin-bottom:8px; color:#666;">
                            <strong>Category:</strong> {{ $complaint->orderItem->product->category->name }}
                        </div>
                    @endif

                    <div style="margin-bottom:8px; color:#28a745; font-weight:bold;">
                        ${{ number_format($complaint->orderItem->product->price ?? 0, 2) }} × {{ $complaint->orderItem->quantity ?? 1 }}
                    </div>

                    @if($complaint->orderItem->variant)
                        <div style="margin-bottom:8px; color:#666;">
                            <strong>Variant:</strong>
                            @if($complaint->orderItem->variant->color)
                                <span style="margin-right:10px;">Color: {{ $complaint->orderItem->variant->color }}</span>
                            @endif
                            @if($complaint->orderItem->variant->size)
                                <span>Size: {{ $complaint->orderItem->variant->size }}</span>
                            @endif
                        </div>
                    @endif

                    <div style="color:#666;">
                        <strong>Sold by:</strong> 
                        {{ $complaint->orderItem->product->client->firstName ?? '' }} {{ $complaint->orderItem->product->client->lastName ?? '' }}
                    </div>

                    <div style="margin-top:10px; font-weight:bold; text-align:right;">
                        Total: ${{ number_format(($complaint->orderItem->product->price ?? 0) * ($complaint->orderItem->quantity ?? 1), 2) }}
                    </div>
                </div>
            </div>

            <!-- Complaint Message -->
            <div style="padding:15px; background:#f1f8ff; border-radius:6px; margin-top:25px; border-left:4px solid #1976d2;">
                <h3 style="color:#00466a; margin-top:0; margin-bottom:10px; font-size:1.1em;">Customer Complaint</h3>
                <div style="margin:0; line-height:1.6; color:#555;">
                    {!! $complaint->message !!}
                </div>
                <div style="margin-top:10px; font-size:0.9em; color:#666;">
                    <strong>Submitted on:</strong> 
                    {{ \Carbon\Carbon::parse($complaint->cmp_date)->format('M d, Y') }} at {{ $complaint->cmp_time }}
                </div>
                <div style="margin-top:8px; font-size:0.9em; color:#666;">
                    <strong>Complaint By:</strong> {{ $complaint->customer->firstName ?? '' }} {{ $complaint->customer->lastName ?? '' }}
                </div>
                <div style="margin-top:5px; font-size:0.9em; color:#666;">
                    <strong>Mobile:</strong> {{ $complaint->customer->mobile ?? 'N/A' }}
                </div>
                <div style="margin-top:5px; font-size:0.9em; color:#666;">
                    <strong>Email:</strong> {{ $complaint->customer->email ?? 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top:30px; padding-top:20px; border-top:1px solid #eee; text-align:center; font-size:0.9em;">
            <p style="color:#666; margin:5px 0;">
                © {{ date('Y') }} SpareX. All rights reserved.
            </p>
            <div style="color:#999; margin-top:10px;">
                <p style="margin:3px 0;">Need help? Contact us at support@sparex.com</p>
            </div>
        </div>
    </div>
</div>
