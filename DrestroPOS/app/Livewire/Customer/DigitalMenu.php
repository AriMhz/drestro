<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Table;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;

class DigitalMenu extends Component
{
    public $table;
    public $restaurant;
    public $categories;
    public $items;
    public $activeCategoryId = null;

    // Cart State
    public $cart = []; // Array of ['id' => itemId, 'quantity' => q, 'item' => array]
    public $showCart = false;
    public $orderNotes = '';
    
    // Order State
    public $currentOrder = null;
    public $currentOrderId = null;

    public function mount()
    {
        $tableId = request()->query('table');
        
        if ($tableId) {
            $this->table = Table::find($tableId);
            if ($this->table) {
                $this->restaurant = Restaurant::find($this->table->restaurant_id);
                // Check if active QR order exists in session
                $sessOrderId = session('qr_order_' . $this->table->id);
                if ($sessOrderId) {
                    $order = Order::with('items.menuItem')->find($sessOrderId);
                    if ($order && !in_array($order->status, ['completed', 'cancelled'])) {
                        $this->currentOrderId = $order->id;
                        $this->currentOrder = $order;
                    }
                }
            }
        }

        if (!$this->restaurant) {
            $this->restaurant = current_restaurant();
        }

        if ($this->restaurant) {
            $this->categories = MenuCategory::where('restaurant_id', $this->restaurant->id)->where('is_active', true)->get();
            $this->items = MenuItem::where('restaurant_id', $this->restaurant->id)->where('is_available', true)->get();
            
            if ($this->categories->count() > 0) {
                $this->activeCategoryId = $this->categories->first()->id;
            }
        } else {
            $this->categories = collect();
            $this->items = collect();
        }
    }

    public function checkCurrentOrder()
    {
        if ($this->currentOrderId) {
            $order = Order::with('items.menuItem')->find($this->currentOrderId);
            if ($order) {
                $this->currentOrder = $order;
            }
        }
    }

    public function filterByCategory($categoryId)
    {
        $this->activeCategoryId = $categoryId;
    }

    // Cart Methods
    public function toggleCart()
    {
        $this->showCart = !$this->showCart;
    }

    public function addToCart($itemId)
    {
        $item = $this->items->firstWhere('id', $itemId);
        if (!$item) return;

        $existingKey = collect($this->cart)->search(fn($i) => $i['id'] == $itemId);

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
        
        // Ensure cart drawer opens automatically on desktop if hidden (mobile keeps floating button)
        $this->showCart = true;
    }

    public function incrementQuantity($index)
    {
        if (isset($this->cart[$index])) {
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
                $this->cart = array_values($this->cart); // reindex
                if (count($this->cart) === 0) {
                    $this->showCart = false;
                }
            }
        }
    }

    public function getCartTotalProperty()
    {
        return collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);
    }

    public function getCartCountProperty()
    {
        return collect($this->cart)->sum('quantity');
    }

    public function placeOrder()
    {
        if (empty($this->cart) || !$this->restaurant) return;

        // Create Order with pending_approval status (waiter must confirm first)
        $order = Order::create([
            'restaurant_id' => $this->restaurant->id,
            'table_id' => $this->table ? $this->table->id : null,
            'order_number' => 'ORD-' . strtoupper(Str::random(6)),
            'total_amount' => $this->cartTotal,
            'status' => 'pending_approval',
            'order_type' => $this->table ? 'dine_in' : 'takeaway',
            'special_instructions' => $this->orderNotes,
        ]);

        // Create Order Items
        foreach ($this->cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
                'is_printed' => false,
            ]);
        }

        $this->currentOrderId = $order->id;
        $this->currentOrder = Order::with('items.menuItem')->find($order->id);

        if ($this->table) {
            session(['qr_order_' . $this->table->id => $order->id]);
        }

        // Clear cart and show waiting screen
        $this->cart = [];
        $this->orderNotes = '';
        $this->showCart = false;
    }

    public function startNewOrder()
    {
        if ($this->table) {
            session()->forget('qr_order_' . $this->table->id);
        }
        $this->currentOrder = null;
        $this->currentOrderId = null;
    }

    public function render()
    {
        return view('livewire.customer.digital-menu')->layout('components.layouts.customer', ['title' => 'Digital Menu']);
    }
}
