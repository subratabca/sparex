<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; width:100%; overflow:auto; line-height:1.6; color: #333; background: #f8f9fa; padding: 20px 0;">
    <div style="margin: 0 auto; width: 90%; max-width: 600px; background: #fff; border-radius: 8px; overflow: hidden;">
        
        <!-- Header -->
        <div style="border-bottom: 1px solid #eee; padding: 20px; text-align: center; background: #f8f9fa;">
            <a href="{{ url('/') }}" style="font-size: 1.6em; color: #00466a; text-decoration: none; font-weight: 700; display: inline-block;">
                {{ config('app.name') }}
            </a>
            <p style="font-size: 1.1em; color: #666; margin-top: 5px;">New Product Submission</p>
        </div>

        <!-- Main Content -->
        <div style="padding: 20px;">
            @if($product->image)
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="{{ asset('upload/product/small/' . $product->image) }}" 
                     alt="{{ $product->name }}"
                     style="max-width: 100%; height: auto; border-radius: 6px; border: 1px solid #ddd; padding: 5px;">
            </div>
            @endif

            <h2 style="color: #00466a; margin: 0 0 15px 0; text-align: center; font-size: 1.4em;">{{ $product->name }}</h2>
            
            <div style="margin-bottom: 20px; background: white; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; font-size: 0.95em;">
                    
                    <div style="flex: 1 1 100%; display: flex; justify-content: space-between;">
                        <span style="color: #666; font-weight: 500;">Uploaded At:</span>
                        <strong>{{ $product->created_at->format('M d, Y H:i') }}</strong>
                    </div>
                    
                    <div style="flex: 1 1 100%; display: flex; justify-content: space-between;">
                        <span style="color: #666; font-weight: 500;">Price:</span>
                        <div>
                            @if($product->price == 0)
                                <span style="display: inline-block; padding: 4px 12px; background: #28a745; color: white; border-radius: 20px; font-size: 0.9em;">
                                    FREE
                                </span>
                            @else
                                <strong style="color: #28a745;">${{ number_format($product->price, 2) }}</strong>
                            @endif
                        </div>
                    </div>

                    <div style="flex: 1 1 100%; display: flex; justify-content: space-between;">
                        <span style="color: #666; font-weight: 500;">Product Owner:</span>
                        <strong>{{ $product->client->firstName }} {{ $product->client->lastName }}</strong>
                    </div>
                </div>

                @if($product->description)
                <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; margin-top: 15px; font-size: 0.95em; color: #555;">
                    <h3 style="color: #00466a; margin-top: 0; margin-bottom: 10px; font-size: 1.1em;">Description</h3>
                    <div style="margin: 0; line-height: 1.6;">
                        {!! ($product->description) !!}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 10px; padding: 15px; border-top: 1px solid #eee; text-align: center; font-size: 0.85em; background: #f8f9fa;">
            <p style="color: #666; margin: 5px 0;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            <div style="color: #999; margin-top: 10px;">
                <p style="margin: 3px 0;">Need help? Contact us at {{ config('mail.from.address') }}</p>
                <p style="margin: 3px 0;">123 Business Street, Suite 456, New York, NY 10001</p>
            </div>
        </div>
    </div>
</div>
