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

class RoomService extends Component
{
    public $restaurant;
    public $selectedTableId = '';
    public $tableSelectionMode = true;
    public $activeCategoryId = null;
    public $searchQuery = '';
    
    // Booking Form State
    public $bookingMode = false;
    public $guestName = '';
    public $guestPhone = '';
    public $guestIdNumber = '';
    public $roomRate = '';

    // Cart State
    public $cart = [];
    public $orderNotes = '';

    // Order State
    public $currentOrder = null;
    public $orderError = '';

    // Track which rooms are occupied
    public $occupiedTableIds = [];

    // Properties to hold data
    public $categories = [];
    public $items = [];

    public function mount($room = null)
    {
        $this->restaurant = current_restaurant();
        $this->loadMenu();
        $this->loadOccupiedTables();

        if ($room) {
            $this->selectTable($room);
        }
    }

    /**
     * Livewire lifecycle hook: sanitize guestPhone on every update.
     */
    public function updatedGuestPhone($value)
    {
        $this->guestPhone = substr(preg_replace('/[^0-9]/', '', $value), 0, 10);
    }

    public function loadMenu()
    {
        if (!$this->restaurant) return;
        $this->categories = MenuCategory::where('restaurant_id', $this->restaurant->id)->where('is_active', true)->get();
        $query = MenuItem::where('restaurant_id', $this->restaurant->id)->where('is_available', true);
        if ($this->activeCategoryId) $query->where('menu_category_id', $this->activeCategoryId);
        if (!empty($this->searchQuery)) $query->where('name', 'like', '%' . strtolower($this->searchQuery) . '%');
        $this->items = $query->get();
    }

