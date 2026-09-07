"use client";

import { useState } from "react";
import { Key, Trash2, Edit, CheckCircle2, XCircle, AlertTriangle, Monitor, Smartphone, Tag, User, Building, Calendar, Search, Lock, Unlock, RotateCcw, Download, MapPin } from "lucide-react";
import Image from "next/image";
import { toast } from "react-hot-toast";

const PLAN_LIMITS: Record<string, { tableLimit: number, staffLimit: number, dishLimit: number, roomLimit: number }> = {
  free: { tableLimit: 5, staffLimit: 2, dishLimit: 50, roomLimit: 0 },
  basic: { tableLimit: 20, staffLimit: 5, dishLimit: 500, roomLimit: 10 },
  premium_trial: { tableLimit: 50, staffLimit: 24, dishLimit: 1000, roomLimit: 20 },
  premium: { tableLimit: 50, staffLimit: 24, dishLimit: 1000, roomLimit: 20 },
  platinum: { tableLimit: 0, staffLimit: 0, dishLimit: 0, roomLimit: 0 },
};

const NEPAL_CITIES = [
  "Kathmandu",
  "Pokhara",
  "Lalitpur",
  "Bhaktapur",
  "Chitwan",
  "Butwal",
  "Biratnagar",
  "Birgunj",
  "Dharan",
  "Itahari",
  "Nepalgunj",
  "Dhangadhi",
  "Janakpur",
  "Hetauda",
  "Birtamode"
];

