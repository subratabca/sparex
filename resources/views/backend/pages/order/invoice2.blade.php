<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_no }}</title>
    <style>
        :root {
            --primary-color: #7367f0;
            --secondary-color: #82868b;
            --success-color: #28c76f;
            --border-color: #ebe9f1;
            --text-color: #6e6b7b;
            --heading-color: #5e5873;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f8f8;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 2.2rem;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(34, 41, 47, 0.1);
            border-radius: 10px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .company-info {
            font-size: 0.9rem;
            color: var(--secondary-color);
            line-height: 1.6;
        }
        
        .invoice-title {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .invoice-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .invoice-meta {
            text-align: right;
        }
        
        .invoice-meta p {
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .bill-to, .payment-info {
            flex: 1;
            padding: 1.2rem;
            background-color: #fafafa;
            border-radius: 8px;
        }
        
        .bill-to h3, .payment-info h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--heading-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.5rem;
        }
        
        .bill-to p, .payment-info p {
            margin-bottom: 0.4rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        
        table th {
            background-color: #f9f9f9;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--heading-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2rem;
        }
        
        .totals table {
            width: 300px;
            background-color: #fafafa;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .totals td {
            padding: 0.8rem 1rem;
        }
        
        .totals tr:last-child td {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
            border-bottom: none;
        }
        
        .note {
            padding: 1.2rem;
            background-color: #f5f5ff;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .note p {
            color: var(--heading-color);
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            font-size: 0.85rem;
            color: var(--secondary-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            background-color: #e7f8f0;
            color: var(--success-color);
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
            }
            
            .invoice-title, .invoice-details {
                flex-direction: column;
            }
            
            .invoice-meta {
                text-align: left;
                margin-top: 1.5rem;
            }
            
            .bill-to, .payment-info {
                margin-bottom: 1.5rem;
            }
            
            .totals table {
                width: 100%;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div>
                <div class="logo">Materialize</div>
                <div class="company-info">
                    Office 149, 450 South Brand Brooklyn<br>
                    San Diego County, CA 91905, USA<br>
                    +1 (123) 456 7891, +44 (876) 543 2198
                </div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-number">INVOICE #{{ $order->invoice_no }}</div>
                <p>Order #: {{ $order->order_number }}</p>
                <p>Status: <span class="status-badge">{{ ucfirst($order->status) }}</span></p>
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
                    <th>COLOR</th>
                    <th>SIZE</th>
                    <th class="text-right">UNIT PRICE</th>
                    <th class="text-right">QTY</th>
                    <th class="text-right">PRICE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>
                        @if($item->product)
                            {{ $item->product->name }}
                        @else
                            Product #{{ $item->product_id }}
                        @endif
                    </td>
                    <td>{{ $item->color ?? '-' }}</td>
                    <td>{{ $item->size ?? '-' }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="totals">
            <table>
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Discount:</strong></td>
                    <td class="text-right">-${{ number_format($order->coupon_discount, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td class="text-right">${{ number_format($order->tax, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td class="text-right">${{ number_format($order->payable_amount, 2) }}</td>
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