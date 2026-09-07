import re

with open('app/Livewire/Admin/StaffManager.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the saveStaff method with inviteStaff
invite_method = r"""
    public function inviteStaff()
    {
        if (auth()->user()->role !== 'super_admin') return;

        $this->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
        ]);

        if ($this->role === 'super_admin' && auth()->user()->role !== 'super_admin') {
            $this->role = 'admin';
        }

        $limit = $this->activeLicense['limits']['users'] ?? 0;
        if ($limit > 0 && auth()->user()->role !== 'super_admin' && User::count() >= $limit) {
            session()->flash('error', "Limit reached: Your plan allows a maximum of {$limit} users. Please upgrade to add more.");
            return;
        }

        $token = \Illuminate\Support\Str::random(60);
        
        $user = User::create([
            'name' => 'Pending Invite',
            'first_name' => 'Pending',
            'last_name' => 'Invite',
            'email' => $this->email,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)), // Random temp password
            'role' => $this->role,
            'remember_token' => $token, // Store the invite token here
            'allowed_pages' => !empty($this->allowedPages) ? $this->allowedPages : null,
        ]);

        $inviteUrl = url('/invite/' . $token);
        
        $this->closeModal();
        $this->loadData();
        
        // Show success with link
        session()->flash('invite_link', $inviteUrl);
        session()->flash('success', "Invitation created successfully!");
    }
"""

content = re.sub(r'public function saveStaff\(\).*?public function deleteStaff', invite_method + '\n\n    public function deleteStaff', content, flags=re.DOTALL)

with open('app/Livewire/Admin/StaffManager.php', 'w', encoding='utf-8') as f:
    f.write(content)
