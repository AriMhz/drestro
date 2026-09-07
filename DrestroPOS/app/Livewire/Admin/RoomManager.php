<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Table;
use App\Models\Restaurant;

class RoomManager extends Component
{
    public $rooms;
    public $restaurant;
    public $activeLicense;
    
    // Form fields
    public $newRoomName = '';
    public $newRoomPrice = 0;
    public $showAddModal = false;
    public $editingRoomId = null;

    public function mount()
    {
        $this->restaurant = current_restaurant();
        $this->loadRooms();
        $this->activeLicense = $this->restaurant?->license_data ?? \App\Services\LicenseManager::getFreeLimits();
    }

    public function loadRooms()
    {
        $this->rooms = Table::where('restaurant_id', $this->restaurant->id)
            ->where('type', 'room')
            ->get();
    }

    public function toggleAddModal()
    {
        $this->showAddModal = !$this->showAddModal;
        if (!$this->showAddModal) {
            $this->editingRoomId = null;
            $this->newRoomName = '';
        }
    }

    public function editRoom($id)
    {
        $room = Table::find($id);
        if ($room) {
            $this->editingRoomId = $room->id;
            $this->newRoomName = $room->name;
            $this->showAddModal = true;
        }
    }

    public function saveRoom()
    {
        $this->validate([
            'newRoomName' => 'required|string|max:255',
        ]);

        if ($this->editingRoomId) {
            $room = Table::find($this->editingRoomId);
            if ($room) {
                $room->update(['name' => $this->newRoomName]);
            }
            $this->editingRoomId = null;
        } else {
            $limit = $this->activeLicense['limits']['rooms'] ?? 0;
            if ($limit > 0 && $this->rooms->count() >= $limit) {
                session()->flash('error', "Limit reached: Your plan allows a maximum of {$limit} rooms. Please upgrade to add more.");
                return;
            }

            Table::create([
                'restaurant_id' => $this->restaurant->id,
                'name' => $this->newRoomName,
                'capacity' => 1,
                'type' => 'room',
            ]);
        }

        $this->newRoomName = '';
        $this->showAddModal = false;
        
        $this->loadRooms();
    }

    public function deleteRoom($id)
    {
        Table::find($id)?->delete();
        $this->loadRooms();
    }

    public function render()
    {
        return view('livewire.admin.room-manager')->layout('components.layouts.app', ['title' => 'Hotel Room Manager']);
    }
}
