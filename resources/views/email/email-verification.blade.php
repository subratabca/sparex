<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification</title>
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
        
        .button {
            display: inline-block;
            margin: 25px 0;
            padding: 12px 24px;
            background: #EE9E23;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            text-align: center;
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
            }
            
            .button {
                display: block;
                width: 100%;
                box-sizing: border-box;
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
            <p>Hi {{ $user->firstName }},</p>
            
            <p>Thank you for registering as a {{ $userType }} with us. To complete your registration, please verify your email address by clicking the button below:</p>
            
            <a href="{{ $userType === 'client' ? route('verify.new.client', ['email' => $user->email]) : route('verify.new.customer', ['email' => $user->email]) }}" class="button">
                Verify Email Address
            </a>
            
            <p>If you didn't create an account with us, please ignore this email.</p>
            
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