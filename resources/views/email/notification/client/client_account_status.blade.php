<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Client Account Status Update</title>
    <style type="text/css">
        /* Base Styles */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            width: 100% !important;
        }
        
        /* Main Container */
        .email-container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            padding: 20px 0;
        }
        
        /* Header */
        .header {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .logo {
            font-size: 28px;
            color: #00466a;
            text-decoration: none;
            font-weight: 700;
            display: inline-block;
        }
        
        .title {
            font-size: 18px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Status Alert */
        .status-alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
            border-left: 4px solid;
        }
        
        .status-active {
            background: #e8f5e9;
            border-left-color: #4caf50;
        }
        
        .status-inactive {
            background: #ffebee;
            border-left-color: #f44336;
        }
        
        .status-text {
            margin: 0;
            font-weight: 500;
        }
        
        .active-text {
            color: #2e7d32;
        }
        
        .inactive-text {
            color: #c62828;
        }
        
        .btn {
            display: inline-block;
            margin: 10px 0 2px 0;
            padding: 8px 16px;
            background-color: #17a2b8;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Client Details */
        .client-details {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .client-row {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }
        
        .client-image {
            flex: 0 0 100px;
        }
        
        .client-image img {
            width: 100%;
            height: auto;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        
        .client-info {
            flex: 1;
        }
        
        .client-name {
            color: #00466a;
            margin: 0 0 10px 0;
            font-size: 22px;
        }
        
        .detail-row {
            margin-bottom: 8px;
            color: #666;
        }
        
        .location-section {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ddd;
        }
        
        .location-title {
            color: #00466a;
            margin: 0 0 8px 0;
            font-size: 18px;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 14px;
        }
        
        .copyright {
            color: #666;
            margin: 5px 0;
        }
        
        .support {
            color: #999;
            margin-top: 10px;
        }
        
        /* Responsive Styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100%;
                padding: 10px;
            }
            
            .client-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .client-image {
                flex: 0 0 auto;
                text-align: center;
            }
            
            .client-image img {
                max-width: 120px;
            }
            
            .logo {
                font-size: 24px;
            }
            
            .title {
                font-size: 16px;
            }
            
            .client-name {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <a href="{{ url('/') }}" class="logo">SpareX</a>
            <p class="title">Client Account Status Update</p>
        </div>

        <!-- Status Alert -->
        <div class="status-alert {{ $client->status ? 'status-active' : 'status-inactive' }}">
            <p class="status-text {{ $client->status ? 'active-text' : 'inactive-text' }}">
                Client account has been {{ $client->status ? 'activated' : 'deactivated' }}
            </p>
            <a href="{{ url('/client/document/') }}" class="btn">
               View Account Details
            </a>
        </div>

        <!-- Client Details -->
        <div class="client-details">
            <div class="client-row">
                <div class="client-image">
                    <img src="{{ $client->image ? asset('upload/client-profile/small/' . $client->image) : asset('upload/no_image.jpg') }}" 
                         alt="Client Image">
                </div>
                
                <div class="client-info">
                    <h2 class="client-name">
                        {{ $client->firstName }} {{ $client->lastName ?? '' }}
                    </h2>
                    
                    <div class="detail-row">
                        <strong>Email:</strong> {{ $client->email }}
                    </div>
                    
                    @if($client->mobile)
                    <div class="detail-row">
                        <strong>Phone:</strong> {{ $client->mobile }}
                    </div>
                    @endif
                    
                    <div class="detail-row">
                        <strong>Account Status:</strong> 
                        <span style="color: {{ $client->status ? '#4caf50' : '#f44336' }}; font-weight: 500;">
                            {{ $client->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    @if($client->address1)
                    <div class="detail-row">
                        <strong>Address:</strong> {{ $client->address1 }} {{ $client->address2 ?? '' }}
                    </div>
                    @endif

                    <!-- Location Information -->
                    @if($client->zip_code || $client->city || $client->county || $client->country)
                    <div class="location-section">
                        <h3 class="location-title">Location Details</h3>
                        
                        @if($client->zip_code)
                        <div class="detail-row">
                            <strong>Postal Code:</strong> {{ $client->zip_code }}
                        </div>
                        @endif
                        
                        @if($client->city)
                        <div class="detail-row">
                            <strong>City:</strong> {{ $client->city->name ?? $client->city }}
                        </div>
                        @endif
                        
                        @if($client->county)
                        <div class="detail-row">
                            <strong>County:</strong> {{ $client->county->name ?? $client->county }}
                        </div>
                        @endif
                        
                        @if($client->country)
                        <div class="detail-row">
                            <strong>Country:</strong> {{ $client->country->name ?? $client->country }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="copyright">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            <div class="support">
                <p style="margin: 3px 0;">Need help? Contact us at {{ config('mail.from.address') }}</p>
            </div>
        </div>
    </div>
</body>
</html>