<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsManager extends Component
{
    public $dateRange = 'today'; // today, week, month, year, custom
    public $customDate;
    public $customStartDate;
    public $customEndDate;

    public function mount()
    {
        $this->customDate = date('Y-m-d');
        $this->customStartDate = date('Y-m-d', strtotime('-6 days'));
        $this->customEndDate = date('Y-m-d');
    }

    public function setPeriod($period)
    {
        $this->dateRange = $period;
    }

    public function getStartDateProperty()
    {
        return match($this->dateRange) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            'custom' => Carbon::parse($this->customStartDate)->startOfDay(),
            default => Carbon::today(),
        };
    }

    public function getEndDateProperty()
    {
        return match($this->dateRange) {
            'today' => Carbon::today()->endOfDay(),
            'week' => Carbon::now()->endOfWeek(),
            'month' => Carbon::now()->endOfMonth(),
            'year' => Carbon::now()->endOfYear(),
            'custom' => Carbon::parse($this->customEndDate)->endOfDay(),
            default => Carbon::today()->endOfDay(),
        };
    }

    public function getStatsProperty()
    {
        $query = Order::whereNotIn('status', ['cancelled']);
        $invoiceQuery = Invoice::where('payment_status', 'paid');

        $start = $this->startDate;
        $end = $this->endDate;

        $query->whereBetween('created_at', [$start, $end]);
        $invoiceQuery->whereBetween('created_at', [$start, $end]);

        $totalRevenue = $invoiceQuery->sum('grand_total');
        $totalOrders = $query->count();
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            'revenue' => $totalRevenue,
            'orders' => $totalOrders,
            'aov' => $averageOrderValue,
        ];
    }

    public function getTopItemsProperty()
    {
        $start = $this->startDate;
        $end = $this->endDate;

        $items = OrderItem::with('menuItem')
            ->whereHas('order', function ($query) use ($start, $end) {
                $query->whereNotIn('status', ['cancelled'])
                      ->whereBetween('created_at', [$start, $end]);
            })
            ->select('menu_item_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('menu_item_id')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        // Include Hotel Room Stays as a top item
        $hotelStays = Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$start, $end])
            ->where('order_number', 'LIKE', 'STAY-%')
            ->get();

        if ($hotelStays->count() > 0) {
            $stayRevenue = $hotelStays->sum('total_amount');
            
            // Create a pseudo-item for the view
            $stayItem = new \Illuminate\Support\Fluent([
                'menuItem' => new \Illuminate\Support\Fluent(['name' => 'Hotel Room Stay']),
                'total_quantity' => $hotelStays->count(),
                'total_revenue' => $stayRevenue
            ]);

            // Add to items and re-sort
            $items->push($stayItem);
            $items = $items->sortByDesc('total_revenue')->take(5)->values();
        }

        return $items;
    }

    public function getPaymentMethodsProperty()
    {
        $start = $this->startDate;
        $end = $this->endDate;

        return Invoice::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', 'payment_provider', DB::raw('SUM(grand_total) as total'))
            ->groupBy('payment_method', 'payment_provider')
            ->get();
    }

    public function getStaffPerformanceProperty()
    {
        $start = $this->startDate;
        $end = $this->endDate;

        return Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$start, $end])
            ->select('user_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function getSalesChartDataProperty()
    {
        $start = $this->startDate;
        $end = $this->endDate;
        $diffInDays = Carbon::parse($start)->diffInDays(Carbon::parse($end));

        if ($diffInDays === 0) {
            // Hourly breakdown for a single day
            $date = Carbon::parse($start);
            $labels = [];
            $data = [];
            for ($h = 6; $h <= 23; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $data[] = (float) Order::where('status', 'completed')
                    ->whereDate('created_at', $date->toDateString())
                    ->whereRaw("strftime('%H', created_at) = ?", [sprintf('%02d', $h)])
                    ->sum('total_amount');
            }
            return ['labels' => $labels, 'data' => $data];
        }

        // Daily breakdown for multiple days
        $labels = [];
        $data = [];
        for ($i = $diffInDays; $i >= 0; $i--) {
            $date = Carbon::parse($end)->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = (float) Order::where('status', 'completed')
                ->whereDate('created_at', $date->toDateString())
                ->sum('total_amount');
        }
        return ['labels' => $labels, 'data' => $data];
    }

    public function getEbillingInvoicesProperty()
    {
        $start = $this->startDate;
        $end = $this->endDate;

        return Invoice::whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->take(100)
            ->get();
    }

    public function syncSingleInvoice($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        if ($invoice) {
            $success = \App\Services\NepalEBillingService::syncInvoice($invoice);
            if ($success) {
                session()->flash('ebilling_success', 'Invoice ' . $invoice->invoice_number . ' successfully synced.');
            } else {
                session()->flash('ebilling_error', 'Sync failed for ' . $invoice->invoice_number . ': ' . $invoice->nepal_ebilling_error);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.reports-manager', [
            'stats' => $this->stats,
            'topItems' => $this->topItems,
            'paymentMethods' => $this->paymentMethods
        ])->layout('components.layouts.app', ['title' => 'Reports & Analytics']);
    }
}
