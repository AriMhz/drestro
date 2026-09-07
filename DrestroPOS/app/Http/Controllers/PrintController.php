<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function kot(Order $order)
    {
        $order->load(['items.menuItem.category', 'table']);
        $restaurant = current_restaurant();
        
        // Filter out Bar items (Drinks) to only print Kitchen items
        // Usually handled by checking category type, but for now we print all items unless categorized
        $items = $order->items->filter(function($item) {
            // If category contains "drink" or "bar", skip it in KOT.
            // If the schema doesn't distinguish, we'll just print everything for now.
            // A more robust app would have a boolean flag 'is_bar_item' on the category.
            $categoryName = strtolower($item->menuItem->category->name ?? '');
            return !str_contains($categoryName, 'drink') && !str_contains($categoryName, 'beverage') && !str_contains($categoryName, 'bar');
        });

        return view('print.kot', compact('order', 'items', 'restaurant'));
    }

    public function bot(Order $order)
    {
        $order->load(['items.menuItem.category', 'table']);
        $restaurant = current_restaurant();

        // Filter out Food items to only print Bar items
        $items = $order->items->filter(function($item) {
            $categoryName = strtolower($item->menuItem->category->name ?? '');
            return str_contains($categoryName, 'drink') || str_contains($categoryName, 'beverage') || str_contains($categoryName, 'bar');
        });

        return view('print.bot', compact('order', 'items', 'restaurant'));
    }

    public function receipt(Order $order)
    {
        $order->load(['items.menuItem', 'table']);
        $restaurant = current_restaurant();
        
        return view('print.receipt', compact('order', 'restaurant'));
    }
}
