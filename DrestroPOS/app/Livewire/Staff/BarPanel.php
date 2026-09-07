<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\OrderItem;
use App\Models\Restaurant;

class BarPanel extends Component
{
    public $restaurant;

    public function mount()
    {
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
                $query->where('department', 'bar');
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
            $allReady = !$item->order->items()->whereIn('status', ['pending', 'preparing'])->exists();
            if ($allReady) {
                $item->order->update(['status' => 'ready']);
            }
        }
    }

    public function render()
    {
        return view('livewire.staff.kitchen-panel')->layout('components.layouts.app', ['title' => 'Bar Display']);
    }
}
