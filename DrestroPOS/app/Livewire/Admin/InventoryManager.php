<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\Restaurant;
use Illuminate\Validation\Rule;

class InventoryManager extends Component
{
    public $restaurant;

    // Modal states
    public $showItemModal = false;
    public $showStockModal = false;
    public $showHistoryModal = false;
    public $editingItemId = null;

    // Item form
    public $itemName = '';
    public $itemUnit = 'kg';
    public $itemCategory = 'general';
    public $itemCostPerUnit = 0;
    public $itemLowStockThreshold = 5;
    public $itemQuantity = 0;
    public $itemDate = '';

    // Stock adjustment form
    public $stockItemId = null;
    public $stockType = 'stock_in';
    public $stockQuantity = '';
    public $stockDate = '';
    public $stockNote = '';

    // History state
    public $historyItem = null;
    public $historyLogs = [];

    // Filter
    public $filterCategory = '';
    public $searchQuery = '';
    public $filterDate = '';
    public $period = 'today';
    public $customStartDate;
    public $customEndDate;

    public function mount()
    {
        $this->restaurant = current_restaurant();
        $this->stockDate = now()->format('Y-m-d');
        $this->filterDate = now()->format('Y-m-d');
        $this->customStartDate = now()->format('Y-m-d');
        $this->customEndDate = now()->format('Y-m-d');
    }

    public function getItemsProperty()
    {
        if (!$this->restaurant) return collect();

        $query = InventoryItem::where('restaurant_id', $this->restaurant->id);

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        if ($this->searchQuery) {
            $query->where('name', 'like', '%' . $this->searchQuery . '%');
        }

        return $query->orderBy('name')->get();
    }

    public function getLowStockCountProperty()
    {
        if (!$this->restaurant) return 0;
        return InventoryItem::where('restaurant_id', $this->restaurant->id)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->count();
    }

    public function getTotalValueProperty()
    {
        if (!$this->restaurant) return 0;
        return InventoryItem::where('restaurant_id', $this->restaurant->id)
            ->selectRaw('SUM(quantity * cost_per_unit) as total')
            ->value('total') ?? 0;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
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

    public function getDailyLogsProperty()
    {
        if (!$this->restaurant) return collect();
        
        return InventoryLog::with('item')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereHas('item', function($q) {
                $q->where('restaurant_id', $this->restaurant->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // --- Item CRUD ---

    public function openItemModal($itemId = null)
    {
        $this->resetItemForm();
        if ($itemId) {
            $item = InventoryItem::find($itemId);
            if ($item) {
                $this->editingItemId = $item->id;
                $this->itemName = $item->name;
                $this->itemUnit = $item->unit;
                $this->itemCategory = $item->category;
                $this->itemCostPerUnit = $item->cost_per_unit;
                $this->itemLowStockThreshold = $item->low_stock_threshold;
                $this->itemQuantity = $item->quantity;
            }
        }
        $this->showItemModal = true;
    }

    public function closeItemModal()
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    public function resetItemForm()
    {
        $this->editingItemId = null;
        $this->itemName = '';
        $this->itemUnit = 'kg';
        $this->itemCategory = 'general';
        $this->itemCostPerUnit = 0;
        $this->itemLowStockThreshold = 5;
        $this->itemQuantity = 0;
        $this->itemDate = now()->format('Y-m-d');
    }

    public function saveItem()
    {
        $this->validate([
            'itemName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inventory_items', 'name')
                    ->where('restaurant_id', $this->restaurant->id)
                    ->ignore($this->editingItemId)
            ],
            'itemUnit' => 'required|string',
            'itemCategory' => 'required|string',
            'itemCostPerUnit' => 'required|numeric|min:0',
            'itemLowStockThreshold' => 'required|numeric|min:0',
            'itemDate' => 'required|date',
        ]);

        if ($this->editingItemId) {
            $item = InventoryItem::find($this->editingItemId);
            $item->update([
                'name' => $this->itemName,
                'unit' => $this->itemUnit,
                'category' => $this->itemCategory,
                'cost_per_unit' => $this->itemCostPerUnit,
                'low_stock_threshold' => $this->itemLowStockThreshold,
            ]);
        } else {
            $item = InventoryItem::create([
                'restaurant_id' => $this->restaurant->id,
                'name' => $this->itemName,
                'unit' => $this->itemUnit,
                'category' => $this->itemCategory,
                'cost_per_unit' => $this->itemCostPerUnit,
                'low_stock_threshold' => $this->itemLowStockThreshold,
                'quantity' => $this->itemQuantity,
            ]);

            if ($this->itemQuantity > 0) {
                $logDate = \Carbon\Carbon::parse($this->itemDate);
                if ($logDate->isToday()) {
                    $logDate = now();
                } else {
                    $logDate = $logDate->startOfDay();
                }

                InventoryLog::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'stock_in',
                    'quantity' => $this->itemQuantity,
                    'balance_after' => $this->itemQuantity,
                    'note' => 'Opening stock',
                    'created_at' => $logDate,
                    'updated_at' => $logDate,
                ]);
            }
        }

        if ($this->editingItemId) {
            $this->closeItemModal();
        } else {
            $this->resetItemForm();
            session()->flash('success', 'Item added successfully. You can add another.');
        }
    }

    public function deleteItem($itemId)
    {
        InventoryItem::destroy($itemId);
    }

    // --- Stock Adjustment ---

    public function openStockModal($itemId)
    {
        $this->stockItemId = $itemId;
        $this->stockType = 'stock_in';
        $this->stockQuantity = '';
        $this->stockDate = now()->format('Y-m-d');
        $this->stockNote = '';
        $this->showStockModal = true;
    }

    public function closeStockModal()
    {
        $this->showStockModal = false;
        $this->stockItemId = null;
    }

    public function adjustStock()
    {
        $this->validate([
            'stockQuantity' => 'required|numeric|min:0.01',
            'stockType' => 'required|in:stock_in,stock_out,adjustment',
            'stockDate' => 'required|date',
        ]);

        $item = InventoryItem::find($this->stockItemId);
        if (!$item) return;

        $qty = (float) $this->stockQuantity;

        if ($this->stockType === 'stock_in') {
            $item->quantity += $qty;
        } elseif ($this->stockType === 'stock_out') {
            $item->quantity = max(0, $item->quantity - $qty);
        } else {
            // Direct adjustment - set to exact value
            $item->quantity = $qty;
        }

        $item->save();

        // Create log with the specific date (time will be current time on that date)
        $date = \Carbon\Carbon::parse($this->stockDate);
        if ($date->isToday()) {
            $logDate = now();
        } else {
            $logDate = $date->startOfDay();
        }

        InventoryLog::create([
            'inventory_item_id' => $item->id,
            'type' => $this->stockType,
            'quantity' => $qty,
            'balance_after' => $item->quantity,
            'note' => $this->stockNote ?: null,
            'created_at' => $logDate,
            'updated_at' => $logDate,
        ]);

        $this->closeStockModal();
        session()->flash('success', 'Stock updated successfully.');
    }

    public function openHistoryModal($itemId)
    {
        $this->historyItem = InventoryItem::find($itemId);
        if ($this->historyItem) {
            $this->historyLogs = InventoryLog::where('inventory_item_id', $itemId)
                ->orderBy('created_at', 'desc')
                ->get();
            $this->showHistoryModal = true;
        }
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->historyItem = null;
        $this->historyLogs = [];
    }

    public function render()
    {
        return view('livewire.admin.inventory-manager')->layout('components.layouts.app', ['title' => 'Inventory Manager']);
    }
}
