"use client";

import { useState } from "react";
import { ShoppingBag, Trash2, Clock, CheckCircle2, XCircle, Truck, Phone, MapPin, User, Search, ArrowLeft, Receipt, Loader2, RefreshCw, Plus, Check, Mail } from "lucide-react";
import toast from "react-hot-toast";

interface CustomerContact {
  name: string;
  email: string;
  phone: string;
  address: string;
}

export default function OrdersClient({ 
  initialOrders, 
  hardwareList = [],
  customerList = []
}: { 
  initialOrders: any[]; 
  hardwareList?: any[];
  customerList?: CustomerContact[];
}) {
  const [orders, setOrders] = useState(initialOrders);
  const [selectedOrder, setSelectedOrder] = useState<any | null>(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [statusFilter, setStatusFilter] = useState<"ALL" | "PENDING" | "COMPLETED" | "CANCELLED">("ALL");
  const [isUpdating, setIsUpdating] = useState(false);
  const [isSyncingEbilling, setIsSyncingEbilling] = useState(false);

  // States for Place Manual Order Modal
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [newOrderName, setNewOrderName] = useState("");
  const [newOrderEmail, setNewOrderEmail] = useState("");
  const [newOrderPhone, setNewOrderPhone] = useState("");
  const [newOrderAddress, setNewOrderAddress] = useState("");
  const [newOrderStatus, setNewOrderStatus] = useState("COMPLETED");
  const [newOrderItems, setNewOrderItems] = useState<{ id: string; name: string; price: number; quantity: number; image: string }[]>([]);
  const [discountType, setDiscountType] = useState<"percent" | "amount">("percent");
  const [discountValue, setDiscountValue] = useState<number>(0);
  const [hardwareSearch, setHardwareSearch] = useState("");
  
  // Customer Search Autocomplete
  const [customerSearch, setCustomerSearch] = useState("");
  const [showCustomerDropdown, setShowCustomerDropdown] = useState(false);

  const getStatusColor = (status: string) => {
    switch (status) {
      case "PENDING":
        return "bg-amber-500/10 text-amber-500 border border-amber-500/20";
      case "COMPLETED":
        return "bg-emerald-500/10 text-emerald-500 border border-emerald-500/20";
      case "CANCELLED":
        return "bg-red-500/10 text-red-500 border border-red-500/20";
      default:
        return "bg-slate-100 text-slate-500 border border-slate-200";
    }
  };

  const handleStatusChange = async (id: string, newStatus: string) => {
    setIsUpdating(true);
    const toastId = toast.loading("Updating status...");
    try {
      const res = await fetch(`/api/admin/orders/${id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ status: newStatus }),
      });

      if (res.ok) {
        const updated = await res.json();
        toast.success(`Order marked as ${newStatus.toLowerCase()}`, { id: toastId });
        setOrders(orders.map(o => o.id === id ? updated : o));
        if (selectedOrder && selectedOrder.id === id) {
          setSelectedOrder(updated);
        }
      } else {
        toast.error("Failed to update status", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    } finally {
      setIsUpdating(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this order?")) return;

    setIsUpdating(true);
    const toastId = toast.loading("Deleting order...");
    try {
      const res = await fetch(`/api/admin/orders?id=${id}`, {
        method: "DELETE",
      });

      if (res.ok) {
        toast.success("Order deleted", { id: toastId });
        setOrders(orders.filter(o => o.id !== id));
        if (selectedOrder && selectedOrder.id === id) {
          setSelectedOrder(null);
        }
      } else {
        toast.error("Failed to delete order", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    } finally {
      setIsUpdating(false);
    }
  };

  const parseItems = (itemsStr: string): any[] => {
    try {
      return JSON.parse(itemsStr);
    } catch (e) {
      return [];
    }
  };



  // Manual Order Helpers
  const handleAddItemToOrder = (item: any) => {
    const existing = newOrderItems.find(i => i.id === item.id);
    if (existing) {
      setNewOrderItems(newOrderItems.map(i => i.id === item.id ? { ...i, quantity: i.quantity + 1 } : i));
    } else {
      setNewOrderItems([
        ...newOrderItems,
        {
          id: item.id,
          name: item.name,
          price: item.price,
          quantity: 1,
          image: item.imageUrl || "/images/hardware-placeholder.png"
        }
      ]);
    }
  };

  const handleUpdateItemQty = (id: string, qty: number) => {
    if (qty <= 0) {
      setNewOrderItems(newOrderItems.filter(i => i.id !== id));
    } else {
      setNewOrderItems(newOrderItems.map(i => i.id === id ? { ...i, quantity: qty } : i));
    }
  };

  const handleCreateOrder = async (e: React.FormEvent) => {
    e.preventDefault();
    if (newOrderItems.length === 0) {
      toast.error("Please add at least one hardware item to the order.");
      return;
    }

    setIsUpdating(true);
    const orderSubtotal = newOrderItems.reduce((acc, i) => acc + i.price * i.quantity, 0);
    const finalDiscount = discountType === "percent" 
      ? Math.round((orderSubtotal * Math.min(100, Math.max(0, discountValue))) / 100)
      : discountValue;

    const toastId = toast.loading("Creating manual order...");
    try {
      const res = await fetch("/api/admin/orders", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          fullName: newOrderName,
          email: newOrderEmail || null,
          phone: newOrderPhone,
          address: newOrderAddress,
          status: newOrderStatus,
          items: newOrderItems,
          discount: finalDiscount
        })
      });

      const data = await res.json();

      if (res.ok && data.success) {
        toast.success("Manual order placed successfully!", { id: toastId });
        setOrders([data.order, ...orders]);
        
        // Reset state
        setNewOrderName("");
        setNewOrderEmail("");
        setNewOrderPhone("");
        setNewOrderAddress("");
        setCustomerSearch("");
        setNewOrderStatus("COMPLETED");
        setNewOrderItems([]);
        setDiscountType("percent");
        setDiscountValue(0);
        setIsCreateModalOpen(false);
      } else {
        toast.error(data.error || "Failed to create order", { id: toastId });
      }
    } catch (err: any) {
      toast.error(err.message || "An error occurred", { id: toastId });
    } finally {
      setIsUpdating(false);
    }
  };

  const [dateFilter, setDateFilter] = useState<string>("all");
  const [customStartDate, setCustomStartDate] = useState<string>("");
  const [customEndDate, setCustomEndDate] = useState<string>("");

  const filteredHardware = hardwareList.filter(h => 
    h.name.toLowerCase().includes(hardwareSearch.toLowerCase())
  );

  const filteredCustomers = customerList.filter(c => 
    c.name.toLowerCase().includes(customerSearch.toLowerCase()) ||
    c.phone.toLowerCase().includes(customerSearch.toLowerCase()) ||
    c.email.toLowerCase().includes(customerSearch.toLowerCase())
  );

  const filteredOrders = orders.filter((o) => {
    // 1. Status Filter
    const matchesStatus = statusFilter === "ALL" || o.status === statusFilter;

    // 2. Search Query Filter
    const q = searchQuery.toLowerCase();
    const matchesSearch =
      o.fullName.toLowerCase().includes(q) ||
      o.phone.includes(q) ||
      (o.email && o.email.toLowerCase().includes(q));

    if (!matchesStatus || !matchesSearch) return false;

    // 3. Date Filter
    if (dateFilter === "all") return true;
    const itemDate = new Date(o.createdAt);
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
      if (customStartDate && new Date(o.createdAt) < new Date(customStartDate)) return false;
      if (customEndDate) {
        const end = new Date(customEndDate);
        end.setHours(23, 59, 59, 999);
        if (new Date(o.createdAt) > end) return false;
      }
    }
    return true;
  });

  return (
    <div className="grid grid-cols-1 xl:grid-cols-3 gap-8 min-h-[calc(100vh-140px)]">
      {/* Orders List */}
      <div className={`xl:col-span-2 bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] overflow-hidden flex flex-col ${selectedOrder ? "hidden xl:flex" : "flex"}`}>
        
        {/* Header Tabs & Filters */}
        <div className="p-6 bg-slate-50/50 dark:bg-[#141414]/50 border-b border-slate-200/60 dark:border-[#222222] space-y-4">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <div className="flex items-center gap-3">
                <h2 className="text-lg font-black text-slate-800 dark:text-white tracking-tight">Order Queue</h2>
                <button
                  onClick={() => setIsCreateModalOpen(true)}
                  className="bg-[#E53935] hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1 shrink-0"
                >
                  <Plus size={14} /> Place Manual Order
                </button>
              </div>
              <p className="text-xs text-slate-400 mt-0.5">Manage and track hardware orders placed via WhatsApp.</p>
            </div>

            <div className="flex flex-wrap items-center gap-3 w-full sm:w-auto">
              <div className="relative flex-1 sm:w-56">
                <span className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <Search className="h-4 w-4 text-slate-400" />
                </span>
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Search buyer name, phone..."
                  className="w-full pl-9 pr-4 py-2 text-xs font-medium bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2a2a2a]/50 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-red-500"
                />
              </div>

              {/* Date Filter Dropdown */}
              <div className="flex items-center gap-2">
                <select
                  value={dateFilter}
                  onChange={(e) => setDateFilter(e.target.value)}
                  className="px-3 py-2 text-xs font-bold bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2a2a2a]/50 rounded-xl text-slate-800 dark:text-white focus:outline-none cursor-pointer"
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
                      className="px-2 py-1 text-xs bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] rounded-lg text-slate-800 dark:text-white focus:outline-none"
                    />
                    <span className="text-xs text-slate-400">to</span>
                    <input
                      type="date"
                      value={customEndDate}
                      onChange={(e) => setCustomEndDate(e.target.value)}
                      className="px-2 py-1 text-xs bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] rounded-lg text-slate-800 dark:text-white focus:outline-none"
                    />
                  </div>
                )}
              </div>
            </div>
          </div>

          <div className="flex gap-2 overflow-x-auto hide-scrollbar p-1 bg-slate-100 dark:bg-[#1a1a1a] rounded-2xl border border-slate-200/50 dark:border-[#2a2a2a]/50">
            {(["ALL", "PENDING", "COMPLETED", "CANCELLED"] as const).map((filter) => (
              <button
                key={filter}
                onClick={() => setStatusFilter(filter)}
                className={`px-4 py-2 rounded-xl text-xs font-bold transition-all duration-300 whitespace-nowrap ${
                  statusFilter === filter
                    ? "bg-red-500 text-white shadow-lg shadow-red-500/20"
                    : "text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200"
                }`}
              >
                {filter}
                <span className={`ml-1.5 px-1.5 py-0.5 text-[10px] rounded-md ${statusFilter === filter ? "bg-white/20 text-white" : "bg-slate-200 dark:bg-[#2a2a2a] text-slate-600 dark:text-slate-400"}`}>
                  {orders.filter(o => filter === "ALL" ? true : o.status === filter).length}
                </span>
              </button>
            ))}
          </div>
        </div>

        {/* Orders Table Container */}
        <div className="flex-1 overflow-y-auto">
          {filteredOrders.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 px-6">
              <div className="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/30 dark:border-[#2a2a2a] flex items-center justify-center text-slate-400 mb-3">
                <ShoppingBag className="w-6 h-6" />
              </div>
              <p className="text-sm font-bold text-slate-600 dark:text-slate-300">No orders found</p>
              <p className="text-xs text-slate-400 mt-1">There are no hardware orders matching the current filter criteria.</p>
            </div>
          ) : (
            <div className="divide-y divide-slate-100/60 dark:divide-[#222222]/80">
              {filteredOrders.map((order) => {
                const orderItems = parseItems(order.items);
                const itemCount = orderItems.reduce((acc, i) => acc + i.quantity, 0);

                return (
                  <div
                    key={order.id}
                    onClick={() => setSelectedOrder(order)}
                    className={`p-6 flex items-start gap-4 hover:bg-slate-50/50 dark:hover:bg-[#151515]/30 cursor-pointer transition-all duration-200 border-l-4 ${selectedOrder?.id === order.id ? "bg-slate-50/80 dark:bg-[#151515]/50 border-l-red-500" : "border-l-transparent"}`}
                  >
                    <div className="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-[#181818] border border-slate-200/40 dark:border-slate-800 flex items-center justify-center font-black text-xs text-slate-600 dark:text-slate-400 shrink-0 select-none">
                      {order.fullName.substring(0, 2).toUpperCase()}
                    </div>
                    <div className="flex-grow min-w-0">
                      <div className="flex items-center justify-between gap-2">
                        <div>
                          <h3 className="text-sm font-black text-slate-800 dark:text-slate-100 truncate">{order.fullName}</h3>
                          {order.email && <p className="text-[10px] text-slate-400 truncate">{order.email}</p>}
                        </div>
                        <span className="text-[10px] font-semibold text-slate-400 whitespace-nowrap">
                          {new Date(order.createdAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}
                        </span>
                      </div>
                      <div className="flex items-center gap-2 mt-2">
                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold tracking-wider uppercase ${getStatusColor(order.status)}`}>
                          {order.status}
                        </span>
                        {order.status === "COMPLETED" && (
                          order.nepalEbillingSynced ? (
                            <span className="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                              Synced
                            </span>
                          ) : order.nepalEbillingError ? (
                            <span className="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">
                              Failed
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-slate-500/10 text-slate-500 border border-slate-500/20">
                              Pending VAT
                            </span>
                          )
                        )}
                        <span className="text-xs font-semibold text-slate-400">
                          {itemCount} {itemCount === 1 ? 'item' : 'items'}
                        </span>
                      </div>
                      <div className="mt-2">
                        {order.discount > 0 && (
                          <span className="text-[10px] text-slate-400 font-semibold line-through mr-1.5">
                            Rs. {(order.totalPrice + order.discount).toLocaleString()}
                          </span>
                        )}
                        <span className="text-sm font-black text-red-500">Rs. {order.totalPrice.toLocaleString()}</span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>

      {/* Order Details Panel */}
      <div className={`xl:col-span-1 ${selectedOrder ? "block animate-fadeIn" : "hidden xl:block"}`}>
        {selectedOrder ? (
          <div className="bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] p-6 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] sticky top-6">
            <button 
              onClick={() => setSelectedOrder(null)}
              className="xl:hidden mb-5 px-3 py-2 bg-slate-100 dark:bg-[#1a1a1a] hover:bg-slate-200 dark:hover:bg-[#252525] text-slate-500 dark:text-slate-400 rounded-xl flex items-center gap-2 text-xs font-bold transition-all"
            >
              <ArrowLeft size={14} /> Back to Orders
            </button>

            <div className="flex justify-between items-start gap-4 mb-5 pb-5 border-b border-slate-100 dark:border-[#222222]">
              <div>
                <span className={`inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider mb-2 ${getStatusColor(selectedOrder.status)}`}>
                  {selectedOrder.status}
                </span>
                <h2 className="text-lg font-black text-slate-800 dark:text-white leading-snug tracking-tight">Order #{selectedOrder.id.substring(0, 8).toUpperCase()}</h2>
                <div className="flex items-center gap-1.5 mt-2">
                  <User className="w-3.5 h-3.5 text-slate-400" />
                  <p className="text-xs font-semibold text-slate-400">Customer <span className="font-bold text-slate-700 dark:text-slate-300">{selectedOrder.fullName}</span></p>
                </div>
              </div>
            </div>

            {/* Delivery Info */}
            <div className="mb-6 space-y-3 bg-slate-50/50 dark:bg-[#141414]/50 border border-slate-200/40 dark:border-[#222222]/80 p-4.5 rounded-2xl">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1">
                <Truck className="w-3.5 h-3.5 text-slate-400" /> Shipping Details
              </h3>
              
              <div className="grid grid-cols-1 gap-2.5 text-xs">
                <div className="flex items-center gap-2">
                  <Phone className="w-4 h-4 text-slate-400 shrink-0" />
                  <span className="text-slate-400">Phone:</span>
                  <a href={`tel:${selectedOrder.phone}`} className="font-bold text-red-500 hover:underline">{selectedOrder.phone}</a>
                </div>

                {selectedOrder.email && (
                  <div className="flex items-center gap-2">
                    <Mail className="w-4 h-4 text-slate-400 shrink-0" />
                    <span className="text-slate-400">Email:</span>
                    <a href={`mailto:${selectedOrder.email}`} className="font-bold text-red-500 hover:underline truncate">{selectedOrder.email}</a>
                  </div>
                )}
                
                <div className="flex items-start gap-2">
                  <MapPin className="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                  <span className="text-slate-400">Address:</span>
                  <span className="font-bold text-slate-700 dark:text-slate-300 leading-snug">{selectedOrder.address}</span>
                </div>
              </div>
            </div>

            {/* Order Items */}
            <div className="mb-6">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                <ShoppingBag className="w-3.5 h-3.5 text-slate-400" /> Items List
              </h3>
              <div className="p-4 bg-slate-50 dark:bg-[#161616] rounded-2xl border border-slate-200/40 dark:border-slate-800/80 max-h-[200px] overflow-y-auto space-y-3 divide-y divide-slate-100 dark:divide-neutral-800">
                {parseItems(selectedOrder.items).map((item, idx) => (
                  <div key={item.id || idx} className={`flex items-center gap-3 ${idx > 0 ? 'pt-3' : ''}`}>
                    <div className="w-10 h-10 bg-white dark:bg-[#111] rounded-lg border border-slate-200/40 dark:border-neutral-800 flex items-center justify-center p-1 shrink-0">
                      <img src={item.image} alt={item.name} className="max-w-full max-h-full object-contain" />
                    </div>
                    <div className="flex-grow min-w-0">
                      <h4 className="text-xs font-bold text-slate-700 dark:text-slate-200 truncate leading-snug">{item.name}</h4>
                      <p className="text-[10px] font-medium text-slate-400 mt-0.5">{item.quantity} x Rs. {item.price.toLocaleString()}</p>
                    </div>
                    <span className="text-xs font-black text-slate-700 dark:text-slate-300">Rs. {(item.price * item.quantity).toLocaleString()}</span>
                  </div>
                ))}
              </div>
              <div className="space-y-1.5 mt-4 pt-4 border-t border-slate-100 dark:border-[#222222]/80 px-2 text-xs">
                {selectedOrder.discount > 0 && (
                  <>
                    <div className="flex justify-between items-center text-slate-400">
                      <span>Subtotal</span>
                      <span className="font-semibold text-slate-700 dark:text-slate-350">Rs. {(selectedOrder.totalPrice + selectedOrder.discount).toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between items-center text-emerald-500 font-medium">
                      <span>Discount Given</span>
                      <span>- Rs. {selectedOrder.discount.toLocaleString()}</span>
                    </div>
                  </>
                )}
                <div className="flex justify-between items-center pt-1">
                  <span className="font-extrabold text-slate-400 uppercase tracking-wider">Total amount</span>
                  <span className="text-base font-black text-red-500">Rs. {selectedOrder.totalPrice.toLocaleString()}</span>
                </div>
              </div>
            </div>

            {/* Status updates */}
            <div className="space-y-3 pt-4 border-t border-slate-100 dark:border-[#222222]">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Update Order Status</h3>
              
              <div className="grid grid-cols-2 gap-2">
                <button
                  disabled={isUpdating || selectedOrder.status === "PENDING"}
                  onClick={() => handleStatusChange(selectedOrder.id, "PENDING")}
                  className={`py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer ${
                    selectedOrder.status === "PENDING"
                      ? "bg-amber-500/10 text-amber-500 border border-amber-500/20"
                      : "bg-slate-100 dark:bg-[#202020] border border-slate-200/50 dark:border-[#2b2b2b] text-slate-500 hover:bg-slate-200 dark:hover:bg-[#252525]"
                  }`}
                >
                  <Clock className="w-3.5 h-3.5" />
                  <span>Pending</span>
                </button>

                <button
                  disabled={isUpdating || selectedOrder.status === "COMPLETED"}
                  onClick={() => handleStatusChange(selectedOrder.id, "COMPLETED")}
                  className={`py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer ${
                    selectedOrder.status === "COMPLETED"
                      ? "bg-emerald-500/10 text-emerald-500 border border-emerald-500/20"
                      : "bg-slate-100 dark:bg-[#202020] border border-slate-200/50 dark:border-[#2b2b2b] text-slate-500 hover:bg-slate-200 dark:hover:bg-[#252525]"
                  }`}
                >
                  <CheckCircle2 className="w-3.5 h-3.5" />
                  <span>Completed</span>
                </button>

                <button
                  disabled={isUpdating || selectedOrder.status === "CANCELLED"}
                  onClick={() => handleStatusChange(selectedOrder.id, "CANCELLED")}
                  className={`py-2.5 px-3 rounded-xl text-xs font-bold transition-all col-span-2 flex items-center justify-center gap-1.5 cursor-pointer ${
                    selectedOrder.status === "CANCELLED"
                      ? "bg-red-500/10 text-red-500 border border-red-500/20"
                      : "bg-slate-100 dark:bg-[#202020] border border-slate-200/50 dark:border-[#2b2b2b] text-slate-500 hover:bg-slate-200 dark:hover:bg-[#252525]"
                  }`}
                >
                  <XCircle className="w-3.5 h-3.5" />
                  <span>Mark as Cancelled</span>
                </button>
              </div>

              {selectedOrder.status === "COMPLETED" && (
                <div className="p-4 bg-slate-50 dark:bg-[#161616] border border-slate-200/50 dark:border-[#2b2b2b] rounded-2xl space-y-3">
                  <div className="flex items-center justify-between">
                    <h4 className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1 font-semibold">
                      <Receipt className="w-3.5 h-3.5" /> Nepal E-Billing VAT
                    </h4>
                    {selectedOrder.nepalEbillingSynced ? (
                      <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 uppercase tracking-wider">
                        Synced
                      </span>
                    ) : selectedOrder.nepalEbillingError ? (
                      <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-500/10 text-rose-500 border border-rose-500/20 uppercase tracking-wider">
                        Failed
                      </span>
                    ) : (
                      <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-slate-500/10 text-slate-500 border border-slate-500/20 uppercase tracking-wider">
                        Pending
                      </span>
                    )}
                  </div>

                  {selectedOrder.nepalEbillingSynced ? (
                    <div className="text-xs">
                      <span className="text-slate-400 font-semibold">Invoice ID:</span>{" "}
                      <span className="font-mono font-bold text-slate-700 dark:text-slate-300">
                        {selectedOrder.nepalEbillingInvoiceId}
                      </span>
                    </div>
                  ) : (
                    <>
                      {selectedOrder.nepalEbillingError && (
                        <div className="text-[11px] font-semibold text-rose-500 leading-snug">
                          {selectedOrder.nepalEbillingError}
                        </div>
                      )}
                      <button
                        type="button"
                        disabled={isSyncingEbilling}
                        onClick={async () => {
                          setIsSyncingEbilling(true);
                          const toastId = toast.loading("Syncing order to Nepal E-Billing...");
                          try {
                            const res = await fetch(`/api/admin/orders/${selectedOrder.id}/sync`, {
                              method: "POST"
                            });
                            const data = await res.json();
                            if (res.ok && data.success) {
                              toast.success("Order synced successfully!", { id: toastId });
                              const updatedOrder = {
                                ...selectedOrder,
                                nepalEbillingSynced: true,
                                nepalEbillingInvoiceId: data.invoiceId,
                                nepalEbillingError: null
                              };
                              setSelectedOrder(updatedOrder);
                              setOrders(orders.map(o => o.id === selectedOrder.id ? updatedOrder : o));
                            } else {
                              const errorMsg = data.error || "Sync failed";
                              toast.error(errorMsg, { id: toastId });
                              const updatedOrder = {
                                ...selectedOrder,
                                nepalEbillingSynced: false,
                                nepalEbillingError: errorMsg
                              };
                              setSelectedOrder(updatedOrder);
                              setOrders(orders.map(o => o.id === selectedOrder.id ? updatedOrder : o));
                            }
                          } catch (err: any) {
                            toast.error(`Sync error: ${err.message}`, { id: toastId });
                          } finally {
                            setIsSyncingEbilling(false);
                          }
                        }}
                        className="w-full bg-slate-100 dark:bg-[#202020] hover:bg-red-500/10 dark:hover:bg-red-500/15 border border-slate-200/50 dark:border-[#2b2b2b] text-slate-700 dark:text-slate-300 hover:text-red-500 dark:hover:text-red-400 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                      >
                        {isSyncingEbilling ? (
                          <Loader2 className="w-3.5 h-3.5 animate-spin" />
                        ) : (
                          <RefreshCw className="w-3.5 h-3.5" />
                        )}
                        <span>Sync to Nepal E-Billing</span>
                      </button>
                    </>
                  )}
                </div>
              )}

              <button
                disabled={isUpdating}
                onClick={() => handleDelete(selectedOrder.id)}
                className="w-full bg-slate-100 hover:bg-red-500/10 dark:bg-[#202020] dark:hover:bg-red-500/15 border border-slate-200/50 dark:border-[#2b2b2b] text-slate-600 dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 py-3 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
              >
                <Trash2 className="w-4 h-4 shrink-0" />
                <span>Delete Order Log</span>
              </button>
            </div>
          </div>
        ) : (
          <div className="bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] p-6 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] flex flex-col items-center justify-center text-center h-[340px] xl:h-full">
            <div className="w-14 h-14 rounded-3xl bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/30 dark:border-[#2a2a2a] flex items-center justify-center text-slate-400 dark:text-slate-500 mb-4 animate-pulse">
              <ShoppingBag className="w-7 h-7" />
            </div>
            <p className="text-sm font-bold text-slate-700 dark:text-slate-300">Select an Order</p>
            <p className="text-xs text-slate-400 max-w-[200px] mt-1 mx-auto leading-relaxed">Choose an order from the list queue to manage status, inspect shipping info, or delete.</p>
          </div>
        )}
      </div>

      {/* Place Manual Order Modal */}
      {isCreateModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0a0a0a]/75 backdrop-blur-sm animate-in fade-in duration-300">
          <div className="bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#222222] rounded-3xl p-6 md:p-8 w-full max-w-4xl shadow-2xl flex flex-col max-h-[90vh]">
            <div className="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-[#222222] mb-6">
              <div>
                <h2 className="text-lg font-black text-slate-800 dark:text-white tracking-tight">Place Manual Hardware Order</h2>
                <p className="text-xs text-slate-400 mt-0.5">Create a hardware order for a customer. Auto-fill details from existing client accounts below.</p>
              </div>
              <button 
                onClick={() => {
                  setIsCreateModalOpen(false);
                  setCustomerSearch("");
                  setShowCustomerDropdown(false);
                }}
                className="text-slate-400 hover:text-[#111111] dark:hover:text-white text-sm font-bold"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateOrder} className="flex-1 flex flex-col md:flex-row gap-8 overflow-hidden">
              {/* Left Column: Customer details */}
              <div className="flex-1 space-y-4 overflow-y-auto pr-2">
                <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest">Customer Details</h3>
                
                {/* Autocomplete Customer Lookup Search */}
                <div className="relative">
                  <label className="text-xs font-bold text-slate-500">🔍 Auto-fill from Customer Account (Name / Phone / Email)</label>
                  <div className="relative mt-1">
                    <input
                      type="text"
                      value={customerSearch}
                      onChange={(e) => {
                        setCustomerSearch(e.target.value);
                        setShowCustomerDropdown(true);
                      }}
                      onFocus={() => setShowCustomerDropdown(true)}
                      placeholder="Type to lookup registered accounts..."
                      className="w-full bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#E53935]"
                    />
                    {customerSearch && (
                      <button
                        type="button"
                        onClick={() => {
                          setCustomerSearch("");
                          setShowCustomerDropdown(false);
                        }}
                        className="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold"
                      >
                        Clear
                      </button>
                    )}
                  </div>

                  {showCustomerDropdown && (
                    <div className="absolute z-10 w-full mt-1 bg-white dark:bg-[#151515] border border-slate-200 dark:border-[#2a2a2a] rounded-xl shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-neutral-800">
                      {filteredCustomers.map((c, idx) => (
                        <button
                          key={idx}
                          type="button"
                          onClick={() => {
                            setNewOrderName(c.name);
                            setNewOrderPhone(c.phone);
                            setNewOrderEmail(c.email);
                            setNewOrderAddress(c.address);
                            setCustomerSearch(c.name || c.phone || c.email);
                            setShowCustomerDropdown(false);
                            toast.success(`Auto-filled: ${c.name || 'Customer'}`);
                          }}
                          className="w-full text-left px-4 py-2.5 text-xs hover:bg-slate-50 dark:hover:bg-[#1e1e1e] flex flex-col gap-0.5 transition-colors"
                        >
                          <span className="font-bold text-slate-800 dark:text-white">{c.name || 'Merchant'}</span>
                          <span className="text-[10px] text-slate-400 truncate">
                            {c.phone && `📞 ${c.phone}`} {c.email && ` | ✉️ ${c.email}`} {c.address && ` | 📍 ${c.address}`}
                          </span>
                        </button>
                      ))}
                      {filteredCustomers.length === 0 && (
                        <p className="text-xs text-slate-400 p-3 text-center">No accounts found matching search.</p>
                      )}
                    </div>
                  )}
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-slate-500">Customer Full Name</label>
                  <input
                    type="text"
                    required
                    value={newOrderName}
                    onChange={(e) => setNewOrderName(e.target.value)}
                    placeholder="Enter customer name..."
                    className="w-full bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#E53935]"
                  />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-slate-500">Contact Phone Number (Lookup Key)</label>
                  <input
                    type="text"
                    required
                    value={newOrderPhone}
                    onChange={(e) => setNewOrderPhone(e.target.value)}
                    placeholder="Enter phone (must match client contact)..."
                    className="w-full bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#E53935]"
                  />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-slate-500">Email Address (Optional)</label>
                  <input
                    type="email"
                    value={newOrderEmail}
                    onChange={(e) => setNewOrderEmail(e.target.value)}
                    placeholder="Enter email address..."
                    className="w-full bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#E53935]"
                  />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-slate-500">Shipping Address</label>
                  <input
                    type="text"
                    required
                    value={newOrderAddress}
                    onChange={(e) => setNewOrderAddress(e.target.value)}
                    placeholder="Enter shipping address..."
                    className="w-full bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#E53935]"
                  />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-slate-500">Order Status</label>
                  <select
                    value={newOrderStatus}
                    onChange={(e) => setNewOrderStatus(e.target.value)}
                    className="w-full bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2a2a2a]/50 text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#E53935]"
                  >
                    <option value="PENDING">PENDING</option>
                    <option value="COMPLETED">COMPLETED</option>
                    <option value="CANCELLED">CANCELLED</option>
                  </select>
                </div>

                {/* Custom Discount Input Field */}
                <div className="space-y-1.5 pt-2">
                  <div className="flex items-center justify-between">
                    <label className="text-xs font-bold text-slate-500">💰 Give Discount</label>
                    <div className="flex items-center bg-slate-100 dark:bg-[#222222] p-0.5 rounded-lg border border-slate-200 dark:border-[#333333]">
                      <button
                        type="button"
                        onClick={() => setDiscountType("percent")}
                        className={`px-2 py-0.5 text-[10px] font-extrabold rounded-md transition-all ${
                          discountType === "percent" 
                            ? "bg-[#E53935] text-white shadow-sm" 
                            : "text-slate-500 dark:text-gray-400"
                        }`}
                      >
                        % Percent
                      </button>
                      <button
                        type="button"
                        onClick={() => setDiscountType("amount")}
                        className={`px-2 py-0.5 text-[10px] font-extrabold rounded-md transition-all ${
                          discountType === "amount" 
                            ? "bg-[#E53935] text-white shadow-sm" 
                            : "text-slate-500 dark:text-gray-400"
                        }`}
                      >
                        Rs. Flat
                      </button>
                    </div>
                  </div>
                  <div className="relative">
                    <input
                      type="number"
                      min="0"
                      max={discountType === "percent" ? 100 : 999999}
                      value={discountValue || ""}
                      onChange={(e) => setDiscountValue(Math.max(0, Number(e.target.value) || 0))}
                      placeholder={discountType === "percent" ? "Enter discount percentage (e.g. 10)..." : "Enter discount amount in Rs..."}
                      className="w-full bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-[#E53935]"
                    />
                  </div>
                </div>

                {/* Selected Items */}
                <div className="pt-4 border-t border-slate-100 dark:border-[#222222] space-y-2">
                  {(() => {
                    const orderSubtotal = newOrderItems.reduce((acc, i) => acc + i.price * i.quantity, 0);
                    const customDiscount = discountType === "percent" 
                      ? Math.round((orderSubtotal * Math.min(100, Math.max(0, discountValue))) / 100)
                      : discountValue;

                    return (
                      <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center justify-between">
                        <span>Selected Items ({newOrderItems.length})</span>
                        {newOrderItems.length > 0 && (
                          <div className="flex flex-col items-end gap-0.5">
                            <span className="text-slate-400 text-[10px] font-semibold">
                              Subtotal: Rs. {orderSubtotal.toLocaleString()}
                            </span>
                            {customDiscount > 0 && (
                              <span className="text-emerald-500 text-[10px] font-bold">
                                Discount ({discountType === "percent" ? `${discountValue}%` : "Flat"}): - Rs. {customDiscount.toLocaleString()}
                              </span>
                            )}
                            <span className="text-red-500 text-[11px] font-black">
                              Total Price: Rs. {Math.max(0, orderSubtotal - customDiscount).toLocaleString()}
                            </span>
                          </div>
                        )}
                      </h3>
                    );
                  })()}
                  
                  {newOrderItems.length === 0 ? (
                    <p className="text-xs text-slate-400 py-6 text-center bg-slate-50 dark:bg-[#181818] rounded-xl border border-dashed border-slate-200 dark:border-[#2a2a2a]">
                      No items selected. Choose items from the right column.
                    </p>
                  ) : (
                    <div className="space-y-2 max-h-48 overflow-y-auto">
                      {newOrderItems.map(item => (
                        <div key={item.id} className="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-[#181818] border border-slate-200 dark:border-slate-800 rounded-xl text-xs">
                          <div className="min-w-0 flex-1 pr-2">
                            <h4 className="font-bold text-slate-800 dark:text-white truncate">{item.name}</h4>
                            <p className="text-[10px] text-slate-400">Rs. {item.price.toLocaleString()} each</p>
                          </div>
                          
                          <div className="flex items-center gap-2">
                            <button
                              type="button"
                              onClick={() => handleUpdateItemQty(item.id, item.quantity - 1)}
                              className="w-6 h-6 bg-white dark:bg-neutral-800 border border-slate-200 dark:border-neutral-700 rounded-lg font-black flex items-center justify-center hover:bg-slate-100 dark:hover:bg-neutral-750 transition-colors"
                            >
                              -
                            </button>
                            <span className="font-bold w-6 text-center">{item.quantity}</span>
                            <button
                              type="button"
                              onClick={() => handleUpdateItemQty(item.id, item.quantity + 1)}
                              className="w-6 h-6 bg-white dark:bg-neutral-800 border border-slate-200 dark:border-neutral-700 rounded-lg font-black flex items-center justify-center hover:bg-slate-100 dark:hover:bg-neutral-750 transition-colors"
                            >
                              +
                            </button>
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              {/* Right Column: Hardware search and select */}
              <div className="w-full md:w-1/2 flex flex-col border-t md:border-t-0 md:border-l border-slate-100 dark:border-[#222222] pt-4 md:pt-0 md:pl-6 max-h-full">
                <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Hardware Inventory</h3>
                
                <div className="mb-3">
                  <input
                    type="text"
                    value={hardwareSearch}
                    onChange={(e) => setHardwareSearch(e.target.value)}
                    placeholder="Search hardware by name..."
                    className="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2a2a2a]/50 text-[#111111] dark:text-white rounded-xl focus:outline-none focus:border-[#E53935]"
                  />
                </div>

                <div className="flex-1 overflow-y-auto space-y-2 pr-1">
                  {filteredHardware.length === 0 ? (
                    <p className="text-xs text-slate-400 py-12 text-center">No matching hardware items found.</p>
                  ) : (
                    filteredHardware.map(h => {
                      const selectedCount = newOrderItems.find(i => i.id === h.id)?.quantity || 0;
                      return (
                        <div key={h.id} className="flex items-center justify-between p-2.5 bg-slate-50/50 dark:bg-[#161616]/40 border border-slate-200/50 dark:border-slate-800/60 rounded-xl text-xs hover:bg-slate-50 dark:hover:bg-[#161616] transition-colors">
                          <div className="min-w-0 flex-1 pr-2">
                            <h4 className="font-bold text-slate-700 dark:text-slate-200 truncate">{h.name}</h4>
                            <p className="text-[10px] text-red-500 font-bold">Rs. {h.price.toLocaleString()}</p>
                          </div>
                          
                          <button
                            type="button"
                            onClick={() => handleAddItemToOrder(h)}
                            className="bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white border border-red-500/20 hover:border-red-500 px-3 py-1.5 rounded-lg text-[10px] font-black tracking-wide uppercase transition-all shrink-0 flex items-center gap-1"
                          >
                            <Plus size={10} strokeWidth={3} /> Add {selectedCount > 0 && `(${selectedCount})`}
                          </button>
                        </div>
                      );
                    })
                  )}
                </div>
              </div>
            </form>

            <div className="flex justify-end gap-3 pt-6 border-t border-slate-100 dark:border-[#222222] mt-6 shrink-0">
              <button
                type="button"
                onClick={() => {
                  setIsCreateModalOpen(false);
                  setCustomerSearch("");
                  setShowCustomerDropdown(false);
                }}
                className="px-6 py-2.5 border border-slate-200 dark:border-neutral-700 text-slate-600 dark:text-neutral-300 font-bold rounded-xl text-xs hover:bg-slate-50 dark:hover:bg-neutral-800 transition-all"
              >
                Cancel
              </button>
              
              <button
                type="button"
                onClick={handleCreateOrder}
                disabled={isUpdating || newOrderItems.length === 0}
                className="px-6 py-2.5 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl text-xs shadow-lg shadow-red-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
              >
                Create Order
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
