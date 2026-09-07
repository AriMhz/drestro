<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;

class MenuManager extends Component
{
    use WithFileUploads;

    public $restaurant;
    public $categories;
    public $items;
    public $activeLicense;

    // Modals
    public $showCategoryModal = false;
    public $showItemModal = false;

    // Editing state
    public $editingCategoryId = null;
    public $editingItemId = null;

    // Category Form
    public $newCategoryName = '';
    public $newCategoryDepartment = 'kitchen';
    public $newCategoryImage;

    // Item Form
    public $newItemCategoryId = '';
    public $newItemName = '';
    public $newItemPrice = '';
    public $newItemDescription = '';
    public $newItemIsVeg = true;
    public $newItemImage;
    public $newItemInventoryItemId = '';
    public $newItemAutoDeduct = false;
    public $newItemDeductAmount = 1;

    // Excel Import
    public $excelFile;
    public $importResult = '';
    public $importError = '';
    public $inventoryItems = [];

    public function mount()
    {
        $this->restaurant = current_restaurant() ?? Restaurant::firstOrCreate(
            ['id' => 1],
            ['name' => 'Drestro Main Branch', 'is_active' => true]
        );
        $this->loadData();
        $this->activeLicense = current_restaurant()?->license_data ?? \App\Services\LicenseManager::getFreeLimits();
        
        // Ensure storage link exists (Self-healing)
        $storageLink = public_path('storage');
        $storageTarget = storage_path('app/public');
        
        if (file_exists($storageLink) && !is_link($storageLink)) {
            @rmdir($storageLink); // Delete empty folder if it's not a link
        }

        if (!file_exists($storageLink)) {
            try {
                if (PHP_OS_FAMILY === 'Windows') {
                    // Windows specific symlink (requires specific permissions, but this is best effort)
                    @exec('mklink /J "' . $storageLink . '" "' . $storageTarget . '"');
                } else {
                }
            } catch (\Exception $e) {
                // Fallback
            }
        }

        // Self-healing schema for Inventory Link
        if (!\Illuminate\Support\Facades\Schema::hasColumn('menu_items', 'inventory_item_id')) {
            try {
                \Illuminate\Support\Facades\Schema::table('menu_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->unsignedBigInteger('inventory_item_id')->nullable()->after('menu_category_id');
                    $table->boolean('auto_deduct_inventory')->default(false)->after('inventory_item_id');
                    $table->decimal('deduct_amount', 10, 2)->default(1)->after('auto_deduct_inventory');
                });
                
                \Illuminate\Support\Facades\Schema::table('order_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->boolean('inventory_deducted')->default(false)->after('quantity');
                });
            } catch (\Exception $e) {}
        }

        // Self-heal: add deduct_amount column if missing
        if (\Illuminate\Support\Facades\Schema::hasColumn('menu_items', 'inventory_item_id') && !\Illuminate\Support\Facades\Schema::hasColumn('menu_items', 'deduct_amount')) {
            try {
                \Illuminate\Support\Facades\Schema::table('menu_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->decimal('deduct_amount', 10, 2)->default(1)->after('auto_deduct_inventory');
                });
            } catch (\Exception $e) {}
        }
    }

    public function loadData()
    {
        $this->categories = MenuCategory::where('restaurant_id', $this->restaurant->id)->get();
        $this->items = MenuItem::where('restaurant_id', $this->restaurant->id)->get();
        $this->inventoryItems = \App\Models\InventoryItem::where('restaurant_id', $this->restaurant->id)->where('is_active', true)->get();
    }

    // Category Logic
    public function toggleCategoryModal()
    {
        $this->showCategoryModal = !$this->showCategoryModal;
        $this->editingCategoryId = null;
        $this->newCategoryName = '';
        $this->newCategoryDepartment = 'kitchen';
        $this->newCategoryImage = null;
    }

    public function editCategory($id)
    {
        $category = MenuCategory::find($id);
        if ($category) {
            $this->editingCategoryId = $category->id;
            $this->newCategoryName = $category->name;
            $this->newCategoryDepartment = $category->department;
            $this->newCategoryImage = null;
            $this->showCategoryModal = true;
        }
    }

    public function saveCategory()
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:255',
            'newCategoryDepartment' => 'required|in:kitchen,bar',
            'newCategoryImage' => 'nullable|image|max:2048'
        ]);

        $data = [
            'restaurant_id' => $this->restaurant->id,
            'name' => $this->newCategoryName,
            'department' => $this->newCategoryDepartment,
            'is_active' => true,
        ];

        if ($this->newCategoryImage) {
            $data['image'] = $this->processAndStoreImage($this->newCategoryImage, 'categories');
        }

        if ($this->editingCategoryId) {
            MenuCategory::find($this->editingCategoryId)?->update($data);
        } else {
            MenuCategory::create($data);
        }

        $this->toggleCategoryModal();
        $this->loadData();
    }

    public function deleteCategory($id)
    {
        MenuCategory::find($id)?->delete();
        $this->loadData();
    }

    // Item Logic
    public function toggleItemModal()
    {
        $this->showItemModal = !$this->showItemModal;
        $this->editingItemId = null;
        $this->newItemCategoryId = $this->categories->first()?->id ?? '';
        $this->newItemName = '';
        $this->newItemPrice = '';
        $this->newItemDescription = '';
        $this->newItemIsVeg = true;
        $this->newItemImage = null;
        $this->newItemInventoryItemId = '';
        $this->newItemAutoDeduct = false;
        $this->newItemDeductAmount = 1;
    }

    public function editItem($id)
    {
        $item = MenuItem::find($id);
        if ($item) {
            $this->editingItemId = $item->id;
            $this->newItemCategoryId = $item->menu_category_id;
            $this->newItemName = $item->name;
            $this->newItemPrice = $item->price;
            $this->newItemDescription = $item->description;
            $this->newItemIsVeg = $item->is_veg;
            $this->newItemImage = null;
            $this->newItemInventoryItemId = $item->inventory_item_id ?? '';
            $this->newItemAutoDeduct = (bool)($item->auto_deduct_inventory ?? false);
            $this->newItemDeductAmount = $item->deduct_amount ?? 1;
            $this->showItemModal = true;
        }
    }

    public function saveItem()
    {
        $this->validate([
            'newItemCategoryId' => 'required|exists:menu_categories,id',
            'newItemName' => 'required|string|max:255',
            'newItemPrice' => 'required|numeric|min:0',
            'newItemDescription' => 'nullable|string',
            'newItemIsVeg' => 'boolean',
            'newItemImage' => 'nullable|image|max:2048'
        ]);

        $data = [
            'restaurant_id' => $this->restaurant->id,
            'menu_category_id' => $this->newItemCategoryId,
            'name' => $this->newItemName,
            'price' => $this->newItemPrice,
            'description' => $this->newItemDescription,
            'is_veg' => $this->newItemIsVeg,
            'is_available' => true,
            'inventory_item_id' => $this->newItemInventoryItemId ?: null,
            'auto_deduct_inventory' => $this->newItemAutoDeduct,
            'deduct_amount' => $this->newItemDeductAmount ?? 1,
        ];

        if ($this->newItemImage) {
            $data['image'] = $this->processAndStoreImage($this->newItemImage, 'menu_items');
        }

        if ($this->editingItemId) {
            MenuItem::find($this->editingItemId)?->update($data);
        } else {
            // Check limits for new items
            $limit = $this->activeLicense['limits']['items'] ?? 0;
            if ($limit > 0 && $this->items->count() >= $limit) {
                session()->flash('error', "Limit reached: Your plan allows a maximum of {$limit} items. Please upgrade to add more.");
                return;
            }
            MenuItem::create($data);
        }
        
        $this->toggleItemModal();
        $this->loadData();
    }

    public function deleteItem($id)
    {
        MenuItem::find($id)?->delete();
        $this->loadData();
    }

    // Excel/CSV Import
    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $this->importResult = '';
        $this->importError = '';

        try {
            $path = $this->excelFile->getRealPath();
            $extension = $this->excelFile->getClientOriginalExtension();

            $rows = [];
            
            // Parse CSV/TXT
            if (in_array(strtolower($extension), ['csv', 'txt'])) {
                $handle = fopen($path, 'r');
                $header = null;
                while (($line = fgetcsv($handle)) !== false) {
                    if (!$header) {
                        $header = array_map(fn($h) => strtolower(trim($h)), $line);
                        continue;
                    }
                    if (count($line) >= 2) {
                        $rows[] = array_combine($header, array_pad($line, count($header), ''));
                    }
                }
                fclose($handle);
            } else {
                $this->importError = 'Please use CSV format. Save your Excel file as CSV first.';
                return;
            }

            $created = 0;
            $skipped = 0;

            foreach ($rows as $row) {
                $categoryName = trim($row['category'] ?? '');
                $itemName = trim($row['name'] ?? $row['item name'] ?? $row['item'] ?? '');
                $price = floatval($row['price'] ?? 0);
                $description = trim($row['description'] ?? '');
                $isVeg = isset($row['veg']) ? (strtolower(trim($row['veg'])) === 'yes' || $row['veg'] === '1') : true;

                if (empty($itemName) || $price <= 0) {
                    $skipped++;
                    continue;
                }

                // Auto-create category if needed
                $category = null;
                if (!empty($categoryName)) {
                    $category = MenuCategory::firstOrCreate(
                        ['restaurant_id' => $this->restaurant->id, 'name' => $categoryName],
                        ['department' => 'kitchen', 'is_active' => true]
                    );
                } else {
                    $category = $this->categories->first();
                }

                if (!$category) {
                    $skipped++;
                    continue;
                }

                // Check limits
                $limit = $this->activeLicense['limits']['items'] ?? 0;
                if ($limit > 0 && MenuItem::where('restaurant_id', $this->restaurant->id)->count() >= $limit) {
                    $this->importError = "Import partially completed. Reached your plan limit of {$limit} items.";
                    break;
                }

                MenuItem::create([
                    'restaurant_id' => $this->restaurant->id,
                    'menu_category_id' => $category->id,
                    'name' => $itemName,
                    'price' => $price,
                    'description' => $description,
                    'is_veg' => $isVeg,
                    'is_available' => true,
                ]);
                $created++;
            }

            $this->importResult = "✅ Imported {$created} items" . ($skipped > 0 ? ", skipped {$skipped} rows" : "");
            $this->excelFile = null;
            $this->loadData();

        } catch (\Exception $e) {
            $this->importError = 'Import failed: ' . $e->getMessage();
        }
    }

    private function processAndStoreImage($uploadedFile, $folder)
    {
        // Fallback to original if GD is not enabled on this server
        if (!function_exists('imagecreatefromjpeg')) {
            return $uploadedFile->store($folder, 'public');
        }

        try {
            $filename = uniqid() . '.webp';
            $storagePath = storage_path('app/public/' . $folder);
            
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $imagePath = $uploadedFile->getRealPath();
            $imageInfo = @getimagesize($imagePath);
            if (!$imageInfo) return $uploadedFile->store($folder, 'public');
            
            $mime = $imageInfo['mime'];
            $image = null;

            switch ($mime) {
                case 'image/jpeg': $image = @imagecreatefromjpeg($imagePath); break;
                case 'image/png': 
                    $image = @imagecreatefrompng($imagePath);
                    if ($image) {
                        imagepalettetotruecolor($image);
                        imagealphablending($image, true);
                        imagesavealpha($image, true);
                    }
                    break;
                case 'image/webp': 
                    if (function_exists('imagecreatefromwebp')) {
                        $image = @imagecreatefromwebp($imagePath);
                    }
                    break;
            }

            if (!$image) return $uploadedFile->store($folder, 'public');

            // Resize if too large (max 800px)
            $width = imagesx($image);
            $height = imagesy($image);
            $maxSize = 800;
            
            if ($width > $maxSize || $height > $maxSize) {
                if ($width > $height) {
                    $newWidth = $maxSize;
                    $newHeight = floor($height * ($maxSize / $width));
                } else {
                    $newHeight = $maxSize;
                    $newWidth = floor($width * ($maxSize / $height));
                }
                $tmp = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $tmp;
            }

            // Double check imagewebp exists
            if (function_exists('imagewebp')) {
                imagewebp($image, $storagePath . '/' . $filename, 80);
                imagedestroy($image);
                return $folder . '/' . $filename;
            }
            
            imagedestroy($image);
            return $uploadedFile->store($folder, 'public');
            
        } catch (\Exception $e) {
            return $uploadedFile->store($folder, 'public');
        }
    }

    public function render()
    {
        return view('livewire.admin.menu-manager')->layout('components.layouts.app', ['title' => 'Menu Manager']);
    }
}
