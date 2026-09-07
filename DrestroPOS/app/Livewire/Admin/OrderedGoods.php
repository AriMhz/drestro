<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class OrderedGoods extends Component
{
    public $orders = [];
    public $isLoading = true;
    public $errorMessage = null;

    public function mount()
    {
        $this->fetchOrders();
    }

    public function fetchOrders()
    {
        $this->isLoading = true;
        $this->errorMessage = null;

        $restaurant = current_restaurant();
        $licenseKey = $restaurant ? $restaurant->license_key : null;

        if (!$licenseKey) {
            $this->errorMessage = 'No active license key found. Cannot fetch ordered goods.';
            $this->isLoading = false;
            return;
        }

        try {
            // Adjust to your actual production domain when deploying, but for now we use drestro.com
            $apiUrl = 'https://drestro.com/api/portal/ordered-goods';
            
            $response = Http::timeout(10)->get($apiUrl, [
                'licenseKey' => $licenseKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['orders'])) {
                    $this->orders = $data['orders'];
                } else if (isset($data['error'])) {
                    $this->errorMessage = $data['error'];
                }
            } else {
                $this->errorMessage = 'Could not connect to the DRestro server to fetch orders.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to fetch orders. Please check your internet connection.';
        }

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.admin.ordered-goods')->layout('components.layouts.app', ['title' => 'My Orders']);
    }
}
