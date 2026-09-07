<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Menu Items</h2>
            <p class="text-slate-500 text-sm mt-1">Manage your food items, prices, and categories.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="toggleCategoryModal" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors shadow-sm flex items-center">
                <svg class="w-5 h-5 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                Add Category
            </button>
            <button wire:click="toggleItemModal" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Menu Item
            </button>
        </div>
    </div>

    <!-- Excel/CSV Import Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <h3 class="font-bold text-slate-700 text-sm mb-1">📊 Import from CSV / Excel</h3>
                <p class="text-xs text-slate-400">Upload a CSV file with columns: <code class="bg-slate-100 px-1 rounded">Category, Name, Price, Description, Veg</code> (Veg: yes/no)</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mt-3 sm:mt-0">
                <input wire:model="excelFile" type="file" accept=".csv,.txt,.xlsx,.xls" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <button wire:click="importExcel" type="button" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-sm whitespace-nowrap" {{ $excelFile ? '' : 'disabled' }}>
                    Import
                </button>
            </div>
        </div>
        <div wire:loading wire:target="excelFile" class="text-sm text-slate-500 mt-2">Uploading file...</div>
        @if($importResult)
        <div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm font-semibold text-emerald-700">{{ $importResult }}</div>
        @endif
        @if($importError)
        <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm font-semibold text-red-700">{{ $importError }}</div>
        @endif
    </div>

    <!-- Modals -->
    <!-- Category Modal -->
    @if($showCategoryModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
            <h3 class="text-xl font-bold text-slate-800 mb-4">{{ $editingCategoryId ? 'Edit Category' : 'Add Category' }}</h3>
            <form wire:submit.prevent="saveCategory">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category Name</label>
                    <input wire:model="newCategoryName" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none" required placeholder="e.g. Momo">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Routing Department</label>
                    <select wire:model="newCategoryDepartment" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none" required>
                        <option value="kitchen">Kitchen (Food)</option>
                        <option value="bar">Bar (Drinks/Beverages)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category Image</label>
                    <input wire:model="newCategoryImage" type="file" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" accept="image/*">
                    <div wire:loading wire:target="newCategoryImage" class="text-sm text-slate-500 mt-1">Uploading...</div>
                    @if ($newCategoryImage)
                        <img src="{{ $newCategoryImage->temporaryUrl() }}" class="mt-2 h-20 w-20 object-cover rounded-lg">
                    @endif
                </div>
                <div class="flex gap-3">
                    <button type="button" wire:click="toggleCategoryModal" class="flex-1 px-4 py-2 bg-slate-100 rounded-lg">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg">{{ $editingCategoryId ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Item Modal -->
    @if($showItemModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
            <h3 class="text-xl font-bold text-slate-800 mb-4">{{ $editingItemId ? 'Edit Menu Item' : 'Add Menu Item' }}</h3>
            <form wire:submit.prevent="saveItem" class="space-y-4">
                @if($categories->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-red-500 text-sm font-medium mb-4">Please create a category first before adding items.</p>
                        <button type="button" wire:click="toggleItemModal" class="px-6 py-2 bg-slate-100 text-slate-700 font-bold rounded-lg hover:bg-slate-200 transition-colors">Close</button>
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                        <select wire:model="newItemCategoryId" class="w-full px-4 py-2 border rounded-lg" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Item Name</label>
                        <input wire:model="newItemName" type="text" class="w-full px-4 py-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Price (Rs.)</label>
                        <input wire:model="newItemPrice" type="number" class="w-full px-4 py-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea wire:model="newItemDescription" class="w-full px-4 py-2 border rounded-lg" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Item Image</label>
                        <input wire:model="newItemImage" type="file" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" accept="image/*">
                        <div wire:loading wire:target="newItemImage" class="text-sm text-slate-500 mt-1">Uploading...</div>
                        @if ($newItemImage)
                            <img src="{{ $newItemImage->temporaryUrl() }}" class="mt-2 h-20 w-20 object-cover rounded-lg">
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="newItemIsVeg" type="checkbox" id="is_veg" class="w-4 h-4 text-emerald-600">
                        <label for="is_veg" class="text-sm font-medium text-slate-700">Is Vegetarian?</label>
                    </div>

                    <!-- Inventory Link Section -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-400">Inventory Automation</h4>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Link to Stock Item</label>
                            <select wire:model.live="newItemInventoryItemId" class="w-full px-4 py-2 border rounded-lg text-sm">
                                <option value="">-- No Link --</option>
                                @foreach($inventoryItems as $invItem)
                                    <option value="{{ $invItem->id }}">{{ $invItem->name }} ({{ $invItem->quantity }} {{ $invItem->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        @if($newItemInventoryItemId)
                        <div class="flex items-center gap-2">
                            <input wire:model="newItemAutoDeduct" type="checkbox" id="auto_deduct" class="w-4 h-4 text-emerald-600">
                            <label for="auto_deduct" class="text-xs font-bold text-slate-700">Auto-deduct stock on order</label>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Deduct Amount Per Order <span class="text-slate-400">(e.g. 50 for 50ml peg)</span></label>
                            <input wire:model="newItemDeductAmount" type="number" step="0.01" min="0.01" class="w-full px-4 py-2 border rounded-lg text-sm" placeholder="50">
                            <p class="text-[10px] text-slate-400 mt-1">How much stock to deduct each time this item is ordered. Uses the inventory item's unit (ml, g, pcs, etc.)</p>
                        </div>
                        @endif
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="toggleItemModal" class="flex-1 px-4 py-2 bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg">{{ $editingItemId ? 'Update Item' : 'Save Item' }}</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
    @endif

    <!-- Data Display -->
    @if($categories->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 flex flex-col items-center justify-center text-center">
            <h3 class="text-lg font-medium text-slate-900">No menu items yet</h3>
            <p class="mt-1 text-sm text-slate-500">Get started by creating categories and adding items.</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach($categories as $category)
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-2">
                        <div class="flex items-center gap-2">
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" class="w-8 h-8 rounded-full object-cover shadow-sm">
                            @endif
                            <h3 class="text-xl font-bold text-slate-800">{{ $category->name }}</h3>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $category->department == 'bar' ? 'bg-amber-100 text-amber-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ ucfirst($category->department) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="editCategory({{ $category->id }})" class="text-sm text-blue-500 hover:underline">Edit</button>
                            <button wire:click="deleteCategory({{ $category->id }})" class="text-sm text-red-500 hover:underline">Delete</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($items->where('menu_category_id', $category->id) as $item)
                            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex flex-col justify-between group relative overflow-hidden">
                                @if($item->image)
                                    <div class="-mx-4 -mt-4 mb-4 h-40 bg-slate-100 relative group-hover:opacity-95 transition-all">
                                        <img src="{{ Storage::url($item->image) }}" 
                                             onerror="this.src='https://placehold.co/600x400/f1f5f9/64748b?text={{ urlencode($item->name) }}'; this.className='w-full h-full object-contain p-8 opacity-50';"
                                             class="w-full h-full object-cover" 
                                             alt="{{ $item->name }}">
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                                        <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-all transform translate-y-[-10px] group-hover:translate-y-0">
                                            <button wire:click="editItem({{ $item->id }})" class="text-white bg-blue-600 shadow-lg rounded-full p-1.5 hover:scale-110 transition-transform">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <button wire:click="deleteItem({{ $item->id }})" class="text-white bg-red-600 shadow-lg rounded-full p-1.5 hover:scale-110 transition-transform">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                        <button wire:click="editItem({{ $item->id }})" class="text-blue-400 hover:text-blue-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button wire:click="deleteItem({{ $item->id }})" class="text-red-400 hover:text-red-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                @endif
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-semibold text-slate-800">{{ $item->name }}</h4>
                                        <span class="w-4 h-4 rounded-sm border {{ $item->is_veg ? 'border-green-500' : 'border-red-500' }} flex items-center justify-center p-0.5"><span class="w-full h-full {{ $item->is_veg ? 'bg-green-500' : 'bg-red-500' }} rounded-full"></span></span>
                                    </div>
                                    <p class="text-sm text-slate-500 line-clamp-2">{{ $item->description }}</p>
                                </div>
                                <div class="mt-4 font-bold text-slate-900">Rs. {{ number_format($item->price, 0) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
