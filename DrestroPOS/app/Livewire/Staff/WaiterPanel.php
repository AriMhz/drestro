<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\OrderItem;
use App\Models\Restaurant;

class WaiterPanel extends Component
{
    public $restaurant;

    public function mount()
    {
        $this->restaurant = current_restaurant();
    }

    public function getReadyItemsProperty()
    {
        if (!$this->restaurant) return collect();

        // Get all items that are ready to be served
        return OrderItem::with(['order.table', 'menuItem'])
            ->whereHas('order', function ($query) {
                $query->where('restaurant_id', $this->restaurant->id)
                      ->where('status', '!=', 'completed'); // Order isn't fully paid yet
            })
            ->where('status', 'ready')
            ->orderBy('updated_at', 'asc') // Oldest ready first
            ->get()
            ->groupBy(function($item) {
                return $item->order->table ? $item->order->table->name : 'Takeaway';
            });
    }

    public function markServed($itemId)
    {
        $item = OrderItem::find($itemId);
        if ($item && $item->status === 'ready') {
            $item->update(['status' => 'served']);
        }
    }

    public function markTableServed($tableKey)
    {
        $items = $this->readyItems->get($tableKey);
        if ($items) {
            foreach ($items as $item) {
                $item->update(['status' => 'served']);
            }
        }
    }

    public $previousReadyCount = 0;

    public function render()
    {
        $currentCount = collect($this->readyItems)->flatten()->count();
        
        if ($currentCount > $this->previousReadyCount) {
            $this->dispatch('play-ding-sound');
        }
        
        $this->previousReadyCount = $currentCount;

        return view('livewire.staff.waiter-panel')->layout('components.layouts.app', ['title' => 'Ready to Serve']);
    }
}
