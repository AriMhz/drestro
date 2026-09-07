<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Table;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $restaurant;
    public $period = 'today'; // today, week, month, custom
    public $customDate;
    public $customStartDate;
    public $customEndDate;
    
    // Backup reminder
    public $showBackupReminder = false;
    public $usbBackupResult = '';

    public function mount()
    {
        $user = auth()->user();
        if ($user && $user->role !== 'super_admin' && !empty($user->allowed_pages)) {
            if (!in_array('dashboard', $user->allowed_pages)) {
                $allowed = $user->allowed_pages;
                
                $routeMap = [
                    'take_order' => 'staff.take-order',
                    'take_room_service' => 'staff.room-service',
                    'hotel_reception' => 'staff.hotel-reception',
                    'cashier_panel' => 'staff.cashier',
                    'waiter_dashboard' => 'staff.waiter',
                    'kitchen_display' => 'staff.kitchen',
                    'bar_display' => 'staff.bar',
                    'menu_manager' => 'admin.menus',
                    'restaurant_tables' => 'admin.tables',
                    'hotel_room_manager' => 'admin.rooms',
                    'inventory' => 'admin.inventory',
                    'staff_roles' => 'admin.staff',
                    'reports' => 'admin.reports',
                    'license' => 'admin.license',
                    'settings' => 'admin.settings',
                ];

                foreach ($routeMap as $page => $routeName) {
                    if (in_array($page, $allowed)) {
                        return redirect()->route($routeName);
                    }
                }
                
                auth()->logout();
                return redirect()->route('login');
            }
        }

        // Self-healing migration for last_backup_reminder_at column
        if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', 'last_backup_reminder_at')) {
            try {
                \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->timestamp('last_backup_reminder_at')->nullable();
                });
            } catch (\Exception $e) {
                // Silently skip
            }
        }

        $this->restaurant = current_restaurant();
        $this->customDate = date('Y-m-d');
        $this->customStartDate = date('Y-m-d', strtotime('-6 days'));
        $this->customEndDate = date('Y-m-d');

        // Backup reminder disabled for cloud-hosted deployments.
        // USB backup only makes sense for offline/local POS installations.
        // To re-enable for offline use, uncomment the block below:
        // if ($this->restaurant) {
        //     $lastReminder = $this->restaurant->last_backup_reminder_at;
        //     if (!$lastReminder || Carbon::parse($lastReminder)->diffInDays(Carbon::now()) >= 15) {
        //         $this->showBackupReminder = true;
        //     }
        // }
    }

    public function setPeriod($period)
    {
        $this->period = $period;
    }

    // --- Computed Properties ---

    public function getStartDateProperty()
    {
        return match($this->period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'custom' => Carbon::parse($this->customStartDate)->startOfDay(),
            default => Carbon::today(),
        };
    }

    public function getEndDateProperty()
    {
        return match($this->period) {
            'today' => Carbon::today()->endOfDay(),
            'week' => Carbon::now()->endOfWeek(),
            'month' => Carbon::now()->endOfMonth(),
            'custom' => Carbon::parse($this->customEndDate)->endOfDay(),
            default => Carbon::today()->endOfDay(),
        };
    }

    public function getTotalOrdersProperty()
    {
        if (!$this->restaurant) return 0;
        return Order::where('restaurant_id', $this->restaurant->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();
    }

    public function getTotalRevenueProperty()
    {
        if (!$this->restaurant) return 0;
        return \App\Models\Invoice::whereHas('order', function($q) {
            $q->where('restaurant_id', $this->restaurant->id)
              ->whereBetween('created_at', [$this->startDate, $this->endDate])
              ->where('status', 'completed');
        })->sum('grand_total');
    }

    public function getPendingOrdersProperty()
    {
        if (!$this->restaurant) return 0;
        return Order::where('restaurant_id', $this->restaurant->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
    }

    public function getActiveTablesProperty()
    {
        if (!$this->restaurant) return ['active' => 0, 'total' => 0];
        $total = Table::where('restaurant_id', $this->restaurant->id)->count();
        $activeTableIds = Order::where('restaurant_id', $this->restaurant->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('table_id')
            ->pluck('table_id')
            ->unique()
            ->count();
        return ['active' => $activeTableIds, 'total' => $total];
    }

    public function getMenuItemCountProperty()
    {
        if (!$this->restaurant) return 0;
        return MenuItem::where('restaurant_id', $this->restaurant->id)->count();
    }

    public function getRecentOrdersProperty()
    {
        if (!$this->restaurant) return collect();
        return Order::with('table')
            ->where('restaurant_id', $this->restaurant->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function dismissBackupReminder()
    {
        if ($this->restaurant) {
            $this->restaurant->update(['last_backup_reminder_at' => Carbon::now()]);
        }
        $this->showBackupReminder = false;
        session()->flash('info', 'Backup reminder postponed for 15 days.');
    }

    public function backupToUsbFromDashboard()
    {
        $this->usbBackupResult = '';
        try {
            $drives = \App\Helpers\BackupHelper::backupToUsb();
            if (!empty($drives)) {
                if ($this->restaurant) {
                    $this->restaurant->update(['last_backup_reminder_at' => Carbon::now()]);
                }
                $this->showBackupReminder = false;
                $drivePaths = array_map(fn($d) => $d . '\\DrestroPOS_Backups', $drives);
                session()->flash('success', 'Database successfully backed up to USB drive(s): ' . implode(', ', $drivePaths));
            } else {
                session()->flash('error', 'No connected writable USB Pendrive detected. Please insert a USB drive and try again.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'USB Backup failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->layout('components.layouts.app', ['title' => 'Analytics Dashboard']);
    }
}
