<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Banned Customer Notification</title>
    <style>
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 10px !important;
            }
            .content-box {
                flex-direction: column !important;
            }
            .customer-image {
                margin-bottom: 15px !important;
                text-align: center !important;
            }
            .action-button {
                display: block !important;
                width: 100% !important;
                margin-bottom: 10px !important;
            }
        }
    </style>
</head>
<body style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; margin: 0; padding: 0; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 30px auto; padding: 20px;">
        <!-- Header -->
        <div style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 30px; text-align: center;">
            <a href="{{ url('/') }}" style="font-size: 1.8em; color: #00466a; text-decoration: none; font-weight: 700;">
                SpareX
            </a>
            <p style="font-size: 1.2em; color: #666; margin-top: 5px;">
                @if($action === 'banned')
                    Customer Restriction Notification
                @else
                    Customer Access Restored Notification
                @endif
            </p>
        </div>

        <!-- Complaint Alert -->
        <div style="background: @if($action === 'banned') #fff8e1; border-left: 4px solid #ffc107; @else #e8f5e9; border-left: 4px solid #4caf50; @endif padding: 15px; margin-bottom: 25px; border-radius: 4px;">
            <p style="margin: 0; color: @if($action === 'banned') #2e7d32; @else #1b5e20; @endif font-weight: 500;">
                @if($notifiable->role === 'admin')
                    <strong>{{ $bannedCustomer->client->firstName }} {{ $bannedCustomer->client->lastName }}</strong> 
                    @if($action === 'banned')
                        has restricted a customer's access.
                    @else
                        has restored a customer's access.
                    @endif
                @else
                    @if($action === 'banned')
                        Your account has been restricted by <strong>{{ $bannedCustomer->client->firstName }} {{ $bannedCustomer->client->lastName }}</strong>.
                        You will not be able to purchase products from this seller until further notice.
                    @else
                        Your account access has been restored by <strong>{{ $bannedCustomer->client->firstName }} {{ $bannedCustomer->client->lastName }}</strong>.
                        You can now purchase products from this seller again.
                    @endif
                @endif
            </p>
            <p style="margin: 10px 0 0 0; color: @if($action === 'banned') #856404; @else #2e7d32; @endif font-weight: 500;">
                Please review the details below.
            </p>
        </div>

        <!-- Main Content -->
        <div style="padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: flex; gap: 20px; margin-bottom: 20px; align-items: center;" class="content-box">
                <!-- Customer Image -->
                <div style="flex: 0 0 100px;" class="customer-image">
                    <img src="{{ $bannedCustomer->customer->image ? asset('upload/customer-profile/small/' . $bannedCustomer->customer->image) : asset('upload/no_image.jpg') }}" 
                         alt="{{ $bannedCustomer->customer->firstName }}"
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
                </div>
                
                <!-- Customer Details -->
                <div style="flex: 1;">
                    <h2 style="color: #00466a; margin: 0 0 10px 0; font-size: 1.5em;">
                        {{ $bannedCustomer->customer->firstName }} {{ $bannedCustomer->customer->lastName }}
                    </h2>
                    
                    <div style="margin-bottom: 8px; color: #666;">
                        <strong>Email:</strong> {{ $bannedCustomer->customer->email }}
                    </div>
                    
                    <div style="margin-bottom: 8px; color: #666;">
                        <strong>Phone:</strong> {{ $bannedCustomer->customer->mobile ?? 'N/A' }}
                    </div>
                    
                    <div style="margin-bottom: 8px; color: #666;">
                        <strong>Location:</strong> 
                        {{ $bannedCustomer->customer->city->name ?? '' }}, 
                        {{ $bannedCustomer->customer->county->name ?? '' }}, 
                        {{ $bannedCustomer->customer->country->name ?? '' }}
                    </div>
                    
                    <div style="margin-top: 10px; color: #666;">
                        <strong>Banned On:</strong> {{ \Carbon\Carbon::parse($bannedCustomer->created_at)->format('M d, Y \a\t h:i A') }}
                    </div>
                </div>
            </div>

            @if($action === 'banned')
            <!-- Complaint Message -->
            <div style="padding: 15px; background: #f1f8ff; border-radius: 6px; margin-top: 25px; border-left: 4px solid #1976d2;">
                <h3 style="color: #00466a; margin-top: 0; margin-bottom: 10px; font-size: 1.1em;">Restriction Reason</h3>
                <div style="margin: 0; line-height: 1.6; color: #555;">
                    {!! ($bannedCustomer->message) !!}
                </div>
            </div>
            @endif

            <!-- Client Info -->
            <div style="padding: 15px; background: #f5f5f5; border-radius: 6px; margin-top: 20px;">
                <h3 style="color: #00466a; margin-top: 0; margin-bottom: 10px; font-size: 1.1em;">
                    Banned By
                </h3>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <img src="{{ $bannedCustomer->client->image ? asset('upload/client-profile/small/' . $bannedCustomer->client->image) : asset('upload/no_image.jpg') }}"
                         alt="{{ $bannedCustomer->client->firstName }}"
                         style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
                    <div>
                        <div style="font-weight: bold; color: #333;">
                            {{ $bannedCustomer->client->firstName }} {{ $bannedCustomer->client->lastName }}
                        </div>
                        <div style="color: #666; margin-top: 3px;">
                            {{ $bannedCustomer->client->email }}
                        </div>
                        <div style="color: #666; margin-top: 3px;">
                            {{ $bannedCustomer->client->mobile ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            @if($notifiable->role === 'admin')
            <div style="margin-top: 20px; text-align: center;">
                <a href="{{ url('/admin/banned-customers/' . $bannedCustomer->id) }}" 
                   style="display: inline-block; padding: 10px 20px; background-color: #17a2b8; color: #fff; 
                          text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;"
                   class="action-button">
                   View Ban Details
                </a>
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
</body>
</html>
