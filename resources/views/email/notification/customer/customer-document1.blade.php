<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Document Submission Notification</title>
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
            font-size: 1.4em;
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
            display: flex;
            flex-wrap: wrap;
        }
        
        .detail-label {
            font-weight: 600;
            color: #00466a;
            width: 100px;
        }
        
        .detail-value {
            flex: 1;
            min-width: 200px;
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
        
        .address {
            font-size: 0.8em;
            color: #aaa;
            line-height: 1;
            font-weight: 300;
            margin-top: 5px;
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
            <a href="{{ url('/') }}" class="logo">Your Brand</a>
        </div>
        
        <div class="content">
            <p>Hi,</p>
            
            <p>{{ $customer->firstName }} has submitted required documents and needs approval for item request. Below are the customer details:</p>
            
            <div class="customer-details">
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $customer->firstName }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $customer->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Submission Date:</span>
                    <span class="detail-value">{{ now()->format('F j, Y \a\t g:i a') }}</span>
                </div>
            </div>
            
            <div class="button-container">
                <a href="{{ url('/admin/customer/details/' . $customer->id) }}" class="button">Review Documents</a>
            </div>
            
            <p>Please review the submitted documents and approve the customer if all requirements are met.</p>
            
            <p>Regards,<br>The Your Brand Team</p>
        </div>
        
        <div class="footer">
            <div class="address">
                <p>Your Brand Inc</p>
                <p>1600 Amphitheatre Parkway</p>
                <p>California</p>
            </div>
            <div>&copy; {{ date('Y') }} Your Brand. All rights reserved.</div>
            <div>If you didn't request this email, you can safely ignore it.</div>
        </div>
    </div>
</body>
</html>