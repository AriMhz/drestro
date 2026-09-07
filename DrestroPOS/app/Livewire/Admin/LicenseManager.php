<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Restaurant;
use App\Services\LicenseManager as LicenseService;

class LicenseManager extends Component
{
    public $licenseKey;
    public $machineId;
    public $licenseData = null;
    public $activationError = null;

    public function mount()
    {
        $this->machineId = LicenseService::getMachineId();
        
        $restaurant = current_restaurant();
        if ($restaurant) {
            $this->licenseKey = $restaurant->license_key;
            
            // If we have cached license_data in the database, use it (works offline)
            if ($restaurant->license_data) {
                $this->licenseData = $restaurant->license_data;
            }
            
            // Try to verify the key online if we have one
            if ($this->licenseKey) {
                $verified = LicenseService::verify($this->licenseKey);
                if ($verified) {
                    $this->licenseData = $verified;
                    // Cache for offline use
                    $restaurant->license_data = $verified;
                    $restaurant->save();
                }
            }
        }
        
        if (!$this->licenseData) {
            $this->licenseData = LicenseService::getFreeLimits();
        }
    }

    /**
     * Activate a license key entered by the user.
     * Sends both the key AND the machine_id to the server.
     * The server will lock the key to this machine on first activation.
     */
    public function activateLicense()
    {
        $this->activationError = null;
        
        if (empty($this->licenseKey)) {
            $this->activationError = 'Please enter a license key.';
            return;
        }

        try {
            $billingApiUrl = config('app.env') === 'production' 
                ? 'https://drestro.com/api/license/verify' 
                : 'https://drestro.com/api/license/verify';

            $response = \Illuminate\Support\Facades\Http::timeout(10)->post($billingApiUrl, [
                'license_key' => $this->licenseKey,
                'machine_id' => $this->machineId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['valid']) && $data['valid']) {
                    // Save the key + full license data to local database
                    $restaurant = current_restaurant() ?? new Restaurant();
                    $restaurant->license_key = $this->licenseKey;
                    $restaurant->machine_id = $this->machineId;
                    $restaurant->license_data = $data['data'] ?? [
                        'plan' => $data['clientName'] ?? 'Premium',
                        'status' => 'active',
                        'expires_at' => $data['expiryDate'] ?? null,
                        'features' => ['all'],
                        'limits' => $data['data']['limits'] ?? [],
                    ];
                    $restaurant->save();

                    $this->licenseData = $restaurant->license_data;
                    session()->flash('success', '✅ License activated successfully! Your software is now fully unlocked.');
                } else {
                    $this->activationError = $data['message'] ?? 'License could not be verified.';
                    session()->flash('error', $this->activationError);
                }
            } else {
                $this->activationError = 'Could not connect to the DRestro Billing Server. Please check your internet connection.';
                session()->flash('error', $this->activationError);
            }
        } catch (\Exception $e) {
            $this->activationError = 'Activation failed: Please ensure you have an active internet connection.';
            session()->flash('error', $this->activationError);
        }
    }

    /**
     * Sync license from billing server using machine_id.
     * This re-validates the current key + machine combo.
     */
    public function syncLicense()
    {
        try {
            $billingApiUrl = config('app.env') === 'production' 
                ? 'https://drestro.com/api/license/verify' 
                : 'https://drestro.com/api/license/verify';

            $payload = ['machine_id' => $this->machineId];
            
            // If we have a license key, send it too for proper binding verification
            if ($this->licenseKey) {
                $payload['license_key'] = $this->licenseKey;
            }

            $response = \Illuminate\Support\Facades\Http::post($billingApiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['valid']) && $data['valid']) {
                    $restaurant = current_restaurant() ?? new Restaurant();
                    $restaurant->license_data = $data['data'] ?? [
                        'plan' => 'Premium',
                        'status' => 'active',
                        'expires_at' => \Carbon\Carbon::parse($data['expiryDate'])->toDateTimeString(),
                        'features' => ['all'],
                    ];
                    $restaurant->machine_id = $this->machineId;
                    $restaurant->save();

                    $this->licenseData = $restaurant->license_data;
                    session()->flash('success', 'License successfully synchronized from billing server!');
                } else {
                    session()->flash('error', $data['message'] ?? 'License could not be verified.');
                    $this->licenseData = LicenseService::getFreeLimits();
                }
            } else {
                session()->flash('error', 'Could not connect to Drestro Billing Server. Please check your internet connection.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Sync failed: Please ensure the billing server is reachable.');
        }
    }

    public function render()
    {
        return view('livewire.admin.license-manager')->layout('components.layouts.app', ['title' => 'License & Billing']);
    }
}