    public function getFilteredTablesProperty()
    {
        return Table::where('type', 'room')->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function loadOccupiedTables()
    {
        $this->occupiedTableIds = Table::where('type', 'room')
            ->where('room_status', 'occupied')
            ->pluck('id')
            ->toArray();
    }

    public function selectTable($id)
    {
        $table = Table::find($id);
        if (!$table) return;

        $this->selectedTableId = $id;

        if ($table->room_status === 'occupied') {
            // Room is already booked, go to food ordering
            $this->tableSelectionMode = false;
            $this->bookingMode = false;
            $this->loadMenu();
        } else {
            // Room is available, show booking form
            $this->bookingMode = true;
            $this->tableSelectionMode = false;
        }
    }

    public function bookRoom()
    {
        $this->validate([
            'guestName' => 'required|string|max:100',
            'guestIdNumber' => 'nullable|string|max:50',
            'roomRate' => 'required|numeric|min:0',
        ]);

        $table = Table::find($this->selectedTableId);
        if ($table) {
            $table->update([
                'guest_name' => $this->guestName,
                'guest_phone' => $this->guestPhone,
                'guest_id_number' => $this->guestIdNumber,
                'room_rate' => $this->roomRate,
                'room_status' => 'occupied',
                'check_in_at' => now(),
            ]);

            // After booking, stay on the dashboard or go to order?
            // User says "show room for booking", let's go back to dashboard to see it booked.
            $this->startNewOrder();
            session()->flash('success', "Room {$table->name} booked successfully for {$this->guestName}");
        }
    }

    public function checkOut()
    {
        $table = Table::find($this->selectedTableId);
        if ($table) {
            // Get all active orders for this room to print the final bill
            $orders = Order::where('table_id', $table->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->with('items.menuItem')
                ->get();

            try {
                \App\Services\PrinterService::printHotelBillAsync($table, $orders);
            } catch (\Exception $e) {
                Log::error('Hotel Bill Print Failed: ' . $e->getMessage());
            }

            // Mark orders as completed
            foreach ($orders as $order) {
                $order->update(['status' => 'completed']);
            }

            // Clear the room
            $table->update([
                'guest_name' => null,
                'guest_phone' => null,
                'room_rate' => null,
                'room_status' => 'available',
                'check_in_at' => null,
            ]);
            $this->startNewOrder();
            session()->flash('success', "Room {$table->name} checked out and bill printed.");
        }
    }

    public function printCurrentBill()
    {
        $table = Table::find($this->selectedTableId);
        if ($table) {
            $orders = Order::where('table_id', $table->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->with('items.menuItem')
                ->get();

            try {
                \App\Services\PrinterService::printHotelBillAsync($table, $orders);
                session()->flash('success', "Interim bill printed for Room {$table->name}");
            } catch (\Exception $e) {
                Log::error('Hotel Bill Print Failed: ' . $e->getMessage());
                $this->orderError = "Printer Error: " . $e->getMessage();
            }
        }
    }

    public function cancelBookingMode()
    {
        $this->bookingMode = false;
        $this->tableSelectionMode = true;
        $this->selectedTableId = '';
    }

    // Cart & Order Methods (Keep existing for room service)
    public function addToCart($itemId)
    {
        $item = MenuItem::find($itemId);
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
            $this->orderError = '';
        }
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->orderError = '';
    }

    public function placeOrder()
    {
        if (empty($this->cart) || !$this->restaurant) return;
        
        $this->orderError = '';

        // Pre-validate stock for ALL cart items
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
            \Illuminate\Support\Facades\DB::transaction(function () {
                $this->currentOrder = Order::create([
                    'restaurant_id' => $this->restaurant->id,
                    'table_id' => $this->selectedTableId,
                    'user_id' => auth()->id(),
                    'order_number' => 'ROOM-' . strtoupper(Str::random(6)),
                    'total_amount' => collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']),
                    'status' => 'pending',
                    'order_type' => 'dine_in',
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
            });
        } catch (\Exception $e) {
            Log::error('Room Service Order Transaction Failed: ' . $e->getMessage());
            $this->orderError = 'Could not place order. Please try again.';
            return;
        }

        // Automated Inventory Deduction
        try {
            \App\Services\InventoryService::deductFromOrder($this->currentOrder);
        } catch (\Exception $e) {
            Log::error('Room Service Inventory Deduction Failed: ' . $e->getMessage());
        }

        $this->currentOrder = Order::with(['items.menuItem', 'table'])->find($this->currentOrder->id);
        
        // ── Print Logic ──
        $itemsToPrint = $this->currentOrder->items()->where('is_printed', false)->with('menuItem.category')->get();
        if ($itemsToPrint->isNotEmpty()) {
            $kotSuccess = false;
            $botSuccess = false;
            
            try {
                // Print to Kitchen (Food items)
                $kotResult = \App\Services\PrinterService::printKOT($this->currentOrder, 'kitchen', $itemsToPrint);
                if ($kotResult !== false) {
                    $kotSuccess = true;
                }
            } catch (\Exception $e) {
                Log::error('Room Service Kitchen KOT Print Failed: ' . $e->getMessage());
            }

            try {
                // Print to Cashier/Bar (Bar/Counter/Summary)
                $botResult = \App\Services\PrinterService::printKOT($this->currentOrder, 'cashier', $itemsToPrint);
                if ($botResult !== false) {
                    $botSuccess = true;
                }
            } catch (\Exception $e) {
                Log::error('Room Service Cashier BOT Print Failed: ' . $e->getMessage());
            }

            // Mark items as printed so they aren't printed again
            if ($kotSuccess || $botSuccess) {
                foreach ($itemsToPrint as $item) {
                    $item->update(['is_printed' => true]);
                }
            }
        }

        $this->cart = [];
        $this->orderNotes = '';
    }

    public function startNewOrder()
    {
        $this->currentOrder = null;
        $this->selectedTableId = '';
        $this->tableSelectionMode = true;
        $this->bookingMode = false;
        $this->guestName = '';
        $this->guestPhone = '';
        $this->guestIdNumber = '';
        $this->roomRate = '';
        $this->loadOccupiedTables();
    }

    public function render()
    {
        return view('livewire.staff.room-service')->layout('components.layouts.app', ['title' => 'Hotel Room Service']);
    }
}
