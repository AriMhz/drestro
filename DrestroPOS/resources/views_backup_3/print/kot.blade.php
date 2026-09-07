<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT - Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Standard thermal printer font */
            margin: 0;
            padding: 10px;
            width: 80mm; /* Standard thermal printer width */
            color: #000;
            font-size: 14px;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-lg { font-size: 18px; }
        .text-xl { font-size: 24px; }
        .border-b { border-bottom: 1px dashed #000; }
        .border-t { border-top: 1px dashed #000; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .my-2 { margin-top: 8px; margin-bottom: 8px; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 4px 0; }
        th { border-bottom: 1px solid #000; }
        .mb-1 { margin-bottom: 4px; }
        .mt-4 { margin-top: 16px; }
    </style>
</head>
<body onload="window.print();">
    <div class="text-center mb-1">
        <h2 class="font-bold text-xl m-0">KOT</h2>
        <div class="font-bold text-lg m-0">(Kitchen Order Ticket)</div>
    </div>
    
    <div class="border-b border-t py-2 my-2">
        <div class="flex justify-between mb-1">
            <span>Date: {{ \App\Helpers\DateHelper::format($order->created_at) }}</span>
            <span>Time: {{ $order->created_at->format('H:i') }}</span>
        </div>
        <div class="flex justify-between mb-1">
            <span>Order #: <strong>{{ $order->id }}</strong></span>
            <span>Token: <strong>{{ $order->token_number ?? '-' }}</strong></span>
        </div>
        @if($order->table && $order->table->type === 'room')
            <div class="mb-1">Room: <strong>{{ $order->table->name }}</strong></div>
        @else
            <div class="mb-1">Table: <strong>{{ $order->table->name ?? 'Walk-in' }}</strong></div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Qty</th>
                <th style="width: 85%">Item</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td class="font-bold text-lg">{{ $item->quantity }}x</td>
                <td>
                    <div class="font-bold text-lg">{{ $item->menuItem->name ?? 'Unknown' }}</div>
                    @if($item->variation_name)
                        <div style="font-size: 12px; margin-left: 5px;">- {{ $item->variation_name }}</div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($order->special_instructions)
    <div class="mt-4 border-t pt-2">
        <strong>Notes:</strong><br>
        {{ $order->special_instructions }}
    </div>
    @endif
</body>
</html>
