<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Customer Registration</title>
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
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 15px;
            margin-bottom: 20px;
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
        
        .customer-details {
            background: #f5f9ff;
            border-left: 4px solid #00466a;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        
        .detail-row {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #00466a;
            display: inline-block;
            width: 80px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            font-size: 14px;
            color: #999999;
            text-align: right;
        }
        
        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
                padding: 15px;
            }
            
            .detail-label {
                display: block;
                width: auto;
                margin-bottom: 5px;
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
            
            <p>A new customer has registered. Here are the customer details:</p>
            
            <div class="customer-details">
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span>{{ $customer->firstName }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span>{{ $customer->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span>{{ now()->format('F j, Y \a\t g:i a') }}</span>
                </div>
            </div>
            
            <p>You can view the customer's profile in your admin dashboard.</p>
            
            <p>Best regards,<br>{{ config('app.name') }}</p>
        </div>
        
        <div class="footer">
            <div>{{ config('app.name') }}</div>
            <div>1600 Amphitheatre Parkway</div>
            <div>California, USA</div>
        </div>
    </div>
</body>
</html>