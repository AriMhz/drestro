<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Response;

class Settings extends Component
{
    use WithFileUploads;

    public $restaurant;

    #[Url(as: 'tab')]
    public $activeTab = 'list';

    // Form fields
    public $name = '';
    public $tagline = '';
    public $address = '';
    public $ward = '';
    public $city = '';
    public $phone = '';
    public $email = '';
    public $currency = 'Rs.';
    public $taxPercent = 0;
    public $serviceChargePercent = 0;
    public $panNumber = '';
    public $dateCalendarType = 'AD';
    public $newLogo;
    public $newQrCode;
    public $newEsewaQr;
    public $newKhaltiQr;
    public $newFonepayQr;
    public $newBankTransferQr;
    public $backupFile;

    // Change Password fields
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    // Nepal E-Billing Settings
    public $nepalEbillingEnabled = false;
    public $nepalEbillingApiKey = '';
    public $nepalEbillingEnvironment = 'staging';
    public $nepalEbillingSubdomain = 'sky';

    // Printer Settings
    public $kitchenPrinterType = 'network';
    public $kitchenPrinterPath = '';
    public $cashierPrinterType = 'network';
    public $cashierPrinterPath = '';
    public $autoPrintKot = false;
    public $autoPrintReceipt = false;

    // Hotel Printer
    public $hotelPrinterType = 'windows_usb_share';
    public $hotelPrinterPath = '';
    public $hotelAutoPrint = true;

    // BOT Printer
    public $botPrinterType = 'network';
    public $botPrinterPath = '';
    public $separateKotBot = false;

    // Connection
    public $localIp = '';
    public $saved = false;
    public $printerTestResult = '';
    public $printerTestError = '';

    // Network Scanner
    public $scannedPrinters = [];
    public $isScanning = false;

    // USB Scanner
    public $usbPrinters = [];
    public $isScanningUsb = false;

    // USB Database Backup Manual Result
    public $usbBackupResult = '';

