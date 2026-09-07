<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CustomerManager extends Component
{
    // Search & KPI
    public $searchQuery = '';
    public $toReceive = 0;
    public $toPay = 0;
    public $netToReceive = 0;

    // Modals
    public $showModal = false;
    public $showDetails = false;
    public $editingCustomerId = null;

    // Basic Details
    public $name = '';
    public $phone = '';
    public $email = '';
    public $loyaltyDiscount = 0;
    public $openingBalance = 0;
    public $balanceType = 'collect'; // 'collect' (To Receive / Dr) or 'pay' (To Pay / Cr)
    public $dob = '';
    public $group = '';

    // Billing & Credit Details
    public $legalName = '';
    public $taxNumber = '';
    public $address = '';
    public $creditLimit = 0;
    public $creditTerm = 0;

    // Dining Preferences
    public $favoriteDish = '';
    public $preferredSeating = '';
    public $dietaryType = '';
    public $allergies = '';
    public $preferredVisitingTime = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'loyaltyDiscount' => 'nullable|numeric|min:0|max:100',
        'openingBalance' => 'nullable|numeric|min:0',
        'balanceType' => 'required|in:collect,pay',
        'dob' => 'nullable|string',
        'group' => 'nullable|string',
        'legalName' => 'nullable|string|max:255',
        'taxNumber' => 'nullable|string|max:100',
        'address' => 'nullable|string|max:255',
        'creditLimit' => 'nullable|numeric|min:0',
        'creditTerm' => 'nullable|integer|min:0',
        'favoriteDish' => 'nullable|string',
        'preferredSeating' => 'nullable|string',
        'dietaryType' => 'nullable|string',
        'allergies' => 'nullable|string',
        'preferredVisitingTime' => 'nullable|string',
    ];

    public function mount()
    {
        // Self-healing migration for customers table
        if (!Schema::hasTable('customers')) {
            try {
                Schema::create('customers', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id');
                    $table->string('name');
                    $table->string('phone')->nullable();
                    $table->string('email')->nullable();
                    $table->float('loyalty_discount')->default(0);
                    $table->float('opening_balance')->default(0);
                    $table->string('balance_type')->default('collect');
                    $table->string('dob')->nullable();
                    $table->string('group')->nullable();
                    $table->string('legal_name')->nullable();
                    $table->string('tax_number')->nullable();
                    $table->string('address')->nullable();
                    $table->float('credit_limit')->default(0);
                    $table->integer('credit_term')->default(0);
                    $table->string('favorite_dish')->nullable();
                    $table->string('preferred_seating')->nullable();
                    $table->string('dietary_type')->nullable();
                    $table->string('allergies')->nullable();
                    $table->string('preferred_visiting_time')->nullable();
                    $table->float('due_amount')->default(0);
                    $table->timestamps();
                });
            } catch (\Exception $e) {
                // Ignore if fails
            }
        }
        
        $this->updateKPIs();
    }

    public function updateKPIs()
    {
        $restaurant = current_restaurant();
        if (!$restaurant) return;

        $customers = DB::table('customers')->where('restaurant_id', $restaurant->id)->get();

        $this->toReceive = 0;
        $this->toPay = 0;

        foreach ($customers as $c) {
            $amt = $c->opening_balance + $c->due_amount;
            if ($c->balance_type === 'collect') {
                $this->toReceive += $amt;
            } else {
                $this->toPay += $amt;
            }
        }

        $this->netToReceive = $this->toReceive - $this->toPay;
    }

    public function toggleAddModal()
    {
        $this->resetForm();
        $this->editingCustomerId = null;
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->loyaltyDiscount = 0;
        $this->openingBalance = 0;
        $this->balanceType = 'collect';
        $this->dob = '';
        $this->group = '';
        $this->legalName = '';
        $this->taxNumber = '';
        $this->address = '';
        $this->creditLimit = 0;
        $this->creditTerm = 0;
        $this->favoriteDish = '';
        $this->preferredSeating = '';
        $this->dietaryType = '';
        $this->allergies = '';
        $this->preferredVisitingTime = '';
    }

    public function editCustomer($id)
    {
        $c = DB::table('customers')->where('id', $id)->first();
        if (!$c) return;

        $this->editingCustomerId = $c->id;
        $this->name = $c->name;
        $this->phone = $c->phone ?? '';
        $this->email = $c->email ?? '';
        $this->loyaltyDiscount = $c->loyalty_discount;
        $this->openingBalance = $c->opening_balance;
        $this->balanceType = $c->balance_type;
        $this->dob = $c->dob ?? '';
        $this->group = $c->group ?? '';
        $this->legalName = $c->legal_name ?? '';
        $this->taxNumber = $c->tax_number ?? '';
        $this->address = $c->address ?? '';
        $this->creditLimit = $c->credit_limit;
        $this->creditTerm = $c->credit_term;
        $this->favoriteDish = $c->favorite_dish ?? '';
        $this->preferredSeating = $c->preferred_seating ?? '';
        $this->dietaryType = $c->dietary_type ?? '';
        $this->allergies = $c->allergies ?? '';
        $this->preferredVisitingTime = $c->preferred_visiting_time ?? '';

        $this->showModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        $restaurant = current_restaurant();
        if (!$restaurant) return;

        $data = [
            'restaurant_id' => $restaurant->id,
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'loyalty_discount' => floatval($this->loyaltyDiscount),
            'opening_balance' => floatval($this->openingBalance),
            'balance_type' => $this->balanceType,
            'dob' => $this->dob ?: null,
            'group' => $this->group ?: null,
            'legal_name' => $this->legalName ?: null,
            'tax_number' => $this->taxNumber ?: null,
            'address' => $this->address ?: null,
            'credit_limit' => floatval($this->creditLimit),
            'credit_term' => intval($this->creditTerm),
            'favorite_dish' => $this->favoriteDish ?: null,
            'preferred_seating' => $this->preferredSeating ?: null,
            'dietary_type' => $this->dietaryType ?: null,
            'allergies' => $this->allergies ?: null,
            'preferred_visiting_time' => $this->preferredVisitingTime ?: null,
            'updated_at' => now(),
        ];

        if ($this->editingCustomerId) {
            DB::table('customers')->where('id', $this->editingCustomerId)->update($data);
        } else {
            $data['due_amount'] = 0;
            $data['created_at'] = now();
            DB::table('customers')->insert($data);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->updateKPIs();
        session()->flash('message', 'Customer saved successfully!');
    }

    public function deleteCustomer($id)
    {
        DB::table('customers')->where('id', $id)->delete();
        $this->updateKPIs();
        session()->flash('message', 'Customer deleted successfully!');
    }

    public function render()
    {
        $restaurant = current_restaurant();
        $customers = collect();

        if ($restaurant) {
            $query = DB::table('customers')->where('restaurant_id', $restaurant->id);

            if (!empty($this->searchQuery)) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('phone', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
                });
            }

            $customers = $query->orderBy('created_at', 'desc')->get();
        }

        return view('livewire.admin.customer-manager', [
            'customers' => $customers
        ])->layout('components.layouts.app', ['title' => 'Customers']);
    }
}
