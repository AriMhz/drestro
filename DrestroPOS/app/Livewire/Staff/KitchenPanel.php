<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Carbon\Carbon;

class KitchenPanel extends Component
{
    public $restaurant;

    public function mount()
    {
        // In a real app, get from auth()->user()->restaurant_id
        $this->restaurant = current_restaurant();
    }

    public function getPendingItemsProperty()
    {
        if (!$this->restaurant) return collect();

        return OrderItem::with(['order.table', 'menuItem.category'])
            ->whereHas('order', function ($query) {
                $query->where('restaurant_id', $this->restaurant->id)
                      ->whereIn('status', ['pending', 'preparing']);
            })
            ->whereHas('menuItem.category', function ($query) {
                $query->where('department', 'kitchen');
            })
            ->whereIn('status', ['pending', 'preparing'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function markPreparing($itemId)
    {
        $item = OrderItem::find($itemId);
        if ($item && $item->status === 'pending') {
            $item->update(['status' => 'preparing']);
            
            // If the parent order is still pending, mark it as preparing
            if ($item->order->status === 'pending') {
                $item->order->update(['status' => 'preparing']);
            }
        }
    }

    public function markReady($itemId)
    {
        $item = OrderItem::find($itemId);
        if ($item && $item->status === 'preparing') {
            $item->update(['status' => 'ready']);
            
            // Check if all items in the order are ready
            $allReady = !$item->order->items()->whereIn('status', ['pending', 'preparing'])->exists();
            if ($allReady) {
                $item->order->update(['status' => 'ready']);
            }
        }
    }

    public function render()
    {
        return view('livewire.staff.kitchen-panel')->layout('components.layouts.app', ['title' => 'Kitchen Display']);
    }
}