    public function backupToUsbManual()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized action.');
        }
        $this->usbBackupResult = '';
        try {
            $drives = \App\Helpers\BackupHelper::backupToUsb();
            if (!empty($drives)) {
                $drivePaths = array_map(fn($d) => $d . '\\DrestroPOS_Backups', $drives);
                session()->flash('usb_success', 'Database successfully backed up to USB drive(s): ' . implode(', ', $drivePaths));
            } else {
                session()->flash('usb_error', 'No connected writable USB Pendrive detected. Please plug in a USB drive and try again.');
            }
        } catch (\Exception $e) {
            session()->flash('usb_error', 'USB Backup failed: ' . $e->getMessage());
        }
    }

    public function mount()
    {
        // Self-healing migration for BOT Printer settings
        if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', 'bot_printer_type')) {
            try {
                \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('bot_printer_type')->nullable()->default('network');
                    $table->string('bot_printer_path')->nullable();
                    $table->boolean('separate_kot_bot')->default(false);
                });
            } catch (\Exception $e) {
                // If it fails here, the columns might partially exist or DB is locked
            }
        }

        // Self-healing migration for ward/city address fields
        if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', 'ward')) {
            try {
                \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('ward')->nullable();
                    $table->string('city')->nullable();
                });
            } catch (\Exception $e) {
                // columns might already exist
            }
        }

        // Self-healing migration for Nepal E-Billing settings
        if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', 'nepal_ebilling_enabled')) {
            try {
                \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->boolean('nepal_ebilling_enabled')->default(false);
                    $table->string('nepal_ebilling_api_key')->nullable();
                    $table->string('nepal_ebilling_environment')->default('staging');
                });
            } catch (\Exception $e) {
                // Ignore if fails
            }
        }

        // Self-healing migration for Nepal E-Billing subdomain settings
        if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', 'nepal_ebilling_subdomain')) {
            try {
                \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('nepal_ebilling_subdomain')->nullable()->default('sky');
                });
            } catch (\Exception $e) {
                // Ignore if fails
            }
        }

        // Self-healing migration for payment QR code
        if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', 'payment_qr_code')) {
            try {
                \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('payment_qr_code')->nullable();
                });
            } catch (\Exception $e) {
                // Ignore if fails
            }
        }

        // Self-healing migration for provider-specific QR codes
        $providerQrCols = ['esewa_qr', 'khalti_qr', 'fonepay_qr', 'bank_transfer_qr'];
        foreach ($providerQrCols as $col) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', $col)) {
                try {
                    \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) use ($col) {
                        $table->string($col)->nullable();
                    });
                } catch (\Exception $e) {
                    // Ignore if fails
                }
            }
        }

        // Self-healing migration for Invoices Nepal E-Billing status
        if (\Illuminate\Support\Facades\Schema::hasTable('invoices') && !\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'nepal_ebilling_synced')) {
            try {
                \Illuminate\Support\Facades\Schema::table('invoices', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->boolean('nepal_ebilling_synced')->default(false);
                    $table->string('nepal_ebilling_invoice_id')->nullable();
                    $table->text('nepal_ebilling_error')->nullable();
                });
            } catch (\Exception $e) {
                // Ignore if fails
            }
        }

        $this->localIp = gethostbyname(gethostname());
        $this->restaurant = current_restaurant();

        if ($this->restaurant) {
            $this->name = $this->restaurant->name ?? '';
            $this->tagline = $this->restaurant->tagline ?? '';
            $this->address = $this->restaurant->address ?? '';
            $this->phone = !empty($this->restaurant->phone) ? $this->restaurant->phone : (auth()->user()->phone ?? '');
            $this->email = !empty($this->restaurant->email) ? $this->restaurant->email : (auth()->user()->email ?? '');
            $this->ward = $this->restaurant->ward ?? '';
            $this->city = $this->restaurant->city ?? '';

            if (empty($this->city) && !empty($this->address) && str_contains($this->address, ',')) {
                $parts = array_map('trim', explode(',', $this->address));
                if (count($parts) >= 2) {
                    $this->city = end($parts);
                }
            }
            $this->currency = $this->restaurant->currency ?? 'Rs.';
            $this->taxPercent = $this->restaurant->tax_percent ?? 0;
            $this->serviceChargePercent = $this->restaurant->service_charge_percent ?? 0;
            $this->panNumber = $this->restaurant->pan_number ?? '';
            $this->dateCalendarType = $this->restaurant->date_calendar_type ?? 'AD';

            // E-Billing Bindings
            $this->nepalEbillingEnabled = (bool)($this->restaurant->nepal_ebilling_enabled ?? false);
            $this->nepalEbillingApiKey = $this->restaurant->nepal_ebilling_api_key ?? '';
            $this->nepalEbillingEnvironment = $this->restaurant->nepal_ebilling_environment ?? 'staging';
            $this->nepalEbillingSubdomain = $this->restaurant->nepal_ebilling_subdomain ?? 'sky';

            // Printers
            $this->kitchenPrinterType = $this->restaurant->kitchen_printer_type ?? 'network';
            $this->kitchenPrinterPath = $this->restaurant->kitchen_printer_path ?? '';
            $this->cashierPrinterType = $this->restaurant->cashier_printer_type ?? 'network';
            $this->cashierPrinterPath = $this->restaurant->cashier_printer_path ?? '';
            $this->autoPrintKot = $this->restaurant->auto_print_kot ?? false;
            $this->autoPrintReceipt = $this->restaurant->auto_print_receipt ?? false;

            // Hotel Printer
            $this->hotelPrinterType = $this->restaurant->hotel_printer_type ?? 'windows_usb_share';
            $this->hotelPrinterPath = $this->restaurant->hotel_printer_address ?? '';
            $this->hotelAutoPrint = (bool)($this->restaurant->hotel_auto_print ?? true);

            // BOT Printer
            $this->botPrinterType = $this->restaurant->bot_printer_type ?? 'network';
            $this->botPrinterPath = $this->restaurant->bot_printer_path ?? '';
            $this->separateKotBot = (bool)($this->restaurant->separate_kot_bot ?? false);
        }
    }

    public function assignPrinterConfig($target, $type, $value)
    {
        if ($target === 'kitchen') {
            if ($this->kitchenPrinterPath === $value && $this->kitchenPrinterType === $type) {
                $this->kitchenPrinterPath = '';
            } else {
                $this->kitchenPrinterType = $type;
                $this->kitchenPrinterPath = $value;
            }
        } elseif ($target === 'cashier') {
            if ($this->cashierPrinterPath === $value && $this->cashierPrinterType === $type) {
                $this->cashierPrinterPath = '';
            } else {
                $this->cashierPrinterType = $type;
                $this->cashierPrinterPath = $value;
            }
        } elseif ($target === 'bar' || $target === 'bot') {
            if ($this->botPrinterPath === $value && $this->botPrinterType === $type) {
                $this->botPrinterPath = '';
            } else {
                $this->botPrinterType = $type;
                $this->botPrinterPath = $value;
            }
        } elseif ($target === 'hotel') {
            if ($this->hotelPrinterPath === $value && $this->hotelPrinterType === $type) {
                $this->hotelPrinterPath = '';
            } else {
                $this->hotelPrinterType = $type;
                $this->hotelPrinterPath = $value;
            }
        }
    }

    public function assignPreset($shareName)
    {
        $path = 'smb://127.0.0.1/' . $shareName;
        $this->kitchenPrinterType = 'usb';
        $this->kitchenPrinterPath = $path;
        $this->cashierPrinterType = 'usb';
        $this->cashierPrinterPath = $path;
    }

    /**
     * Livewire lifecycle hook: sanitize phone on every update.
     * Strips non-digit characters and limits to 10 digits.
     */
    public function updatedPhone($value)
    {
        $this->phone = substr(preg_replace('/[^0-9]/', '', $value), 0, 10);
    }

    public function saveSettings()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:255',
            'currency' => 'required|string|max:10',
            'taxPercent' => 'nullable|numeric|min:0|max:100',
            'serviceChargePercent' => 'nullable|numeric|min:0|max:100',
            'newLogo' => 'nullable|image|max:2048',
            'newQrCode' => 'nullable|image|max:2048',
            'newEsewaQr' => 'nullable|image|max:2048',
            'newKhaltiQr' => 'nullable|image|max:2048',
            'newFonepayQr' => 'nullable|image|max:2048',
            'newBankTransferQr' => 'nullable|image|max:2048',
        ]);

        if (!$this->restaurant) {
            $this->restaurant = Restaurant::create(['name' => $this->name, 'is_active' => true]);
        }

        $data = [
            'name' => $this->name,
            'tagline' => $this->tagline,
            'address' => $this->address,
            'ward' => $this->ward,
            'city' => $this->city,
            'phone' => $this->phone,
            'email' => $this->email,
            'currency' => $this->currency,
            'tax_percent' => $this->taxPercent,
            'service_charge_percent' => $this->serviceChargePercent,
            'pan_number' => $this->panNumber,
            'kitchen_printer_type' => $this->kitchenPrinterType,
            'kitchen_printer_path' => $this->kitchenPrinterPath,
            'cashier_printer_type' => $this->cashierPrinterType,
            'cashier_printer_path' => $this->cashierPrinterPath,
            'auto_print_kot' => $this->autoPrintKot,
            'auto_print_receipt' => $this->autoPrintReceipt,
            'hotel_printer_type' => $this->hotelPrinterType,
            'hotel_printer_address' => $this->hotelPrinterPath,
            'hotel_auto_print' => $this->hotelAutoPrint,
            'bot_printer_type' => $this->botPrinterType,
            'bot_printer_path' => $this->botPrinterPath,
            'separate_kot_bot' => $this->separateKotBot,
            'date_calendar_type' => in_array(strtoupper($this->dateCalendarType), ['AD', 'BS']) ? strtoupper($this->dateCalendarType) : ($this->restaurant->date_calendar_type ?? 'AD'),
            'nepal_ebilling_enabled' => $this->nepalEbillingEnabled,
            'nepal_ebilling_api_key' => $this->nepalEbillingApiKey,
            'nepal_ebilling_environment' => $this->nepalEbillingEnvironment,
            'nepal_ebilling_subdomain' => $this->nepalEbillingSubdomain,
        ];

        // Strict backend guard: Non-super_admins, Non-admins and Non-managers cannot modify printer/hardware settings
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
            $data['kitchen_printer_type'] = $this->restaurant->kitchen_printer_type ?? 'network';
            $data['kitchen_printer_path'] = $this->restaurant->kitchen_printer_path ?? '';
            $data['cashier_printer_type'] = $this->restaurant->cashier_printer_type ?? 'network';
            $data['cashier_printer_path'] = $this->restaurant->cashier_printer_path ?? '';
            $data['auto_print_kot'] = $this->restaurant->auto_print_kot ?? false;
            $data['auto_print_receipt'] = $this->restaurant->auto_print_receipt ?? false;
            $data['hotel_printer_type'] = $this->restaurant->hotel_printer_type ?? 'windows_usb_share';
            $data['hotel_printer_address'] = $this->restaurant->hotel_printer_address ?? '';
            $data['hotel_auto_print'] = $this->restaurant->hotel_auto_print ?? true;
            $data['bot_printer_type'] = $this->restaurant->bot_printer_type ?? 'network';
            $data['bot_printer_path'] = $this->restaurant->bot_printer_path ?? '';
            $data['separate_kot_bot'] = $this->restaurant->separate_kot_bot ?? false;
        }

        if ($this->newLogo) {
            $oldLogo = $this->restaurant->logo ?? null;
            if ($oldLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
            $path = $this->newLogo->store('logos', 'public');
            $data['logo'] = $path;
        }

        if ($this->newQrCode) {
            $oldQr = $this->restaurant->payment_qr_code ?? null;
            if ($oldQr && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldQr)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldQr);
            }
            $path = $this->newQrCode->store('qrcodes', 'public');
            $data['payment_qr_code'] = $path;
        }

        if ($this->newEsewaQr) {
            $oldQr = $this->restaurant->esewa_qr ?? null;
            if ($oldQr && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldQr)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldQr);
            }
            $path = $this->newEsewaQr->store('qrcodes', 'public');
            $data['esewa_qr'] = $path;
        }

        if ($this->newKhaltiQr) {
            $oldQr = $this->restaurant->khalti_qr ?? null;
            if ($oldQr && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldQr)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldQr);
            }
            $path = $this->newKhaltiQr->store('qrcodes', 'public');
            $data['khalti_qr'] = $path;
        }

        if ($this->newFonepayQr) {
            $oldQr = $this->restaurant->fonepay_qr ?? null;
            if ($oldQr && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldQr)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldQr);
            }
            $path = $this->newFonepayQr->store('qrcodes', 'public');
            $data['fonepay_qr'] = $path;
        }

        if ($this->newBankTransferQr) {
            $oldQr = $this->restaurant->bank_transfer_qr ?? null;
            if ($oldQr && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldQr)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldQr);
            }
            $path = $this->newBankTransferQr->store('qrcodes', 'public');
            $data['bank_transfer_qr'] = $path;
        }

        $this->restaurant->update($data);

        // Sync settings to Next.js SaaS Web Portal (drestro.com)
        if (!empty($this->restaurant->slug)) {
            try {
                $syncPayload = [
                    'id' => $this->restaurant->slug,
                    'name' => $this->name,
                    'tagline' => $this->tagline,
                    'address' => $this->address,
                    'ward' => $this->ward,
                    'city' => $this->city,
                    'phone' => $this->phone,
                    'email' => $this->email,
                    'currency' => $this->currency,
                    'tax_percent' => floatval($this->taxPercent),
                    'service_charge_percent' => floatval($this->serviceChargePercent),
                    'pan_number' => $this->panNumber,
                    'date_calendar_type' => $this->dateCalendarType,
                ];
                
                $signData = $this->restaurant->slug . '|' . $this->name . '|' . $this->phone;
                $token = hash_hmac('sha256', $signData, 'DrestroPOS_Secure_Key_2026_X9P2');
                
                $baseUrl = str_contains(config('app.url', ''), 'portal.drestro.com') ? 'https://drestro.com' : 'http://localhost:3000';
                
                \Illuminate\Support\Facades\Http::timeout(5)->post($baseUrl . '/api/restaurant/sync', [
                    'payload' => $syncPayload,
                    'token' => $token
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('SaaS Restaurant Sync failed: ' . $e->getMessage());
            }
        }

        $this->saved = true;
        $this->newLogo = null;
        $this->newQrCode = null;
        $this->newEsewaQr = null;
        $this->newKhaltiQr = null;
        $this->newFonepayQr = null;
        $this->newBankTransferQr = null;
    }

    public function removeQr($type)
    {
        if ($this->restaurant) {
            if ($type === 'default') {
                $oldPath = $this->restaurant->payment_qr_code;
                if ($oldPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
                $this->restaurant->update(['payment_qr_code' => null]);
            } else {
                $column = $type . '_qr'; // e.g. esewa_qr
                $oldPath = $this->restaurant->$column;
                if ($oldPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
                $this->restaurant->update([$column => null]);
            }
            $this->restaurant->refresh();
            $this->newLogo = null;
            $this->newQrCode = null;
            $this->newEsewaQr = null;
            $this->newKhaltiQr = null;
            $this->newFonepayQr = null;
            $this->newBankTransferQr = null;
            session()->flash('qr_success', 'QR Code removed successfully.');
        }
    }

    public function downloadBackup()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized action.');
        }
        $databasePath = database_path('database.sqlite');
        $fileName = 'drestro_backup_' . date('Y-m-d_His') . '.sqlite';
        
        return Response::download($databasePath, $fileName);
    }

    public function updatedBackupFile()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized action.');
        }
        $this->validate([
            'backupFile' => 'required|file|max:51200', // 50MB max
        ]);

        $extension = $this->backupFile->getClientOriginalExtension();
        if (strtolower($extension) !== 'sqlite') {
            session()->flash('backup_error', 'Invalid file type. Please upload a valid .sqlite backup file.');
            $this->backupFile = null;
            return;
        }

        try {
            $backupPath = $this->backupFile->getRealPath();
            
            // Disconnect database connection to release any locks
            \Illuminate\Support\Facades\DB::disconnect();
            
            $currentDbPath = database_path('database.sqlite');
            $tempBackup = database_path('database.sqlite.temp');
            
            if (file_exists($currentDbPath)) {
                copy($currentDbPath, $tempBackup);
            }
            
            if (copy($backupPath, $currentDbPath)) {
                if (file_exists($tempBackup)) {
                    unlink($tempBackup);
                }
                
                session()->flash('backup_success', 'Database backup restored successfully! Reloading...');
                $this->backupFile = null;
                
                return redirect()->to('/login');
            } else {
                if (file_exists($tempBackup)) {
                    copy($tempBackup, $currentDbPath);
                    unlink($tempBackup);
                }
                session()->flash('backup_error', 'Failed to overwrite the database file. Please try again.');
            }
        } catch (\Exception $e) {
            session()->flash('backup_error', 'Error restoring backup: ' . $e->getMessage());
        }

        $this->backupFile = null;
    }

    public function testKitchenPrinter()
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }
        $this->printerTestResult = '';
        $this->printerTestError = '';
        try {
            \App\Services\PrinterService::testPrint($this->kitchenPrinterType, $this->kitchenPrinterPath);
            $this->printerTestResult = 'kitchen_ok';
        } catch (\Exception $e) {
            $this->printerTestResult = 'kitchen_fail';
            $this->printerTestError = $e->getMessage();
        }
    }

    public function testCashierPrinter()
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }
        $this->printerTestResult = '';
        $this->printerTestError = '';
        try {
            \App\Services\PrinterService::testPrint($this->cashierPrinterType, $this->cashierPrinterPath);
            $this->printerTestResult = 'cashier_ok';
        } catch (\Exception $e) {
            $this->printerTestResult = 'cashier_fail';
            $this->printerTestError = $e->getMessage();
        }
    }

    public function testHotelPrinter()
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }
        $this->printerTestResult = '';
        $this->printerTestError = '';
        try {
            \App\Services\PrinterService::testPrint($this->hotelPrinterType, $this->hotelPrinterPath);
            $this->printerTestResult = 'hotel_ok';
        } catch (\Exception $e) {
            $this->printerTestResult = 'hotel_fail';
            $this->printerTestError = $e->getMessage();
        }
    }

    public function testBotPrinter()
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }
        $this->printerTestResult = '';
        $this->printerTestError = '';
        try {
            \App\Services\PrinterService::testPrint($this->botPrinterType, $this->botPrinterPath);
            $this->printerTestResult = 'bot_ok';
        } catch (\Exception $e) {
            $this->printerTestResult = 'bot_fail';
            $this->printerTestError = $e->getMessage();
        }
    }

    public function scanPrinters()
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }
        $this->isScanning = true;
        $this->scannedPrinters = [];
        
        $localIps = [];
        if (function_exists('net_get_interfaces')) {
            $interfaces = @net_get_interfaces();
            if (is_array($interfaces)) {
                foreach ($interfaces as $iface) {
                    if (empty($iface['unicast'])) continue;
                    foreach ($iface['unicast'] as $addr) {
                        if (isset($addr['family']) && $addr['family'] === 2) {
                            $ip = $addr['address'] ?? '';
                            if ($ip && $ip !== '127.0.0.1' && !str_starts_with($ip, '169.254.')) {
                                $localIps[] = $ip;
                            }
                        }
                    }
                }
            }
        }

        if (empty($localIps)) {
            $localIp = gethostbyname(gethostname());
            if ($localIp && $localIp !== '127.0.0.1') {
                $localIps[] = $localIp;
            }
        }
        $localIps = array_unique($localIps);

        $subnets = [];
        foreach ($localIps as $ip) {
            $subPos = strrpos($ip, '.');
            if ($subPos !== false) {
                $subnets[] = substr($ip, 0, $subPos + 1);
            }
        }
        $subnets = array_unique($subnets);

        // Limit scanning to avoid server timeout (sweep first 2 subnets max)
        $subnets = array_slice($subnets, 0, 2);

        foreach ($subnets as $subnet) {
            for ($i = 1; $i <= 254; $i++) {
                $ip = $subnet . $i;
                if (in_array($ip, $localIps)) continue;
                
                $connection = @fsockopen($ip, 9100, $errno, $errstr, 0.025);
                if (is_resource($connection)) {
                    $this->scannedPrinters[] = $ip . ':9100';
                    fclose($connection);
                }
            }
        }
        
        $this->isScanning = false;
        
        if (empty($this->scannedPrinters)) {
            session()->flash('scan_error', 'No network printers found. Please ensure the printer is turned on and connected to the same Wi-Fi router.');
        }
    }

    public function scanUsbPrinters()
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }
        $this->isScanningUsb = true;
        $this->usbPrinters = [];
        
        try {
            // Get local printers using PowerShell
            $output = shell_exec('powershell -Command "Get-Printer | Select-Object Name, Shared, ShareName | ConvertTo-Json"');
            if ($output) {
                $printers = json_decode($output, true);
                
                // If it's a single object instead of array of objects
                if (isset($printers['Name'])) {
                    $printers = [$printers];
                }
                
                if (is_array($printers)) {
                    foreach ($printers as $printer) {
                        // We only want ESC/POS thermal printers, but since we can't reliably filter,
                        // we'll exclude obvious virtual ones
                        $name = strtolower($printer['Name'] ?? '');
                        if (str_contains($name, 'pdf') || str_contains($name, 'xps') || str_contains($name, 'onenote') || str_contains($name, 'fax')) {
                            continue;
                        }
                        
                        $this->usbPrinters[] = [
                            'name' => $printer['Name'] ?? 'Unknown',
                            'is_shared' => $printer['Shared'] ?? false,
                            'share_name' => $printer['ShareName'] ?? '',
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            session()->flash('usb_scan_error', 'Could not scan local USB printers: ' . $e->getMessage());
        }
        
        $this->isScanningUsb = false;
        
        if (empty($this->usbPrinters)) {
            session()->flash('usb_scan_error', 'No physical USB printers found on this computer.');
        }
    }

    public function resetRestaurantData()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $restaurantId = $this->restaurant->id;
            \Illuminate\Support\Facades\DB::table('orders')->where('restaurant_id', $restaurantId)->delete();
            
            if (\Illuminate\Support\Facades\Schema::hasTable('inventory_logs')) {
                \Illuminate\Support\Facades\DB::table('inventory_logs')->where('restaurant_id', $restaurantId)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
                \Illuminate\Support\Facades\DB::table('invoices')->where('restaurant_id', $restaurantId)->delete();
            }
            
            session()->flash('danger_success', 'Restaurant transaction data (orders, logs) has been successfully reset!');
        } catch (\Exception $e) {
            session()->flash('danger_error', 'Failed to reset restaurant data: ' . $e->getMessage());
        }
    }

    public function deleteRestaurant()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $restaurantId = $this->restaurant->id;
            \Illuminate\Support\Facades\DB::table('orders')->where('restaurant_id', $restaurantId)->delete();
            
            if (\Illuminate\Support\Facades\Schema::hasTable('inventory_logs')) {
                \Illuminate\Support\Facades\DB::table('inventory_logs')->where('restaurant_id', $restaurantId)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
                \Illuminate\Support\Facades\DB::table('invoices')->where('restaurant_id', $restaurantId)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('inventory_items')) {
                \Illuminate\Support\Facades\DB::table('inventory_items')->where('restaurant_id', $restaurantId)->delete();
            }
            
            \Illuminate\Support\Facades\DB::table('menu_items')->where('restaurant_id', $restaurantId)->delete();
            \Illuminate\Support\Facades\DB::table('menu_categories')->where('restaurant_id', $restaurantId)->delete();
            \Illuminate\Support\Facades\DB::table('tables')->where('restaurant_id', $restaurantId)->delete();
            \Illuminate\Support\Facades\DB::table('restaurants')->where('id', $restaurantId)->delete();

            session()->flash('danger_success', 'Restaurant has been completely deleted!');
            return redirect()->to('/login');
        } catch (\Exception $e) {
            session()->flash('danger_error', 'Failed to delete restaurant: ' . $e->getMessage());
        }
    }

    public $ebillingTestResult = '';
    public $ebillingTestError = '';

    public function testEbillingConnection()
    {
        $this->ebillingTestResult = '';
        $this->ebillingTestError = '';

        if (empty($this->nepalEbillingApiKey)) {
            $this->ebillingTestResult = 'fail';
            $this->ebillingTestError = 'API key is required to test the connection.';
            return;
        }

        try {
            $subdomain = $this->nepalEbillingSubdomain ?: 'sky';
            $baseUrl = $this->nepalEbillingEnvironment === 'production' 
                ? "https://{$subdomain}.nepalebilling.com" 
                : "https://{$subdomain}.staging.nepalebilling.com";
            
            // Send a test GET request to the sales-invoice-generation endpoint with custom headers
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->withHeaders([
                    'X-API-KEY' => $this->nepalEbillingApiKey,
                    'X-Api-Key' => $this->nepalEbillingApiKey,
                    'Accept' => 'application/json'
                ])->get($baseUrl . '/invoices/sales-invoice-generation/');

            if ($response->status() === 401 || $response->status() === 403) {
                $this->ebillingTestResult = 'fail';
                $this->ebillingTestError = $response->json('detail') ?? 'Unauthorized: Invalid API Key.';
            } elseif ($response->successful() || $response->status() === 405) {
                $this->ebillingTestResult = 'ok';
            } else {
                $this->ebillingTestResult = 'fail';
                $this->ebillingTestError = 'API Error (' . $response->status() . '): ' . ($response->json('detail') ?? $response->body());
            }
        } catch (\Exception $e) {
            $this->ebillingTestResult = 'fail';
            $this->ebillingTestError = 'Connection failed: ' . $e->getMessage();
        }
    }

    public function changePassword()
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The provided password does not match your current password.');
            return;
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($this->new_password);
        $user->save();

        // Reset password fields
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        session()->flash('password_success', 'Password updated successfully!');
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout('components.layouts.app', ['title' => 'Restaurant Settings']);
    }
}
