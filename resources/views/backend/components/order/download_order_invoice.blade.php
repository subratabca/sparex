<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .invoice-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-address {
            line-height: 1.6;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #3b3f5c;
        }
        .invoice-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .table-invoice {
            width: 100%;
        }
        .table-invoice th {
            background: #f8f9fa;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        .total-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .bank-details {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .status-badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 20px;
        }
        @media print {
            body {
                background: none;
            }
            .invoice-container {
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header row">
            <div class="col-md-6">
                <div class="company-address">
                    <h2 class="fw-bold">Your Company Name</h2>
                    <p>Office Address Line 1<br>
                    City, State, ZIP Code<br>
                    Phone: +1 (123) 456-7890</p>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <h1 class="invoice-title">INVOICE</h1>
                <div class="invoice-details d-inline-block text-start mt-3">
                    <p class="mb-1"><strong>Invoice #:</strong> {{ $order->invoice_no }}</p>
                    <p class="mb-1"><strong>Order #:</strong> {{ $order->order_number }}</p>
                    <p class="mb-1"><strong>Date Issued:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('F d, Y') }}</p>
                    <p class="mb-0">
                        <strong>Status:</strong> 
                        <span class="status-badge badge bg-{{ $order->status == 'canceled' ? 'danger' : ($order->status == 'completed' ? 'success' : 'warning') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Invoice To:</h5>
                        <p class="mb-1"><strong>{{ $order->customer->firstName }} {{ $order->customer->lastName }}</strong></p>
                        <p class="mb-1">{{ $order->shippingAddress->address1 }}</p>
                        <p class="mb-1">{{ $order->shippingAddress->city->name ?? '' }}, {{ $order->shippingAddress->county->name ?? '' }}, {{ $order->shippingAddress->zip_code }}</p>
                        <p class="mb-1">Phone: {{ $order->shippingAddress->phone }}</p>
                        <p class="mb-0">Email: {{ $order->shippingAddress->email }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Details:</h5>
                        <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                        <p class="mb-1"><strong>Currency:</strong> {{ strtoupper($order->currency) }}</p>
                        <p class="mb-1"><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('F d, Y H:i') }}</p>
                        @if($order->approve_date)
                        <p class="mb-0"><strong>Approved On:</strong> {{ \Carbon\Carbon::parse($order->approve_date)->format('F d, Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-invoice">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Variant</th>
                        <th>Price</th>
                        <th>QTY</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->orderItems as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            @if($item->product->brand)
                            <br><small>Brand: {{ $item->product->brand->name }}</small>
                            @endif
                        </td>
                        <td>
                            @if($item->variant)
                                Size: {{ $item->variant->size }}<br>
                                Color: {{ $item->variant->color }}
                            @endif
                        </td>
                        <td>${{ number_format($item->product->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format($item->product->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="total-section">
                    <div class="row mb-2">
                        <div class="col-6"><strong>Subtotal:</strong></div>
                        <div class="col-6 text-end">${{ number_format($order->subtotal, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Tax:</strong></div>
                        <div class="col-6 text-end">${{ number_format($order->tax, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Discount:</strong></div>
                        <div class="col-6 text-end">${{ number_format($order->coupon_discount, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Shipping:</strong></div>
                        <div class="col-6 text-end">$0.00</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Total Paid:</strong></div>
                        <div class="col-6 text-end fw-bold">${{ number_format($order->paid_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bank-details">
            <h5>Payment Details:</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                    <p class="mb-1"><strong>Payment Status:</strong> Paid</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Amount Paid:</strong> ${{ number_format($order->paid_amount, 2) }}</p>
                    <p class="mb-0"><strong>Payment Date:</strong> {{ \Carbon\Carbon::parse($order->approve_date)->format('F d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="bi bi-printer"></i> Print Invoice
            </button>
            <a href="{{ route('admin.invoice.download', $order) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Download PDF
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>