<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class FinanceManager extends Component
{
    // Navigation / Tab
    public $activeView = 'list'; // 'list', 'daybook', 'transactions', 'sales_purchase', 'income_expenses', 'payments', 'cash_banks', 'reports'

    // Daybook Filter & Auto-Close Properties
    public $daybookDate;
    public $daybookPeriod = 'today'; // 'today', 'yesterday', 'custom'

    // Modals & Sub-forms
    public $showTxModal = false;
    public $showAccountModal = false;
    public $showSalesInvoiceModal = false;

    // Transaction Fields (Daybook / Expense / Income)
    public $txId = null;
    public $txType = 'expense'; // 'income', 'expense', 'purchase'
    public $txAmount = 0;
    public $txCategory = 'Raw Materials';
    public $txDescription = '';
    public $txRemarks = '';
    public $txAccountId = null;
    public $txPartyType = 'supplier'; // 'supplier', 'staff', 'customer'
    public $txPartyName = '';
    public $txPaymentStatus = 'paid'; // 'paid', 'unpaid'
    public $txReferenceNumber = '';
    public $txDate = '';

    // Account Fields (Cash & Banks)
    public $accountId = null;
    public $accountName = '';
    public $accountType = 'bank'; // 'bank', 'cash', 'wallet', 'personal', 'loan'
    public $bankName = '';
    public $accountNumber = '';
    public $accountBalance = 0;
    public $accountDescription = '';

    // Sales Invoice Fields
    public $invoiceCustomerId = null;
    public $invoiceTxnDate = '';
    public $invoiceSalesStaff = '';
    public $invoiceRemarks = '';
    public $invoicePaymentMode = 'cash'; // 'cash', 'card', 'nepal_pay', 'fonepay', 'bank_transfer'
    public $invoicePaymentStatus = 'paid'; // 'paid', 'unpaid'
    public $invoiceItems = []; // Array of ['name' => '', 'qty' => 1, 'rate' => 0, 'amount' => 0]
    public $invoiceTotal = 0;

    protected $rules = [
        'txType' => 'required|in:income,expense,purchase',
        'txAmount' => 'required|numeric|min:0.01',
        'txCategory' => 'required|string',
        'txDescription' => 'nullable|string',
        'txRemarks' => 'nullable|string',
        'txAccountId' => 'required|integer',
        'txPartyType' => 'required|in:supplier,staff,customer',
        'txPartyName' => 'nullable|string',
        'txPaymentStatus' => 'required|in:paid,unpaid',
        'txReferenceNumber' => 'nullable|string',
        'txDate' => 'required|date',
    ];

    public function mount()
    {
        $this->daybookDate = date('Y-m-d');
        $rId = current_restaurant() ? current_restaurant()->id : 1;

        // Self-healing migration for daybook_sessions table
        if (!Schema::hasTable('daybook_sessions')) {
            try {
                Schema::create('daybook_sessions', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id');
                    $table->date('date');
                    $table->timestamp('opened_at')->useCurrent();
                    $table->timestamp('closed_at')->nullable();
                    $table->string('status')->default('open');
                    $table->float('opening_balance')->default(0);
                    $table->float('closing_balance')->default(0);
                    $table->timestamps();
                });
            } catch (\Exception $e) {}
        }

        // 24-Hour Daybook Auto-Close Check
        $activeSession = DB::table('daybook_sessions')
            ->where('restaurant_id', $rId)
            ->where('status', 'open')
            ->orderBy('opened_at', 'desc')
            ->first();

        if ($activeSession) {
            $openedAt = \Carbon\Carbon::parse($activeSession->opened_at);
            if (now()->diffInHours($openedAt) >= 24 || $openedAt->format('Y-m-d') < date('Y-m-d')) {
                DB::table('daybook_sessions')
                    ->where('id', $activeSession->id)
                    ->update([
                        'status' => 'auto_closed',
                        'closed_at' => $openedAt->copy()->addHours(24),
                        'updated_at' => now()
                    ]);
            }
        }

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
            } catch (\Exception $e) {}
        }

        // Self-healing migration for finance_accounts table
        if (!Schema::hasTable('finance_accounts')) {
            try {
                Schema::create('finance_accounts', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id');
                    $table->string('name');
                    $table->string('type')->default('cash'); // bank, cash, wallet, personal, loan
                    $table->float('balance')->default(0);
                    $table->string('bank_name')->nullable();
                    $table->string('account_number')->nullable();
                    $table->text('description')->nullable();
                    $table->timestamps();
                });
                
                // Seed default accounts
                DB::table('finance_accounts')->insert([
                    ['restaurant_id' => $rId, 'name' => 'Counter (Cash)', 'type' => 'cash', 'balance' => 0, 'created_at' => now(), 'updated_at' => now()],
                    ['restaurant_id' => $rId, 'name' => 'Bank Account', 'type' => 'bank', 'balance' => 0, 'created_at' => now(), 'updated_at' => now()],
                    ['restaurant_id' => $rId, 'name' => 'Owner\'s Account', 'type' => 'personal', 'balance' => 0, 'created_at' => now(), 'updated_at' => now()],
                ]);
            } catch (\Exception $e) {}
        }

        // Self-healing migration for finance_transactions table
        if (!Schema::hasTable('finance_transactions')) {
            try {
                Schema::create('finance_transactions', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id');
                    $table->string('type'); // income, expense, sales, purchase
                    $table->float('amount');
                    $table->string('category')->nullable();
                    $table->string('description')->nullable();
                    $table->text('remarks')->nullable();
                    $table->string('party_type')->nullable(); // supplier, staff, customer
                    $table->string('party_name')->nullable();
                    $table->string('payment_status')->default('paid'); // paid, unpaid
                    $table->string('reference_number')->nullable();
                    $table->text('items')->nullable();
                    $table->string('sales_staff')->nullable();
                    $table->unsignedBigInteger('account_id')->nullable();
                    $table->date('date');
                    $table->timestamps();
                });
            } catch (\Exception $e) {}
        }

        // Dynamic column checks
        try {
            if (!Schema::hasColumn('finance_accounts', 'bank_name')) {
                Schema::table('finance_accounts', function (Blueprint $table) {
                    $table->string('bank_name')->nullable();
                    $table->string('account_number')->nullable();
                    $table->text('description')->nullable();
                });
            }
            if (!Schema::hasColumn('finance_transactions', 'remarks')) {
                Schema::table('finance_transactions', function (Blueprint $table) {
                    $table->text('remarks')->nullable();
                    $table->string('party_type')->nullable();
                    $table->string('party_name')->nullable();
                    $table->string('payment_status')->default('paid');
                    $table->string('reference_number')->nullable();
                    $table->text('items')->nullable();
                    $table->string('sales_staff')->nullable();
                });
            }
        } catch (\Exception $e) {}

        $this->txDate = date('Y-m-d');
        $this->invoiceTxnDate = date('Y-m-d');
        
        // Select first account by default
        $firstAcc = DB::table('finance_accounts')->where('restaurant_id', $rId)->first();
        if ($firstAcc) {
            $this->txAccountId = $firstAcc->id;
        }

        // Setup empty item for sales invoice
        $this->addInvoiceRow();
    }

    public function navigate($view)
    {
        $this->activeView = $view;
    }

    // Modal triggers
    public function openAddTxModal()
    {
        $this->resetTxForm();
        $this->showTxModal = true;
    }

    public function openAddAccountModal()
    {
        $this->resetAccountForm();
        $this->showAccountModal = true;
    }

    public function openSalesInvoiceModal()
    {
        $this->resetInvoiceForm();
        $this->showSalesInvoiceModal = true;
    }

    private function resetTxForm()
    {
        $this->txId = null;
        $this->txType = 'expense';
        $this->txAmount = 0;
        $this->txCategory = 'Raw Materials';
        $this->txDescription = '';
        $this->txRemarks = '';
        $this->txPartyType = 'supplier';
        $this->txPartyName = '';
        $this->txPaymentStatus = 'paid';
        $this->txReferenceNumber = '';
        $this->txDate = date('Y-m-d');
        $rId = current_restaurant() ? current_restaurant()->id : 1;
        $firstAcc = DB::table('finance_accounts')->where('restaurant_id', $rId)->first();
        if ($firstAcc) {
            $this->txAccountId = $firstAcc->id;
        }
    }

    private function resetAccountForm()
    {
        $this->accountId = null;
        $this->accountName = '';
        $this->accountType = 'bank';
        $this->bankName = '';
        $this->accountNumber = '';
        $this->accountBalance = 0;
        $this->accountDescription = '';
    }

    private function resetInvoiceForm()
    {
        $this->invoiceCustomerId = null;
        $this->invoiceTxnDate = date('Y-m-d');
        $this->invoiceSalesStaff = '';
        $this->invoiceRemarks = '';
        $this->invoicePaymentMode = 'cash';
        $this->invoicePaymentStatus = 'paid';
        $this->invoiceItems = [];
        $this->invoiceTotal = 0;
        $this->addInvoiceRow();
    }

    // Sales Invoice Rows Management
    public function addInvoiceRow()
    {
        $this->invoiceItems[] = [
            'name' => '',
            'qty' => 1,
            'rate' => 0,
            'amount' => 0
        ];
        $this->calculateInvoiceTotal();
    }

    public function removeInvoiceRow($index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);
        if (count($this->invoiceItems) === 0) {
            $this->addInvoiceRow();
        }
        $this->calculateInvoiceTotal();
    }

    public function updateInvoiceRow($index, $field, $value)
    {
        $this->invoiceItems[$index][$field] = $value;
        if ($field === 'qty' || $field === 'rate') {
            $qty = floatval($this->invoiceItems[$index]['qty']);
            $rate = floatval($this->invoiceItems[$index]['rate']);
            $this->invoiceItems[$index]['amount'] = $qty * $rate;
        }
        $this->calculateInvoiceTotal();
    }

    public function calculateInvoiceTotal()
    {
        $this->invoiceTotal = collect($this->invoiceItems)->sum('amount');
    }

    // Save Handlers
    public function saveTransaction()
    {
        $this->validate();

        $rId = current_restaurant() ? current_restaurant()->id : 1;

        $data = [
            'restaurant_id' => $rId,
            'type' => $this->txType,
            'amount' => floatval($this->txAmount),
            'category' => $this->txCategory,
            'description' => $this->txDescription ?: null,
            'remarks' => $this->txRemarks ?: null,
            'party_type' => $this->txPartyType,
            'party_name' => $this->txPartyName ?: null,
            'payment_status' => $this->txPaymentStatus,
            'reference_number' => $this->txReferenceNumber ?: null,
            'account_id' => intval($this->txAccountId),
            'date' => $this->txDate,
            'updated_at' => now(),
        ];

        if ($this->txId) {
            $oldTx = DB::table('finance_transactions')->where('id', $this->txId)->first();
            if ($oldTx) {
                $this->adjustAccountBalance($oldTx->account_id, $oldTx->type, -$oldTx->amount);
            }

            DB::table('finance_transactions')->where('id', $this->txId)->update($data);
            $this->adjustAccountBalance($this->txAccountId, $this->txType, $this->txAmount);
        } else {
            $data['created_at'] = now();
            DB::table('finance_transactions')->insert($data);
            $this->adjustAccountBalance($this->txAccountId, $this->txType, $this->txAmount);
        }

        $this->showTxModal = false;
        session()->flash('message', 'Transaction saved successfully!');
    }

    public function saveAccount()
    {
        $this->validate([
            'accountName' => 'required|string|max:255',
            'accountType' => 'required|in:bank,cash,wallet,personal,loan',
            'bankName' => 'nullable|required_if:accountType,bank|string|max:255',
            'accountNumber' => 'nullable|required_if:accountType,bank|string|max:255',
            'accountBalance' => 'required|numeric',
            'accountDescription' => 'nullable|string',
        ]);

        $rId = current_restaurant() ? current_restaurant()->id : 1;

        $data = [
            'restaurant_id' => $rId,
            'name' => $this->accountName,
            'type' => $this->accountType,
            'bank_name' => $this->bankName ?: null,
            'account_number' => $this->accountNumber ?: null,
            'balance' => floatval($this->accountBalance),
            'description' => $this->accountDescription ?: null,
            'updated_at' => now()
        ];

        if ($this->accountId) {
            DB::table('finance_accounts')->where('id', $this->accountId)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('finance_accounts')->insert($data);
        }

        $this->showAccountModal = false;
        session()->flash('message', 'Finance account saved successfully!');
    }

    public function saveSalesInvoice()
    {
        $this->validate([
            'invoiceTxnDate' => 'required|date',
            'invoiceSalesStaff' => 'nullable|string',
            'invoiceRemarks' => 'nullable|string',
            'invoicePaymentMode' => 'required|string',
            'invoicePaymentStatus' => 'required|in:paid,unpaid',
        ]);

        $rId = current_restaurant() ? current_restaurant()->id : 1;

        // Map payment mode to an account
        $accountMap = [
            'cash' => 'Counter (Cash)',
            'card' => 'Bank Account',
            'nepal_pay' => 'Bank Account',
            'fonepay' => 'Bank Account',
            'bank_transfer' => 'Bank Account',
        ];
        
        $targetAccountName = $accountMap[$this->invoicePaymentMode] ?? 'Counter (Cash)';
        $acc = DB::table('finance_accounts')
            ->where('restaurant_id', $rId)
            ->where('name', 'like', '%' . $targetAccountName . '%')
            ->first();
            
        $accountId = $acc ? $acc->id : DB::table('finance_accounts')->where('restaurant_id', $rId)->value('id');

        $data = [
            'restaurant_id' => $rId,
            'type' => 'sales',
            'amount' => floatval($this->invoiceTotal),
            'category' => 'Direct Sales Cash',
            'description' => $this->invoiceRemarks ?: 'Manual Sales Invoice',
            'remarks' => $this->invoiceRemarks,
            'party_type' => 'customer',
            'party_name' => $this->invoiceCustomerId ? DB::table('customers')->where('id', $this->invoiceCustomerId)->value('name') : 'Walk-in Customer',
            'payment_status' => $this->invoicePaymentStatus,
            'reference_number' => 'INV-' . strtoupper(bin2hex(random_bytes(3))),
            'items' => json_encode($this->invoiceItems),
            'sales_staff' => $this->invoiceSalesStaff,
            'account_id' => $accountId,
            'date' => $this->invoiceTxnDate,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('finance_transactions')->insert($data);
        
        if ($this->invoicePaymentStatus === 'paid' && $accountId) {
            $this->adjustAccountBalance($accountId, 'sales', $this->invoiceTotal);
        }

        $this->showSalesInvoiceModal = false;
        session()->flash('message', 'Sales invoice created successfully!');
    }

    private function adjustAccountBalance($accountId, $type, $amount)
    {
        $acc = DB::table('finance_accounts')->where('id', $accountId)->first();
        if (!$acc) return;

        // income/sales increases balance, expense/purchase decreases it
        $diff = ($type === 'income' || $type === 'sales') ? $amount : -$amount;
        
        DB::table('finance_accounts')->where('id', $accountId)->update([
            'balance' => $acc->balance + $diff,
            'updated_at' => now()
        ]);
    }

    public function deleteTransaction($id)
    {
        $tx = DB::table('finance_transactions')->where('id', $id)->first();
        if ($tx) {
            if ($tx->account_id && $tx->payment_status === 'paid') {
                $this->adjustAccountBalance($tx->account_id, $tx->type, -$tx->amount);
            }
            DB::table('finance_transactions')->where('id', $id)->delete();
        }
        session()->flash('message', 'Transaction deleted successfully!');
    }

    public function render()
    {
        $rId = current_restaurant() ? current_restaurant()->id : 1;

        $accounts = DB::table('finance_accounts')->where('restaurant_id', $rId)->get();
        $transactions = DB::table('finance_transactions')->where('restaurant_id', $rId)->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $customers = DB::table('customers')->where('restaurant_id', $rId)->get();

        // Filtered transactions for Daybook Date selection
        $targetDate = $this->daybookDate ?: date('Y-m-d');
        $daybookTransactions = DB::table('finance_transactions')
            ->where('restaurant_id', $rId)
            ->whereDate('date', $targetDate)
            ->orderBy('id', 'desc')
            ->get();

        // Pull orders sales automatically filtered by date
        $orderSales = DB::table('orders')
            ->where('restaurant_id', $rId)
            ->where('status', 'completed')
            ->whereDate('created_at', $targetDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $activeSession = DB::table('daybook_sessions')
            ->where('restaurant_id', $rId)
            ->orderBy('opened_at', 'desc')
            ->first();

        return view('livewire.admin.finance-manager', [
            'accounts' => $accounts,
            'transactions' => $transactions,
            'daybookTransactions' => $daybookTransactions,
            'orderSales' => $orderSales,
            'customers' => $customers,
            'activeSession' => $activeSession
        ])->layout('components.layouts.app', ['title' => 'Finance']);
    }
}
