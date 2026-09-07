<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'license_data' => 'array',
    ];

    /**
     * Get active license data or fallback safely to default limits
     */
    public function activeLicense()
    {
        return $this->license_data ?? \App\Services\LicenseManager::getFreeLimits();
    }
}
