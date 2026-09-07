<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Restaurant;
use Exception;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrinterService
{
    /**
     * Get the appropriate connector based on type and path
     */
    private static function getConnector($type, $path)
    {
        if (empty($path)) {
            throw new Exception("Printer path/IP is not configured. Go to Admin → Settings → Hardware & Printers.");
        }

        // Clean path (strip http://, https://, and trailing slashes if pasted by mistake)
        $cleanPath = preg_replace('/^https?:\/\//i', '', trim($path));
        $cleanPath = rtrim($cleanPath, '/');

        // Auto-correct mismatch if user selects 'network' but provides a Windows share path
        if (str_starts_with(strtolower($cleanPath), 'smb://') || str_starts_with($cleanPath, '\\\\')) {
            $type = 'usb';
        }

        try {
            if ($type === 'network') {
                // Path should be IP address or DDNS domain, optionally with port (e.g. 192.168.1.100:9100 or my-pos.ddns.net)
                $parts = explode(':', $cleanPath);
                $host = $parts[0];
                $port = isset($parts[1]) ? (int)$parts[1] : 9100;

                // Verify host is resolvable to prevent long socket connection hangs
                if (!filter_var($host, FILTER_VALIDATE_IP)) {
                    $resolvedIp = gethostbyname($host);
                    if ($resolvedIp === $host) {
                        throw new Exception("Could not resolve printer domain '{$host}'. Please verify your DDNS hostname is correct and active.");
                    }
                }

                return new NetworkPrintConnector($host, $port, 3); // 3 second timeout
            } else {
                // Windows USB Share (e.g. smb://computer/printer or just "Receipt Printer")
                return new WindowsPrintConnector($cleanPath);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'smbclient') || str_contains($msg, 'not found') || (PHP_OS !== 'WIN' && $type === 'usb')) {
                throw new Exception("Cloud POS Note: Server-side USB printing requires local Windows POS app running at restaurant. For Cloud Web POS, please use the 'Browser Test Receipt' option!");
            }
            if (str_contains($msg, 'timed out') || str_contains($msg, 'Connection refused')) {
                throw new Exception("Cloud POS Note: Local network printer IP ({$cleanPath}) is on local Wi-Fi and unreachable directly from Cloud server. For Cloud POS, please use 'Browser Test Receipt'!");
            }
            throw $e;
        }
    }

    /**
     * Print the Kitchen Order Ticket (No Prices)
     * Returns true on success, false if auto-print is disabled.
     * Throws Exception on print failure so caller can show feedback.
     */
    public static function printKOT(Order $order, $target = 'kitchen', $items = null)
    {
        $restaurant = current_restaurant();
        
        if (!$restaurant) return false;

        $printerType = null;
        $printerPath = null;

        if ($target === 'kitchen') {
            if (!$restaurant->auto_print_kot) {
                return false;
            }

            // Logic for separate vs combined printing (default to unprinted items if new order batch)
            if (!$items) {
                $unprinted = $order->items()->where('is_printed', false)->with('menuItem.category')->get();
                $sourceItems = $unprinted->count() > 0 ? $unprinted : $order->items()->with('menuItem.category')->get();
            } else {
                $sourceItems = $items;
            }

            if ($restaurant->separate_kot_bot) {
                // Determine if we are printing kitchen or bar items
                $kitchenItems = $sourceItems->filter(fn($item) => ($item->menuItem->category->department ?? 'kitchen') === 'kitchen');
                $barItems = $sourceItems->filter(fn($item) => ($item->menuItem->category->department ?? 'kitchen') === 'bar');

                $success = true;
                
                // Print Kitchen items if they exist
                if ($kitchenItems->count() > 0 && !empty($restaurant->kitchen_printer_path)) {
                    try {
                        self::executePrintKOT($order, $restaurant->kitchen_printer_type, $restaurant->kitchen_printer_path, $kitchenItems, "K O T");
                    } catch (\Exception $e) {
                        Log::error("KOT Print Failed: " . $e->getMessage());
                        $success = false;
                    }
                }

                // Print Bar items if they exist
                if ($barItems->count() > 0 && !empty($restaurant->bot_printer_path)) {
                    try {
                        self::executePrintKOT($order, $restaurant->bot_printer_type, $restaurant->bot_printer_path, $barItems, "B O T");
                    } catch (\Exception $e) {
                        Log::error("BOT Print Failed: " . $e->getMessage());
                        $success = false;
                    }
                }

                return $success;
            } else {
                // Combined Printing (Original Logic)
                if (empty($restaurant->kitchen_printer_path)) return false;
                
                $hasBarItems = $sourceItems->contains(fn($item) => ($item->menuItem->category->department ?? 'kitchen') === 'bar');
                $hasKitchenItems = $sourceItems->contains(fn($item) => ($item->menuItem->category->department ?? 'kitchen') === 'kitchen');

                $label = "K O T";
                if ($hasBarItems && $hasKitchenItems) $label = "K O T / B O T";
                else if ($hasBarItems) $label = "B O T";

                return self::executePrintKOT($order, $restaurant->kitchen_printer_type, $restaurant->kitchen_printer_path, $sourceItems, $label);
            }
        } else if ($target === 'cashier') {
            if (empty($restaurant->cashier_printer_path)) {
                return false;
            }
            $sourceItems = $items ?: $order->items()->with('menuItem.category')->get();
            return self::executePrintKOT($order, $restaurant->cashier_printer_type, $restaurant->cashier_printer_path, $sourceItems, "Order Summary");
        }
    }

    /**
     * Internal helper to execute the actual print command
     */
    private static function executePrintKOT(Order $order, $printerType, $printerPath, $itemsToPrint, $label)
    {
        $restaurant = current_restaurant();
        $subLabel = "";
        
        if ($label === "K O T / B O T") $subLabel = "(Kitchen & Bar Order)";
        else if ($label === "B O T") $subLabel = "(Bar Order Ticket)";
        else if ($label === "K O T") $subLabel = "(Kitchen Order Ticket)";

        $connector = self::getConnector($printerType, $printerPath);
        $printer = new Printer($connector);
        
        // Format Ticket
        $printer->initialize();
        
        // Header
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text(($restaurant->name ?? 'Restaurant') . "\n");
        $printer->setTextSize(2, 2);
        $printer->text($label . "\n");
        $printer->setTextSize(1, 1);
        if ($subLabel) {
            $printer->text($subLabel . "\n");
        }
        $printer->setEmphasis(false);
        $printer->text("------------------------------------------------\n");
        
        // Order Info
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Date: " . self::getFormattedDate($order->created_at) . "  Time: " . $order->created_at->format('H:i') . "\n");
        $printer->text("Order #: " . $order->id . "  Token: " . ($order->token_number ?? '-') . "\n");
        
        $tableName = $order->table ? $order->table->name : 'Takeaway';
        $printer->setEmphasis(true);
        if ($order->table && $order->table->type === 'room') {
            $printer->text("ROOM: " . $tableName . "\n");
            if ($order->table->guest_name) {
                $printer->text("Guest: " . $order->table->guest_name . "\n");
            }
        } else {
            $printer->text("Table: " . $tableName . "\n");
        }
        $printer->setEmphasis(false);

        // Staff "By" line
        $staffName = self::getStaffName($order);
        if ($staffName) {
            $printer->text("By: " . $staffName . "\n");
        }

        $printer->text("------------------------------------------------\n");
        
        // Items (No Prices)
        $printer->setEmphasis(true);
        $printer->text(self::columnify("Item", "Qty", 40, 8));
        $printer->setEmphasis(false);
        $printer->text("------------------------------------------------\n");
        
        // Group Items by MenuItem and Special Instructions
        $groupedItems = $itemsToPrint->groupBy(function ($item) {
            return $item->menu_item_id . '_' . ($item->special_instructions ?? '');
        })->map(function ($group) {
            $first = $group->first();
            return [
                'name' => $first->menuItem->name,
                'quantity' => $group->sum('quantity'),
                'special_instructions' => $first->special_instructions,
            ];
        });

        // Items
        foreach ($groupedItems as $item) {
            $name = $item['name'];
            $qty = $item['quantity'] . "x";
            
            $printer->setEmphasis(true);
            $printer->setTextSize(1, 2); // Double height for readability
            $printer->text(self::columnify($name, $qty, 40, 8));
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            
            if ($item['special_instructions']) {
                $printer->text("  * " . $item['special_instructions'] . "\n");
            }
        }
        
        if ($order->special_instructions) {
            $printer->text("------------------------------------------------\n");
            $printer->setEmphasis(true);
            $printer->text("Notes:\n");
            $printer->setEmphasis(false);
            $printer->text($order->special_instructions . "\n");
        }
        
        $printer->text("------------------------------------------------\n");
        
        // Cut and close
        $printer->feed(3);
        $printer->cut();
        $printer->close();

        // Mark items as printed
        foreach ($itemsToPrint as $printedItem) {
            if (isset($printedItem->id)) {
                \App\Models\OrderItem::where('id', $printedItem->id)->update(['is_printed' => true]);
            }
        }
        
        return true;
    }

    /**
     * Print the Hotel Room Bill (Reception)
     */
    public static function printHotelBill(\App\Models\Table $room, $orders = [])
    {
        $restaurant = current_restaurant();
        
        if (!$restaurant || !$restaurant->hotel_auto_print || empty($restaurant->hotel_printer_address)) {
            return false;
        }

        $connector = self::getConnector($restaurant->hotel_printer_type, $restaurant->hotel_printer_address);
        $printer = new Printer($connector);
        
        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text("HOTEL BILL\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text(($restaurant->name ?? 'Hotel') . "\n");
        if ($restaurant->address) {
            $printer->text($restaurant->address . "\n");
        }
        $printer->text("------------------------------------------------\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setEmphasis(true);
        $printer->text("ROOM: " . $room->name . "\n");
        $printer->text("GUEST: " . ($room->guest_name ?? 'N/A') . "\n");
        $printer->setEmphasis(false);
        
        if ($room->check_in_at) {
            $printer->text("Check-in: " . self::getFormattedDate(\Carbon\Carbon::parse($room->check_in_at)) . " " . \Carbon\Carbon::parse($room->check_in_at)->format('H:i') . "\n");
        }
        $printer->text("Date: " . self::getFormattedDate(now()) . " " . now()->format('H:i') . "\n");

        // Staff "By" line — use the authenticated user at checkout time
        $currentUser = auth()->user();
        if ($currentUser) {
            $byName = trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->last_name ?? ''));
            if ($byName) {
                $printer->text("By: " . $byName . "\n");
            }
        }

        $printer->text("------------------------------------------------\n");

        // Room Tariff
        $currency = $restaurant->currency ?? 'Rs.';
        $printer->setEmphasis(true);
        $printer->text("ROOM TARIFF\n");
        $printer->setEmphasis(false);
        $printer->text(self::columnify("Daily Rate:", $currency . " " . number_format($room->room_rate ?? 0, 2), 24, 24));
        $printer->text("------------------------------------------------\n");

        // Service & Orders
        $totalOrders = 0;
        if (count($orders) > 0) {
            $printer->setEmphasis(true);
            $printer->text("ROOM SERVICE ORDERS\n");
            $printer->setEmphasis(false);
            
            foreach ($orders as $order) {
                $printer->text("Order #" . $order->order_number . " (" . $order->created_at->format('d/m') . ")\n");
                foreach ($order->items as $item) {
                    $printer->text(self::columnifyReceipt($item->menuItem->name ?? 'Item', $item->quantity, number_format($item->subtotal, 2), 30, 6, 10));
                }
                $totalOrders += $order->total_amount;
            }
            $printer->text("------------------------------------------------\n");
        }

        // Grand Total
        $grandTotal = ($room->room_rate ?? 0) + $totalOrders;
        $printer->setEmphasis(true);
        $printer->setTextSize(1, 2);
        $printer->text(self::columnify("TOTAL PAYABLE:", $currency . " " . number_format($grandTotal, 2), 24, 24));
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);

        $printer->text("------------------------------------------------\n");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Thank you for staying with us!\n");
        
        $printer->feed(4);
        $printer->cut();
        $printer->close();

        return true;
    }

    /**
     * Print the Cashier Receipt (With Prices)
     * Returns true on success, false if auto-print is disabled.
     * Throws Exception on print failure so caller can show feedback.
     */
    public static function printReceipt(Order $order, $force = false)
    {
        $restaurant = current_restaurant();
        
        if (!$restaurant || (!$force && !$restaurant->auto_print_receipt) || empty($restaurant->cashier_printer_path)) {
            return false; // Auto-print disabled — not an error
        }

        // Let exceptions propagate to the caller for proper feedback
        $connector = self::getConnector($restaurant->cashier_printer_type, $restaurant->cashier_printer_path);
        $printer = new Printer($connector);
        
        // Format Receipt
        $printer->initialize();
        
        // Header — Restaurant/Company Name
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        if (!empty($restaurant->logo)) {
            try {
                $logoPath = storage_path('app/public/' . $restaurant->logo);
                if (file_exists($logoPath)) {
                    $logo = \Mike42\Escpos\EscposImage::load($logoPath);
                    // Print image centered
                    $printer->bitImage($logo);
                    $printer->feed(1);
                }
            } catch (\Exception $e) {
                // Fallback gracefully if image format is unsupported by ESC/POS
                Log::warning("Could not print receipt logo: " . $e->getMessage());
            }
        }

        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text(($restaurant->name ?? 'Restaurant') . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        
        if ($restaurant->address) {
            $printer->text($restaurant->address . "\n");
        }
        if ($restaurant->phone) {
            $printer->text("Tel: " . $restaurant->phone . "\n");
        }
        $printer->text("------------------------------------------------\n");
        
        // Order Info
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Date: " . self::getFormattedDate($order->created_at) . "  Time: " . $order->created_at->format('H:i') . "\n");
        $printer->text("Order #: " . $order->id . "  Token: " . ($order->token_number ?? '-') . "\n");
        
        $tableName = $order->table ? $order->table->name : 'Takeaway';
        if ($order->table && $order->table->type === 'room') {
            $printer->text("ROOM: " . $tableName . "\n");
            if ($order->table->guest_name) {
                $printer->text("Guest: " . $order->table->guest_name . "\n");
            }
        } else {
            $printer->text("Table: " . $tableName . "\n");
        }

        // Staff "By" line
        $staffName = self::getStaffName($order);
        if ($staffName) {
            $printer->text("By: " . $staffName . "\n");
        }

        $printer->text("------------------------------------------------\n");
        
        // Items (With Prices)
        $printer->setEmphasis(true);
        $printer->text(self::columnifyReceipt("Item", "Qty", "Price", 30, 6, 10));
        $printer->setEmphasis(false);
        $printer->text("------------------------------------------------\n");
        
        foreach ($order->items as $item) {
            $itemName = $item->menuItem->name ?? 'Unknown';
            if ($item->variation_name) {
                $itemName .= " (" . $item->variation_name . ")";
            }
            
            // Wrap item name if too long (expanded to 30 characters)
            $nameLines = explode("\n", wordwrap($itemName, 30, "\n"));
            foreach ($nameLines as $i => $line) {
                if ($i === 0) {
                    $printer->setEmphasis(true);
                    $printer->text(self::columnifyReceipt($line, $item->quantity, number_format($item->subtotal, 2), 30, 6, 10));
                    $printer->setEmphasis(false);
                } else {
                    $printer->text(self::columnifyReceipt($line, "", "", 30, 6, 10));
                }
            }
        }
        
        $printer->text("------------------------------------------------\n");
        
        // Totals with tax, service charge, discount (VAT-Inclusive Menu Prices)
        $currency = $restaurant->currency ?? 'Rs.';
        $itemsTotal = $order->items->sum('subtotal');
        if ($itemsTotal <= 0) $itemsTotal = $order->total_amount;
        
        $order->loadMissing('invoice');
        $invoiceDiscount = $order->invoice ? $order->invoice->discount : 0;
        $taxableTotal = max(0, $itemsTotal - $invoiceDiscount);

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
        
        // Subtotal
        $printer->text(self::columnify("Subtotal:", $currency . " " . number_format($itemsTotal, 2), 24, 24));
        
        // Discount
        if ($invoiceDiscount > 0) {
            $printer->setEmphasis(true);
            $printer->text(self::columnify("Discount:", "- " . $currency . " " . number_format($invoiceDiscount, 2), 24, 24));
            $printer->setEmphasis(false);
        }

        // Service Charge
        if ($scPercent > 0) {
            $printer->text(self::columnify("Svc Charge ({$scPercent}%):", $currency . " " . number_format($scAmt, 2), 24, 24));
        }

        // 13% VAT (Included)
        if ($taxPercent > 0) {
            $printer->text(self::columnify("13% VAT (Included):", $currency . " " . number_format($vatAmt, 2), 24, 24));
        }
        
        $printer->text("------------------------------------------------\n");
        
        // Grand Total
        $printer->setEmphasis(true);
        $printer->setTextSize(1, 2);
        $printer->text(self::columnify("TOTAL:", $currency . " " . number_format($grandTotal > 0 ? $grandTotal : 0, 2), 24, 24));
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        
        // Payment info
        if ($order->invoice && $order->invoice->payment_status === 'paid') {
            $printer->text("------------------------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            if ($order->invoice->payment_method === 'split') {
                $printer->text("PAID via SPLIT PAYMENT\n");
                $printer->setEmphasis(false);
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $currency = $restaurant->currency ?? 'Rs.';
                $printer->text(self::columnify("  Cash Paid:", $currency . " " . number_format($order->invoice->cash_amount, 2), 24, 24));
                $provider = $order->invoice->payment_provider ? ' (' . ucfirst($order->invoice->payment_provider) . ')' : '';
                $printer->text(self::columnify("  Online Paid" . $provider . ":", $currency . " " . number_format($order->invoice->online_amount, 2), 24, 24));
            } else {
                $printer->text("PAID via " . ucfirst($order->invoice->payment_provider ?: $order->invoice->payment_method) . "\n");
            }
            $printer->setEmphasis(false);
        }
        
        
        $printer->text("------------------------------------------------\n");
        
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Please collect original bill from the counter :) thank you\n");
        $printer->text("------------------------------------------------\n");
        // Cut and close
        $printer->feed(4);
        $printer->cut();
        $printer->close();
        
        return true;
    }

    /**
     * Print a test ticket to verify printer configuration
     */
    public static function testPrint($type, $path)
    {
        $connector = self::getConnector($type, $path);
        $printer = new Printer($connector);
        
        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text("TEST PRINT\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text("------------------------------------------------\n");
        $printer->text("Drestro POS\n");
        $printer->text("Printer is working correctly!\n");
        $printer->text("------------------------------------------------\n");
        $printer->text("Type: " . ($type === 'network' ? 'Network (WiFi)' : 'USB/Wired') . "\n");
        $printer->text("Path: " . $path . "\n");
        $printer->text("Time: " . self::getFormattedDate(now()) . " " . now()->format('H:i:s') . "\n");
        $printer->text("------------------------------------------------\n");
        
        $printer->feed(3);
        $printer->cut();
        $printer->close();
        
        return true;
    }
    
    /**
     * Start cashier receipt printing in a background OS process to avoid blocking HTTP requests.
     */
    public static function printReceiptAsync(Order $order, $force = false)
    {
        $restaurant = current_restaurant();
        if (!$restaurant || (!$force && !$restaurant->auto_print_receipt) || empty($restaurant->cashier_printer_path)) {
            return false; // Printing disabled/not configured
        }

        try {
            $dir = storage_path('app/print_jobs');
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . 'receipt_' . $order->id . '_' . time() . '_' . uniqid() . '.json';
            
            $payload = [
                'job_type' => 'receipt',
                'order_id' => $order->id,
                'force' => $force,
            ];

            file_put_contents($filePath, json_encode($payload));

            $phpBinary = file_exists(base_path('php/php.exe')) ? base_path('php/php.exe') : 'php';
            $artisanPath = base_path('artisan');

            if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                $cmd = 'start /B "" "' . $phpBinary . '" "' . $artisanPath . '" app:print-job "' . $filePath . '"';
                pclose(popen($cmd, 'r'));
            } else {
                $cmd = '"' . $phpBinary . '" "' . $artisanPath . '" app:print-job "' . $filePath . '" > /dev/null 2>&1 &';
                exec($cmd);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to trigger background print job for receipt, falling back to sync: ' . $e->getMessage());
            // Fallback to synchronous printing
            try {
                return self::printReceipt($order, $force);
            } catch (\Exception $syncEx) {
                Log::error('Sync print receipt fallback failed: ' . $syncEx->getMessage());
                return false;
            }
        }
    }

    /**
     * Start hotel bill printing in a background OS process to avoid blocking HTTP requests.
     */
    public static function printHotelBillAsync(\App\Models\Table $room, $orders = [])
    {
        $restaurant = current_restaurant();
        if (!$restaurant || !$restaurant->hotel_auto_print || empty($restaurant->hotel_printer_address)) {
            return false; // Printing disabled/not configured
        }

        try {
            $dir = storage_path('app/print_jobs');
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . 'hotelbill_' . $room->id . '_' . time() . '_' . uniqid() . '.json';

            // Serialize the room snapshot
            $roomPayload = [
                'name' => $room->name,
                'guest_name' => $room->guest_name,
                'room_rate' => $room->room_rate,
                'check_in_at' => $room->check_in_at,
            ];

            // Serialize orders and items snapshot
            $ordersPayload = [];
            foreach ($orders as $order) {
                $itemsPayload = [];
                foreach ($order->items as $item) {
                    $itemsPayload[] = [
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                        'menu_item' => [
                            'name' => $item->menuItem->name ?? 'Item',
                        ],
                    ];
                }

                $ordersPayload[] = [
                    'order_number' => $order->order_number,
                    'created_at' => $order->created_at->toDateTimeString(),
                    'total_amount' => $order->total_amount,
                    'items' => $itemsPayload,
                ];
            }

            $payload = [
                'job_type' => 'hotel_bill',
                'room' => $roomPayload,
                'orders' => $ordersPayload,
            ];

            file_put_contents($filePath, json_encode($payload));

            $phpBinary = file_exists(base_path('php/php.exe')) ? base_path('php/php.exe') : 'php';
            $artisanPath = base_path('artisan');

            if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                $cmd = 'start /B "" "' . $phpBinary . '" "' . $artisanPath . '" app:print-job "' . $filePath . '"';
                pclose(popen($cmd, 'r'));
            } else {
                $cmd = '"' . $phpBinary . '" "' . $artisanPath . '" app:print-job "' . $filePath . '" > /dev/null 2>&1 &';
                exec($cmd);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to trigger background print job for hotel bill, falling back to sync: ' . $e->getMessage());
            // Fallback to synchronous printing
            try {
                return self::printHotelBill($room, $orders);
            } catch (\Exception $syncEx) {
                Log::error('Sync print hotel bill fallback failed: ' . $syncEx->getMessage());
                return false;
            }
        }
    }

    
    /**
     * Get staff full name from order's user relationship
     */
    private static function getStaffName(Order $order): ?string
    {
        $order->loadMissing('user');
        if ($order->user) {
            $name = trim(($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? ''));
            return $name ?: ($order->user->name ?? null);
        }
        return null;
    }

    /**
     * Helper to format 2 columns
     */
    private static function columnify($leftCol, $rightCol, $leftWidth, $rightWidth)
    {
        $left = str_pad(substr($leftCol, 0, $leftWidth), $leftWidth, " ", STR_PAD_RIGHT);
        $right = str_pad(substr($rightCol, 0, $rightWidth), $rightWidth, " ", STR_PAD_LEFT);
        return "$left$right\n";
    }
    
    /**
     * Helper to format 3 columns for receipt
     */
    private static function columnifyReceipt($leftCol, $midCol, $rightCol, $leftWidth, $midWidth, $rightWidth)
    {
        $left = str_pad(substr($leftCol, 0, $leftWidth), $leftWidth, " ", STR_PAD_RIGHT);
        $mid = str_pad(substr($midCol, 0, $midWidth), $midWidth, " ", STR_PAD_LEFT);
        $right = str_pad(substr($rightCol, 0, $rightWidth), $rightWidth, " ", STR_PAD_LEFT);
        return "$left $mid $right\n";
    }

    /**
     * Helper to dynamically get formatted date according to AD/BS restaurant setting
     */
    private static function getFormattedDate($carbonDate)
    {
        if (!$carbonDate) return '';
        
        $restaurant = current_restaurant();
        $calType = strtoupper($restaurant->date_calendar_type ?? 'AD');
        
        if ($calType === 'BS') {
            $nc = new \App\Helpers\NepaliCalendar();
            $bs = $nc->eng_to_nep($carbonDate->year, $carbonDate->month, $carbonDate->day);
            if ($bs && isset($bs['date'], $bs['month'], $bs['year'])) {
                return sprintf("%02d/%02d/%04d", $bs['date'], $bs['month'], $bs['year']);
            }
        }
        
        return $carbonDate->format('d/m/Y');
    }
}
