<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Models\Order;
use App\Models\Restaurant;

class CashierPanel extends Component
{
    public $restaurant;
    public $selectedOrder = null;
    public $showPaymentModal = false;
    public $paymentMethod = 'cash'; // 'cash', 'card', 'online', 'split'
    public $paymentProvider = ''; // 'esewa', 'khalti', 'bank_transfer', etc.
    public $discount = 0;
    public $discountType = 'percent'; // 'percent' or 'amount'
    public $discountValue = '';
    public $splitCashAmount = '';
    public $splitOnlineAmount = '';
    
    // Unified date properties supporting visual calendar picker (presets + custom)
    public $period = 'today'; // today, week, month, custom
    public $customStartDate;
    public $customEndDate;

    // Backward-compatibility properties
    public $summaryPeriod = 'today'; 
    public $summaryDate = ''; 
    
    #[Url]
    public $activeTab = 'restaurant'; // 'restaurant' or 'hotel'
    
    #[Url]
    public $statusFilter = 'active'; // 'active', 'completed', 'cancelled', 'all'

    public function mount()
    {
        $this->restaurant = current_restaurant();
        $this->customStartDate = date('Y-m-d');
        $this->customEndDate = date('Y-m-d');
        $this->summaryDate = date('Y-m-d');
        $this->summaryPeriod = 'today';

        // Self-healing schema check for invoices table to prevent SQL payment errors
        if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'cash_amount')) {
                \Illuminate\Support\Facades\Schema::table('invoices', function ($table) {
                    $table->decimal('cash_amount', 10, 2)->default(0)->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'online_amount')) {
                \Illuminate\Support\Facades\Schema::table('invoices', function ($table) {
                    $table->decimal('online_amount', 10, 2)->default(0)->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'payment_provider')) {
                \Illuminate\Support\Facades\Schema::table('invoices', function ($table) {
                    $table->string('payment_provider')->nullable();
                });
            }
        }
    }

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->summaryPeriod = $period; // Sync for legacy code
    }

    public function getStartDateProperty()
    {
        return match($this->period) {
            'today' => \Carbon\Carbon::today(),
            'week' => \Carbon\Carbon::now()->startOfWeek(),
            'month' => \Carbon\Carbon::now()->startOfMonth(),
            'custom' => \Carbon\Carbon::parse($this->customStartDate)->startOfDay(),
            default => \Carbon\Carbon::today(),
        };
    }

    public function getEndDateProperty()
    {
        return match($this->period) {
            'today' => \Carbon\Carbon::today()->endOfDay(),
            'week' => \Carbon\Carbon::now()->endOfWeek(),
            'month' => \Carbon\Carbon::now()->endOfMonth(),
            'custom' => \Carbon\Carbon::parse($this->customEndDate)->endOfDay(),
            default => \Carbon\Carbon::today()->endOfDay(),
        };
    }

    public function getActiveOrdersProperty()
    {
        if (!$this->restaurant) return collect();

        $query = Order::with(['table', 'items.menuItem', 'invoice'])
            ->where('restaurant_id', $this->restaurant->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        // Status filtering
        if ($this->statusFilter === 'active') {
            $query->whereNotIn('status', ['completed', 'cancelled']);
        } elseif ($this->statusFilter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($this->statusFilter === 'cancelled') {
            $query->where('status', 'cancelled');
        }
        // 'all' — no status filter

        if ($this->activeTab === 'hotel') {
            $query->whereHas('table', function($q) {
                $q->where('type', 'room');
            });
        } else {
            $query->where(function($q) {
                $q->whereHas('table', function($sub) {
                    $sub->where('type', 'table');
                })->orWhereDoesntHave('table');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function selectOrder($orderId)
    {
        $this->selectedOrder = Order::with(['table', 'items.menuItem', 'invoice'])->find($orderId);
    }

    public function closeOrderModal()
    {
        $this->selectedOrder = null;
    }

    public function openPaymentModal()
    {
        $this->showPaymentModal = true;
        if ($this->selectedOrder) {
            $customer = $this->selectedOrder->customer ?? null;
            if ($customer && isset($customer->loyalty_discount) && floatval($customer->loyalty_discount) > 0) {
                $this->discountType = 'percent';
                $this->discountValue = floatval($customer->loyalty_discount);
            } else {
                $this->discountType = 'percent';
                $this->discountValue = '';
                $this->discount = 0;
            }
            $this->recalculateDiscount();
        }
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentMethod = 'cash';
        $this->paymentProvider = '';
        $this->discount = 0;
        $this->discountType = 'percent';
        $this->discountValue = '';
        $this->splitCashAmount = '';
        $this->splitOnlineAmount = '';
    }

    public function recalculateDiscount()
    {
        if (!$this->selectedOrder) {
            $this->discount = 0;
            return;
        }
        $itemsSum = $this->selectedOrder->items()->sum('subtotal');
        $subtotal = $itemsSum > 0 ? $itemsSum : $this->selectedOrder->total_amount;
        $val = floatval($this->discountValue);

        if ($this->discountType === 'percent') {
            $this->discount = round(($subtotal * max(0, min(100, $val))) / 100, 2);
        } else {
            $this->discount = max(0, $val);
        }
    }

    public function updatedDiscountType()
    {
        $this->recalculateDiscount();
    }

    public function updatedDiscountValue()
    {
        $this->recalculateDiscount();
    }

    public function processPayment()
    {
        if ($this->selectedOrder) {
            $this->recalculateDiscount();
            $itemsSum = $this->selectedOrder->items()->sum('subtotal');
            $grossSubtotal = $itemsSum > 0 ? $itemsSum : $this->selectedOrder->total_amount;
            $discountAmt = (float)$this->discount;
            $taxableTotal = max(0, $grossSubtotal - $discountAmt);

            $scPercent = $this->restaurant->service_charge_percent ?? 0;
            $scAmt = $taxableTotal * $scPercent / 100;
            $taxableTotalWithSc = $taxableTotal + $scAmt;

            $taxPercent = $this->restaurant->tax_percent ?? 0;
            if ($taxPercent > 0) {
                // Price is 13% VAT Included
                $taxAmt = round($taxableTotalWithSc * ($taxPercent / (100 + $taxPercent)), 2);
                $netSubtotal = round($taxableTotalWithSc - $taxAmt, 2);
            } else {
                $taxAmt = 0;
                $netSubtotal = $taxableTotalWithSc;
            }

            $grandTotal = $taxableTotalWithSc;
            if ($grandTotal < 0) $grandTotal = 0;

            $cashAmt = 0;
            $onlineAmt = 0;
            if ($this->paymentMethod === 'split') {
                $cashAmt = (float)$this->splitCashAmount;
                if ($cashAmt < 0) $cashAmt = 0;
                if ($cashAmt > $grandTotal) $cashAmt = $grandTotal;
                $onlineAmt = max(0, $grandTotal - $cashAmt);
            } elseif ($this->paymentMethod === 'cash') {
                $cashAmt = $grandTotal;
            } elseif (in_array($this->paymentMethod, ['card', 'online'])) {
                $onlineAmt = $grandTotal;
            }

            $invoice = \App\Models\Invoice::updateOrCreate(
                ['order_id' => $this->selectedOrder->id],
                [
                    'invoice_number' => 'INV-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'subtotal' => $netSubtotal,
                    'tax' => $taxAmt,
                    'discount' => $discountAmt,
                    'grand_total' => $grandTotal,
                    'payment_method' => $this->paymentMethod,
                    'payment_provider' => $this->paymentProvider ?: null,
                    'payment_status' => 'paid',
                    'cash_amount' => $cashAmt,
                    'online_amount' => $onlineAmt,
                ]
            );

            // Sync with Nepal E-Billing if enabled
            if ($this->restaurant && $this->restaurant->nepal_ebilling_enabled) {
                try {
                    \App\Services\NepalEBillingService::syncInvoice($invoice);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Nepal E-Billing sync failed on POS checkout: ' . $e->getMessage());
                }
            }

            $this->closePaymentModal();
            
            // Auto-complete the order and all its items immediately upon successful checkout
            $finalOrderTotal = ($grandTotal > 0) ? $grandTotal : (($grossSubtotal > 0) ? $grossSubtotal : ($this->selectedOrder->total_amount ?: 0));
            $this->selectedOrder->update([
                'status' => 'completed',
                'total_amount' => $finalOrderTotal
            ]);
            $this->selectedOrder->items()->update(['status' => 'completed']);
            
            // Auto-print receipt to the configured cash counter printer if setting is enabled
            try {
                \App\Services\PrinterService::printReceiptAsync($this->selectedOrder);
            } catch (\Exception $pe) {
                \Illuminate\Support\Facades\Log::error('Auto-print receipt failed: ' . $pe->getMessage());
            }
            // Auto-backup to USB drive if connected (asynchronously in the background)
            try {
                \App\Helpers\BackupHelper::backupToUsbAsync();
            } catch (\Exception $be) {
                \Illuminate\Support\Facades\Log::error('USB Backup failed: ' . $be->getMessage());
            }

            $this->selectOrder($this->selectedOrder->id); // Refresh
        }
    }

    public function printThermalReceiptDirect()
    {
        if ($this->selectedOrder) {
            try {
                $printed = \App\Services\PrinterService::printReceiptAsync($this->selectedOrder, true);
                if ($printed) {
                    session()->flash('success', 'Receipt sent to Cashier Thermal Printer!');
                } else {
                    $this->dispatch('trigger-browser-print', url: route('print.receipt', ['id' => $this->selectedOrder->id]));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Thermal Print Failed: ' . $e->getMessage());
                $this->dispatch('trigger-browser-print', url: route('print.receipt', ['id' => $this->selectedOrder->id]));
            }
        }
    }

    public function printBrowserKOT()
    {
        if ($this->selectedOrder) {
            $this->dispatch('trigger-browser-print', url: route('print.kot', ['id' => $this->selectedOrder->id]));
        }
    }

    public function printBrowserBOT()
    {
        if ($this->selectedOrder) {
            $this->dispatch('trigger-browser-print', url: route('print.bot', ['id' => $this->selectedOrder->id]));
        }
    }

    public function printBrowserReceipt()
    {
        if ($this->selectedOrder) {
            $this->dispatch('trigger-browser-print', url: route('print.receipt', ['id' => $this->selectedOrder->id]));
        }
    }

    public function markDelivered()
    {
        if ($this->selectedOrder) {
            $this->selectedOrder->update(['status' => 'completed']);
            $this->selectedOrder = null;
        }
    }

    public function cancelOrder()
    {
        if ($this->selectedOrder && !in_array($this->selectedOrder->status, ['completed', 'cancelled'])) {
            $this->selectedOrder->update(['status' => 'cancelled']);
            $this->selectedOrder = null;
        }
    }

    public function getDailySummaryDataProperty()
    {
        if (!$this->restaurant) return null;

        $query = Order::where('restaurant_id', $this->restaurant->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with(['invoice', 'table']);

        $orders = $query->get();

        $subtotal = 0;
        $tax = 0;
        $discount = 0;
        $grandTotal = 0;
        $cashTotal = 0;
        $cardTotal = 0;
        $onlineTotal = 0;

        foreach ($orders as $order) {
            if ($order->invoice) {
                $subtotal += $order->invoice->subtotal;
                $tax += $order->invoice->tax;
                $discount += $order->invoice->discount;
                $grandTotal += $order->invoice->grand_total;

                $method = strtolower($order->invoice->payment_method);
                if ($method === 'split') {
                    $cashTotal += $order->invoice->cash_amount;
                    $onlineTotal += $order->invoice->online_amount;
                } elseif ($method === 'cash') {
                    $cashTotal += $order->invoice->grand_total;
                } elseif ($method === 'card') {
                    $cardTotal += $order->invoice->grand_total;
                } else {
                    $onlineTotal += $order->invoice->grand_total;
                }
            } else {
                $subtotal += $order->total_amount;
                $grandTotal += $order->total_amount;
                $cashTotal += $order->total_amount; // Default fallback
            }
        }

        return [
            'orders' => $orders,
            'count' => $orders->count(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'grand_total' => $grandTotal,
            'cash_total' => $cashTotal,
            'card_total' => $cardTotal,
            'online_total' => $onlineTotal,
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
        ];
    }

    public function render()
    {
        return view('livewire.staff.cashier-panel')->layout('components.layouts.app', ['title' => 'Cashier Dashboard']);
    }
}
