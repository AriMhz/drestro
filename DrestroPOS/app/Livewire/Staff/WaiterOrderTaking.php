<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\Table;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WaiterOrderTaking extends Component
{
    public $restaurant;
    public $tables;
    public $selectedTableId = '';
    public $tableSelectionMode = true;
    public $categories;
    public $items;
    public $activeCategoryId = null;
    public $searchQuery = '';
    public $guestInfo = '';
    public $activeTab = 'restaurant'; // Locked to restaurant for this component

    // Cart State
    public $cart = [];
    public $orderNotes = '';

    // Order State
    public $currentOrder = null;

    // Print Status
    public $printStatus = ''; // 'success', 'partial', 'failed', 'disabled'
    public $printError = '';

    // Error State
    public $orderError = '';

    // Track which tables are occupied and pending QR orders
    public $occupiedTableIds = [];
    public $pendingQrOrders = [];
    public $knownPendingQrIds = [];

    public function mount($table = null)
    {
        $this->restaurant = current_restaurant();

        if ($this->restaurant) {
            $this->categories = MenuCategory::where('restaurant_id', $this->restaurant->id)->where('is_active', true)->get();
            $this->items = MenuItem::where('restaurant_id', $this->restaurant->id)->where('is_available', true)->get();
        } else {
            $this->categories = collect();
            $this->items = collect();
        }

        $this->loadOccupiedTables();

        if ($table) {
            $this->selectTable($table);
        }
    }

    public function getFilteredTablesProperty()
    {
        if (!$this->restaurant) return collect();

        return Table::where('restaurant_id', $this->restaurant->id)
            ->where('type', $this->activeTab === 'hotel' ? 'room' : 'table')
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function loadOccupiedTables()
    {
        $pending = Order::with(['table', 'items.menuItem'])
            ->where('restaurant_id', $this->restaurant->id ?? 0)
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

        // Get all table IDs that have active (non-completed, non-cancelled) orders
        $this->occupiedTableIds = Order::where('restaurant_id', $this->restaurant->id ?? 0)
            ->whereNotIn('status', ['completed', 'cancelled', 'pending_approval'])
            ->whereNotNull('table_id')
            ->pluck('table_id')
            ->unique()
            ->toArray();
    }

    public function filterByCategory($categoryId)
    {
        $this->activeCategoryId = $categoryId;
    }

    public function selectTable($id)
    {
        $this->selectedTableId = $id;
        $this->tableSelectionMode = false;
        $this->orderError = '';
    }

    // Cart Methods
    public function addToCart($itemId)
    {
        $item = $this->items->firstWhere('id', $itemId);
        if (!$item) return;

        $existingKey = collect($this->cart)->search(fn($i) => $i['id'] == $itemId);
        $qtyToOrder = 1;
        if ($existingKey !== false) {
            $qtyToOrder = $this->cart[$existingKey]['quantity'] + 1;
        }

        // Check stock availability
        if ($item->inventory_item_id && $item->auto_deduct_inventory) {
            $invItem = \App\Models\InventoryItem::find($item->inventory_item_id);
            if ($invItem) {
                $deductPerUnit = $item->deduct_amount ?? 1;
                $requiredStock = $deductPerUnit * $qtyToOrder;
                if ($invItem->quantity < $requiredStock) {
                    $availableServings = floor($invItem->quantity / $deductPerUnit);
                    $this->orderError = "Insufficient Stock for {$item->name}! Only " . max(0, $availableServings) . " serving(s) available.";
                    return;
                }
            }
        }

        $this->orderError = '';

        if ($existingKey !== false) {
            $this->cart[$existingKey]['quantity']++;
        } else {
            $this->cart[] = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'is_veg' => $item->is_veg,
                'quantity' => 1
            ];
        }
    }

    public function incrementQuantity($index)
    {
        if (isset($this->cart[$index])) {
            $cartItem = $this->cart[$index];
            $item = MenuItem::find($cartItem['id']);
            
            // Check stock availability
            if ($item && $item->inventory_item_id && $item->auto_deduct_inventory) {
                $invItem = \App\Models\InventoryItem::find($item->inventory_item_id);
                if ($invItem) {
                    $deductPerUnit = $item->deduct_amount ?? 1;
                    $requiredStock = $deductPerUnit * ($cartItem['quantity'] + 1);
                    if ($invItem->quantity < $requiredStock) {
                        $availableServings = floor($invItem->quantity / $deductPerUnit);
                        $this->orderError = "Insufficient Stock for {$item->name}! Only " . max(0, $availableServings) . " serving(s) available.";
                        return;
                    }
                }
            }

            $this->orderError = '';
            $this->cart[$index]['quantity']++;
        }
    }

    public function decrementQuantity($index)
    {
        if (isset($this->cart[$index])) {
            if ($this->cart[$index]['quantity'] > 1) {
                $this->cart[$index]['quantity']--;
            } else {
                unset($this->cart[$index]);
                $this->cart = array_values($this->cart);
            }
        }
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function getCartTotalProperty()
    {
        return collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);
    }

    public function getCartCountProperty()
    {
        return collect($this->cart)->sum('quantity');
    }
    public function getFilteredItemsProperty()
    {
        $items = $this->items;

        if ($this->activeCategoryId) {
            $items = $items->where('menu_category_id', $this->activeCategoryId);
        }

        if (!empty($this->searchQuery)) {
            $query = strtolower($this->searchQuery);
            $items = $items->filter(fn($item) => str_contains(strtolower($item->name), $query));
        }

        return $items;
    }

    public function getItemQuantityInCart($itemId)
    {
        foreach ($this->cart as $item) {
            if ($item['id'] == $itemId) return $item['quantity'];
        }
        return 0;
    }

    public function removeFromCartById($itemId)
    {
        $this->cart = collect($this->cart)->filter(fn($item) => $item['id'] != $itemId)->values()->all();
    }

    public function placeOrder()
    {
        if (empty($this->cart) || !$this->restaurant) return;

        $tableId = $this->selectedTableId ?: null;
        $this->orderError = '';

        // Pre-validate stock for ALL cart items before placing order
        foreach ($this->cart as $cartItem) {
            $item = MenuItem::find($cartItem['id']);
            if ($item && $item->inventory_item_id && $item->auto_deduct_inventory) {
                $invItem = \App\Models\InventoryItem::find($item->inventory_item_id);
                if ($invItem) {
                    $deductPerUnit = $item->deduct_amount ?? 1;
                    $requiredStock = $deductPerUnit * $cartItem['quantity'];
                    if ($invItem->quantity < $requiredStock) {
                        $availableServings = floor($invItem->quantity / $deductPerUnit);
                        $this->orderError = "Insufficient Stock for {$item->name}! Only " . max(0, $availableServings) . " serving(s) available. Please adjust your cart.";
                        return;
                    }
                }
            }
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($tableId) {
                $existingOrder = null;
                if ($tableId) {
                    // Lock the table record to prevent race conditions
                    $table = Table::where('id', $tableId)->lockForUpdate()->first();
                    
                    $existingOrder = Order::where('table_id', $tableId)
                        ->whereNotIn('status', ['completed', 'cancelled'])
                        ->lockForUpdate() // Also lock the order if it exists
                        ->first();
                }

                if ($existingOrder) {
                    // Append to existing order
                    foreach ($this->cart as $item) {
                        OrderItem::create([
                            'order_id' => $existingOrder->id,
                            'menu_item_id' => $item['id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['price'],
                            'subtotal' => $item['price'] * $item['quantity'],
                        ]);
                    }
                    $existingOrder->total_amount += $this->cartTotal;
                    if ($this->orderNotes) {
                        $existingOrder->special_instructions = $existingOrder->special_instructions 
                            ? $existingOrder->special_instructions . ' | ' . $this->orderNotes 
                            : $this->orderNotes;
                    }
                    $existingOrder->save();
                    $this->currentOrder = Order::with(['items.menuItem', 'table'])->find($existingOrder->id);
                } else {
                    $today = \Carbon\Carbon::today();
                    $lastOrder = Order::where('restaurant_id', $this->restaurant->id)
                        ->whereDate('created_at', $today)
                        ->orderBy('id', 'desc')
                        ->lockForUpdate()
                        ->first();

                    $tokenCount = 1;
                    if ($lastOrder && $lastOrder->token_number) {
                        $tokenCount = (int) str_replace('#', '', $lastOrder->token_number) + 1;
                    }
                    $tokenNumber = '#' . str_pad($tokenCount, 2, '0', STR_PAD_LEFT);

                    $this->currentOrder = Order::create([
                        'restaurant_id' => $this->restaurant->id,
                        'table_id' => $tableId,
                        'user_id' => auth()->id(),
                        'guest_info' => $this->guestInfo ?: null,
                        'order_number' => 'ORD-' . strtoupper(Str::random(6)),
                        'token_number' => $tokenNumber,
                        'total_amount' => $this->cartTotal,
                        'status' => 'pending',
                        'order_type' => $tableId ? 'dine_in' : 'takeaway',
                        'special_instructions' => $this->orderNotes,
                    ]);

                    foreach ($this->cart as $item) {
                        OrderItem::create([
                            'order_id' => $this->currentOrder->id,
                            'menu_item_id' => $item['id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['price'],
                            'subtotal' => $item['price'] * $item['quantity'],
                        ]);
                    }

                    $this->currentOrder = Order::with(['items.menuItem', 'table'])->find($this->currentOrder->id);
                }
            });
        } catch (\Exception $e) {
            Log::error('Order Placement Transaction Failed: ' . $e->getMessage());
            $this->orderError = 'Could not place order. Please try again.';
            return;
        }
        
        // Automated Inventory Deduction
        try {
            \App\Services\InventoryService::deductFromOrder($this->currentOrder);
        } catch (\Exception $e) {
            Log::error('Inventory Deduction Failed: ' . $e->getMessage());
        }
        
        // ── Print Logic ──
        $this->printStatus = 'disabled';
        $this->printError = '';
        
        // Get only new/unprinted items for KOT/BOT
        $itemsToPrint = $this->currentOrder->items()->where('is_printed', false)->with('menuItem.category')->get();
        
        if ($itemsToPrint->isEmpty()) {
            $this->cart = [];
            $this->orderNotes = '';
            return;
        }

        $kotSuccess = false;
        $botSuccess = false;
        $kotAttempted = false;
        $botAttempted = false;
        
        try {
            // Print to Kitchen (Only food items from the new batch)
            $kotResult = \App\Services\PrinterService::printKOT($this->currentOrder, 'kitchen', $itemsToPrint);
            if ($kotResult === false) {
                $kotAttempted = false;
            } else {
                $kotAttempted = true;
                $kotSuccess = true;
            }
        } catch (\Exception $e) {
            $kotAttempted = true;
            $kotSuccess = false;
            Log::error('Kitchen KOT Print Failed: ' . $e->getMessage());
            $this->printError = 'Kitchen Print: ' . $e->getMessage();
        }

        try {
            // Print to Cashier (BOT/Counter Copy - Only drinks or summary from the new batch)
            $botResult = \App\Services\PrinterService::printKOT($this->currentOrder, 'cashier', $itemsToPrint);
            if ($botResult === false) {
                $botAttempted = false;
            } else {
                $botAttempted = true;
                $botSuccess = true;
            }
        } catch (\Exception $e) {
            $botAttempted = true;
            $botSuccess = false;
            Log::error('Cashier BOT Print Failed: ' . $e->getMessage());
            $this->printError .= ($this->printError ? ' | ' : '') . 'Cashier Print: ' . $e->getMessage();
        }

        // Mark items as printed so they aren't printed again if more items are added later
        if ($kotSuccess || $botSuccess) {
            foreach ($itemsToPrint as $item) {
                $item->update(['is_printed' => true]);
            }
        }

        // Auto-trigger Browser Printing if server-side USB printing is disabled or failed
        if ($kotResult === 'browser' || (!$kotSuccess && $kotAttempted)) {
            $this->dispatch('trigger-browser-print', url: route('print.kot', ['id' => $this->currentOrder->id]));
        }
        if ($botResult === 'browser' || (!$botSuccess && $botAttempted)) {
            $this->dispatch('trigger-browser-print', url: route('print.bot', ['id' => $this->currentOrder->id]));
        }

        // Determine overall print status
        if (!$kotAttempted && !$botAttempted) {
            $this->printStatus = 'disabled';
            // Always auto-dispatch browser KOT print when server printers are not configured!
            $this->dispatch('trigger-browser-print', url: route('print.kot', ['id' => $this->currentOrder->id]));
        } elseif ($kotSuccess && $botSuccess) {
            $this->printStatus = 'success';
        } elseif ($kotSuccess || $botSuccess) {
            $this->printStatus = 'partial';
        } else {
            $this->printStatus = 'failed';
            // Fallback to browser print on failure
            $this->dispatch('trigger-browser-print', url: route('print.kot', ['id' => $this->currentOrder->id]));
        }

        // Clear cart
        $this->cart = [];
        $this->orderNotes = '';
        // We do NOT set tableSelectionMode = true here so it shows the success screen with the Token Number.
    }

    public function printBrowserKOT()
    {
        if ($this->currentOrder) {
            $this->dispatch('trigger-browser-print', url: route('print.kot', ['id' => $this->currentOrder->id]));
        }
    }

    public function printBrowserBOT()
    {
        if ($this->currentOrder) {
            $this->dispatch('trigger-browser-print', url: route('print.bot', ['id' => $this->currentOrder->id]));
        }
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
            session()->flash('success', "✅ QR Order #{$order->order_number} for Table {$tableName} approved & sent to kitchen!");
            $this->loadOccupiedTables();
        }
    }

    public function rejectQrOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->update(['status' => 'cancelled']);
            $order->items()->update(['status' => 'cancelled']);
            session()->flash('success', "Order #{$order->order_number} was rejected/cancelled.");
            $this->loadOccupiedTables();
        }
    }

    public function startNewOrder()
    {
        $this->currentOrder = null;
        $this->selectedTableId = '';
        $this->guestInfo = '';
        $this->tableSelectionMode = true;
        $this->printStatus = '';
        $this->printError = '';
        $this->orderError = '';
        $this->loadOccupiedTables();
    }

    public function render()
    {
        return view('livewire.staff.waiter-order-taking')->layout('components.layouts.app', ['title' => 'Take Order']);
    }
}
