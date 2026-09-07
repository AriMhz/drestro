<?php

namespace App\Livewire\Staff;

use App\Models\Order;
use App\Models\Table;
use App\Models\Restaurant;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.app')]
class HotelCashier extends Component
{
    public $rooms = [];
    public $selectedRoomId = null;
    public $selectedRoom = null;
    public $activeOrders = [];
    public $totalBill = 0;
    public $manualDays = 0;
    
    // Payment Modal State
    public $showPaymentModal = false;
    public $discount = 0;
    public $paymentMethod = 'cash';
    public $paymentProvider = '';
    public $splitCashAmount = '';
    public $splitOnlineAmount = '';
    
    // Stats for Dashboard
    public $stats = [
        'total_occupied' => 0,
        'total_rooms' => 0,
        'revenue_today' => 0,
    ];

    // Unified date properties supporting visual calendar picker (presets + custom)
    public $period = 'today'; // today, week, month, custom
    public $customStartDate;
    public $customEndDate;

    public function mount()
    {
        $this->customStartDate = date('Y-m-d');
        $this->customEndDate = date('Y-m-d');
        $this->loadRooms();
        $this->calculateStats();
    }

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->calculateStats();
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

    public function loadRooms()
    {
        $this->rooms = Table::where('type', 'room')
            ->where('room_status', 'occupied')
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function calculateStats()
    {
        $this->stats['total_rooms'] = Table::where('type', 'room')->count();
        $this->stats['total_occupied'] = Table::where('type', 'room')->where('room_status', 'occupied')->count();
        
        // Calculate net revenue for selected period from invoices connected to completed hotel orders
        $this->stats['revenue_today'] = \App\Models\Invoice::whereHas('order', function($q) {
            $q->where('status', 'completed')
              ->whereHas('table', function($q2) { $q2->where('type', 'room'); })
              ->whereBetween('updated_at', [$this->startDate, $this->endDate]);
        })->sum('grand_total');
    }

    public function getTodayCheckoutsProperty()
    {
        return Order::where('status', 'completed')
            ->whereHas('table', function($q) { $q->where('type', 'room'); })
            ->whereBetween('updated_at', [$this->startDate, $this->endDate])
            ->with(['table', 'invoice'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function selectRoom($roomId)
    {
        $this->selectedRoomId = $roomId;
        $this->selectedRoom = Table::find($roomId);
        $this->manualDays = 0; // Reset manual days on selection
        
        if ($this->selectedRoom) {
            $this->activeOrders = Order::where('table_id', $this->selectedRoomId)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->with('items.menuItem')
                ->get();
            
            $this->calculateTotal();
        }
    }

    public function addDay()
    {
        $this->manualDays++;
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if (!$this->selectedRoom) return;

        // Calculate Stay Charges
        $checkIn = Carbon::parse($this->selectedRoom->check_in_at);
        $autoDays = max(1, $checkIn->diffInDays(now()));
        $totalDays = $autoDays + $this->manualDays;
        
        $roomCharges = $totalDays * ($this->selectedRoom->room_rate ?? 0);

        // Calculate Food Charges
        $foodCharges = collect($this->activeOrders)->sum('total_amount');

        $this->totalBill = $roomCharges + $foodCharges;
    }

    public function printInterimBill()
    {
        if (!$this->selectedRoom) return;
        
        try {
            \App\Services\PrinterService::printHotelBillAsync($this->selectedRoom, $this->activeOrders);
            session()->flash('success', 'Interim bill sent to Hotel Printer!');
        } catch (\Exception $e) {
            Log::error('Hotel Print Failed: ' . $e->getMessage());
            session()->flash('error', 'Printer Error: ' . $e->getMessage());
        }
    }

    public function openPaymentModal()
    {
        $this->discount = 0;
        $this->paymentMethod = 'cash';
        $this->paymentProvider = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->splitCashAmount = '';
        $this->splitOnlineAmount = '';
    }

    public function checkOut()
    {
        if (!$this->selectedRoom) return;

        try {
            $restaurant = current_restaurant();
            // 1. Print final bill (catch separately to prevent locking database checkout on printer errors)
            try {
                \App\Services\PrinterService::printHotelBillAsync($this->selectedRoom, $this->activeOrders);
            } catch (\Exception $pe) {
                Log::error('Hotel Checkout Print Failed: ' . $pe->getMessage());
                session()->flash('warning', 'Checkout completed in database, but printing failed: ' . $pe->getMessage());
            }

            // 1. Calculate Room Total
            $checkIn = Carbon::parse($this->selectedRoom->check_in_at);
            $autoDays = max(1, $checkIn->diffInDays(now()));
            $totalDays = $autoDays + $this->manualDays;
            $roomTotal = $totalDays * ($this->selectedRoom->room_rate ?? 0);
            $roomGrandTotal = max(0, $roomTotal - $this->discount);

            // 2. Prepare invoices to calculate grand totals for accurate proportional split distribution
            $invoicesToCreate = [];
            
            // Room stay invoice details
            $invoicesToCreate[] = [
                'type' => 'room',
                'subtotal' => $roomTotal,
                'tax' => 0,
                'discount' => $this->discount,
                'grand_total' => $roomGrandTotal,
            ];

            // Service invoices details
            foreach ($this->activeOrders as $order) {
                $subtotal = $order->total_amount;
                $taxAmt = $subtotal * ($restaurant->tax_percent ?? 0) / 100;
                $scAmt = $subtotal * ($restaurant->service_charge_percent ?? 0) / 100;
                $grandTotal = $subtotal + $taxAmt + $scAmt;
                
                $invoicesToCreate[] = [
                    'type' => 'service',
                    'order' => $order,
                    'subtotal' => $subtotal,
                    'tax' => $taxAmt,
                    'discount' => 0,
                    'grand_total' => $grandTotal,
                ];
            }

            // Sum of all grand totals
            $overallTotal = collect($invoicesToCreate)->sum('grand_total');

            // 3. Proportional split distribution calculations
            $overallCash = 0;
            $overallOnline = 0;
            if ($this->paymentMethod === 'split') {
                $overallCash = (float)$this->splitCashAmount;
                if ($overallCash < 0) $overallCash = 0;
                if ($overallCash > $overallTotal) $overallCash = $overallTotal;
                $overallOnline = max(0, $overallTotal - $overallCash);
            }

            $totalInvoices = count($invoicesToCreate);
            $remainingCash = $overallCash;
            $remainingOnline = $overallOnline;

            foreach ($invoicesToCreate as $index => &$item) {
                $isLast = ($index === $totalInvoices - 1);
                
                if ($this->paymentMethod === 'split') {
                    if ($isLast) {
                        $item['cash_amount'] = $remainingCash;
                        $item['online_amount'] = $remainingOnline;
                    } else {
                        $item['cash_amount'] = ($overallTotal > 0) ? round(($item['grand_total'] / $overallTotal) * $overallCash, 2) : 0;
                        if ($item['cash_amount'] > $remainingCash) {
                            $item['cash_amount'] = $remainingCash;
                        }
                        $item['online_amount'] = max(0, $item['grand_total'] - $item['cash_amount']);
                        if ($item['online_amount'] > $remainingOnline) {
                            $item['online_amount'] = $remainingOnline;
                        }
                        $remainingCash = max(0, $remainingCash - $item['cash_amount']);
                        $remainingOnline = max(0, $remainingOnline - $item['online_amount']);
                    }
                } elseif ($this->paymentMethod === 'cash') {
                    $item['cash_amount'] = $item['grand_total'];
                    $item['online_amount'] = 0;
                } elseif (in_array($this->paymentMethod, ['card', 'online'])) {
                    $item['cash_amount'] = 0;
                    $item['online_amount'] = $item['grand_total'];
                }
            }
            unset($item);

            // 4. Actually generate / complete the orders and invoices in DB
            
            // Create Room Stay order
            $roomOrder = \App\Models\Order::create([
                'order_number' => 'STAY-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'table_id' => $this->selectedRoomId,
                'status' => 'completed',
                'total_amount' => $roomTotal,
                'user_id' => auth()->id(),
                'restaurant_id' => $restaurant->id,
                'special_instructions' => 'Stay for ' . $totalDays . ' days (' . $this->selectedRoom->guest_name . ($this->selectedRoom->guest_phone ? ' - ' . $this->selectedRoom->guest_phone : '') . ($this->selectedRoom->guest_id_number ? ' - ID: ' . $this->selectedRoom->guest_id_number : '') . ')',
            ]);

            // Save room invoice (first element of invoicesToCreate)
            $roomInvoiceData = $invoicesToCreate[0];
            $roomInvoice = \App\Models\Invoice::create([
                'order_id' => $roomOrder->id,
                'invoice_number' => 'INV-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'subtotal' => $roomInvoiceData['subtotal'],
                'tax' => $roomInvoiceData['tax'],
                'discount' => $roomInvoiceData['discount'],
                'grand_total' => $roomInvoiceData['grand_total'],
                'payment_method' => $this->paymentMethod,
                'payment_status' => 'paid',
                'payment_provider' => $this->paymentProvider ?: null,
                'cash_amount' => $roomInvoiceData['cash_amount'],
                'online_amount' => $roomInvoiceData['online_amount'],
            ]);

            // Sync room invoice with Nepal E-Billing if enabled
            if ($restaurant && $restaurant->nepal_ebilling_enabled) {
                try {
                    \App\Services\NepalEBillingService::syncInvoice($roomInvoice);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Nepal E-Billing sync failed for room invoice: ' . $e->getMessage());
                }
            }

            // Complete and create invoices for all service orders
            for ($i = 1; $i < count($invoicesToCreate); $i++) {
                $serviceData = $invoicesToCreate[$i];
                $order = $serviceData['order'];
                $order->update(['status' => 'completed']);

                $serviceInvoice = \App\Models\Invoice::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'invoice_number' => 'INV-' . strtoupper(\Illuminate\Support\Str::random(6)),
                        'subtotal' => $serviceData['subtotal'],
                        'tax' => $serviceData['tax'],
                        'discount' => 0,
                        'grand_total' => $serviceData['grand_total'],
                        'payment_method' => $this->paymentMethod,
                        'payment_status' => 'paid',
                        'payment_provider' => $this->paymentProvider ?: null,
                        'cash_amount' => $serviceData['cash_amount'],
                        'online_amount' => $serviceData['online_amount'],
                    ]
                );

                // Sync service invoice with Nepal E-Billing if enabled
                if ($restaurant && $restaurant->nepal_ebilling_enabled) {
                    try {
                        \App\Services\NepalEBillingService::syncInvoice($serviceInvoice);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Nepal E-Billing sync failed for service invoice: ' . $e->getMessage());
                    }
                }
            }

            // 5. Reset room status
            $this->selectedRoom->update([
                'room_status' => 'available',
                'guest_name' => null,
                'guest_phone' => null,
                'guest_id_number' => null,
                'room_rate' => null,
                'check_in_at' => null,
            ]);

            session()->flash('success', "Room {$this->selectedRoom->name} checked out. Stay revenue and service orders recorded.");
            
            // Auto-backup to USB drive if connected (asynchronously in the background)
            \App\Helpers\BackupHelper::backupToUsbAsync();

            $this->showPaymentModal = false;
            $this->reset(['selectedRoomId', 'selectedRoom', 'activeOrders', 'totalBill', 'discount', 'splitCashAmount', 'splitOnlineAmount']);
            $this->loadRooms();
            $this->calculateStats(); // Refresh dashboard stats

        } catch (\Exception $e) {
            Log::error('Check-out Failed: ' . $e->getMessage());
            session()->flash('error', 'Process failed: ' . $e->getMessage());
        }
    }


    public function render()
    {
        return view('livewire.staff.hotel-cashier', [
            'title' => 'Hotel Reception Cashier'
        ]);
    }
}
