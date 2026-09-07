"use client";

import React, { useState } from "react";
import { 
  Receipt, Settings, RefreshCw, CheckCircle2, XCircle, 
  AlertCircle, Loader2, Search, Sliders, Building, Key, Play,
  Plus, Trash2, Printer, Download, Pencil
} from "lucide-react";
import toast from "react-hot-toast";

interface Order {
  id: string;
  fullName: string;
  phone: string;
  address: string;
  items: string;
  totalPrice: number;
  discount?: number;
  status: string;
  nepalEbillingSynced: boolean;
  nepalEbillingInvoiceId: string | null;
  nepalEbillingError: string | null;
  createdAt: Date | string;
  customerPan?: string | null;
}

interface BillingClientProps {
  initialSettings: {
    nepal_ebilling_enabled: string;
    nepal_ebilling_api_key: string;
    nepal_ebilling_environment: string;
    nepal_ebilling_seller_pan: string;
    nepal_ebilling_subdomain: string;
  };
  initialOrders: Order[];
  clients?: any[];
  users?: any[];
  plans?: any[];
  combos?: any[];
  hardware?: any[];
  subscriptionPayments?: any[];
}

export default function BillingClient({ 
  initialSettings, 
  initialOrders, 
  clients = [], 
  users = [],
  plans = [],
  combos = [],
  hardware = [],
  subscriptionPayments = []
}: BillingClientProps) {
  const [activeTab, setActiveTab] = useState<"log" | "settings" | "subscriptions">("log");
  const [orders, setOrders] = useState<Order[]>(initialOrders);
  const [payments, setPayments] = useState<any[]>(subscriptionPayments);
  const [processingPaymentId, setProcessingPaymentId] = useState<string | null>(null);

  const handleApprovePayment = async (paymentId: string, action: "approve" | "reject") => {
    const confirmMsg = action === "approve" 
      ? "Are you sure you want to verify and APPROVE this payment? This will automatically activate their package."
      : "Are you sure you want to REJECT this payment receipt?";
      
    if (!confirm(confirmMsg)) return;

    setProcessingPaymentId(paymentId);
    const toastId = toast.loading(action === "approve" ? "Approving payment and activating plan..." : "Rejecting payment...");

    try {
      const res = await fetch("/api/admin/billing/approve", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ paymentId, action })
      });

      const data = await res.json();
      if (res.ok && data.success) {
        toast.success(data.message, { id: toastId });
        setPayments(prev => prev.map(p => {
          if (p.id === paymentId) {
            return {
              ...p,
              status: action === "approve" ? "COMPLETED" : "REJECTED"
            };
          }
          return p;
        }));
      } else {
        toast.error(data.error || "Action failed.", { id: toastId });
      }
    } catch (err: any) {
      toast.error(`Error: ${err.message}`, { id: toastId });
    } finally {
      setProcessingPaymentId(null);
    }
  };
  
  // Autocomplete data mapping
  const unifiedBuyers = React.useMemo(() => {
    const list: { name: string; phone: string; address: string; pan: string }[] = [];
    
    // Offline client accounts
    clients.forEach((c: any) => {
      list.push({
        name: c.restaurantName || "",
        phone: c.contactNumber || "",
        address: c.location || "",
        pan: ""
      });
    });

    // Cloud users/restaurants
    users.forEach((u: any) => {
      if (u.restaurants && u.restaurants.length > 0) {
        u.restaurants.forEach((r: any) => {
          list.push({
            name: r.name || u.name || "",
            phone: r.phone || u.phone || "",
            address: r.address || "",
            pan: r.panNumber || ""
          });
        });
      } else {
        list.push({
          name: u.name || "",
          phone: u.phone || "",
          address: "",
          pan: ""
        });
      }
    });

    const seen = new Set<string>();
    return list.filter(item => {
      const key = `${item.name.toLowerCase()}|${item.phone}`;
      if (seen.has(key)) return false;
      seen.add(key);
      return true;
    });
  }, [clients, users]);

  const unifiedItems = React.useMemo(() => {
    const list: { name: string; price: number; type: "Plan" | "Combo" | "Hardware" }[] = [];
    
    // Plans
    plans.forEach((p: any) => {
      list.push({
        name: `${p.name} (Yearly Plan)`,
        price: p.priceYearly || 0,
        type: "Plan"
      });
      list.push({
        name: `${p.name} (Half-Yearly Plan)`,
        price: p.priceHalfYearly || 0,
        type: "Plan"
      });
    });

    // Combos
    combos.forEach((c: any) => {
      list.push({
        name: c.name || "",
        price: c.price || 0,
        type: "Combo"
      });
    });

    // Hardware
    hardware.forEach((h: any) => {
      list.push({
        name: h.name || "",
        price: h.price || 0,
        type: "Hardware"
      });
    });

    return list;
  }, [plans, combos, hardware]);

  // Autocomplete UI States
  const [showBuyerNameSuggestions, setShowBuyerNameSuggestions] = useState(false);
  const [showBuyerPhoneSuggestions, setShowBuyerPhoneSuggestions] = useState(false);
  const [activeItemSuggestIndex, setActiveItemSuggestIndex] = useState<number | null>(null);

  // Manual Bill Modal States
  const [showAddModal, setShowAddModal] = useState(false);
  const [editingOrder, setEditingOrder] = useState<Order | null>(null);
  const [buyerName, setBuyerName] = useState("");
  const [buyerPhone, setBuyerPhone] = useState("");
  const [buyerAddress, setBuyerAddress] = useState("");
  const [customerPan, setCustomerPan] = useState("");
  const [billItems, setBillItems] = useState<{ name: string; price: number; quantity: number }[]>([
    { name: "", price: 0, quantity: 1 }
  ]);
  const [discountPercent, setDiscountPercent] = useState<number>(0);
  const [discountAmountInput, setDiscountAmountInput] = useState<number>(0);
  const [discountType, setDiscountType] = useState<"percent" | "amount">("percent");
  const [dateFilter, setDateFilter] = useState<string>("all");
  const [customStartDate, setCustomStartDate] = useState<string>("");
  const [customEndDate, setCustomEndDate] = useState<string>("");

  const checkDateFilter = (createdAtStr: string | Date) => {
    if (dateFilter === "all") return true;
    const itemDate = new Date(createdAtStr);
    const now = new Date();

    if (dateFilter === "today") {
      return itemDate.toDateString() === now.toDateString();
    } else if (dateFilter === "yesterday") {
      const yesterday = new Date();
      yesterday.setDate(now.getDate() - 1);
      return itemDate.toDateString() === yesterday.toDateString();
    } else if (dateFilter === "7days") {
      const sevenDaysAgo = new Date();
      sevenDaysAgo.setDate(now.getDate() - 7);
      return itemDate >= sevenDaysAgo;
    } else if (dateFilter === "30days") {
      const thirtyDaysAgo = new Date();
      thirtyDaysAgo.setDate(now.getDate() - 30);
      return itemDate >= thirtyDaysAgo;
    } else if (dateFilter === "custom") {
      if (customStartDate && new Date(createdAtStr) < new Date(customStartDate)) return false;
      if (customEndDate) {
        const end = new Date(customEndDate);
        end.setHours(23, 59, 59, 999);
        if (new Date(createdAtStr) > end) return false;
      }
    }
    return true;
  };
  const [isCreatingBill, setIsCreatingBill] = useState(false);
  const [selectedInvoiceForPrint, setSelectedInvoiceForPrint] = useState<Order | null>(null);

  const filteredBuyerNameSuggestions = React.useMemo(() => {
    if (!buyerName.trim()) return [];
    return unifiedBuyers.filter(item => 
      item.name.toLowerCase().includes(buyerName.toLowerCase()) ||
      item.phone.includes(buyerName)
    );
  }, [buyerName, unifiedBuyers]);

  const filteredBuyerPhoneSuggestions = React.useMemo(() => {
    if (!buyerPhone.trim()) return [];
    return unifiedBuyers.filter(item => 
      item.phone.includes(buyerPhone) ||
      item.name.toLowerCase().includes(buyerPhone.toLowerCase())
    );
  }, [buyerPhone, unifiedBuyers]);

  const handlePrintInvoice = (order: Order) => {
    setSelectedInvoiceForPrint(order);
    setTimeout(() => {
      window.print();
    }, 150);
  };

  const handleAddItem = () => {
    setBillItems(prev => [...prev, { name: "", price: 0, quantity: 1 }]);
  };

  const handleRemoveItem = (index: number) => {
    if (billItems.length === 1) {
      toast.error("At least one item is required.");
      return;
    }
    setBillItems(prev => prev.filter((_, i) => i !== index));
  };

  const handleItemChange = (index: number, field: "name" | "price" | "quantity", value: string | number) => {
    setBillItems(prev => prev.map((item, i) => {
      if (i === index) {
        return {
          ...item,
          [field]: field === "name" ? value : Number(value)
        };
      }
      return item;
    }));
  };

  // Calculations with Discount
  const rawTotal = billItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  const discountAmount = discountType === "percent" 
    ? Math.round(((rawTotal * (discountPercent || 0)) / 100) * 100) / 100 
    : (discountAmountInput || 0);
  const calculatedTotal = Math.max(0, Math.round((rawTotal - discountAmount) * 100) / 100);
  const calculatedSubtotal = Math.round((calculatedTotal / 1.13) * 100) / 100;
  const calculatedVat = Math.round((calculatedTotal - calculatedSubtotal) * 100) / 100;

  const handleSubmitBill = async (e: React.FormEvent) => {
    e.preventDefault();
    
    // Validation
    if (!buyerName.trim() || !buyerPhone.trim() || !buyerAddress.trim()) {
      toast.error("Please fill in all buyer fields.");
      return;
    }

    const phoneRegex = /^(98|97)\d{8}$/;
    if (!phoneRegex.test(buyerPhone.trim())) {
      toast.error("Phone number must be a valid 10-digit Nepal mobile number starting with 98 or 97.");
      return;
    }

    if (customerPan.trim() !== "") {
      const panRegex = /^\d{9}$/;
      if (!panRegex.test(customerPan.trim())) {
        toast.error("Customer PAN must be exactly 9 digits.");
        return;
      }
    }

    const invalidItem = billItems.find(item => !item.name.trim() || item.price <= 0 || item.quantity <= 0);
    if (invalidItem) {
      toast.error("Please ensure all items have a valid name, price, and quantity.");
      return;
    }

    setIsCreatingBill(true);
    const toastId = toast.loading(
      editingOrder 
        ? "Saving updates and re-syncing with Nepal E-Billing..." 
        : "Creating invoice and syncing with Nepal E-Billing..."
    );

    try {
      const url = editingOrder ? `/api/admin/orders/${editingOrder.id}` : "/api/admin/orders";
      const method = editingOrder ? "PUT" : "POST";
      const bodyPayload: any = {
        fullName: buyerName,
        phone: buyerPhone,
        address: buyerAddress,
        customerPan: customerPan,
        items: JSON.stringify(billItems),
        discount: discountAmount,
        totalPrice: calculatedTotal,
      };

      if (editingOrder) {
        bodyPayload.status = "COMPLETED";
      } else {
        bodyPayload.status = "COMPLETED";
      }

      const res = await fetch(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(bodyPayload)
      });

      const data = await res.json();
      if (res.ok) {
        toast.success(
          editingOrder 
            ? "Order updated and synced successfully!" 
            : "Manual bill created and synced successfully!", 
          { id: toastId }
        );
        
        if (editingOrder) {
          setOrders(prev => prev.map(o => o.id === editingOrder.id ? data : o));
        } else {
          setOrders(prev => [data.order, ...prev]);
        }
        
        // Reset modal form
        setBuyerName("");
        setBuyerPhone("");
        setBuyerAddress("");
        setBillItems([{ name: "", price: 0, quantity: 1 }]);
        setShowAddModal(false);
        setEditingOrder(null);
      } else {
        toast.error(data.error || "Failed to submit bill.", { id: toastId });
      }
    } catch (err: any) {
      toast.error(`Error: ${err.message}`, { id: toastId });
    } finally {
      setIsCreatingBill(false);
    }
  };
  
  // Settings Form State
  const [enabled, setEnabled] = useState(initialSettings.nepal_ebilling_enabled === "true");
  const [apiKey, setApiKey] = useState(initialSettings.nepal_ebilling_api_key || "");
  const [environment, setEnvironment] = useState(initialSettings.nepal_ebilling_environment || "staging");
  const [sellerPan, setSellerPan] = useState(initialSettings.nepal_ebilling_seller_pan || "");
  const [subdomain, setSubdomain] = useState(initialSettings.nepal_ebilling_subdomain || "");
  
  // States
  const [isSaving, setIsSaving] = useState(false);
  const [isTesting, setIsTesting] = useState(false);
  const [syncingOrderId, setSyncingOrderId] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [statusFilter, setStatusFilter] = useState<"ALL" | "SYNCED" | "FAILED" | "PENDING">("ALL");

  // Save Settings
  const handleSaveSettings = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    const toastId = toast.loading("Saving settings...");

    try {
      const res = await fetch("/api/admin/settings", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          nepal_ebilling_enabled: String(enabled),
          nepal_ebilling_api_key: apiKey,
          nepal_ebilling_environment: environment,
          nepal_ebilling_seller_pan: sellerPan,
          nepal_ebilling_subdomain: subdomain
        })
      });

      if (res.ok) {
        toast.success("E-Billing configurations saved!", { id: toastId });
      } else {
        toast.error("Failed to save settings", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred while saving", { id: toastId });
    } finally {
      setIsSaving(false);
    }
  };

  // Test API connection
  const handleTestConnection = async () => {
    if (!apiKey) {
      toast.error("API Key is required to test connection.");
      return;
    }

    if (!subdomain.trim()) {
      toast.error("Tenant subdomain is required. Use the subdomain from your Nepal E-Billing login URL.");
      return;
    }

    setIsTesting(true);
    const toastId = toast.loading("Testing connection to Nepal E-Billing API...");

    try {
      const res = await fetch("/api/admin/settings/test-ebilling", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ apiKey, environment, subdomain })
      });

      const data = await res.json();
      if (res.ok && data.success) {
        toast.success("Connection Successful! API Key is valid.", { id: toastId });
      } else {
        toast.error(data.error || "Connection Failed. Please check API Key.", { id: toastId });
      }
    } catch (err: any) {
      toast.error(`Connection failed: ${err.message}`, { id: toastId });
    } finally {
      setIsTesting(false);
    }
  };

  // Sync / Retry Single Hardware Order
  const handleSyncOrder = async (orderId: string) => {
    setSyncingOrderId(orderId);
    const toastId = toast.loading("Syncing order to Nepal E-Billing...");

    try {
      const res = await fetch(`/api/admin/orders/${orderId}/sync`, {
        method: "POST"
      });

      const data = await res.json();
      if (res.ok && data.success) {
        toast.success("Order synced successfully!", { id: toastId });
        
        // Update local state
        setOrders(prev => prev.map(o => {
          if (o.id === orderId) {
            return {
              ...o,
              nepalEbillingSynced: true,
              nepalEbillingInvoiceId: data.invoiceId,
              nepalEbillingError: null
            };
          }
          return o;
        }));
      } else {
        const errorMsg = data.error || "Failed to sync order.";
        toast.error(errorMsg, { id: toastId });
        
        setOrders(prev => prev.map(o => {
          if (o.id === orderId) {
            return {
              ...o,
              nepalEbillingSynced: false,
              nepalEbillingError: errorMsg
            };
          }
          return o;
        }));
      }
    } catch (err: any) {
      toast.error(`Error: ${err.message}`, { id: toastId });
    } finally {
      setSyncingOrderId(null);
    }
  };

  const handleEditClick = (order: Order) => {
    setEditingOrder(order);
    setBuyerName(order.fullName);
    setBuyerPhone(order.phone);
    setBuyerAddress(order.address);
    setCustomerPan(order.customerPan || "");
    try {
      setBillItems(JSON.parse(order.items));
    } catch (e) {
      setBillItems([{ name: "", price: order.totalPrice, quantity: 1 }]);
    }
    setDiscountPercent(0);
    setDiscountAmountInput(order.discount || 0);
    setDiscountType(order.discount ? "amount" : "percent");
    setShowAddModal(true);
  };

  const handleDeleteOrder = async (orderId: string) => {
    if (!confirm("Are you sure you want to delete this order? This action cannot be undone.")) {
      return;
    }

    const toastId = toast.loading("Deleting hardware order...");
    try {
      const res = await fetch(`/api/admin/orders/${orderId}`, {
        method: "DELETE"
      });

      const data = await res.json();
      if (res.ok && data.success) {
        toast.success("Order deleted successfully!", { id: toastId });
        setOrders(prev => prev.filter(o => o.id !== orderId));
      } else {
        toast.error(data.error || "Failed to delete order.", { id: toastId });
      }
    } catch (err: any) {
      toast.error(`Error: ${err.message}`, { id: toastId });
    }
  };

  // Filter orders
  const filteredOrders = orders.filter(o => {
    // We only bill COMPLETED orders
    const matchesSearch = 
      o.fullName.toLowerCase().includes(searchQuery.toLowerCase()) ||
      o.address.toLowerCase().includes(searchQuery.toLowerCase()) ||
      (o.customerPan && o.customerPan.toLowerCase().includes(searchQuery.toLowerCase())) ||
      o.id.toLowerCase().includes(searchQuery.toLowerCase());
    
    let matchesStatus = true;
    if (statusFilter === "SYNCED") {
      matchesStatus = o.nepalEbillingSynced;
    } else if (statusFilter === "FAILED") {
      matchesStatus = !o.nepalEbillingSynced && !!o.nepalEbillingError;
    } else if (statusFilter === "PENDING") {
      matchesStatus = !o.nepalEbillingSynced && !o.nepalEbillingError;
    }

    return matchesSearch && matchesStatus;
  });

  return (
    <>
      <div className="print:hidden space-y-6 max-w-7xl mx-auto pb-20">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-[#111111] dark:text-white">E-Billing Management</h1>
          <p className="text-sm text-slate-500 dark:text-gray-400 mt-1">
            Configure Nepal E-Billing VAT API integration and manage hardware sales invoice synchronization logs.
          </p>
        </div>
        <button
          onClick={() => {
            setEditingOrder(null);
            setBuyerName("");
            setBuyerPhone("");
            setBuyerAddress("");
            setCustomerPan("");
            setBillItems([{ name: "", price: 0, quantity: 1 }]);
            setDiscountPercent(0);
            setDiscountAmountInput(0);
            setDiscountType("percent");
            setShowAddModal(true);
          }}
          className="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-600/10 transition-all self-start sm:self-auto"
        >
          <Plus size={16} />
          <span>Create Manual Bill</span>
        </button>
      </div>

      {/* Navigation tabs */}
      <div className="flex border-b border-slate-200 dark:border-[#333333] pb-4 gap-4">
        <button
          onClick={() => setActiveTab("log")}
          className={`flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all ${
            activeTab === "log"
              ? "bg-[#E53935] text-white shadow-lg"
              : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"
          }`}
        >
          <Receipt size={16} />
          <span>VAT Invoices Log</span>
        </button>

        <button
          onClick={() => setActiveTab("settings")}
          className={`flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all ${
            activeTab === "settings"
              ? "bg-[#E53935] text-white shadow-lg"
              : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"
          }`}
        >
          <Settings size={16} />
          <span>API Configurations</span>
        </button>

        <button
          onClick={() => setActiveTab("subscriptions")}
          className={`flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all ${
            activeTab === "subscriptions"
              ? "bg-[#E53935] text-white shadow-lg"
              : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"
          }`}
        >
          <Receipt size={16} />
          <span>Subscription Payments</span>
        </button>
      </div>

      {/* Main Content */}
      <div className="transition-all duration-300">
        
        {/* LOG TAB */}
        {activeTab === "log" && (
          <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            
            {/* Filters panel */}
            {(() => {
              const filteredOrders = orders.filter((o) => {
                let matchesStatus = true;
                if (statusFilter === "SYNCED") matchesStatus = o.nepalEbillingSynced;
                else if (statusFilter === "FAILED") matchesStatus = !o.nepalEbillingSynced && !!o.nepalEbillingError;
                else if (statusFilter === "PENDING") matchesStatus = !o.nepalEbillingSynced && !o.nepalEbillingError;

                const q = searchQuery.toLowerCase();
                const matchesSearch =
                  o.fullName.toLowerCase().includes(q) ||
                  o.phone.includes(q) ||
                  (o.address && o.address.toLowerCase().includes(q)) ||
                  (o.customerPan && o.customerPan.includes(q));

                const matchesDate = checkDateFilter(o.createdAt);
                return matchesStatus && matchesSearch && matchesDate;
              });

              return (
                <>
                  <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-[#1A1A1A] p-5 rounded-2xl border border-slate-200 dark:border-[#333333]">
                    <div className="flex flex-wrap items-center gap-3 w-full md:w-auto flex-1">
                      <div className="relative flex-1 max-w-xs">
                        <span className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                          <Search className="h-4 w-4 text-slate-400" />
                        </span>
                        <input
                          type="text"
                          value={searchQuery}
                          onChange={(e) => setSearchQuery(e.target.value)}
                          placeholder="Search firm name, location, or PAN..."
                          className="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-red-500"
                        />
                      </div>

                      {/* Date Filter */}
                      <div className="flex items-center gap-2">
                        <select
                          value={dateFilter}
                          onChange={(e) => setDateFilter(e.target.value)}
                          className="px-3 py-2 text-xs font-bold bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl text-[#111111] dark:text-white focus:outline-none cursor-pointer"
                        >
                          <option value="all">📅 All Time</option>
                          <option value="today">Today</option>
                          <option value="yesterday">Yesterday</option>
                          <option value="7days">Last 7 Days</option>
                          <option value="30days">Last 30 Days</option>
                          <option value="custom">Custom Range...</option>
                        </select>

                        {dateFilter === "custom" && (
                          <div className="flex items-center gap-1.5 animate-in fade-in duration-200">
                            <input
                              type="date"
                              value={customStartDate}
                              onChange={(e) => setCustomStartDate(e.target.value)}
                              className="px-2 py-1.5 text-xs bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-lg text-slate-800 dark:text-white focus:outline-none"
                            />
                            <span className="text-xs text-slate-400">to</span>
                            <input
                              type="date"
                              value={customEndDate}
                              onChange={(e) => setCustomEndDate(e.target.value)}
                              className="px-2 py-1.5 text-xs bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-lg text-slate-800 dark:text-white focus:outline-none"
                            />
                          </div>
                        )}
                      </div>
                    </div>

                    <div className="flex gap-2 p-1 bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl shrink-0">
                      {(["ALL", "SYNCED", "FAILED", "PENDING"] as const).map((filter) => (
                        <button
                          key={filter}
                          onClick={() => setStatusFilter(filter)}
                          className={`px-4 py-2 rounded-lg text-xs font-semibold transition-all whitespace-nowrap ${
                            statusFilter === filter
                              ? "bg-red-500 text-white shadow-sm"
                              : "text-slate-500 dark:text-slate-400 hover:text-[#111111] dark:hover:text-white"
                          }`}
                        >
                          {filter}
                          <span className={`ml-1.5 px-1.5 py-0.5 text-[10px] rounded-md ${
                            statusFilter === filter 
                              ? "bg-white/20 text-white" 
                              : "bg-slate-200 dark:bg-[#222] text-slate-600 dark:text-slate-400"
                          }`}>
                            {orders.filter(o => {
                              if (filter === "ALL") return true;
                              if (filter === "SYNCED") return o.nepalEbillingSynced;
                              if (filter === "FAILED") return !o.nepalEbillingSynced && !!o.nepalEbillingError;
                              if (filter === "PENDING") return !o.nepalEbillingSynced && !o.nepalEbillingError;
                              return true;
                            }).length}
                          </span>
                        </button>
                      ))}
                    </div>
                  </div>

            {/* Invoices Log Table */}
            <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden shadow-sm">
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-500 dark:text-gray-400">
                  <thead className="bg-slate-50 dark:bg-[#222222] text-slate-600 dark:text-gray-300 text-xs uppercase font-semibold">
                    <tr>
                      <th className="px-6 py-4">Order Details</th>
                      <th className="px-6 py-4">Buyer Info</th>
                      <th className="px-6 py-4">Total Amount</th>
                      <th className="px-6 py-4">Sync Status</th>
                      <th className="px-6 py-4">Remote Invoice ID / Trace</th>
                      <th className="px-6 py-4 text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100 dark:divide-[#333333]">
                    {filteredOrders.length === 0 && (
                      <tr>
                        <td colSpan={6} className="px-6 py-12 text-center text-slate-400 dark:text-gray-500">
                          No matching hardware order invoices found in log.
                        </td>
                      </tr>
                    )}
                    {filteredOrders.map((order) => {
                      const displayDate = new Date(order.createdAt).toLocaleDateString(undefined, {
                        month: "short",
                        day: "numeric",
                        year: "numeric"
                      });

                      return (
                        <tr key={order.id} className="hover:bg-slate-50/50 dark:bg-[#1A1A1A] dark:hover:bg-[#222222]/30 transition-colors">
                          <td className="px-6 py-4">
                            <div className="font-bold text-[#111111] dark:text-white text-xs">
                              HW-{order.id.slice(0, 8).toUpperCase()}
                            </div>
                            <div className="text-[10px] text-slate-400 mt-0.5">{displayDate}</div>
                          </td>
                          <td className="px-6 py-4">
                            <div className="font-semibold text-slate-700 dark:text-slate-300 text-xs">{order.fullName}</div>
                            <div className="text-[10px] text-slate-400 mt-0.5">{order.phone}</div>
                          </td>
                          <td className="px-6 py-4 font-bold text-slate-900 dark:text-white">
                            Rs. {order.totalPrice.toLocaleString()}
                          </td>
                          <td className="px-6 py-4">
                            {order.nepalEbillingSynced ? (
                              <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                <CheckCircle2 size={12} />
                                Synced
                              </span>
                            ) : order.nepalEbillingError ? (
                              <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-500 border border-rose-500/20">
                                <XCircle size={12} />
                                Failed
                              </span>
                            ) : (
                              <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-500 border border-slate-500/20">
                                <AlertCircle size={12} />
                                Pending
                              </span>
                            )}
                          </td>
                          <td className="px-6 py-4 max-w-xs truncate">
                            {order.nepalEbillingSynced ? (
                              <span className="font-mono text-xs font-semibold text-slate-600 dark:text-slate-400">
                                {order.nepalEbillingInvoiceId}
                              </span>
                            ) : order.nepalEbillingError ? (
                              <span className="text-xs text-rose-500 font-medium whitespace-pre-wrap block leading-snug" title={order.nepalEbillingError}>
                                {order.nepalEbillingError}
                              </span>
                            ) : (
                              <span className="text-xs text-slate-400 italic">Not sent to API yet</span>
                            )}
                          </td>
                          <td className="px-6 py-4 text-right">
                            <div className="flex justify-end gap-2">
                              <button
                                onClick={() => handlePrintInvoice(order)}
                                className="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-[#222222] dark:hover:bg-[#2c2c2c] text-slate-700 dark:text-gray-300 rounded-lg text-xs font-bold transition-all"
                                title="Print Invoice"
                              >
                                <Printer size={12} />
                                <span>Print</span>
                              </button>

                              <button
                                onClick={() => handlePrintInvoice(order)}
                                className="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/20 dark:hover:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-bold transition-all"
                                title="Save PDF"
                              >
                                <Download size={12} />
                                <span>Save PDF</span>
                              </button>

                              {!order.nepalEbillingSynced && (
                                <button
                                  onClick={() => handleEditClick(order)}
                                  className="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/20 dark:hover:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-bold transition-all"
                                  title="Edit Order"
                                >
                                  <Pencil size={12} />
                                  <span>Edit</span>
                                </button>
                              )}

                              <button
                                onClick={() => handleDeleteOrder(order.id)}
                                className="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-bold transition-all"
                                title="Delete Order"
                              >
                                <Trash2 size={12} />
                                <span>Delete</span>
                              </button>

                              <button
                                disabled={syncingOrderId === order.id || order.nepalEbillingSynced}
                                onClick={() => handleSyncOrder(order.id)}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-red-500 hover:text-white dark:bg-[#222222] dark:hover:bg-red-500 disabled:opacity-40 disabled:hover:bg-slate-100 disabled:dark:hover:bg-[#222222] disabled:hover:text-slate-700 disabled:dark:hover:text-gray-300 text-slate-700 dark:text-gray-300 rounded-lg text-xs font-bold transition-all"
                              >
                                {syncingOrderId === order.id ? (
                                  <Loader2 size={12} className="animate-spin" />
                                ) : (
                                  <RefreshCw size={12} />
                                )}
                                <span>{order.nepalEbillingSynced ? "Synced" : "Sync"}</span>
                              </button>
                            </div>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </div>
            </>
            );
          })()}
          </div>
        )}

        {/* SETTINGS TAB */}
        {activeTab === "settings" && (
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 shadow-sm max-w-3xl animate-in fade-in slide-in-from-bottom-4 duration-500">
            <h2 className="text-lg font-bold text-[#111111] dark:text-white mb-6 flex items-center gap-2">
              <Sliders size={20} className="text-red-500" />
              API Settings Configuration
            </h2>

            <form onSubmit={handleSaveSettings} className="space-y-6">
              {/* Toggle Switch */}
              <div className="flex items-center justify-between p-4 bg-slate-50 dark:bg-[#141414] rounded-xl border border-slate-200 dark:border-[#222222]">
                <div>
                  <h3 className="text-sm font-bold text-slate-800 dark:text-white">Enable Automated VAT Sync</h3>
                  <p className="text-xs text-slate-400 mt-1">Automatically send VAT invoices to Nepal E-Billing API when orders are completed.</p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={enabled}
                    onChange={(e) => setEnabled(e.target.checked)}
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-[#333] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                </label>
              </div>

              {/* API Key */}
              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider flex items-center gap-1.5">
                  <Key size={14} className="text-slate-400" />
                  Nepal E-Billing API Key
                </label>
                <input
                  type="password"
                  value={apiKey}
                  onChange={(e) => setApiKey(e.target.value)}
                  placeholder="bbk_bRZ2nTnL.S7y4QlaPxf5..."
                  className="w-full px-4 py-3 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/50 text-sm font-semibold"
                />
              </div>

              {/* Subdomain and Environment */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">
                    Tenant Subdomain
                  </label>
                  <input
                    type="text"
                    value={subdomain}
                    onChange={(e) => setSubdomain(e.target.value)}
                    placeholder="e.g. mycompany"
                    required
                    className="w-full px-4 py-3 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/50 text-sm font-semibold"
                  />
                  <p className="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    From your Nepal E-Billing login URL: <span className="font-mono">https://<strong>mycompany</strong>.{environment === "production" ? "nepalebilling.com" : "staging.nepalebilling.com"}</span>
                  </p>
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">
                    API Environment
                  </label>
                  <select
                    value={environment}
                    onChange={(e) => setEnvironment(e.target.value)}
                    className="w-full px-4 py-3 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/50 text-sm font-bold"
                  >
                    <option value="staging">Staging (Sandbox)</option>
                    <option value="production">Production (Live)</option>
                  </select>
                </div>
              </div>

              {/* Seller PAN */}
              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider flex items-center gap-1.5">
                  <Building size={14} className="text-slate-400" />
                  Seller PAN Number
                </label>
                <input
                  type="text"
                  value={sellerPan}
                  onChange={(e) => setSellerPan(e.target.value)}
                  placeholder="e.g. 609653245"
                  className="w-full px-4 py-3 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/50 text-sm font-semibold"
                />
              </div>

              {/* Buttons */}
              <div className="pt-6 border-t border-slate-100 dark:border-[#222222] flex flex-col sm:flex-row items-center justify-between gap-4">
                <button
                  type="button"
                  onClick={handleTestConnection}
                  disabled={isTesting}
                  className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-[#222222] dark:hover:bg-[#2b2b2b] text-slate-800 dark:text-white rounded-xl text-sm font-bold transition-all disabled:opacity-50"
                >
                  {isTesting ? <Loader2 size={16} className="animate-spin" /> : <Play size={16} />}
                  <span>Test Connection</span>
                </button>

                <button
                  type="submit"
                  disabled={isSaving}
                  className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold transition-all disabled:opacity-50 shadow-md shadow-red-500/10"
                >
                  {isSaving ? <Loader2 size={16} className="animate-spin" /> : null}
                  <span>Save Settings</span>
                </button>
              </div>
            </form>
          </div>
        )}

        {/* SUBSCRIPTION PAYMENTS TAB */}
        {activeTab === "subscriptions" && (
          <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden shadow-xl">
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-500 dark:text-gray-400">
                  <thead className="bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-300 text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                      <th className="px-6 py-4">Restaurant / Owner</th>
                      <th className="px-6 py-4">Plan</th>
                      <th className="px-6 py-4">Billing Cycle</th>
                      <th className="px-6 py-4">Amount Paid</th>
                      <th className="px-6 py-4">Method</th>
                      <th className="px-6 py-4">Receipt</th>
                      <th className="px-6 py-4">Status</th>
                      <th className="px-6 py-4">Payment Date</th>
                      <th className="px-6 py-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-150 dark:divide-[#333333]">
                    {payments.length === 0 && (
                      <tr>
                        <td colSpan={9} className="px-6 py-16 text-center text-slate-400 dark:text-gray-500">
                          No subscription payments found.
                        </td>
                      </tr>
                    )}
                    {payments.map((payment: any) => (
                      <tr key={payment.id} className="hover:bg-slate-50 dark:hover:bg-[#222]/30 transition-colors">
                        <td className="px-6 py-4 font-bold text-slate-900 dark:text-white">
                          <div>{payment.restaurantName}</div>
                          <div className="text-[10px] text-slate-400 font-mono mt-0.5 flex items-center gap-1.5">
                            <span className="text-[#E53935] font-extrabold">{payment.paymentCode || "TXN-PENDING"}</span>
                            <span>|</span>
                            <span>Cust ID: {payment.user?.customerCode || "N/A"}</span>
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-[#E53935]/10 text-[#E53935] uppercase border border-[#E53935]/20">
                            {payment.planId.toUpperCase()}
                          </span>
                        </td>
                        <td className="px-6 py-4 capitalize font-semibold text-slate-700 dark:text-gray-305">
                          {payment.billingCycle}
                        </td>
                        <td className="px-6 py-4 font-black text-slate-950 dark:text-white">
                          Rs. {payment.amount.toLocaleString()}
                        </td>
                        <td className="px-6 py-4 uppercase font-bold text-emerald-500 text-xs">
                          {payment.paymentMethod}
                        </td>
                        <td className="px-6 py-4">
                          {payment.screenshotUrl ? (
                            <a 
                              href={payment.screenshotUrl} 
                              target="_blank" 
                              rel="noreferrer"
                              className="inline-flex items-center hover:scale-105 transition-transform"
                            >
                              <div className="relative w-12 h-12 rounded-lg border border-slate-200 dark:border-[#333333] overflow-hidden bg-slate-50 dark:bg-[#111111]">
                                <img 
                                  src={payment.screenshotUrl} 
                                  alt="Receipt" 
                                  className="w-full h-full object-cover" 
                                />
                              </div>
                            </a>
                          ) : (
                            <span className="text-slate-400 dark:text-gray-500 text-xs italic">No Receipt</span>
                          )}
                        </td>
                        <td className="px-6 py-4">
                          {payment.status === "PENDING" ? (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                              Pending
                            </span>
                          ) : payment.status === "REJECTED" ? (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">
                              Rejected
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                              Completed
                            </span>
                          )}
                        </td>
                        <td className="px-6 py-4 text-xs text-slate-500 dark:text-gray-400">
                          {new Date(payment.createdAt).toLocaleString()}
                        </td>
                        <td className="px-6 py-4 text-right">
                          {payment.status === "PENDING" && (
                            <div className="flex justify-end gap-2">
                              <button
                                disabled={processingPaymentId === payment.id}
                                onClick={() => handleApprovePayment(payment.id, "approve")}
                                className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition-colors shadow-sm disabled:opacity-50"
                              >
                                Approve
                              </button>
                              <button
                                disabled={processingPaymentId === payment.id}
                                onClick={() => handleApprovePayment(payment.id, "reject")}
                                className="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs transition-colors shadow-sm disabled:opacity-50"
                              >
                                Reject
                              </button>
                            </div>
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

      </div>

      {/* Manual Bill Modal */}
      {showAddModal && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-3xl w-full max-w-2xl shadow-2xl p-6 relative animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
            
            {/* Modal Header */}
            <div className="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-[#222222]">
              <div className="flex items-center gap-2">
                <Receipt className="text-emerald-500 h-5 w-5" />
                <h3 className="text-lg font-bold text-[#111111] dark:text-white">
                  {editingOrder ? "Edit Hardware Order" : "Create Manual VAT Bill"}
                </h3>
              </div>
              <button 
                onClick={() => setShowAddModal(false)}
                className="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm font-semibold transition-colors"
              >
                ✕
              </button>
            </div>

            {/* Modal Body (Scrollable form) */}
            <form onSubmit={handleSubmitBill} className="flex-1 overflow-y-auto py-4 space-y-6 pr-1">
              
              {/* Buyer Information Grid */}
              <div className="space-y-4">
                <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400">Buyer Information</h4>
                
                {clients.length > 0 && (
                  <div className="bg-slate-50 dark:bg-[#151515] p-3 rounded-2xl border border-slate-100 dark:border-[#222222]">
                    <label className="block text-[11px] font-black text-emerald-500 mb-1.5 uppercase tracking-wider">🔍 Auto-fill from Client Account</label>
                    <select
                      onChange={(e) => {
                        const clientId = e.target.value;
                        if (!clientId) return;
                        const client = clients.find((c: any) => c.id === clientId);
                        if (client) {
                          setBuyerName(client.restaurantName || "");
                          setBuyerPhone(client.contactNumber || "");
                          setBuyerAddress(client.location || "");
                        }
                      }}
                      className="w-full px-3 py-2 text-sm bg-white dark:bg-[#111] border border-slate-200 dark:border-[#333] rounded-xl text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
                    >
                      <option value="">-- Choose Client --</option>
                      {clients.map((c: any) => (
                        <option key={c.id} value={c.id}>
                          {c.restaurantName} ({c.contactNumber})
                        </option>
                      ))}
                    </select>
                  </div>
                )}

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="relative">
                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Firm / Buyer Name *</label>
                    <input 
                      type="text"
                      required
                      value={buyerName}
                      onChange={(e) => {
                        setBuyerName(e.target.value);
                        setShowBuyerNameSuggestions(true);
                      }}
                      onFocus={() => setShowBuyerNameSuggestions(true)}
                      onBlur={() => setTimeout(() => setShowBuyerNameSuggestions(false), 200)}
                      placeholder="e.g. Ramesh Karki or Hotel ABC"
                      className="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                    {showBuyerNameSuggestions && filteredBuyerNameSuggestions.length > 0 && (
                      <div className="absolute left-0 right-0 z-30 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#333333] rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        {filteredBuyerNameSuggestions.map((item, idx) => (
                          <div 
                            key={idx}
                            onMouseDown={(e) => {
                              e.preventDefault();
                              setBuyerName(item.name);
                              setBuyerPhone(item.phone);
                              setBuyerAddress(item.address);
                              setCustomerPan(item.pan);
                              setShowBuyerNameSuggestions(false);
                            }}
                            className="px-3 py-2 text-xs hover:bg-slate-100 dark:hover:bg-[#2A2A2A] cursor-pointer text-[#111111] dark:text-white flex justify-between"
                          >
                            <span className="font-semibold">{item.name}</span>
                            <span className="text-slate-400 font-mono">{item.phone}</span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                  <div className="relative">
                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Phone Number *</label>
                    <input 
                      type="text"
                      required
                      value={buyerPhone}
                      onChange={(e) => {
                        setBuyerPhone(e.target.value.replace(/[^0-9]/g, ""));
                        setShowBuyerPhoneSuggestions(true);
                      }}
                      onFocus={() => setShowBuyerPhoneSuggestions(true)}
                      onBlur={() => setTimeout(() => setShowBuyerPhoneSuggestions(false), 200)}
                      maxLength={10}
                      placeholder="e.g. 9851000000"
                      className="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                    {showBuyerPhoneSuggestions && filteredBuyerPhoneSuggestions.length > 0 && (
                      <div className="absolute left-0 right-0 z-30 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#333333] rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        {filteredBuyerPhoneSuggestions.map((item, idx) => (
                          <div 
                            key={idx}
                            onMouseDown={(e) => {
                              e.preventDefault();
                              setBuyerName(item.name);
                              setBuyerPhone(item.phone);
                              setBuyerAddress(item.address);
                              setCustomerPan(item.pan);
                              setShowBuyerPhoneSuggestions(false);
                            }}
                            className="px-3 py-2 text-xs hover:bg-slate-100 dark:hover:bg-[#2A2A2A] cursor-pointer text-[#111111] dark:text-white flex justify-between"
                          >
                            <span className="font-semibold">{item.name}</span>
                            <span className="text-slate-400 font-mono">{item.phone}</span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                  <div>
                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Location / Address *</label>
                    <input 
                      type="text"
                      required
                      value={buyerAddress}
                      onChange={(e) => setBuyerAddress(e.target.value)}
                      placeholder="e.g. Putalisadak, Kathmandu"
                      className="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Customer PAN (Optional)</label>
                    <input 
                      type="text"
                      value={customerPan}
                      onChange={(e) => setCustomerPan(e.target.value.replace(/[^0-9]/g, ""))}
                      maxLength={9}
                      placeholder="e.g. 600000000"
                      className="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                  </div>
                </div>
              </div>

              {/* Items List */}
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400">Bill Items</h4>
                  <button 
                    type="button"
                    onClick={handleAddItem}
                    className="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-500 border border-emerald-500/20 rounded-lg text-xs font-bold transition-all"
                  >
                    <Plus size={12} />
                    <span>Add Item</span>
                  </button>
                </div>

                <div className="space-y-3">
                  {billItems.map((item, idx) => (
                    <div key={idx} className="flex flex-col sm:flex-row gap-3 items-start sm:items-center bg-slate-50/50 dark:bg-[#141414] p-3 rounded-xl border border-slate-100 dark:border-[#222222]">
                      <div className="flex-1 w-full relative">
                        <input 
                          type="text"
                          required
                          value={item.name}
                          onChange={(e) => {
                            handleItemChange(idx, "name", e.target.value);
                            setActiveItemSuggestIndex(idx);
                          }}
                          onFocus={() => setActiveItemSuggestIndex(idx)}
                          onBlur={() => setTimeout(() => setActiveItemSuggestIndex(null), 200)}
                          placeholder="Item description (e.g. DRestro POS Terminal)"
                          className="w-full px-3 py-2 text-xs bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-lg text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                        {activeItemSuggestIndex === idx && item.name.trim() !== "" && (
                          (() => {
                            const suggestions = unifiedItems.filter(ui => 
                              ui.name.toLowerCase().includes(item.name.toLowerCase())
                            );
                            if (suggestions.length === 0) return null;
                            return (
                              <div className="absolute left-0 right-0 z-30 mt-1 bg-white dark:bg-[#1E1E1E] border border-slate-200 dark:border-[#333333] rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                {suggestions.map((sug, sIdx) => (
                                  <div 
                                    key={sIdx}
                                    onMouseDown={(e) => {
                                      e.preventDefault();
                                      handleItemChange(idx, "name", sug.name);
                                      handleItemChange(idx, "price", sug.price);
                                      setActiveItemSuggestIndex(null);
                                    }}
                                    className="px-3 py-2 text-xs hover:bg-slate-100 dark:hover:bg-[#2A2A2A] cursor-pointer text-[#111111] dark:text-white flex justify-between"
                                  >
                                    <span>{sug.name}</span>
                                    <span className="text-emerald-500 font-bold">Rs. {sug.price}</span>
                                  </div>
                                ))}
                              </div>
                            );
                          })()
                        )}
                      </div>
                      <div className="w-full sm:w-28">
                        <input 
                          type="number"
                          required
                          min="0.01"
                          step="0.01"
                          value={item.price || ""}
                          onChange={(e) => handleItemChange(idx, "price", e.target.value)}
                          placeholder="Price (incl. VAT)"
                          className="w-full px-3 py-2 text-xs bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-lg text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold"
                        />
                      </div>
                      <div className="w-full sm:w-20">
                        <input 
                          type="number"
                          required
                          min="1"
                          value={item.quantity || ""}
                          onChange={(e) => handleItemChange(idx, "quantity", e.target.value)}
                          placeholder="Qty"
                          className="w-full px-3 py-2 text-xs bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-lg text-[#111111] dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold"
                        />
                      </div>
                      <button 
                        type="button"
                        onClick={() => handleRemoveItem(idx)}
                        className="p-2 text-rose-500 hover:bg-rose-500/10 rounded-lg transition-all self-end sm:self-auto"
                      >
                        <Trash2 size={14} />
                      </button>
                    </div>
                  ))}
                </div>
              </div>

              {/* Bill Summary */}
              <div className="bg-slate-50 dark:bg-[#141414] p-4 rounded-2xl border border-slate-100 dark:border-[#222222] space-y-3">
                <div className="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                  <span>Gross Item Total (incl. VAT)</span>
                  <span className="font-semibold">Rs. {rawTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </div>

                {/* Discount Input Row */}
                <div className="flex justify-between items-center bg-white dark:bg-[#111111] p-2.5 rounded-xl border border-slate-200 dark:border-[#333333]">
                  <div className="flex items-center gap-2">
                    <span className="text-xs font-bold text-slate-700 dark:text-slate-300">Discount:</span>
                    <div className="flex items-center bg-slate-100 dark:bg-[#222222] p-0.5 rounded-lg border border-slate-200 dark:border-[#333333]">
                      <button
                        type="button"
                        onClick={() => setDiscountType("percent")}
                        className={`px-2.5 py-0.5 text-[11px] font-extrabold rounded-md transition-all ${
                          discountType === "percent" 
                            ? "bg-emerald-600 text-white shadow-sm" 
                            : "text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white"
                        }`}
                      >
                        %
                      </button>
                      <button
                        type="button"
                        onClick={() => setDiscountType("amount")}
                        className={`px-2.5 py-0.5 text-[11px] font-extrabold rounded-md transition-all ${
                          discountType === "amount" 
                            ? "bg-emerald-600 text-white shadow-sm" 
                            : "text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white"
                        }`}
                      >
                        Rs.
                      </button>
                    </div>
                    {discountType === "percent" ? (
                      <div className="flex items-center gap-1">
                        <input
                          type="number"
                          min="0"
                          max="100"
                          step="0.5"
                          value={discountPercent || ""}
                          onChange={(e) => setDiscountPercent(Math.min(100, Math.max(0, Number(e.target.value))))}
                          placeholder="0"
                          className="w-16 px-2 py-1 text-xs bg-slate-50 dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-lg text-emerald-500 font-extrabold focus:outline-none focus:ring-1 focus:ring-emerald-500 text-right"
                        />
                        <span className="text-xs font-bold text-emerald-500">%</span>
                      </div>
                    ) : (
                      <div className="flex items-center gap-1">
                        <span className="text-xs font-bold text-emerald-500">Rs.</span>
                        <input
                          type="number"
                          min="0"
                          step="1"
                          value={discountAmountInput || ""}
                          onChange={(e) => setDiscountAmountInput(Math.max(0, Number(e.target.value)))}
                          placeholder="0"
                          className="w-24 px-2 py-1 text-xs bg-slate-50 dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-lg text-emerald-500 font-extrabold focus:outline-none focus:ring-1 focus:ring-emerald-500 text-right"
                        />
                      </div>
                    )}
                  </div>
                  <span className="text-xs font-black text-rose-500">
                    - Rs. {discountAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                  </span>
                </div>

                <div className="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                  <span>Subtotal (VAT Exclusive)</span>
                  <span>Rs. {calculatedSubtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </div>
                <div className="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                  <span>VAT (13%)</span>
                  <span>Rs. {calculatedVat.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </div>
                <div className="border-t border-slate-200 dark:border-[#333] pt-2 flex justify-between text-sm font-bold text-[#111111] dark:text-white">
                  <span>Grand Total (VAT Inclusive)</span>
                  <span className="text-emerald-500 font-black">Rs. {calculatedTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </div>
              </div>

              {/* Actions */}
              <div className="pt-4 border-t border-slate-100 dark:border-[#222222] flex justify-end gap-3">
                <button 
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-4 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#222] rounded-xl transition-all"
                >
                  Cancel
                </button>
                <button 
                  type="submit"
                  disabled={isCreatingBill}
                  className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all disabled:opacity-50 inline-flex items-center gap-1.5 shadow-md shadow-emerald-600/15"
                >
                  {isCreatingBill && <Loader2 size={12} className="animate-spin" />}
                  <span>{editingOrder ? "Save Changes" : "Create & Sync Bill"}</span>
                </button>
              </div>

            </form>
          </div>
        </div>
      )}
      </div>

      {/* Printable Invoice Template */}
      {selectedInvoiceForPrint && (
        <div id="printable-invoice" className="hidden print:block fixed inset-0 bg-white text-black z-50 p-8 font-sans">
          <div className="max-w-3xl mx-auto space-y-6">
            
            {/* Header / Business Info */}
            <div className="flex justify-between items-start border-b-2 border-gray-300 pb-6">
              <div className="flex items-center gap-4">
                <div className="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center shadow-lg">
                  <span className="text-white font-black text-xl italic">SK</span>
                </div>
                <div>
                  <h1 className="text-3xl font-extrabold tracking-tight">Skylink Solution Pvt Ltd</h1>
                  <p className="text-sm text-gray-500 mt-1">Drestro.com</p>
                  <p className="text-xs text-gray-400 mt-2">Kathmandu, Nepal</p>
                </div>
              </div>
              <div className="text-right">
                <div className="bg-gray-100 px-4 py-2 rounded-lg inline-block">
                  <span className="text-xs font-bold text-gray-500 block uppercase">Seller PAN</span>
                  <span className="text-sm font-mono font-bold">{sellerPan || 'N/A'}</span>
                </div>
                <div className="mt-3 text-xs text-gray-500">
                  <div>Date: {new Date(selectedInvoiceForPrint.createdAt).toLocaleDateString()}</div>
                  <div className="font-mono mt-1">Invoice: HW-{selectedInvoiceForPrint.id.slice(0, 8).toUpperCase()}</div>
                </div>
              </div>
            </div>

            {/* Billing Info */}
            <div className="grid grid-cols-2 gap-8 py-4">
              <div>
                <h3 className="text-xs font-bold uppercase text-gray-400 mb-2">Billed To</h3>
                <div className="text-sm font-bold text-gray-800">{selectedInvoiceForPrint.fullName}</div>
                <div className="text-xs text-gray-500 mt-1">Phone: {selectedInvoiceForPrint.phone}</div>
                <div className="text-xs text-gray-500">Address: {selectedInvoiceForPrint.address}</div>
                {selectedInvoiceForPrint.customerPan && (
                  <div className="text-xs text-gray-500 font-bold mt-1">PAN: <span className="font-mono">{selectedInvoiceForPrint.customerPan}</span></div>
                )}
              </div>
              <div className="text-right">
                <h3 className="text-xs font-bold uppercase text-gray-400 mb-2">VAT Registration</h3>
                <div className="text-sm font-mono font-bold text-emerald-600">
                  {selectedInvoiceForPrint.nepalEbillingSynced ? "IRD REGISTERED" : "MANUAL SALE (UNSYNCED)"}
                </div>
                {selectedInvoiceForPrint.nepalEbillingInvoiceId && (
                  <div className="text-[10px] text-gray-400 mt-1 font-mono">
                    Gov. Trace ID: {selectedInvoiceForPrint.nepalEbillingInvoiceId}
                  </div>
                )}
              </div>
            </div>

            {/* Items Table */}
            <table className="w-full text-left text-sm mt-4 border-collapse">
              <thead>
                <tr className="border-b-2 border-gray-300 text-gray-600 uppercase text-xs font-bold">
                  <th className="py-3">SN</th>
                  <th className="py-3">Particulars</th>
                  <th className="py-3 text-right">Unit Price (Excl. VAT)</th>
                  <th className="py-3 text-center">Qty</th>
                  <th className="py-3 text-right">Total Amount (Excl. VAT)</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {(() => {
                  let itemsList = [];
                  try {
                    itemsList = JSON.parse(selectedInvoiceForPrint.items);
                  } catch(e) {}
                  
                  return itemsList.map((item: any, idx: number) => {
                    const price = item.price || 0;
                    const qty = item.quantity || 1;
                    const totalInclVat = price * qty;
                    const totalExclVat = Math.round((totalInclVat / 1.13) * 100) / 100;
                    const unitExclVat = Math.round((price / 1.13) * 100) / 100;

                    return (
                      <tr key={idx} className="text-gray-800">
                        <td className="py-3">{idx + 1}</td>
                        <td className="py-3 font-semibold">{item.name}</td>
                        <td className="py-3 text-right">Rs. {unitExclVat.toFixed(2)}</td>
                        <td className="py-3 text-center">{qty}</td>
                        <td className="py-3 text-right">Rs. {totalExclVat.toFixed(2)}</td>
                      </tr>
                    );
                  });
                })()}
              </tbody>
            </table>

            {/* Calculations Summary */}
            <div className="border-t border-gray-300 pt-4 flex justify-end">
              <div className="w-64 space-y-2 text-sm">
                <div className="flex justify-between text-gray-500">
                  <span>Subtotal (VAT Exclusive)</span>
                  <span>Rs. {(Math.round((selectedInvoiceForPrint.totalPrice / 1.13) * 100) / 100).toFixed(2)}</span>
                </div>
                <div className="flex justify-between text-gray-500">
                  <span>VAT (13%)</span>
                  <span>Rs. {(selectedInvoiceForPrint.totalPrice - Math.round((selectedInvoiceForPrint.totalPrice / 1.13) * 100) / 100).toFixed(2)}</span>
                </div>
                <div className="border-t-2 border-gray-300 pt-2 flex justify-between font-extrabold text-base text-gray-900">
                  <span>Grand Total</span>
                  <span>Rs. {selectedInvoiceForPrint.totalPrice.toFixed(2)}</span>
                </div>
              </div>
            </div>

            {/* Footer / IRD Disclaimer */}
            <div className="pt-12 text-center text-[10px] text-gray-400 border-t border-gray-200 mt-12 space-y-1">
              <p>This is a computer-generated VAT Invoice.</p>
              {selectedInvoiceForPrint.nepalEbillingSynced && (
                <p className="font-mono">Registered at Nepal IRD e-billing system under ID: {selectedInvoiceForPrint.nepalEbillingInvoiceId}</p>
              )}
              <p className="font-semibold text-gray-500 mt-2">Thank you for your business!</p>
            </div>

          </div>
        </div>
      )}
    </>
  );
}