export default function ClientsClient({ 
  clients, 
  users, 
  generateLicenseKey, 
  deleteClient, 
  deleteUser,
  updateSubscription, 
  resetMachineId, 
  updateRestaurantDetails,
  updateClientDetails,
  updateUserDetails,
  staffLocation = "",
  staffRole = "",
  plans = [],
  combos = [],
  staffId = "",
  marketingStaff = []
}: any) {
  const [activeMainTab, setActiveMainTab] = useState<"web" | "offline">("web");
  const [activePlanTab, setActivePlanTab] = useState<string>("all");
  const [offlineStatsTab, setOfflineStatsTab] = useState<"status" | "plans">("status");
  
  // Modal State
  const [editingSub, setEditingSub] = useState<any>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedDate, setSelectedDate] = useState("");
  const [customLimits, setCustomLimits] = useState({ tableLimit: 0, staffLimit: 0, dishLimit: 0, roomLimit: 0 });
  const [selectedMenus, setSelectedMenus] = useState<string[]>([]);
  
  const [viewingUser, setViewingUser] = useState<any>(null);
  const [isViewModalOpen, setIsViewModalOpen] = useState(false);
  
  const [editingRest, setEditingRest] = useState<any>(null);
  const [isRestModalOpen, setIsRestModalOpen] = useState(false);

  // Edit User State
  const [editingUser, setEditingUser] = useState<any>(null);
  const [isUserModalOpen, setIsUserModalOpen] = useState(false);

  // Edit Client State
  const [editingClient, setEditingClient] = useState<any>(null);
  const [isClientModalOpen, setIsClientModalOpen] = useState(false);
  
  // Search State
  const [searchQuery, setSearchQuery] = useState("");

  const toggleMenuOption = (menuId: string) => {
    setSelectedMenus(prev => prev.includes(menuId) ? prev.filter(m => m !== menuId) : [...prev, menuId]);
  };

  const handleResetRestaurantData = async (restaurantId: string, name: string) => {
    const confirmReset = window.confirm(
      `Are you absolutely sure you want to RESET all transaction data (orders, bills, logs) for "${name}"? This cannot be undone!`
    );
    if (!confirmReset) return;

    const toastId = toast.loading(`Resetting data for "${name}"...`);
    try {
      const res = await fetch("/api/admin/restaurant-action", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ restaurantId, action: "reset" }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        toast.success(`Successfully reset data for "${name}"!`, { id: toastId });
      } else {
        throw new Error(data.error || "Failed to reset restaurant data.");
      }
    } catch (err: any) {
      toast.error(err.message || "An error occurred.", { id: toastId });
    }
  };

  const handleDeleteRestaurantData = async (restaurantId: string, name: string) => {
    const confirmDelete = window.confirm(
      `WARNING: Are you absolutely sure you want to COMPLETELY DELETE "${name}" and all of its staff, menus, and transaction data from the database? This is permanent!`
    );
    if (!confirmDelete) return;

    const toastId = toast.loading(`Deleting restaurant "${name}"...`);
    try {
      const res = await fetch("/api/admin/restaurant-action", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ restaurantId, action: "delete" }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        toast.success(`Successfully deleted "${name}"!`, { id: toastId });
        window.location.reload();
      } else {
        throw new Error(data.error || "Failed to delete restaurant.");
      }
    } catch (err: any) {
      toast.error(err.message || "An error occurred.", { id: toastId });
    }
  };

  const [selectedLocationFilter, setSelectedLocationFilter] = useState<string>("all");

  // Dynamic available locations list
  const availableLocations = Array.from(new Set([
    ...NEPAL_CITIES,
    ...clients.map((c: any) => c.location).filter(Boolean),
    ...users.map((u: any) => u.assignedLocation).filter(Boolean),
    ...users.flatMap((u: any) => u.restaurants ? u.restaurants.map((r: any) => r.city).filter(Boolean) : [])
  ])).sort();

  // Filter by Location / Marketing Staff First (Staff Restriction + Admin Location Dropdown)
  const locationFilteredUsers = users.filter((u: any) => {
    // 1. Staff Role Restriction
    if (staffRole === "MARKETING") {
      const isReferred = u.referredById === staffId;
      if (staffLocation && staffLocation.trim()) {
        const matchesAssignedLoc = u.assignedLocation && u.assignedLocation.toLowerCase().includes(staffLocation.toLowerCase());
        const matchesRestLoc = u.restaurants && u.restaurants.some((r: any) => 
          (r.city && r.city.toLowerCase().includes(staffLocation.toLowerCase())) ||
          (r.address && r.address.toLowerCase().includes(staffLocation.toLowerCase()))
        );
        if (!isReferred && !matchesAssignedLoc && !matchesRestLoc) return false;
      } else if (!isReferred) {
        return false;
      }
    } else if (staffRole !== "SUPERADMIN" && staffRole !== "ADMIN" && staffRole !== "SUPPORT" && staffLocation && staffLocation.toLowerCase() !== "admin") {
      const matchesRestLoc = u.restaurants && u.restaurants.some((r: any) => r.city && r.city.toLowerCase() === staffLocation.toLowerCase());
      const matchesAssignedLoc = u.assignedLocation && u.assignedLocation.toLowerCase() === staffLocation.toLowerCase();
      if (!matchesRestLoc && !matchesAssignedLoc) return false;
    }

    // 2. Admin Location Dropdown Filter
    if (selectedLocationFilter !== "all") {
      const locLower = selectedLocationFilter.toLowerCase();
      const matchesAssigned = u.assignedLocation && u.assignedLocation.toLowerCase().includes(locLower);
      const matchesRestCity = u.restaurants && u.restaurants.some((r: any) => 
        (r.city && r.city.toLowerCase().includes(locLower)) ||
        (r.address && r.address.toLowerCase().includes(locLower))
      );
      if (!matchesAssigned && !matchesRestCity) return false;
    }

    return true;
  });

  const locationFilteredClients = clients.filter((c: any) => {
    // 1. Staff Role Restriction
    if (staffRole === "MARKETING") {
      const isCreated = c.createdById === staffId;
      if (staffLocation && staffLocation.trim()) {
        const matchesLoc = c.location && c.location.toLowerCase().includes(staffLocation.toLowerCase());
        if (!isCreated && !matchesLoc) return false;
      } else if (!isCreated) {
        return false;
      }
    } else if (staffRole !== "SUPERADMIN" && staffRole !== "ADMIN" && staffRole !== "SUPPORT" && staffLocation && staffLocation.toLowerCase() !== "admin") {
      if (!c.location || c.location.toLowerCase() !== staffLocation.toLowerCase()) return false;
    }

    // 2. Admin Location Dropdown Filter
    if (selectedLocationFilter !== "all") {
      const locLower = selectedLocationFilter.toLowerCase();
      if (!c.location || !c.location.toLowerCase().includes(locLower)) return false;
    }

    return true;
  });

  const [dateFilter, setDateFilter] = useState<string>("all");
  const [customStartDate, setCustomStartDate] = useState<string>("");
  const [customEndDate, setCustomEndDate] = useState<string>("");

  const checkDateFilter = (createdAtStr: string) => {
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

  // Filter Web Users
  const filteredUsers = locationFilteredUsers.filter((u: any) => {
    // 1. Check Active Plan Tab
    let matchesPlan = true;
    if (activePlanTab !== "all") {
      matchesPlan = u.restaurants.some((r: any) => 
        r.subscriptions.some((s: any) => {
          const pid = s.planId.toLowerCase();
          if (activePlanTab === "free") return pid.includes("free");
          if (activePlanTab === "basic") return pid.includes("basic");
          if (activePlanTab === "premium") return pid.includes("premium");
          if (activePlanTab === "platinum") return pid.includes("plat");
          return true;
        })
      );
    }
    
    // 2. Check Search Query
    let matchesSearch = true;
    if (searchQuery.trim() !== "") {
      const q = searchQuery.toLowerCase();
      const matchName = u.name?.toLowerCase().includes(q);
      const matchEmail = u.email?.toLowerCase().includes(q);
      const matchRestaurant = u.restaurants.some((r: any) => 
        r.name.toLowerCase().includes(q) || r.phone.includes(q)
      );
      matchesSearch = matchName || matchEmail || matchRestaurant;
    }

    // 3. Check Date Filter
    const matchesDate = checkDateFilter(u.createdAt);

    return matchesPlan && matchesSearch && matchesDate;
  });

  // Filter Offline Clients (exclude online Cloud SaaS auto-created clients)
  const offlineOnlyClients = locationFilteredClients.filter((c: any) => {
    // Exclude clients that correspond to online cloud SaaS users (whose licenseKey is bound to a SaaS restaurant or createdById is null)
    const isOnlineSaaSUser = users.some((u: any) => 
      u.restaurants.some((r: any) => r.offlineLicenseKey && r.offlineLicenseKey === c.licenseKey)
    );
    return !isOnlineSaaSUser && c.createdById !== null;
  });

  const filteredClients = offlineOnlyClients.filter((c: any) => {
    // 1. Check Search Query
    let matchesSearch = true;
    if (searchQuery.trim() !== "") {
      const q = searchQuery.toLowerCase();
      const matchName = c.restaurantName.toLowerCase().includes(q);
      const matchContact = c.contactNumber.includes(q);
      const matchLocation = c.location && c.location.toLowerCase().includes(q);
      const matchKey = c.licenseKey.toLowerCase().includes(q);
      matchesSearch = matchName || matchContact || matchLocation || matchKey;
    }

    // 2. Check Date Filter
    const matchesDate = checkDateFilter(c.createdAt);

    return matchesSearch && matchesDate;
  });

  // Export to CSV function
  const exportToCsv = () => {
    const headers = ['Owner Name', 'Email', 'Phone', 'Restaurant Name', 'Active Plan', 'Joined Date', 'Expiry Date'];
    
    const rows = filteredUsers.map((u: any) => {
      const rest = u.restaurants.length > 0 ? u.restaurants[0] : null;
      const sub = rest && rest.subscriptions.length > 0 ? rest.subscriptions[0] : null;
      
      return [
        u.name || 'Unknown User',
        u.email || '',
        rest ? rest.phone : '',
        rest ? rest.name : 'No restaurant set up',
        sub ? sub.planId.toUpperCase() : 'No active plan',
        new Date(u.createdAt).toLocaleDateString(),
        sub && sub.currentPeriodEnd ? new Date(sub.currentPeriodEnd).toLocaleDateString() : 'N/A'
      ].map(v => `"${v}"`).join(',');
    });

    const csvContent = [headers.join(','), ...rows].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `DRestro_Clients_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-7xl mx-auto pb-20">
      
      {/* Header & Main Tabs */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 pt-4 border-b border-slate-200 dark:border-[#333333] pb-6">
        <div>
          <h1 className="text-3xl font-black text-[#111111] dark:text-white tracking-tight flex items-center gap-3">
            Clients & Users
          </h1>
          <p className="text-slate-500 dark:text-gray-400 mt-2 text-sm">Manage cloud accounts and offline POS licenses</p>
        </div>

        <div className="flex p-1 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-xl">
          <button 
            onClick={() => setActiveMainTab("web")}
            className={`flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold transition-all ${activeMainTab === "web" ? "bg-[#E53935] text-white shadow-lg" : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"}`}
          >
            <User size={16} /> Cloud Accounts ({locationFilteredUsers.length})
          </button>
          <button 
            onClick={() => setActiveMainTab("offline")}
            className={`flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold transition-all ${activeMainTab === "offline" ? "bg-emerald-500 text-white shadow-lg" : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"}`}
          >
            <Key size={16} /> Offline Licenses ({offlineOnlyClients.length})
          </button>
        </div>
      </div>

      {/* WEB USERS SECTION */}
      {activeMainTab === "web" && (
        <div className="space-y-6">
          
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            {/* Sub Tabs for Plans */}
            <div className="flex flex-wrap gap-2">
              {[
                { id: "all", label: "All Users", count: locationFilteredUsers.length },
                { id: "free", label: "Free Plan", count: locationFilteredUsers.filter((u: any) => u.restaurants.some((r: any) => r.subscriptions.some((s: any) => s.planId.includes("free")))).length },
                { id: "basic", label: "Basic", count: locationFilteredUsers.filter((u: any) => u.restaurants.some((r: any) => r.subscriptions.some((s: any) => s.planId.includes("basic")))).length },
                { id: "premium", label: "Premium", count: locationFilteredUsers.filter((u: any) => u.restaurants.some((r: any) => r.subscriptions.some((s: any) => s.planId.includes("premium")))).length },
                { id: "platinum", label: "Platinum", count: locationFilteredUsers.filter((u: any) => u.restaurants.some((r: any) => r.subscriptions.some((s: any) => s.planId.includes("plat")))).length },
              ].map(tab => (
                <button
                  key={tab.id}
                  onClick={() => setActivePlanTab(tab.id)}
                  className={`px-4 py-2 rounded-full text-xs font-bold transition-all border ${
                    activePlanTab === tab.id 
                      ? "bg-[#E53935] text-white border-[#E53935] shadow-md shadow-red-500/10" 
                      : "bg-white dark:bg-[#1A1A1A] text-slate-500 dark:text-gray-400 border-slate-200 dark:border-[#333333] hover:text-[#111111] dark:hover:text-white hover:border-slate-300 dark:hover:border-[#444444]"
                  }`}
                >
                  {tab.label} <span className={`ml-1.5 px-2 py-0.5 rounded-full ${activePlanTab === tab.id ? "bg-white/20 text-white" : "bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-400"}`}>{tab.count}</span>
                </button>
              ))}
            </div>

            {/* Search Bar, Date Filter & Export */}
            <div className="flex flex-wrap items-center gap-3 w-full md:w-auto">
              <div className="relative flex-1 md:w-56 shrink-0">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Search size={16} className="text-slate-400 dark:text-gray-500" />
                </div>
                <input
                  type="text"
                  placeholder="Search name, email, phone..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white text-sm rounded-full pl-9 pr-4 py-2 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-gray-600 dark:text-neutral-400"
                />
              </div>

              {/* Location Filter Dropdown */}
              <div className="flex items-center gap-2">
                <select
                  value={selectedLocationFilter}
                  onChange={(e) => setSelectedLocationFilter(e.target.value)}
                  className="px-3 py-2 rounded-full text-xs font-bold bg-white dark:bg-[#1A1A1A] text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-[#333333] focus:outline-none focus:border-emerald-500 cursor-pointer shadow-sm"
                >
                  <option value="all">📍 All Locations</option>
                  {availableLocations.map((loc: string) => (
                    <option key={loc} value={loc}>📍 {loc}</option>
                  ))}
                </select>
              </div>

              {/* Date Filter */}
              <div className="flex items-center gap-2">
                <select
                  value={dateFilter}
                  onChange={(e) => setDateFilter(e.target.value)}
                  className="px-3 py-2 rounded-full text-xs font-bold bg-white dark:bg-[#1A1A1A] text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-[#333333] focus:outline-none focus:border-emerald-500 cursor-pointer"
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
                      className="px-2 py-1 text-xs bg-white dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#333333] rounded-lg text-slate-800 dark:text-white focus:outline-none"
                    />
                    <span className="text-xs text-slate-400">to</span>
                    <input
                      type="date"
                      value={customEndDate}
                      onChange={(e) => setCustomEndDate(e.target.value)}
                      className="px-2 py-1 text-xs bg-white dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#333333] rounded-lg text-slate-800 dark:text-white focus:outline-none"
                    />
                  </div>
                )}
              </div>

              <button 
                onClick={exportToCsv}
                className="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-[#222222] hover:bg-slate-200 dark:hover:bg-[#333333] text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-[#444444] rounded-full text-xs font-bold transition-all shrink-0"
                title="Export Data to CSV"
              >
                <Download size={14} /> <span className="hidden sm:inline">Export CSV</span>
              </button>
            </div>
          </div>

          {/* Web Users Table */}
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden shadow-xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-500 dark:text-gray-400">
                <thead className="bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-300 text-[11px] uppercase tracking-wider font-semibold">
                  <tr>
                    <th className="px-6 py-4">Owner Name</th>
                    <th className="px-6 py-4">Email</th>
                    <th className="px-6 py-4">Phone</th>
                    <th className="px-6 py-4">Restaurant</th>
                    <th className="px-6 py-4">Active Plan</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#333333]">
                  {filteredUsers.length === 0 && (
                    <tr>
                      <td colSpan={6} className="px-6 py-16 text-center text-slate-400 dark:text-gray-500">
                        <User size={32} className="mx-auto mb-3 opacity-20" />
                        No users found for this plan.
                      </td>
                    </tr>
                  )}
                  {filteredUsers.map((user: any) => (
                    <tr 
                      key={user.id} 
                      onClick={(e) => {
                        if ((e.target as HTMLElement).closest('button, a, input, select')) return;
                        setViewingUser(user);
                        setIsViewModalOpen(true);
                      }}
                      className="hover:bg-slate-100 dark:hover:bg-[#222222]/80 transition-colors group cursor-pointer"
                    >
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-3">
                          <div className="w-10 h-10 rounded-full bg-slate-200 dark:bg-[#333333] flex items-center justify-center text-[#111111] dark:text-white font-bold border border-[#444444] shrink-0">
                            {user.name ? user.name.charAt(0).toUpperCase() : "U"}
                          </div>
                          <div>
                             <div className="font-bold text-[#111111] dark:text-white text-[14px]">{user.name || "Unknown User"}</div>
                            <div className="text-[10px] font-mono mt-0.5 flex flex-wrap items-center gap-1.5">
                              <span className="bg-red-500/10 text-[#E53935] px-1.5 py-0.5 rounded text-[10px] font-extrabold">ID: {user.customerCode || "N/A"}</span>
                              {(user.assignedLocation || (user.restaurants && user.restaurants[0]?.city)) && (
                                <span className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-1.5 py-0.5 rounded text-[10px] font-extrabold">
                                  📍 {user.assignedLocation || user.restaurants[0]?.city}
                                </span>
                              )}
                              {user.referredBy && (
                                <span className="bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 px-1.5 py-0.5 rounded text-[10px] font-extrabold">
                                  👤 Agent: {user.referredBy.email.split('@')[0]}
                                </span>
                              )}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-slate-500 dark:text-gray-400 font-medium text-[13px]">
                        <div>{user.email}</div>
                        <div className="text-[11px] text-slate-400 dark:text-gray-500 mt-1 flex items-center gap-1 font-normal">
                          <Calendar size={10} /> Joined: {new Date(user.createdAt).toLocaleDateString()}
                        </div>
                        {user.lastLoginAt && (
                          <div className="text-[11px] text-slate-400 dark:text-gray-500 mt-1 flex items-center gap-1 font-normal">
                            <span>🌐 Web: <strong className="text-slate-600 dark:text-gray-300">{user.lastLoginIp}</strong> ({user.lastLoginDevice || 'Unknown'}) at {new Date(user.lastLoginAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })} {new Date(user.lastLoginAt).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })}</span>
                          </div>
                        )}
                      </td>
                      <td className="px-6 py-4 font-medium text-slate-600 dark:text-gray-300 text-[13px]">
                        <div className="font-semibold text-slate-800 dark:text-white">{user.phone || <span className="text-slate-400">-</span>}</div>
                        {user.restaurants.length > 0 && user.restaurants.map((rest: any) => {
                          if (rest.phone && rest.phone !== user.phone) {
                            return (
                              <div key={rest.id} className="text-[11px] text-slate-400 dark:text-gray-500 mt-1 flex items-center gap-1">
                                <span>🏢</span> {rest.phone}
                              </div>
                            );
                          }
                          return null;
                        })}
                      </td>
                      <td className="px-6 py-4">
                        {user.restaurants.length > 0 ? (
                          <div className="space-y-3">
                            {user.restaurants.map((rest: any) => {
                              const clientLicense = rest.offlineLicenseKey 
                                ? clients.find((c: any) => c.licenseKey === rest.offlineLicenseKey) 
                                : null;
                              return (
                                <div key={rest.id} className="border-b border-slate-100 dark:border-[#222]/30 pb-2 last:border-0 last:pb-0">
                                  <div className="font-bold text-slate-700 dark:text-gray-200 flex items-center gap-1.5 text-[14px]"><Building size={14} className="text-slate-400 shrink-0"/> {rest.name}</div>
                                  <div className="text-slate-400 dark:text-gray-500 text-[12px] mt-0.5">{rest.type}</div>
                                  {rest.address && (
                                    <div className="text-slate-400 dark:text-gray-500 text-[11px] mt-1 flex items-center gap-1">
                                      <MapPin size={11} className="text-red-500 shrink-0" />
                                      <span className="truncate max-w-[150px]" title={`${rest.address}${rest.ward ? ` (Ward ${rest.ward})` : ''}${rest.city ? `, ${rest.city}` : ''}`}>
                                        {rest.address}{rest.ward && ` (Ward ${rest.ward})`}{rest.city && `, ${rest.city}`}
                                      </span>
                                    </div>
                                  )}
                                  {clientLicense && clientLicense.lastLoginIp && (
                                    <div className="text-[10px] text-slate-400 dark:text-gray-500 mt-1 flex flex-col gap-0.5">
                                      <span className="flex items-center gap-1">
                                        💻 POS: <strong className="text-slate-600 dark:text-gray-300">{clientLicense.lastLoginIp}</strong> ({clientLicense.lastLoginDevice || 'Unknown'})
                                      </span>
                                      <span className="text-[9px] text-slate-400/80 pl-4">
                                        at {new Date(clientLicense.lastLoginAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })} {new Date(clientLicense.lastLoginAt).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })}
                                      </span>
                                    </div>
                                  )}
                                </div>
                              );
                            })}
                          </div>
                        ) : (
                          <span className="text-gray-600 dark:text-neutral-400 italic text-xs">No restaurant set up yet</span>
                        )}
                      </td>
                      <td className="px-6 py-4">
                        {user.restaurants.length > 0 && user.restaurants[0].subscriptions.length > 0 ? (
                          <div className="space-y-2">
                            {user.restaurants[0].subscriptions.map((sub: any) => (
                              <div key={sub.id} className="flex flex-col gap-1">
                                <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold w-fit
                                  ${sub.planId.includes('premium') ? 'bg-[#E53935] text-white border border-transparent' : 
                                    sub.planId.includes('basic') ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' :
                                    sub.planId.includes('plat') ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' :
                                    'bg-gray-500/10 text-slate-500 dark:text-gray-400 border border-gray-500/20'}`}
                                >
                                  {sub.planId.toUpperCase()}
                                </span>
                                <span className="text-[11px] text-slate-400 dark:text-gray-500 capitalize">
                                  {sub.status}
                                  {sub.currentPeriodEnd && <span className="block mt-0.5 text-[#E53935] dark:text-red-400 font-semibold">Exp: {new Date(sub.currentPeriodEnd).toLocaleDateString()}</span>}
                                </span>
                                {sub.updatedBy && (
                                  <span className="text-[10px] text-slate-500 dark:text-gray-400">
                                    Last action by: {sub.updatedBy.email}
                                  </span>
                                )}
                              </div>
                            ))}
                          </div>
                        ) : (
                          <span className="text-gray-600 dark:text-neutral-400 text-xs">No active plan</span>
                        )}
                      </td>

                      <td className="px-6 py-4 text-right">
                        <div className="flex items-center justify-end gap-2">
                          <button
                            onClick={() => {
                              setViewingUser(user);
                              setIsViewModalOpen(true);
                            }}
                            className="text-slate-400 dark:text-gray-500 hover:text-blue-400 hover:bg-blue-500/10 p-2 rounded-lg transition-colors" 
                            title="View Full Details"
                          >
                            <User size={18} />
                          </button>
                          <button
                            onClick={() => {
                              setEditingUser(user);
                              setIsUserModalOpen(true);
                            }}
                            className="text-slate-400 dark:text-gray-500 hover:text-emerald-500 hover:bg-emerald-500/10 p-2 rounded-lg transition-colors" 
                            title="Edit User Details"
                          >
                            <Edit size={18} />
                          </button>
                          {user.restaurants.length > 0 && user.restaurants[0].subscriptions.length > 0 && (
                            <button
                              onClick={() => {
                                const sub = user.restaurants[0].subscriptions[0];
                                setEditingSub(sub);
                                setSelectedDate(sub.currentPeriodEnd ? new Date(sub.currentPeriodEnd).toISOString().split('T')[0] : '');
                                setCustomLimits({
                                  tableLimit: sub.tableLimit || 0,
                                  staffLimit: sub.staffLimit || 0,
                                  dishLimit: sub.dishLimit || 0,
                                  roomLimit: sub.roomLimit || 0,
                                });
                                const defaultMenus = ["pos", "menu", "tables", "rooms", "kot", "reports", "expenses", "staff", "inventory", "customers", "settings"];
                                setSelectedMenus(sub.allowedMenus ? sub.allowedMenus.split(",") : defaultMenus);
                                setIsModalOpen(true);
                              }}
                              className="text-slate-400 dark:text-gray-500 hover:text-purple-500 hover:bg-purple-500/10 p-2 rounded-lg transition-colors" 
                              title="Manage Plan"
                            >
                              <Tag size={18} />
                            </button>
                          )}
                          {user.restaurants.length > 0 && (
                            <>
                              <button
                                onClick={() => handleResetRestaurantData(user.restaurants[0].id, user.restaurants[0].name)}
                                className="text-slate-400 dark:text-gray-500 hover:text-amber-500 hover:bg-amber-500/10 p-2 rounded-lg transition-colors"
                                title="Reset Restaurant Data (Clear Orders, Logs)"
                              >
                                <RotateCcw size={18} />
                              </button>
                              <button
                                onClick={() => handleDeleteRestaurantData(user.restaurants[0].id, user.restaurants[0].name)}
                                className="text-slate-400 dark:text-gray-500 hover:text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition-colors"
                                title="Delete Restaurant & All Data"
                              >
                                <Trash2 size={18} />
                              </button>
                            </>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* OFFLINE POS SECTION */}
      {activeMainTab === "offline" && (
        <div className="space-y-8">
          {/* Main Keygen Card */}
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-8 shadow-2xl relative overflow-hidden">
            <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>

            <div className="flex items-center gap-3 mb-6">
              <span className="text-2xl drop-shadow-md">🔑</span>
              <div>
                <h2 className="text-xl font-bold text-[#111111] dark:text-white">DRestro Keygen</h2>
                <p className="text-[11px] text-slate-500 dark:text-gray-400 uppercase tracking-widest mt-1">Generate Hardware Licenses</p>
              </div>
            </div>
            
            <form action={generateLicenseKey} className="space-y-6">
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Client Name / Business Name</label>
                  <input name="restaurantName" required type="text" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-inner" placeholder="e.g. Himalayan Kitchen" />
                </div>
                
                <div className="space-y-2">
                  <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Contact Number</label>
                  <input name="contactNumber" required type="text" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-inner" placeholder="e.g. 986-5029558" />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div className="space-y-2">
                  <label className="text-sm font-semibold text-slate-600 dark:text-gray-300 flex items-center gap-2">
                    Client's Machine ID
                    <span className="text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded border border-red-500/30">Required</span>
                  </label>
                  <input name="machineId" required type="text" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 font-mono text-sm shadow-inner" placeholder="Paste Machine ID from client's POS License page" />
                  <p className="text-[11px] text-slate-400 dark:text-gray-500">Ask the client to copy their Machine ID from the POS License page and send it via WhatsApp</p>
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Location / City</label>
                  {staffRole !== "SUPERADMIN" && staffRole !== "ADMIN" && staffRole !== "SUPPORT" && staffLocation && staffLocation.toLowerCase() !== "admin" ? (
                    <>
                      <input 
                        type="text" 
                        disabled 
                        value={staffLocation} 
                        className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-slate-500 dark:text-gray-400 rounded-xl px-4 py-3.5 cursor-not-allowed shadow-inner"
                      />
                      <input type="hidden" name="location" value={staffLocation} />
                    </>
                  ) : (
                    <select 
                      name="location" 
                      className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner appearance-none cursor-pointer"
                    >
                      <option value="">Select Location (Optional)</option>
                      {NEPAL_CITIES.map(city => (
                        <option key={city} value={city}>{city}</option>
                      ))}
                    </select>
                  )}
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div className="space-y-2">
                  <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">License Duration</label>
                  <select name="expiryDays" defaultValue="365" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner appearance-none cursor-pointer">
                    <option value="14">14 Days (Free Trial)</option>
                    <option value="30">1 Month</option>
                    <option value="90">3 Months</option>
                    <option value="365">1 Year</option>
                    <option value="730">2 Years</option>
                    <option value="1095">3 Years</option>
                    <option value="36500">Lifetime (100 Years)</option>
                  </select>
                </div>
                
                <div className="space-y-2">
                  <label className="text-sm font-semibold text-slate-600 dark:text-gray-300 flex items-center gap-2">
                    Plan Label <span className="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/30">display only</span>
                  </label>
                  <select name="planLabel" defaultValue="Premium" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner appearance-none cursor-pointer">
                    <option value="Free Trial">Free Trial</option>
                    <option value="Basic">Basic</option>
                    <option value="Standard">Standard</option>
                    <option value="Premium">Premium</option>
                    <option value="Enterprise">Enterprise</option>
                  </select>
                </div>
              </div>

              <div className="pt-2 border-t border-slate-200 dark:border-[#333333] mt-6">
                <h3 className="text-[11px] font-bold text-slate-500 dark:text-gray-400 tracking-[0.2em] uppercase mb-4 mt-4 flex items-center gap-2">
                  Feature Limitations <span className="text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-500/20 normal-case tracking-normal">0 = Unlimited</span>
                </h3>
                
                <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                  <div className="space-y-2">
                    <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Tables</label>
                    <input name="tableLimit" type="number" min="0" defaultValue="0" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner" />
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Staff / Users</label>
                    <input name="staffLimit" type="number" min="0" defaultValue="0" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner" />
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Dishes</label>
                    <input name="dishLimit" type="number" min="0" defaultValue="0" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner" />
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Rooms</label>
                    <input name="roomLimit" type="number" min="0" defaultValue="0" className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner" />
                  </div>
                </div>
              </div>

              <div className="pt-6">
                <button type="submit" className="w-full bg-emerald-500 hover:bg-emerald-400 text-white py-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-[0_4px_15px_rgba(16,185,129,0.3)] hover:shadow-[0_6px_20px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 cursor-pointer">
                  Generate License Key
                </button>
              </div>
            </form>
          </div>

          {/* License Usage Stats Widget */}
          {(() => {
            const totalOfflineLicenses = offlineOnlyClients.length;
            const activeOfflineLicenses = offlineOnlyClients.filter((c: any) => {
              const isExpired = new Date(c.expiryDate) < new Date();
              return !isExpired && c.status === "Active";
            }).length;
            const expiredOfflineLicenses = offlineOnlyClients.filter((c: any) => {
              const isExpired = new Date(c.expiryDate) < new Date();
              return isExpired || c.status === "Expired";
            }).length;
            const unboundOfflineLicenses = offlineOnlyClients.filter((c: any) => !c.machineId).length;
            const boundOfflineLicenses = offlineOnlyClients.filter((c: any) => c.machineId).length;

            const freeTrialOffline = offlineOnlyClients.filter((c: any) => {
              const label = (c.planLabel || "").toLowerCase();
              return label.includes("free") || label.includes("trial");
            }).length;
            const basicOffline = offlineOnlyClients.filter((c: any) => (c.planLabel || "").toLowerCase().includes("basic")).length;
            const standardOffline = offlineOnlyClients.filter((c: any) => (c.planLabel || "").toLowerCase().includes("standard")).length;
            const premiumOffline = offlineOnlyClients.filter((c: any) => (c.planLabel || "").toLowerCase().includes("premium")).length;
            const enterpriseOffline = offlineOnlyClients.filter((c: any) => (c.planLabel || "").toLowerCase().includes("enterprise")).length;

            return (
              <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 shadow-md space-y-6">
                <div className="flex justify-between items-center border-b border-slate-100 dark:border-[#333333] pb-4">
                  <div className="flex gap-2 p-1 bg-slate-100 dark:bg-[#222] rounded-xl">
                    <button
                      type="button"
                      onClick={() => setOfflineStatsTab("status")}
                      className={`px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${
                        offlineStatsTab === "status"
                          ? "bg-white dark:bg-[#1A1A1A] text-slate-800 dark:text-white shadow-sm"
                          : "text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-white"
                      }`}
                    >
                      License Status
                    </button>
                    <button
                      type="button"
                      onClick={() => setOfflineStatsTab("plans")}
                      className={`px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${
                        offlineStatsTab === "plans"
                          ? "bg-white dark:bg-[#1A1A1A] text-slate-800 dark:text-white shadow-sm"
                          : "text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-white"
                      }`}
                    >
                      License Plans
                    </button>
                  </div>
                  <span className="text-[11px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">
                    License Analytics
                  </span>
                </div>

                {offlineStatsTab === "status" ? (
                  <div className="space-y-4">
                    <h4 className="text-sm font-bold text-slate-755 dark:text-gray-300">Number of Licenses by status</h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                      {/* Active */}
                      <div className="space-y-1.5">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {activeOfflineLicenses} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-emerald-500 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (activeOfflineLicenses / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>

                      {/* Expired */}
                      <div className="space-y-1.5">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-red-500"></span> Expired
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {expiredOfflineLicenses} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-red-500 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (expiredOfflineLicenses / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>

                      {/* Bound */}
                      <div className="space-y-1.5">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Bound (Locked to PC)
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {boundOfflineLicenses} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-blue-500 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (boundOfflineLicenses / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>

                      {/* Unbound */}
                      <div className="space-y-1.5">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Unbound (Open)
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {unboundOfflineLicenses} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-amber-500 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (unboundOfflineLicenses / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>
                    </div>
                  </div>
                ) : (
                  <div className="space-y-4">
                    <h4 className="text-sm font-bold text-slate-700 dark:text-gray-300">Number of Licenses by plan</h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                      {/* Free Trial */}
                      <div className="space-y-1.5">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Free Trial
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {freeTrialOffline} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-slate-400 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (freeTrialOffline / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>

                      {/* Basic */}
                      <div className="space-y-1.5">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-blue-400"></span> Basic
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {basicOffline} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-blue-400 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (basicOffline / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>

                      {/* Standard */}
                      <div className="space-y-1.5">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-indigo-400"></span> Standard
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {standardOffline} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-indigo-400 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (standardOffline / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>

                      {/* Premium */}
                      <div className="space-y-1.5">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-red-400"></span> Premium
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {premiumOffline} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-red-400 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (premiumOffline / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>

                      {/* Enterprise */}
                      <div className="space-y-1.5 md:col-span-2">
                        <div className="flex justify-between items-center text-xs font-semibold">
                          <span className="text-slate-500 dark:text-gray-400 flex items-center gap-1.5">
                            <span className="w-2.5 h-2.5 rounded-full bg-purple-400"></span> Enterprise
                          </span>
                          <span className="text-slate-850 dark:text-slate-205 font-bold">
                            {enterpriseOffline} <span className="text-slate-400 dark:text-gray-500 font-normal">/ {totalOfflineLicenses}</span>
                          </span>
                        </div>
                        <div className="h-2 w-full bg-slate-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                          <div
                            className="h-full rounded-full bg-purple-400 transition-all duration-500"
                            style={{ width: `${totalOfflineLicenses ? (enterpriseOffline / totalOfflineLicenses) * 100 : 0}%` }}
                          ></div>
                        </div>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            );
          })()}

          {/* Generated Licenses Table */}
          <div>
            <h3 className="text-[11px] font-bold text-slate-400 dark:text-gray-500 tracking-[0.2em] uppercase mb-4 pl-2">Generated Licenses</h3>
            <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden shadow-xl">
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-500 dark:text-gray-400">
                  <thead className="bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-300 text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                      <th className="px-6 py-4">Client Info</th>
                      <th className="px-6 py-4">Machine & Plan</th>
                      <th className="px-6 py-4">License Key</th>
                      <th className="px-6 py-4">Status / Expiry</th>
                      <th className="px-6 py-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-200 dark:divide-[#333333]">
                    {filteredClients.length === 0 && (
                      <tr>
                        <td colSpan={5} className="px-6 py-12 text-center text-slate-400 dark:text-gray-500">
                          <Key size={32} className="mx-auto mb-3 opacity-20" />
                          No licenses generated yet. Create one above!
                        </td>
                      </tr>
                    )}
                    {filteredClients.map((client: any) => {
                      const isExpired = new Date(client.expiryDate) < new Date();
                      
                      return (
                        <tr key={client.id} className="hover:bg-slate-100 dark:bg-[#222222]/50 transition-colors group">
                          <td className="px-6 py-4">
                            <div className="font-bold text-[#111111] dark:text-white text-[15px]">{client.restaurantName}</div>
                            <div className="text-slate-400 dark:text-gray-500 mt-1 flex flex-col gap-1 text-[13px]">
                              <span className="flex items-center gap-1.5"><Smartphone size={13}/> {client.contactNumber}</span>
                              {client.location && <span className="flex items-center gap-1.5 opacity-80"><Tag size={13}/> {client.location}</span>}
                              {client.createdBy && (
                                <span className="text-[11px] text-slate-500 dark:text-gray-400 flex items-center gap-1 mt-1">
                                  <span>Created by:</span>
                                  <span className="font-semibold">{client.createdBy.email}</span>
                                </span>
                              )}
                              {client.updatedBy && (
                                <span className="text-[11px] text-slate-500 dark:text-gray-400 flex items-center gap-1">
                                  <span>Updated by:</span>
                                  <span className="font-semibold">{client.updatedBy.email}</span>
                                </span>
                              )}
                              {client.lastLoginAt && (
                                <div className="text-[11px] text-slate-400 dark:text-gray-500 mt-1 flex items-center gap-1 font-normal">
                                  <span>🌐 POS: <strong className="text-slate-600 dark:text-gray-300">{client.lastLoginIp}</strong> ({client.lastLoginDevice || 'Unknown'}) at {new Date(client.lastLoginAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })} {new Date(client.lastLoginAt).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })}</span>
                                </div>
                              )}
                            </div>
                          </td>
                          <td className="px-6 py-4">
                            <div className="flex items-center gap-1.5 font-medium text-slate-600 dark:text-gray-300 mb-1">
                              <Tag size={13} className="text-emerald-500" /> {client.planLabel}
                            </div>
                            {client.machineId ? (
                              <div className="flex items-center gap-1.5 mb-1">
                                <Lock size={11} className="text-emerald-500 shrink-0" />
                                <span className="text-[11px] text-slate-400 dark:text-gray-500 font-mono bg-slate-50 dark:bg-[#111111] px-1.5 py-0.5 rounded border border-slate-200 dark:border-[#333333] inline-block" title={client.machineId}>
                                  {client.machineId.substring(0, 16)}...
                                </span>
                              </div>
                            ) : (
                              <div className="flex items-center gap-1.5 mb-1">
                                <Unlock size={11} className="text-amber-500 shrink-0" />
                                <span className="text-[11px] text-amber-400">Not bound</span>
                              </div>
                            )}
                            <div className="flex flex-wrap gap-1 mt-1">
                              {client.tableLimit > 0 && <span className="text-[10px] bg-blue-500/10 text-blue-400 px-1.5 py-0.5 rounded border border-blue-500/20">Tables: {client.tableLimit}</span>}
                              {client.staffLimit > 0 && <span className="text-[10px] bg-indigo-500/10 text-indigo-400 px-1.5 py-0.5 rounded border border-indigo-500/20">Staff: {client.staffLimit}</span>}
                              {client.dishLimit > 0 && <span className="text-[10px] bg-orange-500/10 text-orange-400 px-1.5 py-0.5 rounded border border-orange-500/20">Dishes: {client.dishLimit}</span>}
                              {client.roomLimit > 0 && <span className="text-[10px] bg-pink-500/10 text-pink-400 px-1.5 py-0.5 rounded border border-pink-500/20">Rooms: {client.roomLimit}</span>}
                            </div>
                          </td>
                          <td className="px-6 py-4">
                            <code className="bg-[#111520] border border-emerald-900/50 px-3 py-1.5 rounded-lg text-emerald-400 font-mono text-[13px] select-all shadow-inner font-bold tracking-wide">
                              {client.licenseKey}
                            </code>
                          </td>
                          <td className="px-6 py-4">
                            {isExpired || client.status === "Expired" ? (
                              <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-500/10 text-red-500 border border-red-500/20 mb-1">
                                <XCircle size={14} /> Expired
                              </span>
                            ) : client.status === "Active" ? (
                              <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 mb-1">
                                <CheckCircle2 size={14} /> Active
                              </span>
                            ) : (
                              <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20 mb-1">
                                <AlertTriangle size={14} /> {client.status}
                              </span>
                            )}
                            <div className="text-xs text-slate-400 dark:text-gray-500">
                              {new Date(client.expiryDate).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })}
                            </div>
                          </td>
                          <td className="px-6 py-4 text-right">
                            <div className="flex items-center justify-end gap-1">
                              {client.machineId && (
                                <form action={resetMachineId}>
                                  <input type="hidden" name="clientId" value={client.id} />
                                  <button type="submit" className="text-slate-400 dark:text-gray-500 hover:text-amber-500 hover:bg-amber-500/10 p-2 rounded-lg transition-colors" title="Reset Machine ID (unbind from PC)">
                                    <RotateCcw size={16} />
                                  </button>
                                </form>
                              )}
                              <button 
                                onClick={() => {
                                  setEditingClient(client);
                                  setIsClientModalOpen(true);
                                }} 
                                className="text-slate-400 dark:text-gray-500 hover:text-emerald-500 hover:bg-emerald-500/10 p-2 rounded-lg transition-colors" 
                                title="Edit Client Details"
                              >
                                <Edit size={16} />
                              </button>
                              <button onClick={() => deleteClient(client.id)} className="text-slate-400 dark:text-gray-500 hover:text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition-colors" title="Delete Client">
                                <Trash2 size={18} />
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
          </div>
        </div>
      )}

      {/* VIEW USER DETAILS MODAL */}
      {isViewModalOpen && viewingUser && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 shadow-2xl w-full max-w-2xl animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div className="flex justify-between items-start mb-6">
              <div className="flex items-center gap-4">
                <div className="w-16 h-16 rounded-full bg-slate-100 dark:bg-[#222222] flex items-center justify-center text-[#111111] dark:text-white font-bold text-2xl border-2 border-[#444444]">
                  {viewingUser.name ? viewingUser.name.charAt(0).toUpperCase() : "U"}
                </div>
                <div>
                  <h3 className="text-xl font-bold text-[#111111] dark:text-white">{viewingUser.name || "Unknown User"}</h3>
                  <p className="text-slate-500 dark:text-gray-400 text-sm">{viewingUser.email}</p>
                  <p className="text-slate-400 dark:text-gray-500 text-xs mt-1">ID: {viewingUser.id}</p>
                </div>
              </div>
              <button onClick={() => setIsViewModalOpen(false)} className="text-slate-400 dark:text-gray-500 hover:text-[#111111] dark:text-white transition-colors">
                <XCircle size={24} />
              </button>
            </div>
            
            <div className="space-y-6">
              
              <div className="grid grid-cols-1 gap-4">
                <div className="bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl p-4">
                  <div className="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-1">Joined Date</div>
                  <div className="text-[#111111] dark:text-white font-medium flex items-center gap-2">
                    <Calendar size={16} className="text-slate-500 dark:text-gray-400"/> 
                    {new Date(viewingUser.createdAt).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })}
                  </div>
                </div>
              </div>

              <div>
                <h4 className="text-sm font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-3">Restaurants & Subscriptions</h4>
                {viewingUser.restaurants.length > 0 ? (
                  <div className="space-y-4">
                    {viewingUser.restaurants.map((rest: any) => (
                      <div key={rest.id} className="bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] rounded-xl p-5 relative overflow-hidden">
                        <div className="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                        <div className="flex justify-between items-start mb-4">
                          <div className="w-full">
                            <div className="flex items-center justify-between">
                              <h5 className="text-lg font-bold text-[#111111] dark:text-white flex items-center gap-2">
                                <Building size={18} className="text-emerald-500 shrink-0"/> {rest.name}
                              </h5>
                              <button
                                type="button"
                                onClick={() => {
                                  setEditingRest(rest);
                                  setIsRestModalOpen(true);
                                }}
                                className="text-slate-500 dark:text-gray-400 hover:text-emerald-500 hover:bg-emerald-500/10 p-1.5 rounded-lg transition-all flex items-center gap-1 text-xs font-semibold"
                                title="Edit Restaurant Identity & Details"
                              >
                                <Edit size={14} />
                                <span>Edit details</span>
                              </button>
                            </div>
                            <p className="text-slate-500 dark:text-gray-400 text-sm mt-1">{rest.type} • {rest.phone}</p>
                            {rest.address && <p className="text-slate-400 dark:text-gray-500 text-xs mt-1">{rest.address}</p>}
                          </div>
                        </div>

                        {/* Synced Restaurant Settings Details */}
                        <div className="grid grid-cols-2 gap-4 mt-4 mb-4 bg-slate-50 dark:bg-[#111111]/60 p-4 rounded-xl border border-slate-200 dark:border-[#333333] text-xs">
                          <div className="col-span-2">
                            <h6 className="font-bold text-slate-700 dark:text-gray-300 uppercase tracking-wider text-[10px]">Restaurant Identity</h6>
                          </div>
                          <div>
                            <span className="text-slate-400 dark:text-gray-500 block mb-0.5">Tagline</span>
                            <span className="font-semibold text-[#111111] dark:text-gray-200">{rest.tagline || '-'}</span>
                          </div>
                          <div>
                            <span className="text-slate-400 dark:text-gray-500 block mb-0.5">Email</span>
                            <span className="font-semibold text-[#111111] dark:text-gray-200">{rest.email || '-'}</span>
                          </div>
                          <div>
                            <span className="text-slate-400 dark:text-gray-500 block mb-0.5">Address & Location</span>
                            <span className="font-semibold text-[#111111] dark:text-gray-200">
                              {rest.address ? (
                                <>
                                  {rest.address}
                                  {rest.ward && ` (Ward ${rest.ward})`}
                                  {rest.city && `, ${rest.city}`}
                                </>
                              ) : (
                                '-'
                              )}
                            </span>
                          </div>
                          <div>
                            <span className="text-slate-400 dark:text-gray-500 block mb-0.5">Date / Calendar System</span>
                            <span className="font-semibold text-[#111111] dark:text-gray-200">{rest.dateCalendarType === 'BS' ? 'Bikram Sambat (BS)' : 'Gregorian (AD)'}</span>
                          </div>

                          <div className="col-span-2 border-t border-slate-200 dark:border-[#333333] pt-3 mt-1">
                            <h6 className="font-bold text-slate-700 dark:text-gray-300 uppercase tracking-wider text-[10px]">Billing & Tax</h6>
                          </div>
                          <div>
                            <span className="text-slate-400 dark:text-gray-500 block mb-0.5">Currency Symbol</span>
                            <span className="font-semibold text-[#111111] dark:text-gray-200">{rest.currency || 'Rs.'}</span>
                          </div>
                          <div>
                            <span className="text-slate-400 dark:text-gray-500 block mb-0.5">PAN / VAT Number</span>
                            <span className="font-semibold text-[#111111] dark:text-gray-200">{rest.panNumber || '-'}</span>
                          </div>
                          <div>
                            <span className="text-slate-400 dark:text-gray-500 block mb-0.5">Tax / VAT (%)</span>
                            <span className="font-semibold text-[#111111] dark:text-gray-200">{rest.taxPercent !== null && rest.taxPercent !== undefined ? `${rest.taxPercent}%` : '0%'}</span>
                          </div>
                          <div>
                            <span className="text-slate-400 dark:text-gray-500 block mb-0.5">Service Charge (%)</span>
                            <span className="font-semibold text-[#111111] dark:text-gray-200">{rest.serviceChargePercent !== null && rest.serviceChargePercent !== undefined ? `${rest.serviceChargePercent}%` : '0%'}</span>
                          </div>
                        </div>



                        <div className="border-t border-slate-200 dark:border-[#333333] pt-4 mt-2">
                          <span className="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider block mb-2">Active Plan Status</span>
                          {rest.subscriptions.length > 0 ? (
                            <div className="space-y-3">
                              {rest.subscriptions.map((sub: any) => (
                                <div key={sub.id} className="flex items-center justify-between bg-slate-50 dark:bg-[#111111] p-3 rounded-lg border border-slate-200 dark:border-[#333333]">
                                  <div className="flex items-center gap-3">
                                    <span className={`px-2.5 py-1 rounded-full text-xs font-bold
                                      ${sub.planId.includes('premium') ? 'bg-red-500/10 text-red-500 border border-red-500/20' : 
                                        sub.planId.includes('basic') ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' :
                                        sub.planId.includes('plat') ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' :
                                        'bg-gray-500/10 text-slate-500 dark:text-gray-400 border border-gray-500/20'}`}
                                    >
                                      {sub.planId.toUpperCase()}
                                    </span>
                                    <div>
                                      <span className="text-sm font-medium text-slate-600 dark:text-gray-300 capitalize">{sub.status}</span>
                                      {sub.updatedBy && (
                                        <span className="block text-[10px] text-slate-500 dark:text-gray-400 mt-0.5">
                                          Last action by: {sub.updatedBy.email}
                                        </span>
                                      )}
                                    </div>
                                  </div>
                                  <div className="flex items-center gap-4 text-right">
                                    <div>
                                      <div className="text-xs text-slate-400 dark:text-gray-500 uppercase">Expiry Date</div>
                                      <div className="text-sm text-slate-600 dark:text-gray-300 font-medium">
                                        {sub.currentPeriodEnd ? new Date(sub.currentPeriodEnd).toLocaleDateString() : "Lifetime / Unknown"}
                                      </div>
                                    </div>
                                    <button
                                      onClick={() => {
                                        setIsViewModalOpen(false);
                                        setEditingSub(sub);
                                        setSelectedDate(sub.currentPeriodEnd ? new Date(sub.currentPeriodEnd).toISOString().split('T')[0] : '');
                                        setCustomLimits({
                                          tableLimit: sub.tableLimit || 0,
                                          staffLimit: sub.staffLimit || 0,
                                          dishLimit: sub.dishLimit || 0,
                                          roomLimit: sub.roomLimit || 0,
                                        });
                                        const defaultMenus = ["pos", "menu", "tables", "rooms", "kot", "reports", "expenses", "staff", "inventory", "customers", "settings"];
                                        setSelectedMenus(sub.allowedMenus ? sub.allowedMenus.split(",") : defaultMenus);
                                        setIsModalOpen(true);
                                      }}
                                      className="text-slate-400 dark:text-gray-500 hover:text-emerald-500 hover:bg-emerald-500/10 p-2 rounded-lg transition-colors" 
                                      title="Manage Plan"
                                    >
                                      <Edit size={16} />
                                    </button>
                                  </div>
                                </div>
                              ))}
                            </div>
                          ) : (
                            <p className="text-slate-400 dark:text-gray-500 text-sm">No subscription records found for this restaurant.</p>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-xl p-8 text-center text-slate-400 dark:text-gray-500">
                    <Building size={32} className="mx-auto mb-3 opacity-20" />
                    This user has not set up a restaurant yet.
                  </div>
                )}
              </div>

              {/* Dangerous / Actions Section */}
              <div className="border-t border-slate-200 dark:border-[#333333] pt-6 mt-6 space-y-6">
                
                {/* 1. Quick Edit User Profile Details */}
                <div>
                  <h4 className="text-sm font-bold text-slate-505 dark:text-gray-400 uppercase tracking-wider mb-3">Update User Profile Info</h4>
                  <form action={async (formData) => {
                    await updateUserDetails(formData);
                    // Refresh local user display
                    const updatedName = formData.get("name") as string;
                    const updatedEmail = formData.get("email") as string;
                    const updatedPhone = formData.get("phone") as string;
                    const updatedLocation = formData.get("assignedLocation") as string;
                    const updatedAgentId = formData.get("referredById") as string;
                    const matchedStaff = marketingStaff.find((s: any) => s.id === updatedAgentId);
                    setViewingUser({
                      ...viewingUser,
                      name: updatedName,
                      email: updatedEmail,
                      phone: updatedPhone,
                      assignedLocation: updatedLocation,
                      referredById: updatedAgentId,
                      referredBy: matchedStaff || null
                    });
                    toast.success("User profile details updated successfully!");
                  }} className="space-y-4 bg-slate-50 dark:bg-[#111111]/45 border border-slate-200 dark:border-[#333333] p-4 rounded-xl">
                    <input type="hidden" name="userId" value={viewingUser.id} />
                    
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                      <div>
                        <label className="text-[10px] font-bold text-slate-400 dark:text-neutral-500 uppercase block mb-1">Full Name</label>
                        <input type="text" name="name" required defaultValue={viewingUser.name || ""} className="w-full bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-red-500" />
                      </div>
                      <div>
                        <label className="text-[10px] font-bold text-slate-400 dark:text-neutral-500 uppercase block mb-1">Email Address</label>
                        <input type="email" name="email" required defaultValue={viewingUser.email || ""} className="w-full bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-red-500" />
                      </div>
                      <div>
                        <label className="text-[10px] font-bold text-slate-400 dark:text-neutral-500 uppercase block mb-1">Phone Number</label>
                        <input type="text" name="phone" maxLength={10} placeholder="e.g. 98XXXXXXXX" defaultValue={viewingUser.phone || ""} className="w-full bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-red-500" />
                      </div>
                      <div>
                        <label className="text-[10px] font-bold text-slate-400 dark:text-neutral-500 uppercase block mb-1">Assign Location / City</label>
                        <input type="text" name="assignedLocation" placeholder="e.g. Hetauda, Pokhara" defaultValue={viewingUser.assignedLocation || ""} className="w-full bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-emerald-500" />
                      </div>
                      <div>
                        <label className="text-[10px] font-bold text-slate-400 dark:text-neutral-500 uppercase block mb-1">Assign Marketing Agent</label>
                        <select name="referredById" defaultValue={viewingUser.referredById || ""} className="w-full bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-emerald-500">
                          <option value="">-- Unassigned --</option>
                          {marketingStaff.map((staff: any) => (
                            <option key={staff.id} value={staff.id}>
                              {staff.email} ({staff.role}{staff.location ? ` - ${staff.location}` : ''})
                            </option>
                          ))}
                        </select>
                      </div>
                    </div>
                    
                    <button type="submit" className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer">
                      Update Profile Details
                    </button>
                  </form>
                </div>

                {/* 2. Danger Zone (Delete Account) */}
                <div className="bg-rose-500/5 border border-rose-500/20 rounded-xl p-5">
                  <div className="flex items-start gap-3">
                    <AlertTriangle className="text-rose-500 shrink-0 mt-0.5" size={20} />
                    <div className="space-y-1">
                      <h4 className="text-sm font-bold text-rose-500">Danger Zone</h4>
                      <p className="text-xs text-slate-500 dark:text-gray-400 leading-relaxed">
                        Deleting this user account will completely remove their registered restaurants, cloud data, staff, orders, and payment histories from the system. This cannot be undone.
                      </p>
                      
                      <div className="pt-2">
                        <button
                          type="button"
                          onClick={async () => {
                            if (window.confirm(`Are you absolutely sure you want to COMPLETELY DELETE the user "${viewingUser.name || 'Unknown User'}" and all associated data?`)) {
                              const toastId = toast.loading("Deleting user account...");
                              try {
                                await deleteUser(viewingUser.id);
                                toast.success("User deleted successfully!", { id: toastId });
                                setIsViewModalOpen(false);
                                window.location.reload();
                              } catch (e: any) {
                                toast.error(`Error: ${e.message}`, { id: toastId });
                              }
                            }
                          }}
                          className="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg transition-all shadow-sm shadow-rose-500/10"
                        >
                          <Trash2 size={13} />
                          <span>Delete Customer Account</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

              </div>

            </div>
          </div>
        </div>
      )}

      {/* MANAGE PLAN MODAL */}
      {isModalOpen && editingSub && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 shadow-2xl w-full max-w-md animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-lg font-bold text-[#111111] dark:text-white flex items-center gap-2"><Edit size={20} className="text-emerald-500"/> Manage Plan</h3>
              <button onClick={() => setIsModalOpen(false)} className="text-slate-400 dark:text-gray-500 hover:text-[#111111] dark:text-white transition-colors">
                <XCircle size={24} />
              </button>
            </div>
            
            <form action={(formData) => {
              updateSubscription(formData);
              setIsModalOpen(false);
            }} className="space-y-5">
              <input type="hidden" name="subscriptionId" value={editingSub.id} />
              
              <div className="space-y-2">
                <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Plan</label>
                <select 
                  name="planId" 
                  defaultValue={editingSub.planId} 
                  onChange={(e) => {
                    const plan = e.target.value;
                    if (PLAN_LIMITS[plan]) {
                      setCustomLimits(PLAN_LIMITS[plan]);
                    } else {
                      // Fallback limit for custom/dynamic plans
                      setCustomLimits({ tableLimit: 50, staffLimit: 24, dishLimit: 1000, roomLimit: 20 });
                    }
                  }}
                  className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 appearance-none cursor-pointer"
                >
                  <optgroup label="Default Plans">
                    <option value="free">Free</option>
                    <option value="basic">Basic</option>
                    <option value="premium_trial">Premium Trial</option>
                    <option value="premium">Premium</option>
                    <option value="platinum">Platinum</option>
                  </optgroup>
                  {plans.length > 0 && (
                    <optgroup label="Software Plans (Dynamic)">
                      {plans
                        .filter((p: any) => !["free", "basic", "premium", "platinum", "premium_trial"].includes(p.name.toLowerCase()))
                        .map((p: any) => (
                          <option key={p.id} value={p.name.toLowerCase()}>{p.name}</option>
                        ))
                      }
                    </optgroup>
                  )}
                  {combos.length > 0 && (
                    <optgroup label="Combo Packages">
                      {combos.map((c: any) => (
                        <option key={c.id} value={c.name.toLowerCase()}>{c.name}</option>
                      ))}
                    </optgroup>
                  )}
                </select>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Status</label>
                <select name="status" defaultValue={editingSub.status} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 appearance-none cursor-pointer">
                  <option value="active">Active</option>
                  <option value="trialing">Trialing</option>
                  <option value="past_due">Past Due</option>
                  <option value="canceled">Canceled</option>
                  <option value="suspended">Suspended</option>
                </select>
              </div>

              <div className="space-y-2">
                <div className="flex justify-between items-center">
                  <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Expiry Date (Current Period End)</label>
                </div>
                
                <div className="flex flex-wrap gap-2 mb-2">
                  {[
                    { label: '+14 Days (Trial)', days: 14 },
                    { label: '+1 Year', days: 365 },
                    { label: '+2 Years', days: 730 },
                    { label: '+3 Years', days: 1095 },
                    { label: 'Lifetime', days: 36500 },
                  ].map(preset => (
                    <button
                      key={preset.label}
                      type="button"
                      onClick={() => {
                        const date = new Date();
                        date.setDate(date.getDate() + preset.days);
                        setSelectedDate(date.toISOString().split('T')[0]);
                      }}
                      className="px-3 py-1.5 bg-slate-100 dark:bg-[#222222] hover:bg-slate-200 dark:bg-[#333333] border border-[#444444] rounded-lg text-xs font-semibold text-slate-600 dark:text-gray-300 transition-colors"
                    >
                      {preset.label}
                    </button>
                  ))}
                </div>

                <input 
                  id="expiryDateInput"
                  type="date" 
                  name="currentPeriodEnd" 
                  value={selectedDate}
                  onChange={(e) => setSelectedDate(e.target.value)}
                  className={`w-full bg-slate-50 dark:bg-[#111111] border rounded-xl px-4 py-3 focus:outline-none focus:ring-1 transition-all ${
                    selectedDate 
                      ? "border-red-500 text-red-500 dark:border-red-500 dark:text-red-400 focus:border-red-500 focus:ring-red-500/50 font-bold" 
                      : "border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white focus:border-emerald-500 focus:ring-emerald-500/50"
                  }`} 
                />
              </div>

              <div className="space-y-4 pt-4 border-t border-slate-200 dark:border-[#333333]">
                <div className="flex items-center gap-2 mb-2">
                  <h4 className="text-sm font-bold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Custom Limitations</h4>
                  <span className="text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-500/20">0 = Unlimited</span>
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1.5">
                    <label className="text-xs font-semibold text-slate-500 dark:text-gray-400">Tables</label>
                    <input type="number" name="tableLimit" min="0" value={customLimits.tableLimit} onChange={(e) => setCustomLimits({...customLimits, tableLimit: parseInt(e.target.value) || 0})} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-emerald-500" />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-semibold text-slate-500 dark:text-gray-400">Staff / Users</label>
                    <input type="number" name="staffLimit" min="0" value={customLimits.staffLimit} onChange={(e) => setCustomLimits({...customLimits, staffLimit: parseInt(e.target.value) || 0})} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-emerald-500" />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-semibold text-slate-500 dark:text-gray-400">Dishes</label>
                    <input type="number" name="dishLimit" min="0" value={customLimits.dishLimit} onChange={(e) => setCustomLimits({...customLimits, dishLimit: parseInt(e.target.value) || 0})} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-emerald-500" />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-semibold text-slate-500 dark:text-gray-400">Rooms</label>
                    <input type="number" name="roomLimit" min="0" value={customLimits.roomLimit} onChange={(e) => setCustomLimits({...customLimits, roomLimit: parseInt(e.target.value) || 0})} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-emerald-500" />
                  </div>
                </div>
              </div>

              <div className="space-y-4 pt-4 border-t border-slate-200 dark:border-[#333333]">
                <div className="flex items-center gap-2 mb-2">
                  <h4 className="text-sm font-bold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Client Dashboard Side Menus</h4>
                  <span className="text-[10px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full border border-red-500/20">Checked = Visible to Client</span>
                </div>
                <div className="grid grid-cols-2 gap-3 bg-slate-50 dark:bg-[#111111]/40 border border-slate-200 dark:border-[#333333] p-4 rounded-xl">
                  {[
                    { id: "pos", label: "POS / Billing" },
                    { id: "menu", label: "Menu / Items" },
                    { id: "tables", label: "Tables / Areas" },
                    { id: "rooms", label: "Room / Reservation" },
                    { id: "kot", label: "KOT / Kitchen" },
                    { id: "reports", label: "Sales Reports" },
                    { id: "expenses", label: "Expenses" },
                    { id: "staff", label: "Staff / Users" },
                    { id: "inventory", label: "Inventory / Stock" },
                    { id: "customers", label: "Customers / Ledger" },
                    { id: "settings", label: "Settings" }
                  ].map(option => (
                    <label key={option.id} className="flex items-center gap-2.5 cursor-pointer py-0.5">
                      <input 
                        type="checkbox" 
                        name="allowedMenus" 
                        value={option.id} 
                        checked={selectedMenus.includes(option.id)}
                        onChange={() => toggleMenuOption(option.id)}
                        className="w-4 h-4 rounded border-[#444444] bg-slate-100 dark:bg-[#222222] text-red-500 focus:ring-red-500 focus:ring-1 focus:outline-none"
                      />
                      <span className="text-slate-700 dark:text-gray-300 text-xs font-semibold">{option.label}</span>
                    </label>
                  ))}
                </div>
              </div>

              <button type="submit" className="w-full mt-4 bg-emerald-500 hover:bg-emerald-400 text-[#111111] dark:text-white py-3 rounded-xl font-bold transition-all shadow-[0_4px_15px_rgba(16,185,129,0.3)]">
                Save Changes
              </button>
            </form>
          </div>
        </div>
      )}

      {/* EDIT RESTAURANT DETAILS MODAL */}
      {isRestModalOpen && editingRest && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 shadow-2xl w-full max-w-lg animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-lg font-bold text-[#111111] dark:text-white flex items-center gap-2">
                <Building size={20} className="text-emerald-500"/> Edit Restaurant Details
              </h3>
              <button onClick={() => setIsRestModalOpen(false)} className="text-slate-400 dark:text-gray-500 hover:text-[#111111] dark:text-white transition-colors">
                <XCircle size={24} />
              </button>
            </div>
            
            <form action={async (formData) => {
              await updateRestaurantDetails(formData);
              setIsRestModalOpen(false);
              setIsViewModalOpen(false);
            }} className="space-y-4">
              <input type="hidden" name="restaurantId" value={editingRest.id} />
              
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1.5 col-span-2">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Restaurant Name</label>
                  <input type="text" name="name" required defaultValue={editingRest.name} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Phone</label>
                  <input type="text" name="phone" required maxLength={10} placeholder="e.g. 98XXXXXXXX" defaultValue={editingRest.phone} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Email</label>
                  <input type="email" name="email" defaultValue={editingRest.email || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
                </div>

                <div className="space-y-1.5 col-span-2">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Tagline</label>
                  <input type="text" name="tagline" defaultValue={editingRest.tagline || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" placeholder="e.g. Taste the Best" />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Type</label>
                  <input type="text" name="type" defaultValue={editingRest.type} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" placeholder="e.g. Restaurant / Hotel" />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">City</label>
                  <input type="text" name="city" defaultValue={editingRest.city || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" placeholder="e.g. Kathmandu" />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Ward Number</label>
                  <input type="text" name="ward" defaultValue={editingRest.ward || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" placeholder="e.g. 3" />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">PAN / VAT Number</label>
                  <input type="text" name="panNumber" defaultValue={editingRest.panNumber || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" placeholder="e.g. 600123456" />
                </div>

                <div className="space-y-1.5 col-span-2">
                  <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Address / Location</label>
                  <input type="text" name="address" defaultValue={editingRest.address || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" placeholder="e.g. Thamel" />
                </div>

                <div className="space-y-3 col-span-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                  <label className="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider block">
                    Client Module & Feature Access (ON / OFF)
                  </label>
                  <div className="grid grid-cols-2 gap-3 bg-slate-50 dark:bg-[#111111] p-3 rounded-xl border border-slate-200 dark:border-[#333333]">
                    <label className="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                      <input type="checkbox" name="featureHotel" defaultChecked={editingRest.featureHotel !== false} className="w-4 h-4 accent-emerald-500 rounded" />
                      🏨 Hotel Management
                    </label>
                    <label className="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                      <input type="checkbox" name="featureInventory" defaultChecked={editingRest.featureInventory !== false} className="w-4 h-4 accent-emerald-500 rounded" />
                      📦 Inventory & Stock
                    </label>
                    <label className="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                      <input type="checkbox" name="featureReports" defaultChecked={editingRest.featureReports !== false} className="w-4 h-4 accent-emerald-500 rounded" />
                      📊 Reports & Analytics
                    </label>
                    <label className="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                      <input type="checkbox" name="featureDaybook" defaultChecked={editingRest.featureDaybook !== false} className="w-4 h-4 accent-emerald-500 rounded" />
                      📖 Finance & Daybook
                    </label>
                    <label className="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                      <input type="checkbox" name="featureEbilling" defaultChecked={editingRest.featureEbilling === true} className="w-4 h-4 accent-emerald-500 rounded" />
                      🇳🇵 Nepal E-Billing API
                    </label>
                    <label className="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                      <input type="checkbox" name="featureMultiUser" defaultChecked={editingRest.featureMultiUser !== false} className="w-4 h-4 accent-emerald-500 rounded" />
                      👥 Multi-User Staff
                    </label>
                  </div>
                </div>
              </div>

              <button type="submit" className="w-full mt-4 bg-emerald-500 hover:bg-emerald-400 text-[#111111] dark:text-white py-3 rounded-xl font-bold transition-all shadow-[0_4px_15px_rgba(16,185,129,0.3)]">
                Save Restaurant Details
              </button>
            </form>
          </div>
        </div>
      )}

      {/* EDIT USER DETAILS MODAL */}
      {isUserModalOpen && editingUser && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 shadow-2xl w-full max-w-md animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-lg font-bold text-[#111111] dark:text-white flex items-center gap-2">
                <User size={20} className="text-emerald-500"/> Edit User Details
              </h3>
              <button onClick={() => setIsUserModalOpen(false)} className="text-slate-400 dark:text-gray-500 hover:text-[#111111] dark:text-white transition-colors">
                <XCircle size={24} />
              </button>
            </div>
            
            <form action={async (formData) => {
              await updateUserDetails(formData);
              setIsUserModalOpen(false);
            }} className="space-y-4">
              <input type="hidden" name="userId" value={editingUser.id} />
              
              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Name</label>
                <input type="text" name="name" required defaultValue={editingUser.name || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Email</label>
                <input type="email" name="email" required defaultValue={editingUser.email || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Phone</label>
                <input type="text" name="phone" maxLength={10} placeholder="e.g. 98XXXXXXXX" defaultValue={editingUser.phone || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Assigned Location / City</label>
                <input type="text" name="assignedLocation" placeholder="e.g. Hetauda, Pokhara" defaultValue={editingUser.assignedLocation || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Assign Marketing Agent</label>
                <select name="referredById" defaultValue={editingUser.referredById || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                  <option value="">-- Unassigned --</option>
                  {marketingStaff.map((staff: any) => (
                    <option key={staff.id} value={staff.id}>
                      {staff.email} ({staff.role}{staff.location ? ` - ${staff.location}` : ''})
                    </option>
                  ))}
                </select>
              </div>

              <button type="submit" className="w-full mt-4 bg-emerald-500 hover:bg-emerald-400 text-[#111111] dark:text-white py-3 rounded-xl font-bold transition-all shadow-[0_4px_15px_rgba(16,185,129,0.3)] cursor-pointer">
                Save User Details
              </button>
            </form>
          </div>
        </div>
      )}

      {/* EDIT CLIENT DETAILS MODAL */}
      {isClientModalOpen && editingClient && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 shadow-2xl w-full max-w-md animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-lg font-bold text-[#111111] dark:text-white flex items-center gap-2">
                <Key size={20} className="text-emerald-500"/> Edit Client License Details
              </h3>
              <button onClick={() => setIsClientModalOpen(false)} className="text-slate-400 dark:text-gray-500 hover:text-[#111111] dark:text-white transition-colors">
                <XCircle size={24} />
              </button>
            </div>
            
            <form action={async (formData) => {
              await updateClientDetails(formData);
              setIsClientModalOpen(false);
            }} className="space-y-4">
              <input type="hidden" name="clientId" value={editingClient.id} />
              
              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Client / Business Name</label>
                <input type="text" name="restaurantName" required defaultValue={editingClient.restaurantName || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Contact Number</label>
                <input type="text" name="contactNumber" required defaultValue={editingClient.contactNumber || ''} className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50" />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-600 dark:text-gray-300">Location / City</label>
                {staffRole !== "SUPERADMIN" && staffRole !== "ADMIN" && staffRole !== "SUPPORT" && staffLocation && staffLocation.toLowerCase() !== "admin" ? (
                  <>
                    <input 
                      type="text" 
                      disabled 
                      value={staffLocation} 
                      className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-slate-500 dark:text-gray-400 rounded-xl px-4 py-2.5 cursor-not-allowed"
                    />
                    <input type="hidden" name="location" value={staffLocation} />
                  </>
                ) : (
                  <select 
                    name="location" 
                    defaultValue={editingClient.location || ''} 
                    className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 appearance-none cursor-pointer"
                  >
                    <option value="">Select Location (Optional)</option>
                    {NEPAL_CITIES.map(city => (
                      <option key={city} value={city}>{city}</option>
                    ))}
                  </select>
                )}
              </div>

              <button type="submit" className="w-full mt-4 bg-emerald-500 hover:bg-emerald-400 text-[#111111] dark:text-white py-3 rounded-xl font-bold transition-all shadow-[0_4px_15px_rgba(16,185,129,0.3)] cursor-pointer">
                Save Client Details
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
