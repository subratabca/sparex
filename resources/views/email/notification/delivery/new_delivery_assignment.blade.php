<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Delivery Assignment</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .order-badge {
            display: inline-block;
            background: #f0f7ff;
            color: #3498db;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            flex: 1;
        }
        
        .info-value {
            flex: 2;
            text-align: right;
            color: #333;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #fff8e1 0%, #ffeaa7 100%);
            border-left: 4px solid #f39c12;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        
        .cta-button {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            text-decoration: none;
            padding: 18px;
            text-align: center;
            border-radius: 10px;
            font-weight: 700;
            font-size: 18px;
            margin: 30px 0;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
        }
        
        .warning {
            background: #fff5f5;
            border: 1px solid #ffcccc;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            color: #e74c3c;
            font-weight: 600;
            margin: 20px 0;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #666;
            font-size: 14px;
        }
        
        .footer a {
            color: #3498db;
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            .content {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-value {
                text-align: left;
                margin-top: 5px;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🚚 New Delivery Assignment</h1>
            <p>A new delivery opportunity is waiting for you!</p>
        </div>
        
        <div class="content">
            <div class="order-badge">Order #{{ $data['order_tracking'] }}</div>
            
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">Pickup Address:</div>
                    <div class="info-value">{{ $pickupAddress }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Delivery Address:</div>
                    <div class="info-value">{{ $deliveryAddress }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Delivery Charge:</div>
                    <div class="info-value" style="color: #4CAF50; font-weight: 700;">${{ $deliveryCharge }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Meal Date:</div>
                    <div class="info-value">{{ $deliveryDate }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Items:</div>
                    <div class="info-value">{{ $itemsCount }} item(s)</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Meal Type:</div>
                    <div class="info-value">{{ $mealType }}</div>
                </div>
                @if($data['customer_details'])
                <div class="info-row">
                    <div class="info-label">Customer:</div>
                    <div class="info-value">{{ $data['customer_details']['name'] }}</div>
                </div>
                @endif
            </div>
            
            <div class="highlight-box">
                <strong>First come, first served!</strong><br>
                This delivery will be assigned to the first delivery person who accepts it.
            </div>
            
            <a href="{{ $actionUrl }}" class="cta-button">
                ✅ Accept Delivery Assignment
            </a>
            
            <div class="warning">
                ⏰ Accept before: {{ $acceptDeadline }}
            </div>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Meal Delivery Service. All rights reserved.</p>
            <p>This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>