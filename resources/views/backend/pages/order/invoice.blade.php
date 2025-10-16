<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_no }}</title>
    <style>
        :root {
            --primary-color: #7367f0;
            --primary-light: #f5f4ff;
            --secondary-color: #82868b;
            --success-color: #28c76f;
            --warning-color: #ff9f43;
            --border-color: #ebe9f1;
            --text-color: #5e5873;
            --heading-color: #3a3845;
            --bg-color: #f8f8f8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.5;
            font-size: 14px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 2rem;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(34, 41, 47, 0.08);
            border-radius: 12px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        
        .company-info {
            font-size: 0.85rem;
            color: var(--secondary-color);
            line-height: 1.5;
        }
        
        .invoice-title {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .invoice-number {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .invoice-meta {
            text-align: right;
        }
        
        .invoice-meta p {
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            gap: 1.5rem;
        }
        
        .bill-to, .payment-info {
            flex: 1;
            min-width: 0; /* Prevents flex items from overflowing */
            padding: 1rem;
            background-color: #fafafa;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .bill-to h3, .payment-info h3 {
            font-size: 1rem;
            margin-bottom: 0.8rem;
            color: var(--heading-color);
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed var(--border-color);
        }
        
        .bill-to p, .payment-info p {
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.2rem;
            font-size: 0.85rem;
        }
        
        table th {
            background-color: var(--primary-light);
            padding: 0.8rem;
            text-align: left;
            font-weight: 600;
            color: var(--heading-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        table td {
            padding: 0.8rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
        }
        
        .totals table {
            width: 280px;
            background-color: var(--primary-light);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .totals td {
            padding: 0.6rem 0.8rem;
        }
        
        .totals tr:last-child td {
            font-weight: 700;
            color: var(--primary-color);
            border-top: 1px solid var(--border-color);
            background-color: rgba(255,255,255,0.7);
        }
        
        .note {
            padding: 1rem;
            background-color: var(--primary-light);
            border-radius: 8px;
            margin-bottom: 1.2rem;
            border-left: 4px solid var(--primary-color);
            font-size: 0.85rem;
        }
        
        .note p {
            color: var(--heading-color);
        }
        
        .footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.2rem;
            border-top: 1px solid var(--border-color);
            font-size: 0.8rem;
            color: var(--secondary-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #e7f8f0;
            color: var(--success-color);
        }
        
        .paid {
            background-color: #e7f8f0;
            color: var(--success-color);
        }
        
        .pending {
            background-color: #fff4e5;
            color: var(--warning-color);
        }
        
        .product-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            margin-right: 10px;
            border: 1px solid var(--border-color);
        }
        
        .product-cell {
            display: flex;
            align-items: center;
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
                margin-top: 1.2rem;
            }
            
            .bill-to, .payment-info {
                margin-bottom: 1rem;
                width: 100%;
            }
            
            .totals table {
                width: 100%;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <p><strong>Date:</strong> {{ date('M d, Y', strtotime($order->created_at)) }}</p>
                <p><strong>Order #:</strong> {{ $order->order_number }}</p>
                <p><strong>Status:</strong> <span class="status-badge {{ $order->status }}">{{ ucfirst($order->status) }}</span></p>
                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
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
                <p><strong>Phone:</strong> {{ $order->shippingAddress->phone }}</p>
                <p><strong>Email:</strong> {{ $order->shippingAddress->email }}</p>
            </div>
            <div class="payment-info">
                <h3>Payment Information:</h3>
                <p><strong>Total Due:</strong> ${{ number_format($order->payable_amount, 2) }}</p>
                <p><strong>Currency:</strong> {{ strtoupper($order->currency) }}</p>
                <p><strong>Payment Type:</strong> {{ ucfirst($order->payment_type) }}</p>
                <p><strong>Transaction ID:</strong> {{ $order->transaction_id ?? 'N/A' }}</p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th class="text-center">COLOR</th>
                    <th class="text-center">SIZE</th>
                    <th class="text-right">UNIT PRICE</th>
                    <th class="text-center">QTY</th>
                    <th class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>
                        <div class="product-cell">
                            @if($item->product && $item->product->thumbnail)
                                <img src="{{ $item->product->thumbnail }}" class="product-image" alt="Product">
                            @endif
                            <div>
                                @if($item->product)
                                    {{ $item->product->name }}
                                @else
                                    Product #{{ $item->product_id }}
                                @endif
                                @if($item->product && $item->product->sku)
                                    <div style="font-size: 0.75rem; color: var(--secondary-color);">SKU: {{ $item->product->sku }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center">{{ $item->color ?? '-' }}</td>
                    <td class="text-center">{{ $item->size ?? '-' }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
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
                @if($order->coupon_discount > 0)
                <tr>
                    <td><strong>Discount:</strong></td>
                    <td class="text-right">-${{ number_format($order->coupon_discount, 2) }}</td>
                </tr>
                @endif
                @if($order->tax > 0)
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td class="text-right">${{ number_format($order->tax, 2) }}</td>
                </tr>
                @endif
                @if($order->shipping_cost > 0)
                <tr>
                    <td><strong>Shipping:</strong></td>
                    <td class="text-right">${{ number_format($order->shipping_cost, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>Grand Total:</strong></td>
                    <td class="text-right">${{ number_format($order->payable_amount, 2) }}</td>
                </tr>
            </table>
        </div>
        
        <div class="note">
            <p><strong>Note:</strong> Thank you for your business. We appreciate your trust in our services. Please don't hesitate to contact us if you have any questions about this invoice. Payments should be made within 15 days of receipt.</p>
        </div>
        
        <div class="footer">
            <p>Materialize &copy; {{ date('Y') }} | Office 149, 450 South Brand Brooklyn, San Diego County, CA 91905, USA</p>
            <p style="margin-top: 0.5rem;">support@materialize.com | www.materialize.com</p>
        </div>
    </div>
</body>
</html>