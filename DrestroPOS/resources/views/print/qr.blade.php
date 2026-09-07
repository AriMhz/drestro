<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR - {{ $table->name }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f1f5f9;
        }

        .qr-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 40px;
            width: 400px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .restaurant-name {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px 0;
            letter-spacing: -0.025em;
        }

        .table-name {
            font-size: 40px;
            font-weight: 900;
            color: #e11d48;
            margin: 0 0 24px 0;
            letter-spacing: -0.05em;
        }

        .qr-container {
            width: 280px;
            height: 280px;
            padding: 16px;
            border-radius: 20px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-image {
            width: 100%;
            height: 100%;
            object-contain: fit;
        }

        .instruction-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 4px 0;
        }

        .instruction-desc {
            font-size: 13px;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }

        /* Action Buttons (Hidden on Print) */
        .actions-panel {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 100;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s, opacity 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-print {
            background-color: #0f172a;
            color: #ffffff;
        }

        .btn-close {
            background-color: #ffffff;
            color: #0f172a;
            border: 1px solid #e2e8f0;
        }

        @media print {
            body {
                background-color: #ffffff;
                min-height: 0;
            }
            .qr-card {
                box-shadow: none;
                border: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            .actions-panel {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="qr-card">
        <h2 class="restaurant-name">{{ $restaurant->name ?? 'DRestro Restaurant' }}</h2>
        <h1 class="table-name">{{ $table->name }}</h1>
        
        <div class="qr-container">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data={{ urlencode(get_tenant_menu_url($table->id)) }}" alt="QR Code" class="qr-image" />
        </div>

        <h3 class="instruction-title">Scan & Order</h3>
        <p class="instruction-desc">Scan this QR code using your smartphone camera to view the digital menu and place your order instantly.</p>
    </div>

    <div class="actions-panel">
        <button class="btn btn-close" onclick="window.close();">Close Window</button>
        <button class="btn btn-print" onclick="window.print();">Print QR Code</button>
    </div>

</body>
</html>
