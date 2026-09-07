<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Table;
use App\Models\Restaurant;

class TableManager extends Component
{
    public $tables;
    public $restaurant;
    public $activeLicense;
    
    // Form fields
    public $newTableName = '';
    public $newTableCapacity = 4;
    public $showAddModal = false;

    public function mount()
    {
        // For development, ensure we have at least one restaurant
        $this->restaurant = current_restaurant() ?? Restaurant::firstOrCreate(
            ['id' => 1],
            ['name' => 'Drestro Main Branch', 'is_active' => true]
        );
        
        $this->loadTables();
        $this->activeLicense = current_restaurant()?->license_data ?? \App\Services\LicenseManager::getFreeLimits();
    }

    public function loadTables()
    {
        $this->tables = Table::where('restaurant_id', $this->restaurant->id)
            ->where('type', 'table')
            ->get();
    }

    public function toggleAddModal()
    {
        $this->showAddModal = !$this->showAddModal;
    }

    public function saveTable()
    {
        $this->validate([
            'newTableName' => 'required|string|max:255',
            'newTableCapacity' => 'required|integer|min:1|max:20',
        ]);

        $limit = $this->activeLicense['limits']['tables'] ?? 0;
        if ($limit > 0 && $this->tables->count() >= $limit) {
            session()->flash('error', "Limit reached: Your plan allows a maximum of {$limit} tables. Please upgrade to add more.");
            return;
        }

        $table = Table::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => $this->newTableName,
            'capacity' => $this->newTableCapacity,
            'type' => 'table',
        ]);

        // Generate a unique URL for the QR code
        $table->update([
            'qr_code' => url('/menu?table=' . $table->id)
        ]);

        $this->newTableName = '';
        $this->newTableCapacity = 4;
        $this->showAddModal = false;
        
        $this->loadTables();
    }

    public function deleteTable($id)
    {
        Table::find($id)?->delete();
        $this->loadTables();
    }

    public function render()
    {
        return view('livewire.admin.table-manager')->layout('components.layouts.app', ['title' => 'Table Management & QR Codes']);
    }
}
