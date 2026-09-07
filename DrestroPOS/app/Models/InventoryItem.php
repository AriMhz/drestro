<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    public function logs()
    {
        return $this->hasMany(InventoryLog::class)->orderBy('created_at', 'desc');
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->low_stock_threshold;
    }
}
