<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .company-info {
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .invoice-info div {
            flex: 1;
        }
        
        .invoice-info h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #444;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .bill-to, .payment-info {
            flex: 1;
        }
        
        .bill-to h3, .payment-info h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #444;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        table th {
            background-color: #f9f9f9;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #ddd;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        
        .totals table {
            width: 300px;
        }
        
        .note {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .invoice-info, .invoice-details {
                flex-direction: column;
            }
            
            .invoice-info div, .bill-to, .payment-info {
                margin-bottom: 20px;
            }
            
            .totals table {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <h1>Materialize</h1>
            <div class="company-info">
                Office 149, 450 South Brand Brooklyn<br>
                San Diego County, CA 91905, USA<br>
                +1 (123) 456 7891, +44 (876) 543 2198
            </div>
        </div>
        
        <div class="invoice-info">
            <div>
                <h2>INVOICE #{{ $order->invoice_no }}</h2>
                <p>Date Issued: {{ $order->order_date }}</p>
                <p>Date Due: {{ \Carbon\Carbon::parse($order->order_date)->addDays(10)->format('Y-m-d') }}</p>
            </div>
            <div style="text-align: right;">
                <p>Order #: {{ $order->order_number }}</p>
                <p>Status: {{ ucfirst($order->status) }}</p>
                <p>Payment Method: {{ ucfirst($order->payment_method) }}</p>
            </div>
        </div>
        
        <div class="invoice-details">
            <div class="bill-to">
                <h3>Bill To:</h3>
                <p><strong>{{ $order->shippingAddress->name }}</strong></p>
                <p>{{ $order->shippingAddress->address1 }}</p>
                @if($order->shippingAddress->address2)
                <p>{{ $order->shippingAddress->address2 }}</p>
                @endif
                <p>{{ $order->shippingAddress->city->name }}, {{ $order->shippingAddress->county->name }}, {{ $order->shippingAddress->zip_code }}</p>
                <p>{{ $order->shippingAddress->country->name }}</p>
                <p>Phone: {{ $order->shippingAddress->phone }}</p>
                <p>Email: {{ $order->shippingAddress->email }}</p>
            </div>
            <div class="payment-info">
                <h3>Payment Information:</h3>
                <p><strong>Total Due:</strong> ${{ number_format($order->payable_amount, 2) }}</p>
                <p><strong>Currency:</strong> {{ strtoupper($order->currency) }}</p>
                <p><strong>Payment Type:</strong> {{ ucfirst($order->payment_type) }}</p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>DESCRIPTION</th>
                    <th>COLOR</th>
                    <th>SIZE</th>
                    <th>UNIT PRICE</th>
                    <th>QTY</th>
                    <th>PRICE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>Product #{{ $item->product_id }}</td>
                    <td>
                        @if($item->variant)
                            {{ $item->variant->name }}
                        @else
                            Standard
                        @endif
                    </td>
                    <td>{{ $item->color ?? '-' }}</td>
                    <td>{{ $item->size ?? '-' }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="totals">
            <table>
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td>${{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Discount:</strong></td>
                    <td>-${{ number_format($order->coupon_discount, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td>${{ number_format($order->tax, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td>${{ number_format($order->payable_amount, 2) }}</td>
                </tr>
            </table>
        </div>
        
        <div class="note">
            <p><strong>Note:</strong> Thank you for your business. We appreciate your trust in our services. Please don't hesitate to contact us if you have any questions about this invoice.</p>
        </div>
        
        <div class="footer">
            <p>Materialize &copy; {{ date('Y') }} | Office 149, 450 South Brand Brooklyn, San Diego County, CA 91905, USA</p>
        </div>
    </div>
</body>
</html>