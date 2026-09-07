<?php

namespace App\Livewire\Admin;

use App\Models\Restaurant;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class SupportTickets extends Component
{
    public $title = '';
    public $description = '';
    public $priority = 'MEDIUM';
    public $grantAccess = false;
    public $showCreateModal = false;

    public function createTicket()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,URGENT',
        ]);

        $restaurant = current_restaurant();

        $ticket = SupportTicket::create([
            'restaurant_id' => $restaurant->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => 'OPEN',
        ]);

        try {
            // Send API Request to Central drestro-web Server
            // Get the license key from the local restaurant database
            $licenseKey = $restaurant->license_key;
            if ($licenseKey) {
                \Illuminate\Support\Facades\Http::post('https://drestro.com/api/support/tickets', [
                    'licenseKey' => $licenseKey,
                    'title' => $this->title,
                    'description' => $this->description,
                    'priority' => $this->priority,
                    'grantAccess' => $this->grantAccess ?? false,
                ]);
            }

            // Send email to Support Team as fallback
            Mail::raw("New Support Ticket from {$restaurant->name}!\n\nTitle: {$this->title}\nPriority: {$this->priority}\nGrant Access: " . ($this->grantAccess ? 'Yes' : 'No') . "\n\nDescription:\n{$this->description}", function ($message) use ($restaurant) {
                $message->to('support@drestro.com')
                        ->subject("New Support Ticket: {$this->title} - {$restaurant->name}");
            });
        } catch (\Exception $e) {
            // Ignore errors in development
        }

        $this->reset(['title', 'description', 'priority', 'grantAccess', 'showCreateModal']);
        session()->flash('success', 'Ticket submitted successfully! Our support team will get back to you shortly.');
    }

    public $editTicketId = null;
    public $showEditModal = false;
    public $editTitleMatch = '';

    public function editTicket($id)
    {
        $ticket = SupportTicket::find($id);
        if ($ticket && $ticket->status === 'OPEN') {
            $this->editTicketId = $ticket->id;
            $this->title = $ticket->title;
            $this->editTitleMatch = $ticket->title; // Used to identify the ticket on the central server
            $this->description = $ticket->description;
            $this->priority = $ticket->priority;
            $this->showEditModal = true;
        }
    }

    public function updateTicket()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,URGENT',
        ]);

        $ticket = SupportTicket::find($this->editTicketId);
        if ($ticket && $ticket->status === 'OPEN') {
            $ticket->update([
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
            ]);

            try {
                $restaurant = current_restaurant();
                $licenseKey = $restaurant ? $restaurant->license_key : null;
                if ($licenseKey) {
                    \Illuminate\Support\Facades\Http::timeout(5)->put('https://drestro.com/api/support/tickets?titleMatch=' . urlencode($this->editTitleMatch), [
                        'licenseKey' => $licenseKey,
                        'title' => $this->title,
                        'description' => $this->description,
                        'priority' => $this->priority,
                    ]);
                }
            } catch (\Exception $e) {}

            $this->reset(['title', 'description', 'priority', 'showEditModal', 'editTicketId', 'editTitleMatch']);
            session()->flash('success', 'Ticket updated successfully!');
        }
    }

    public function cancelTicket($id)
    {
        $ticket = SupportTicket::find($id);
        if ($ticket && $ticket->status === 'OPEN') {
            $ticket->update(['status' => 'CLOSED']);

            try {
                $restaurant = current_restaurant();
                $licenseKey = $restaurant ? $restaurant->license_key : null;
                if ($licenseKey) {
                    \Illuminate\Support\Facades\Http::timeout(5)->put('https://drestro.com/api/support/tickets?titleMatch=' . urlencode($ticket->title), [
                        'licenseKey' => $licenseKey,
                        'status' => 'CLOSED',
                    ]);
                }
            } catch (\Exception $e) {}

            session()->flash('success', 'Ticket cancelled successfully!');
        }
    }

    public function deleteTicket($id)
    {
        $ticket = SupportTicket::find($id);
        if ($ticket) {
            $titleMatch = $ticket->title;
            $ticket->delete();

            try {
                $restaurant = current_restaurant();
                $licenseKey = $restaurant ? $restaurant->license_key : null;
                if ($licenseKey) {
                    \Illuminate\Support\Facades\Http::timeout(5)->delete('https://drestro.com/api/support/tickets?titleMatch=' . urlencode($titleMatch) . '&licenseKey=' . urlencode($licenseKey));
                }
            } catch (\Exception $e) {}

            session()->flash('success', 'Ticket deleted successfully!');
        }
    }

    public function render()
    {
        $restaurant = current_restaurant();

        // Sync statuses from central server
        try {
            $licenseKey = $restaurant ? $restaurant->license_key : null;
            if ($licenseKey) {
                $response = \Illuminate\Support\Facades\Http::timeout(3)->get('https://drestro.com/api/support/tickets', [
                    'licenseKey' => $licenseKey
                ]);
                if ($response->successful() && isset($response['tickets'])) {
                    foreach ($response['tickets'] as $remoteTicket) {
                        // Assuming the central server returns matching titles and dates, or we just trust the latest ones.
                        // Ideally we would sync by a global UUID. Since we don't have one, we'll try to match by title and created_at
                        // and update the status.
                        $localTicket = SupportTicket::where('title', $remoteTicket['title'])
                                        ->where('restaurant_id', $restaurant->id)
                                        ->first();
                        if ($localTicket && $localTicket->status !== $remoteTicket['status']) {
                            $localTicket->update(['status' => $remoteTicket['status']]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore connection errors
        }

        $tickets = $restaurant ? SupportTicket::where('restaurant_id', $restaurant->id)->latest()->get() : collect();

        return view('livewire.admin.support-tickets', [
            'tickets' => $tickets
        ])->layout('components.layouts.app', ['title' => 'Support']);
    }
}
