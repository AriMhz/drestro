<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Customers</h1>
            <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">Manage client relationships, loyalty discounts, and credit records.</p>
        </div>
        <button wire:click="toggleAddModal" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md active:scale-95 transition-all text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Customer
        </button>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between dark:bg-slate-900 dark:border-slate-800">
            <div class="flex items-center gap-2.5 text-emerald-600 dark:text-emerald-400 mb-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"></path></svg>
                </div>
                <span class="text-sm font-bold uppercase tracking-wider">To Receive</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-0.5">Total Receivables</span>
                <span class="text-2xl font-black text-slate-800 dark:text-white">Rs. {{ number_format($toReceive, 0) }}</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between dark:bg-slate-900 dark:border-slate-800">
            <div class="flex items-center gap-2.5 text-rose-500 dark:text-rose-450 mb-2">
                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"></path></svg>
                </div>
                <span class="text-sm font-bold uppercase tracking-wider">To Pay</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-0.5">Total Payables</span>
                <span class="text-2xl font-black text-slate-800 dark:text-white">Rs. {{ number_format($toPay, 0) }}</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between dark:bg-slate-900 dark:border-slate-800">
            <div class="flex items-center gap-2.5 text-blue-500 dark:text-blue-400 mb-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                </div>
                <span class="text-sm font-bold uppercase tracking-wider">Net To Receive</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-0.5">Balance Net</span>
                <span class="text-2xl font-black text-slate-800 dark:text-white">Rs. {{ number_format($netToReceive, 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Search & List -->
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 border-b border-slate-100 dark:border-slate-850">
            <div class="relative max-w-md w-full">
                <input wire:model.live.debounce.300ms="searchQuery" type="text" placeholder="Search customer by name, phone or email..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                <svg class="w-4.5 h-4.5 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        @if(session()->has('message'))
        <div class="mx-6 mt-4 p-4 bg-emerald-50 text-emerald-800 border border-emerald-100 rounded-xl text-sm font-semibold dark:bg-emerald-950/20 dark:text-emerald-450 dark:border-emerald-900/30">
            {{ session('message') }}
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider dark:bg-slate-950 dark:border-slate-850">
                        <th class="px-6 py-4">SN</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Phone Number</th>
                        <th class="px-6 py-4">DOB</th>
                        <th class="px-6 py-4">Loyalty Dis</th>
                        <th class="px-6 py-4">Due Amount</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                    @forelse($customers as $index => $c)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                          <div className="font-semibold text-slate-850 dark:text-white text-sm">{{ $c->name }}</div>
                          @if($c->group)
                          <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-600 rounded-md uppercase dark:bg-slate-800 dark:text-slate-400">{{ $c->group }}</span>
                          @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-450">{{ $c->email ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-650 dark:text-gray-300 font-semibold">{{ $c->phone ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-450">{{ $c->dob ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-450 font-bold">
                            @if($c->loyalty_discount > 0)
                            <span class="text-emerald-600 dark:text-emerald-450">{{ $c->loyalty_discount }}%</span>
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white">
                            @php $bal = $c->opening_balance + $c->due_amount; @endphp
                            @if($bal > 0)
                            <span class="{{ $c->balance_type === 'collect' ? 'text-emerald-600 dark:text-emerald-450' : 'text-rose-500' }}">
                                Rs. {{ number_format($bal, 0) }} 
                                <span class="text-[10px] uppercase font-bold">({{ $c->balance_type === 'collect' ? 'Dr' : 'Cr' }})</span>
                            </span>
                            @else
                            Rs. 0
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2.5">
                                <button wire:click="editCustomer({{ $c->id }})" class="p-1.5 text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors" title="Edit Customer">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button wire:click="deleteCustomer({{ $c->id }})" wire:confirm="Are you sure you want to delete this customer?" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-colors" title="Delete Customer">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-900">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            No customers found. Create a new customer above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col animate-in scale-in duration-300">
            <div class="px-6 py-4 border-b border-slate-150 dark:border-[#333333] flex justify-between items-center bg-slate-50 dark:bg-[#222222]">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">
                    {{ $editingCustomerId ? 'Edit Customer' : 'Add Customer' }}
                </h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-650 dark:hover:text-white p-1 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6 text-left">
                <!-- 1. Basic Customer Details -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider border-b pb-1 border-slate-100 dark:border-slate-850">1. Basic Customer Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Customer Full Name *</label>
                            <input wire:model="name" type="text" placeholder="Enter Customer Name" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                            @error('name') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Phone Number</label>
                            <input wire:model="phone" type="text" placeholder="e.g. +977 98XXXXXXX" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                            @error('phone') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Email</label>
                            <input wire:model="email" type="email" placeholder="Email Address" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                            @error('email') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Loyalty Discount (%)</label>
                            <input wire:model="loyaltyDiscount" type="number" step="0.01" placeholder="0.00 %" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                            @error('loyaltyDiscount') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Opening Balance Type</label>
                            <select wire:model="balanceType" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium">
                                <option value="collect">To Collect (Dr) ⬇</option>
                                <option value="pay">To Pay (Cr) ⬆</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Opening Balance Amount (Rs.)</label>
                            <input wire:model="openingBalance" type="number" placeholder="Rs. 0" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Date of Birth</label>
                            <input wire:model="dob" type="text" placeholder="YYYY-MM-DD" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Group / Segment</label>
                        <input wire:model="group" type="text" placeholder="e.g. VIP, Corporate, Regular" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                    </div>
                </div>

                <!-- 2. Billing & Credit Details -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-850">
                    <h4 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider border-b pb-1 border-slate-100 dark:border-slate-850">2. Billing & Credit Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Legal Name</label>
                            <input wire:model="legalName" type="text" placeholder="Company Legal Name" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Tax / VAT Number</label>
                            <input wire:model="taxNumber" type="text" placeholder="PAN or VAT ID" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Address</label>
                        <input wire:model="address" type="text" placeholder="Billing Address" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Credit Limit (Rs.)</label>
                            <input wire:model="creditLimit" type="number" placeholder="Rs. 0" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Credit Term (Days)</label>
                            <input wire:model="creditTerm" type="number" placeholder="Days to pay" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>
                </div>

                <!-- 3. Dining Preferences -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-850">
                    <h4 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider border-b pb-1 border-slate-100 dark:border-slate-850">3. Dining Preferences</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Favorite Dish</label>
                            <input wire:model="favoriteDish" type="text" placeholder="e.g. Momo, Pizza" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Preferred Seating</label>
                            <input wire:model="preferredSeating" type="text" placeholder="e.g. Window, Balcony" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Dietary Type</label>
                            <input wire:model="dietaryType" type="text" placeholder="e.g. Vegetarian, Vegan" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Allergies & Restrictions</label>
                            <input wire:model="allergies" type="text" placeholder="e.g. Peanuts, Gluten" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Preferred Visiting Time</label>
                            <input wire:model="preferredVisitingTime" type="text" placeholder="e.g. 7 PM - 9 PM" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-150 dark:border-[#333333] flex justify-end gap-3 bg-slate-50 dark:bg-[#222222]">
                <button wire:click="resetForm" type="button" class="px-5 py-2.5 border border-slate-200 dark:border-[#333333] hover:bg-slate-100 dark:hover:bg-[#111111] text-slate-650 dark:text-white text-sm font-bold rounded-xl transition-colors">
                    Reset
                </button>
                <button wire:click="saveCustomer" type="button" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-colors shadow-md">
                    Save Customer
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
