<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffManager extends Component
{
    public $staffMembers;
    public $activeLicense;
    
    // Form fields
    public $staffId;
    public $firstName;
    public $lastName;
    public $email;
    public $altEmail;
    public $staffAddress;
    public $staffPhone;
    public $username;
    public $password;
    public $passwordConfirmation;
    public $pin;
    public $role;
    public $description;
    public $allowedPages = [];

    public $showModal = false;
    public $isEditing = false;

    // All available pages for access control
    public $allPages = [
        'dashboard' => 'Dashboard',
        'menu_manager' => 'Menu Manager',
        'restaurant_tables' => 'Restaurant Tables',
        'hotel_room_manager' => 'Hotel Room Manager',
        'inventory' => 'Inventory',
        'take_order' => 'Take Restaurant Order',
        'take_room_service' => 'Take Room Service',
        'hotel_reception' => 'Hotel Reception Cashier',
        'cashier_panel' => 'Cashier Dashboard',
        'waiter_dashboard' => 'Waiter Dashboard',
        'kitchen_display' => 'Kitchen Display',
        'bar_display' => 'Bar Display',
        'staff_roles' => 'Staff & Roles',
        'reports' => 'Reports & Analytics',
        'license' => 'License & Billing',
        'settings' => 'Settings',
    ];

    // Available roles (super_admin only visible to super_admins)
    public function getAvailableRolesProperty()
    {
        $roles = ['admin', 'manager', 'waiter', 'cashier', 'hotel', 'receptionist', 'kitchen', 'bar'];
        if (auth()->user()->role === 'super_admin') {
            array_unshift($roles, 'super_admin');
        }
        return $roles;
    }

    public function mount()
    {
        $this->activeLicense = current_restaurant()?->license_data ?? \App\Services\LicenseManager::getFreeLimits();
        $this->loadData();
    }

    /**
     * Livewire lifecycle hook: sanitize staffPhone on every update.
     */
    public function updatedStaffPhone($value)
    {
        $this->staffPhone = substr(preg_replace('/[^0-9]/', '', $value), 0, 10);
    }

    public function loadData()
    {
        $currentUser = auth()->user();
        if ($currentUser) {
            $this->staffMembers = User::where('restaurant_id', $currentUser->restaurant_id)->get();
        } else {
            $this->staffMembers = collect();
        }
    }

    public function openModal()
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin') return;
        $this->resetFields();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function editStaff($id)
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin') return;
        $this->resetFields();
        $staff = User::find($id);
        if ($staff) {
            // Non-super_admins can't edit super_admin users
            if ($staff->role === 'super_admin' && auth()->user()->role !== 'super_admin') {
                return;
            }

            $this->staffId = $staff->id;
            $this->firstName = $staff->first_name;
            $this->lastName = $staff->last_name;
            $this->email = $staff->email;
            $this->altEmail = $staff->alt_email;
            $this->staffAddress = $staff->address;
            $this->staffPhone = $staff->phone;
            $this->username = $staff->username;
            $this->pin = $staff->pin;
            $this->role = $staff->role;
            $this->description = $staff->description;
            $this->allowedPages = $staff->allowed_pages ?? [];
            
            $this->isEditing = true;
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function resetFields()
    {
        $this->staffId = null;
        $this->firstName = '';
        $this->lastName = '';
        $this->email = '';
        $this->altEmail = '';
        $this->staffAddress = '';
        $this->staffPhone = '';
        $this->username = '';
        $this->password = '';
        $this->passwordConfirmation = '';
        $this->pin = '';
        $this->role = '';
        $this->description = '';
        $this->allowedPages = [];
    }

    public function generatePin()
    {
        $this->pin = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function togglePage($page)
    {
        if (in_array($page, $this->allowedPages)) {
            $this->allowedPages = array_values(array_diff($this->allowedPages, [$page]));
        } else {
            $this->allowedPages[] = $page;
        }
    }

    
    public function updatedRole($value)
    {
        $presets = [
            'super_admin' => ['dashboard', 'menu_manager', 'restaurant_tables', 'hotel_room_manager', 'inventory', 'take_order', 'take_room_service', 'hotel_reception', 'cashier_panel', 'waiter_dashboard', 'kitchen_display', 'bar_display', 'staff_roles', 'reports', 'license', 'settings'],
            'admin' => ['dashboard', 'menu_manager', 'restaurant_tables', 'hotel_room_manager', 'inventory', 'take_order', 'take_room_service', 'hotel_reception', 'cashier_panel', 'waiter_dashboard', 'kitchen_display', 'bar_display', 'staff_roles', 'reports', 'license', 'settings'],
            'manager' => ['dashboard', 'menu_manager', 'restaurant_tables', 'hotel_room_manager', 'inventory', 'take_order', 'take_room_service', 'hotel_reception', 'cashier_panel', 'waiter_dashboard', 'kitchen_display', 'bar_display', 'staff_roles', 'reports'],
            'waiter' => ['restaurant_tables', 'take_order', 'waiter_dashboard'],
            'cashier' => ['cashier_panel', 'take_order', 'restaurant_tables'],
            'hotel' => ['hotel_room_manager', 'take_room_service', 'hotel_reception'],
            'receptionist' => ['hotel_room_manager', 'hotel_reception', 'take_room_service'],
            'kitchen' => ['kitchen_display'],
            'bar' => ['bar_display']
        ];
        
        if (array_key_exists($value, $presets)) {
            $this->allowedPages = $presets[$value];
        } else {
            $this->allowedPages = [];
        }
    }

    public function selectAllPages()
    {
        $this->allowedPages = array_keys($this->allPages);
    }

    public function deselectAllPages()
    {
        $this->allowedPages = [];
    }

    public function saveStaff()
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin') return;

        $restaurant = current_restaurant();
        $userCount = User::where('restaurant_id', $restaurant->id)->count();

        $restaurant = current_restaurant();
        $userCount = User::where('restaurant_id', $restaurant->id)->count();

        $existingUserWithEmail = User::withoutGlobalScopes()->where('email', $this->email)->first();
        $targetStaffId = $this->staffId ?? ($existingUserWithEmail && $existingUserWithEmail->restaurant_id == $restaurant->id ? $existingUserWithEmail->id : null);

        if (!$this->isEditing && !$targetStaffId) {
            $isMultiUserAllowed = $restaurant->feature_multi_user ?? true;
            if (!$isMultiUserAllowed || ($restaurant->license_data['limits']['users'] ?? 0) === 1 && $userCount >= 1) {
                session()->flash('error', 'Free plan is limited to 1 user account only. Please upgrade your package to add more staff.');
                return;
            }
        }

        $rules = [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($targetStaffId ?? 'NULL'),
            'altEmail' => 'nullable|email',
            'staffAddress' => 'nullable|string|max:500',
            'staffPhone' => 'required|string|size:10',
            'username' => 'nullable|string|max:100|unique:users,username,' . ($targetStaffId ?? 'NULL'),
            'pin' => [
                'nullable',
                'string',
                'max:4',
                \Illuminate\Validation\Rule::unique('users', 'pin')
                    ->ignore($targetStaffId)
                    ->where('restaurant_id', auth()->user()->restaurant_id)
            ],
            'role' => 'required|string',
            'description' => 'nullable|string|max:500',
        ];

        if (!$this->isEditing) {
            $rules['password'] = 'required|min:6';
            $rules['passwordConfirmation'] = 'required|same:password';
        } else {
            if (!empty($this->password)) {
                $rules['password'] = 'min:6';
                $rules['passwordConfirmation'] = 'required|same:password';
            }
        }

        $this->validate($rules, [
            'passwordConfirmation.same' => 'Passwords do not match.',
            'passwordConfirmation.required' => 'Please re-enter the password.',
        ]);

        // Prevent non-super_admins from creating super_admin users
        if ($this->role === 'super_admin' && auth()->user()->role !== 'super_admin') {
            $this->role = 'admin';
        }

        $fullName = trim($this->firstName . ' ' . $this->lastName);

        $data = [
            'name' => $fullName,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'alt_email' => $this->altEmail ?: null,
            'address' => $this->staffAddress ?: null,
            'phone' => $this->staffPhone ?: null,
            'username' => $this->username ?: null,
            'pin' => $this->pin,
            'role' => $this->role,
            'description' => $this->description ?: null,
            'allowed_pages' => !empty($this->allowedPages) ? $this->allowedPages : null,
        ];

        $passwordChanged = false;
        if (!empty($this->password)) {
            $data['password'] = $this->password;
            $passwordChanged = true;
        }

        if ($this->isEditing || $targetStaffId) {
            $staff = User::find($targetStaffId ?? $this->staffId);
            if ($staff) {
                $staff->update($data);
            }

            if ($passwordChanged) {
                // Send email notification to user
                $recipient = $this->email;
                $altRecipient = $this->altEmail;
                $name = $this->firstName;
                try {
                    $html = "
                    <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px;'>
                        <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                            <h2 style='color: #1e293b;'>Password Updated</h2>
                            <p style='color: #475569; font-size: 16px;'>Hello <strong>{$name}</strong>,</p>
                            <p style='color: #475569; font-size: 16px;'>Your staff login password has been successfully updated by the administrator.</p>
                            <p style='color: #475569; font-size: 16px;'>If you did not request or authorize this change, please contact your administrator immediately.</p>
                            <p style='margin-top: 30px; font-size: 12px; color: #94a3b8;'>This is an automated notification from DRestro POS.</p>
                        </div>
                    </div>
                    ";
                    \Illuminate\Support\Facades\Mail::html($html, function ($message) use ($recipient, $altRecipient) {
                        $message->to($recipient)
                                ->subject("Your DRestro Account Password Was Updated");
                        if ($altRecipient) {
                            $message->cc($altRecipient);
                        }
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send password update email: " . $e->getMessage());
                }
            }
        } else {
            // Check limits for new users
            $limit = $this->activeLicense['limits']['users'] ?? 0;
            $currentUserCount = User::withoutGlobalScopes()->where('restaurant_id', auth()->user()->restaurant_id)->count();
            if ($limit > 0 && $currentUserCount >= $limit) {
                session()->flash('error', "Limit reached: Your plan allows a maximum of {$limit} users. Please upgrade to add more.");
                return;
            }
            $data['restaurant_id'] = auth()->user()->restaurant_id;
            User::create($data);
        }

        $this->closeModal();
        $this->loadData();
    }

    public function inviteStaff()
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) return;

        $this->validate([
            'email' => 'required|email',
            'role' => 'required|string',
        ]);

        if ($this->role === 'super_admin' && auth()->user()->role !== 'super_admin') {
            $this->role = 'admin';
        }

        $restaurantId = auth()->user()->restaurant_id;
        $existingUser = User::withoutGlobalScopes()->where('email', $this->email)->first();

        if ($existingUser) {
            // If user already belongs to this restaurant or is unassigned
            if ($existingUser->restaurant_id == $restaurantId || empty($existingUser->restaurant_id)) {
                $token = \Illuminate\Support\Str::random(60);
                $existingUser->update([
                    'role' => $this->role,
                    'restaurant_id' => $restaurantId,
                    'allowed_pages' => !empty($this->allowedPages) ? $this->allowedPages : null,
                    'remember_token' => $token,
                ]);

                $inviteUrl = url('/invite/' . $token);
                $this->sendInviteEmail($this->email, $this->role, $inviteUrl);

                $this->closeModal();
                $this->loadData();
                session()->flash('invite_link', $inviteUrl);
                session()->flash('success', "✅ Staff member found ({$existingUser->email}). Role & access updated, and fresh invitation link generated!");
                return;
            } else {
                $this->addError('email', 'This email is already registered to another restaurant account. Please use a unique email.');
                return;
            }
        }

        $limit = $this->activeLicense['limits']['users'] ?? 0;
        $currentUserCount = User::withoutGlobalScopes()->where('restaurant_id', $restaurantId)->count();
        if ($limit > 0 && $currentUserCount >= $limit) {
            session()->flash('error', "Limit reached: Your plan allows a maximum of {$limit} users. Please upgrade to add more.");
            return;
        }

        $token = \Illuminate\Support\Str::random(60);
        
        $user = User::create([
            'name' => 'Pending Invite',
            'first_name' => 'Pending',
            'last_name' => 'Invite',
            'email' => $this->email,
            'password' => \Illuminate\Support\Str::random(16),
            'role' => $this->role,
            'restaurant_id' => $restaurantId,
            'allowed_pages' => !empty($this->allowedPages) ? $this->allowedPages : null,
            'remember_token' => $token,
        ]);

        $inviteUrl = url('/invite/' . $token);
        $this->sendInviteEmail($this->email, $this->role, $inviteUrl);

        $this->closeModal();
        $this->loadData();
        
        session()->flash('invite_link', $inviteUrl);
        session()->flash('success', "Invitation created successfully!");
    }

    private function sendInviteEmail($recipientEmail, $role, $inviteUrl)
    {
        try {
            $restaurant = auth()->user()->restaurant;
            $restaurantName = $restaurant ? $restaurant->name : 'DRestro POS';
            $roleName = ucfirst(str_replace('_', ' ', $role));
            
            $html = "
            <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                    <h2 style='color: #1e293b;'>You're Invited!</h2>
                    <p style='color: #475569; font-size: 16px;'>You have been invited to join <strong>{$restaurantName}</strong> as a <strong>{$roleName}</strong>.</p>
                    <p style='color: #475569; font-size: 16px;'>Click the button below to accept your invitation and securely set up your account.</p>
                    
                    <a href='{$inviteUrl}' style='display: inline-block; background-color: #10b981; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; margin-top: 20px;'>Accept Invitation</a>
                    
                    <p style='margin-top: 30px; font-size: 12px; color: #94a3b8;'>If you didn't expect this invitation, you can safely ignore this email.</p>
                </div>
            </div>
            ";
            
            \Illuminate\Support\Facades\Mail::html($html, function ($message) use ($recipientEmail, $restaurantName) {
                $message->to($recipientEmail)
                        ->subject("You're invited to join {$restaurantName}!");
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send invitation email: " . $e->getMessage());
        }
    }

    public function deleteStaff($id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) return;

        $staff = User::find($id);
        if ($staff && $staff->id !== auth()->id()) {
            $staff->delete();
            $this->loadData();
            session()->flash('success', "Staff member removed successfully.");
        }
    }

    public function render()
    {
        return view('livewire.admin.staff-manager')->layout('components.layouts.app', ['title' => 'Staff & Roles']);
    }
}
