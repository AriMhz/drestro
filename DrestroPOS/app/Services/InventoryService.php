<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public static function deductFromOrder(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                self::deductItem($item);
            }
        });
    }

    public static function deductItem(OrderItem $item)
    {
        // Don't deduct if already done or if not linked
        if ($item->inventory_deducted) return;
        
        $menuItem = $item->menuItem;
        if (!$menuItem || !$menuItem->inventory_item_id || !$menuItem->auto_deduct_inventory) return;

        $invItem = InventoryItem::find($menuItem->inventory_item_id);
        if (!$invItem) return;

        // Use deduct_amount (serving size) × order quantity
        // e.g., 1 peg ordered × 50ml deduct_amount = 50ml deducted
        $deductAmount = ($menuItem->deduct_amount ?? 1) * $item->quantity;

        // Deduct stock
        $newQuantity = max(0, $invItem->quantity - $deductAmount);
        $invItem->update(['quantity' => $newQuantity]);

        // Create log
        InventoryLog::create([
            'inventory_item_id' => $invItem->id,
            'type' => 'stock_out',
            'quantity' => $deductAmount,
            'balance_after' => $newQuantity,
            'note' => "Auto: {$item->quantity}x {$menuItem->name} ({$deductAmount} {$invItem->unit}) — Order #{$item->order_id}",
        ]);

        // Mark as deducted
        $item->update(['inventory_deducted' => true]);
    }

    public static function revertItem(OrderItem $item)
    {
        if (!$item->inventory_deducted) return;

        $menuItem = $item->menuItem;
        if (!$menuItem || !$menuItem->inventory_item_id) return;

        $invItem = InventoryItem::find($menuItem->inventory_item_id);
        if (!$invItem) return;

        // Revert using same formula: deduct_amount × quantity
        $revertAmount = ($menuItem->deduct_amount ?? 1) * $item->quantity;

        // Revert stock
        $newQuantity = $invItem->quantity + $revertAmount;
        $invItem->update(['quantity' => $newQuantity]);

        // Create log
        InventoryLog::create([
            'inventory_item_id' => $invItem->id,
            'type' => 'stock_in',
            'quantity' => $revertAmount,
            'balance_after' => $newQuantity,
            'note' => "Stock revert: {$item->quantity}x {$menuItem->name} ({$revertAmount} {$invItem->unit}) — Order #{$item->order_id}",
        ]);

        // Mark as NOT deducted
        $item->update(['inventory_deducted' => false]);
    }
}
