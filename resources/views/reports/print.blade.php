<!DOCTYPE html>
<html>
<head>
    <title>Daily Report - {{ $date }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 14px; }
        h2, h3 { margin-top: 30px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        .totals { margin-top: 20px; font-weight: bold; }
        .totals p { margin: 4px 0; }
    </style>
</head>
<body>
    <h2>Daily Report - {{ $date }}</h2>

    {{-- Totals --}}
    <div class="totals">
        <p>Total Quantity (All): {{ $totalQuantity }}</p>
        <p>Total Value (Valid Stock Only): ₱{{ number_format($validTotalValue, 2) }}</p>
        <p>Expired Quantity: {{ $expiredTotal }}</p>
        <p>Pulled Out Quantity: {{ $pulledOutTotal }}</p>
    </div>

    {{-- Valid Stock --}}
    <h3>Stocked In</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Value</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($validReports as $item)
                <tr>
                    <td>{{ $item->product->productName }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₱{{ number_format($item->quantity * $item->product->price, 2) }}</td>
                    <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Pulled Out --}}
    <h3>Pulled Out Items</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pulledOutReports as $item)
                <tr>
                    <td>{{ $item->product->productName }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ ucfirst($item->reason) }}</td>
                    <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Expired --}}
    <h3>Expired Items</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expiredReports as $item)
                <tr>
                    <td>{{ $item->product->productName }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>window.print();</script>
</body>
</html>
