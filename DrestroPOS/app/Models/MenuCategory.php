<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    use \App\Traits\BelongsToRestaurant;

    protected $guarded = [];
}
