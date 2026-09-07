<?php

namespace App\Livewire\Staff;

use App\Models\Order;
use App\Models\Table;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class WaiterDashboard extends Component
{
    public $tables = [];
    public $readyOrderIds = [];
    public $pendingQrOrders = [];
    public $knownPendingQrIds = [];

    public function mount()
    {
        $this->loadTables();
    }

    public function loadTables()
    {
        // 1. Get pending QR table orders that need waiter approval
        $pending = Order::with(['table', 'items.menuItem'])
            ->where('status', 'pending_approval')
            ->latest()
            ->get();

        $currentPendingIds = $pending->pluck('id')->toArray();
        $newPendingIds = array_diff($currentPendingIds, $this->knownPendingQrIds);
        if (!empty($newPendingIds)) {
            $this->dispatch('play-qr-alert');
        }
        $this->knownPendingQrIds = $currentPendingIds;
        $this->pendingQrOrders = $pending;

        // 2. Get restaurant tables
        $tables = Table::where('type', 'table')->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        // Check if table has an active order (not completed or cancelled)
        foreach ($tables as $table) {
            $activeOrder = Order::where('table_id', $table->id)
                ->whereNotIn('status', ['completed', 'cancelled', 'pending_approval'])
                ->latest()
                ->first();

            $hasPendingQr = $pending->where('table_id', $table->id)->isNotEmpty();

            $table->status = $activeOrder ? 'occupied' : ($hasPendingQr ? 'pending_qr' : 'empty');
            $table->active_order = $activeOrder;
            $table->pending_qr_order = $pending->firstWhere('table_id', $table->id);
        }

        $this->tables = $tables;

        // Check for orders marked as 'ready' to notify waiter
        $currentReadyIds = Order::where('status', 'ready')
            ->where('updated_at', '>=', Carbon::now()->subMinutes(10))
            ->pluck('id')
            ->toArray();

        // If we find new ready IDs that weren't there in the last poll
        $newReadyIds = array_diff($currentReadyIds, $this->readyOrderIds);
        
        if (!empty($newReadyIds)) {
            $this->dispatch('play-ready-sound');
        }

        $this->readyOrderIds = $currentReadyIds;
    }

    public function approveQrOrder($orderId)
    {
        $order = Order::with(['table', 'items.menuItem'])->find($orderId);
        if ($order) {
            $order->update(['status' => 'in_kitchen']);
            $order->items()->where('is_printed', false)->update(['is_printed' => true]);
            
            if ($order->table) {
                $order->table->update(['status' => 'occupied']);
            }
            
            try {
                \App\Services\PrinterService::printOrder($order);
            } catch (\Exception $e) {}
            
            $this->dispatch('trigger-browser-print', url: route('print.kot', ['id' => $order->id, 'all' => 1]));
            
            $tableName = $order->table ? $order->table->name : 'Walk-in';
            session()->flash('success', "✅ QR Order #{$order->order_number} for Table {$tableName} confirmed and sent to kitchen!");
            $this->loadTables();
        }
    }

    public function rejectQrOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->update(['status' => 'cancelled']);
            $order->items()->update(['status' => 'cancelled']);
            session()->flash('success', "Order #{$order->order_number} was rejected/cancelled.");
            $this->loadTables();
        }
    }

    public function clearTable($tableId)
    {
        // Mark the active order on this table as served
        $activeOrders = Order::where('table_id', $tableId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        foreach ($activeOrders as $order) {
            // Update all ready items of this order to served
            $order->items()->where('status', 'ready')->update(['status' => 'served']);
            
            // If all items of the order are now served/completed, mark order as served
            $hasUnserved = $order->items()->whereNotIn('status', ['served', 'completed', 'cancelled'])->exists();
            if (!$hasUnserved) {
                $order->update(['status' => 'served']);
            }
        }

        $this->loadTables();
        session()->flash('success', 'Order marked as served! Table remains active for cashier billing.');
    }

    public function cancelOrder($tableId)
    {
        $activeOrders = Order::where('table_id', $tableId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        foreach ($activeOrders as $order) {
            $order->update(['status' => 'cancelled']);
            $order->items()->update(['status' => 'cancelled']);
        }

        $this->loadTables();
        session()->flash('success', 'Order cancelled successfully. Table is now free.');
    }

    public function completeTable($tableId)
    {
        $activeOrders = Order::where('table_id', $tableId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        foreach ($activeOrders as $order) {
            $order->update(['status' => 'completed']);
            $order->loadMissing('invoice');
            if (!$order->invoice) {
                \App\Models\Invoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'subtotal' => $order->total_amount,
                    'discount' => 0,
                    'tax' => 0,
                    'grand_total' => $order->total_amount,
                    'payment_method' => 'CASH',
                    'payment_status' => 'paid',
                ]);
            }
        }

        $this->loadTables();
        session()->flash('success', 'Order completed and table freed up successfully!');
    }

    public function render()
    {
        return view('livewire.staff.waiter-dashboard');
    }
}
