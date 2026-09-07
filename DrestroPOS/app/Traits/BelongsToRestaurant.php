<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToRestaurant
{
    /**
     * Boot the trait to apply global scopes.
     */
    protected static function bootBelongsToRestaurant()
    {
        // 1. Automatically scope all queries to the active tenant/restaurant
        static::addGlobalScope('restaurant', function (Builder $builder) {
            $restaurant = current_restaurant();
            if ($restaurant) {
                $builder->where('restaurant_id', $restaurant->id);
            }
        });

        // 2. Automatically assign the restaurant_id when creating a new record
        static::creating(function ($model) {
            $restaurant = current_restaurant();
            if ($restaurant && empty($model->restaurant_id)) {
                $model->restaurant_id = $restaurant->id;
            }
        });
    }

    /**
     * Relationship to the Restaurant.
     */
    public function restaurant()
    {
        return $this->belongsTo(\App\Models\Restaurant::class);
    }
}
