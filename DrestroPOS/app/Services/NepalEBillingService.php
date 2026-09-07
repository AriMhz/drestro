<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Restaurant;
use App\Models\Order;
use App\Helpers\NepaliCalendar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NepalEBillingService
{
    /**
     * Ensure required database columns exist.
     */
    public static function checkSchema()
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', 'nepal_ebilling_enabled')) {
            try {
                \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->boolean('nepal_ebilling_enabled')->default(false);
                    $table->string('nepal_ebilling_api_key')->nullable();
                    $table->string('nepal_ebilling_environment')->default('staging');
                });
            } catch (\Exception $e) {
                Log::error('Self-healing failed for restaurants table: ' . $e->getMessage());
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('invoices') && !\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'nepal_ebilling_synced')) {
            try {
                \Illuminate\Support\Facades\Schema::table('invoices', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->boolean('nepal_ebilling_synced')->default(false);
                    $table->string('nepal_ebilling_invoice_id')->nullable();
                    $table->text('nepal_ebilling_error')->nullable();
                });
            } catch (\Exception $e) {
                Log::error('Self-healing failed for invoices table: ' . $e->getMessage());
            }
        }
    }

    /**
     * Sync a single invoice to Nepal E-Billing API.
     *
     * @param Invoice $invoice
     * @return bool
     */
    public static function syncInvoice(Invoice $invoice)
    {
        self::checkSchema();
        $restaurant = current_restaurant();
        if (!$restaurant) {
            $restaurant = Restaurant::first();
        }

        if (!$restaurant || !$restaurant->nepal_ebilling_enabled) {
            return false;
        }

        $apiKey = $restaurant->nepal_ebilling_api_key;
        if (empty($apiKey)) {
            $invoice->update([
                'nepal_ebilling_synced' => false,
                'nepal_ebilling_error' => 'API Key is missing in settings.'
            ]);
            return false;
        }

        $env = $restaurant->nepal_ebilling_environment ?? 'staging';
        $subdomain = $restaurant->nepal_ebilling_subdomain ?? 'sky';
        $baseUrl = $env === 'production' 
            ? "https://{$subdomain}.nepalebilling.com" 
            : "https://{$subdomain}.staging.nepalebilling.com";

        // Load invoice relations
        $invoice->load(['order', 'order.items', 'order.items.menuItem']);
        $order = $invoice->order;

        if (!$order) {
            $invoice->update([
                'nepal_ebilling_synced' => false,
                'nepal_ebilling_error' => 'Invoice has no associated order.'
            ]);
            return false;
        }

        // Calculate Nepali Fiscal Year
        $createdDate = Carbon::parse($invoice->created_at);
        $fiscalYear = self::calculateNepaliFiscalYear($createdDate);

        // Map items matching ProductEntry schema for SalesInvoiceGeneration
        $itemsPayload = [];
        foreach ($order->items as $item) {
            $name = $item->menuItem->name ?? 'Item';
            if ($item->variation_name) {
                $name .= ' (' . $item->variation_name . ')';
            }
            $itemsPayload[] = [
                'name' => $name,
                'quantity' => (float)$item->quantity,
                'rate' => (float)$item->price,
                'taxable' => ($invoice->tax > 0)
            ];
        }

        // Prepare buyer info
        $buyerName = 'Cash Customer';
        $buyerPan = '';
        $buyerPhone = '';
        
        // If guest_info exists in order (often set for room service / table orders)
        if (!empty($order->guest_info)) {
            // Check if there is a PAN-like number in guest_info or parse it
            if (preg_replace('/[^0-9]/', '', $order->guest_info)) {
                $numOnly = preg_replace('/[^0-9]/', '', $order->guest_info);
                if (strlen($numOnly) >= 9) {
                    $buyerPan = substr($numOnly, 0, 9);
                }
            }
            $buyerName = $order->guest_info;
        }

        // Check if there's table / room booking guest info (from HotelCashier checkout room guest name)
        // Hotel Cashier checkOut creates orders with special instructions: "Stay for X days (Guest Name - Phone - ID)"
        if ($order->special_instructions && str_contains($order->special_instructions, 'Stay for')) {
            // Extract guest name between parentheses: "Stay for X days (Guest Name - Phone)"
            if (preg_match('/\(([^)]+)\)/', $order->special_instructions, $matches)) {
                $guestDetails = explode('-', $matches[1]);
                $buyerName = trim($guestDetails[0]);
                if (isset($guestDetails[1])) {
                    // Try to see if there's phone or pan
                    $possiblePan = preg_replace('/[^0-9]/', '', $guestDetails[1]);
                    if (strlen($possiblePan) == 9) {
                        $buyerPan = $possiblePan;
                    }
                }
            }
        }

        // Determine payment mode matching SalesInvoiceGeneration schema
        $paymentMethod = strtolower($invoice->payment_method);
        $paymentProvider = strtolower($invoice->payment_provider ?? '');

        $paymentMode = 'CA'; // Default to Cash (CA)
        if ($paymentMethod === 'card') {
            $paymentMode = 'PO'; // POS/Card
        } elseif ($paymentMethod === 'online') {
            if ($paymentProvider === 'esewa') {
                $paymentMode = 'ES';
            } elseif ($paymentProvider === 'khalti') {
                $paymentMode = 'KH';
            } elseif ($paymentProvider === 'bank_transfer' || $paymentProvider === 'fonepay') {
                $paymentMode = 'QR';
            } else {
                $paymentMode = 'QR';
            }
        }

        // SalesInvoiceGeneration payload
        $payload = [
            'customer_name' => $buyerName,
            'payment_mode' => $paymentMode,
            'invoice_date' => $createdDate->format('Y-m-d'),
            'products' => $itemsPayload,
            'reference_number' => $invoice->invoice_number,
            'phone_number' => $buyerPhone,
            'address' => $buyerName !== 'Cash Customer' ? $buyerName : '',
            'pan_number' => $buyerPan
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-KEY' => $apiKey,
                    'X-Api-Key' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])->post($baseUrl . '/invoices/sales-invoice-generation/', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $remoteInvoiceId = $data['invoice_id'] ?? ($data['id'] ?? 'remote-' . time());
                $invoice->update([
                    'nepal_ebilling_synced' => true,
                    'nepal_ebilling_invoice_id' => $remoteInvoiceId,
                    'nepal_ebilling_error' => null
                ]);
                Log::info("Nepal E-Billing: Synced invoice {$invoice->invoice_number} successfully. Remote ID: {$remoteInvoiceId}");
                return true;
            } else {
                $errorMsg = 'API Error (' . $response->status() . '): ' . ($response->json('message') ?? $response->body());
                $invoice->update([
                    'nepal_ebilling_synced' => false,
                    'nepal_ebilling_error' => $errorMsg
                ]);
                Log::error("Nepal E-Billing: Sync failed for {$invoice->invoice_number}. Error: {$errorMsg}");
                return false;
            }
        } catch (\Exception $e) {
            $errorMsg = 'Exception: ' . $e->getMessage();
            $invoice->update([
                'nepal_ebilling_synced' => false,
                'nepal_ebilling_error' => $errorMsg
            ]);
            Log::error("Nepal E-Billing Sync Exception for {$invoice->invoice_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate Nepali Fiscal Year from Gregorian Date.
     *
     * @param Carbon $date
     * @return string
     */
    private static function calculateNepaliFiscalYear(Carbon $date)
    {
        try {
            $calendar = new NepaliCalendar();
            $bs = $calendar->eng_to_nep($date->year, $date->month, $date->day);
            if ($bs && isset($bs['year']) && isset($bs['month'])) {
                $nepaliYear = $bs['year'];
                $nepaliMonth = $bs['month'];
                
                // Nepali Fiscal Year starts on Shrawan (4th month)
                if ($nepaliMonth >= 4) {
                    $nextYearShort = sprintf('%02d', ($nepaliYear + 1) % 100);
                    return "{$nepaliYear}/{$nextYearShort}";
                } else {
                    $prevYear = $nepaliYear - 1;
                    $currentYearShort = sprintf('%02d', $nepaliYear % 100);
                    return "{$prevYear}/{$currentYearShort}";
                }
            }
        } catch (\Exception $e) {
            Log::error("Nepal E-Billing: Failed to calculate Nepali fiscal year. Defaulting to Gregorian: " . $e->getMessage());
        }

        // Fallback to Gregorian fiscal year (starting July)
        $year = $date->year;
        if ($date->month >= 7) {
            $nextYearShort = sprintf('%02d', ($year + 1) % 100);
            return "{$year}/{$nextYearShort}";
        } else {
            $prevYear = $year - 1;
            $currentYearShort = sprintf('%02d', $year % 100);
            return "{$prevYear}/{$currentYearShort}";
        }
    }
}
