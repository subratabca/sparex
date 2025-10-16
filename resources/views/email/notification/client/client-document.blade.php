<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Document Submission Notification</title>
    <style type="text/css">
        /* Base styles */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            color: #333333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
        }
        
        .logo {
            color: #00466a;
            font-size: 24px;
            font-weight: 600;
            text-decoration: none;
        }
        
        .content {
            padding: 0 10px;
        }
        
        .client-details {
            background: #f5f9ff;
            border-left: 4px solid #00466a;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        
        .detail-row {
            margin-bottom: 10px;
            display: flex;
            flex-wrap: wrap;
        }
        
        .detail-label {
            font-weight: 600;
            color: #00466a;
            width: 150px;
        }
        
        .detail-value {
            flex: 1;
            min-width: 200px;
        }
        
        .status-verified {
            color: #28a745;
            font-weight: 600;
        }
        
        .status-not-verified {
            color: #dc3545;
            font-weight: 600;
        }
        
        .button-container {
            text-align: center;
            margin: 25px 0;
        }
        
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #00466a;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        
        .button:hover {
            background-color: #00324d;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            font-size: 14px;
            color: #999999;
            text-align: center;
        }
        
        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
                padding: 15px;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .detail-value {
                min-width: 100%;
            }
            
            .button {
                display: block;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ url('/') }}" class="logo">{{ config('app.name') }}</a>
        </div>
        
        <div class="content">
            <p>Hello Admin,</p>
            
            <p>{{ $client->firstName }}{{ isset($client->lastName) ? ' ' . $client->lastName : '' }} has submitted required documents and needs approval for item upload. Below are the client details:</p>
            
            <div class="client-details">
                <div class="detail-row">
                    <span class="detail-label">Client Name:</span>
                    <span class="detail-value">{{ $client->firstName }}{{ isset($client->lastName) ? ' ' . $client->lastName : '' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email Address:</span>
                    <span class="detail-value">{{ $client->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Document Verified:</span>
                    <span class="detail-value">
                        @if($client->status == 0)
                            <span class="status-not-verified">Not Verified</span>
                        @else
                            <span class="status-verified">Verified</span>
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Submission Date:</span>
                    <span class="detail-value">{{ now()->format('F j, Y \a\t g:i a') }}</span>
                </div>
            </div>
            
            <div class="button-container">
                <a href="{{ url('/admin/client/details/' . $client->id) }}" class="button">Review Documents</a>
            </div>
            
            <p>Please review the submitted documents and approve the client if all requirements are met.</p>
            
            <p>Best regards,<br>{{ config('app.name') }}</p>
        </div>
        
        <div class="footer">
            <div>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</div>
            <div>If you didn't request this email, you can safely ignore it.</div>
        </div>
    </div>
</body>
</html>

