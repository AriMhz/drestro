<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 10px;
            width: 80mm;
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
        .text-right { text-align: right; }
        .mb-1 { margin-bottom: 4px; }
        .mt-4 { margin-top: 16px; }
    </style>
</head>
<body onload="window.print();" onafterprint="setTimeout(function(){ try { window.close(); } catch(e){} }, 300);">
    <center class="mb-1">
        <h2 class="font-bold text-xl m-0">{{ $restaurant->name ?? 'Restaurant' }}</h2>
        @if($restaurant && ($restaurant->address || $restaurant->ward || $restaurant->city))
            <div>{{ collect([$restaurant->address, $restaurant->ward, $restaurant->city])->filter()->implode(', ') }}</div>
        @endif
        @if($restaurant && $restaurant->phone)
            <div>Tel: {{ $restaurant->phone }}</div>
        @endif
        @if($restaurant && $restaurant->pan_number)
            <div>PAN: {{ $restaurant->pan_number }}</div>
        @endif
    </center>
    
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

    @php
        $currency = $restaurant->currency ?? 'Rs.';
        $grossItemsTotal = $order->items->sum('subtotal');
        if ($grossItemsTotal <= 0) $grossItemsTotal = $order->total_amount;

        $invoiceDiscount = $order->invoice ? $order->invoice->discount : 0;
        $taxableTotal = max(0, $grossItemsTotal - $invoiceDiscount);

        $scPercent = $restaurant->service_charge_percent ?? 0;
        $scAmt = $taxableTotal * $scPercent / 100;
        $taxableTotalWithSc = $taxableTotal + $scAmt;

        $taxPercent = $restaurant->tax_percent ?? 0;
        if ($taxPercent > 0) {
            $vatAmt = round($taxableTotalWithSc * ($taxPercent / (100 + $taxPercent)), 2);
            $netSubtotal = round($taxableTotalWithSc - $vatAmt, 2);
        } else {
            $vatAmt = 0;
            $netSubtotal = $taxableTotalWithSc;
        }

        $grandTotal = $taxableTotalWithSc;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 50%">Item</th>
                <th style="width: 15%" class="text-center">Qty</th>
                <th style="width: 35%" class="text-right">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <div class="font-bold">{{ $item->menuItem->name ?? 'Unknown' }}</div>
                    @if($item->variation_name)
                        <div style="font-size: 12px;">- {{ $item->variation_name }}</div>
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-t mt-4" style="padding-top: 8px;">
        <div class="flex justify-between mb-1">
            <span>Subtotal</span>
            <span>{{ $currency }} {{ number_format($grossItemsTotal, 2) }}</span>
        </div>
        @if($invoiceDiscount > 0)
        <div class="flex justify-between mb-1 font-bold">
            <span>Discount</span>
            <span>- {{ $currency }} {{ number_format($invoiceDiscount, 2) }}</span>
        </div>
        @endif
        @if($scPercent > 0)
        <div class="flex justify-between mb-1">
            <span>Service Charge ({{ $scPercent }}%)</span>
            <span>{{ $currency }} {{ number_format($scAmt, 2) }}</span>
        </div>
        @endif
        @if($taxPercent > 0)
        <div class="flex justify-between mb-1" style="font-size: 12px; color: #555;">
            <span>13% VAT (Included)</span>
            <span>{{ $currency }} {{ number_format($vatAmt, 2) }}</span>
        </div>
        @endif
        <div class="border-t" style="padding-top: 8px; margin-top: 4px;">
            <div class="flex justify-between font-bold text-lg">
                <span>TOTAL</span>
                <span>{{ $currency }} {{ number_format($grandTotal > 0 ? $grandTotal : 0, 2) }}</span>
            </div>
        </div>
    </div>

    @if($order->invoice && $order->invoice->payment_status === 'paid')
    <div class="text-center mt-4" style="font-size: 12px;">
        <strong>PAID via {{ ucfirst($order->invoice->payment_provider ?: $order->invoice->payment_method) }}</strong>
    </div>
    @endif
</body>
</html>
