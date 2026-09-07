"use client";

import { useState, useEffect } from "react";
import { Plus, Trash2, Shield, UserCog, Mail, Key, Percent, Link as LinkIcon, Loader2, Edit2, X, Check, Search } from "lucide-react";
import toast from "react-hot-toast";

const ALL_TABS = [
  { id: "dashboard", label: "Dashboard Overview", path: "" },
  { id: "inbox", label: "Inbox", path: "/inbox" },
  { id: "orders", label: "Orders", path: "/orders" },
  { id: "clients", label: "Clients & Licenses", path: "/clients" },
  { id: "hardware", label: "Hardware Store", path: "/hardware" },
  { id: "combos", label: "Combo Packages", path: "/combos" },
  { id: "pricing", label: "Pricing & Plans", path: "/pricing" },
  { id: "faq", label: "FAQs", path: "/faq" },
  { id: "client-logos", label: "Client Logos", path: "/client-logos" },
  { id: "footer", label: "Footer Settings", path: "/footer" },
  { id: "billing", label: "Billing", path: "/billing" },
  { id: "support", label: "Support Tickets", path: "/support" },
];

const NEPAL_CITIES = [
  "Admin",
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

export default function AdminStaffPage() {
  const [staff, setStaff] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingStaff, setEditingStaff] = useState<any>(null);
  const [submitting, setSubmitting] = useState(false);
  const [currentUser, setCurrentUser] = useState<any>(null);
  const [searchQuery, setSearchQuery] = useState("");

  // Form State
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [role, setRole] = useState("SALES");
  const [permissions, setPermissions] = useState<string[]>([]);
  const [referralCode, setReferralCode] = useState("");
  const [commissionRate, setCommissionRate] = useState("0");
  const [location, setLocation] = useState("");
  const [showPassword, setShowPassword] = useState(false);

  useEffect(() => {
    fetchStaff();
    fetchSession();
  }, []);

  const fetchSession = async () => {
    try {
      const res = await fetch("/api/admin/session");
      if (res.ok) setCurrentUser(await res.json());
    } catch (err) {
      console.error("Failed to fetch session", err);
    }
  };

  const fetchStaff = async () => {
    try {
      const res = await fetch("/api/admin/staff");
      if (res.ok) setStaff(await res.json());
    } catch (err) {
      toast.error("Failed to fetch staff");
    } finally {
      setLoading(false);
    }
  };

  const resetForm = () => {
    setEmail(""); setPassword(""); setRole("SALES");
    setPermissions(["dashboard", "inbox", "orders", "clients", "billing"]);
    setReferralCode(""); setCommissionRate("0");
    setLocation(""); setEditingStaff(null); setShowPassword(false);
  };

  const openCreate = () => { resetForm(); setIsModalOpen(true); };

  const openEdit = (s: any) => {
    setEditingStaff(s);
    setEmail(s.email);
    setPassword(""); // leave blank = don't change
    setRole(s.role);
    try { setPermissions(JSON.parse(s.permissions || "[]")); } catch { setPermissions([]); }
    setReferralCode(s.referralCode || "");
    setCommissionRate(String(s.commissionRate || 0));
    setLocation(s.location || "");
    setShowPassword(false);
    setIsModalOpen(true);
  };

  const togglePermission = (tabId: string) => {
    setPermissions(prev => prev.includes(tabId) ? prev.filter(p => p !== tabId) : [...prev, tabId]);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const isEdit = !!editingStaff;
      const body: any = {
        email, role, permissions, location,
        referralCode: role === "MARKETING" ? referralCode : null,
        commissionRate: role === "MARKETING" ? commissionRate : 0,
      };
      if (isEdit) body.id = editingStaff.id;
      if (!isEdit || password) body.password = password; // only send if set

      const res = await fetch("/api/admin/staff", {
        method: isEdit ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      });
      const data = await res.json();

      if (res.ok) {
        toast.success(isEdit ? "Staff updated!" : "Staff created!");
        setIsModalOpen(false);
        resetForm();
        fetchStaff();
      } else {
        toast.error(data.error || "Failed");
      }
    } catch (err) {
      toast.error("An error occurred");
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this staff member?")) return;
    try {
      const res = await fetch(`/api/admin/staff?id=${id}`, { method: "DELETE" });
      const data = await res.json();
      if (res.ok) { toast.success("Staff deleted"); fetchStaff(); }
      else toast.error(data.error || "Failed to delete");
    } catch (err) { toast.error("An error occurred"); }
  };

  const parsePermissions = (p: string) => {
    try { return JSON.parse(p || "[]"); } catch { return []; }
  };

  const roleColor = (r: string) => {
    if (r === "SUPERADMIN") return { bg: "bg-purple-500/10", text: "text-purple-400", badge: "bg-purple-500/20 text-purple-300" };
    if (r === "SALES") return { bg: "bg-blue-500/10", text: "text-blue-400", badge: "bg-blue-500/20 text-blue-300" };
    if (r === "SUPPORT") return { bg: "bg-green-500/10", text: "text-green-400", badge: "bg-green-500/20 text-green-300" };
    return { bg: "bg-orange-500/10", text: "text-orange-400", badge: "bg-orange-500/20 text-orange-300" };
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-[#1A1A1A] p-6 rounded-2xl border border-slate-200 dark:border-[#333333]">
        <div>
          <h1 className="text-2xl font-bold text-[#111111] dark:text-white mb-1">Staff Management</h1>
          <p className="text-slate-500 dark:text-gray-400 text-sm">Manage Superadmins, Sales, Support, and Marketing teams.</p>
        </div>
        <button onClick={openCreate}
          className="bg-[#E53935] hover:bg-red-600 text-[#111111] dark:text-white px-5 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2 shadow-lg shadow-red-500/20">
          <Plus size={18} /> <span>Add Staff</span>
        </button>
      </div>

      {/* Search Bar */}
      <div className="relative max-w-md w-full">
        <span className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <Search className="h-4 w-4 text-slate-400 dark:text-gray-500" />
        </span>
        <input
          type="text"
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          placeholder="Search staff by email, role, or location..."
          className="w-full pl-10 pr-4 py-2.5 text-sm bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-xl text-slate-800 dark:text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/50 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-sm"
        />
      </div>

      {/* Staff List */}
      {loading ? (
        <div className="flex items-center justify-center p-12"><Loader2 className="w-8 h-8 text-[#E53935] animate-spin" /></div>
      ) : (
        <div className="grid gap-4">
          {(() => {
            const filteredStaff = staff.filter((s: any) => 
              s.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
              s.role.toLowerCase().includes(searchQuery.toLowerCase()) ||
              (s.location && s.location.toLowerCase().includes(searchQuery.toLowerCase()))
            );
            
            if (filteredStaff.length === 0) {
              return (
                <div className="text-center p-12 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl text-slate-400 dark:text-gray-500">
                  No staff members found matching your search.
                </div>
              );
            }

            return filteredStaff.map((s) => {
              const rc = roleColor(s.role);
              const perms = parsePermissions(s.permissions);
              const permLabels = ALL_TABS.filter(t => perms.includes(t.id)).map(t => t.label);
              return (
                <div key={s.id} className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-5">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                      <div className={`w-12 h-12 rounded-full flex items-center justify-center ${rc.bg} ${rc.text}`}>
                        {s.role === "SUPERADMIN" ? <Shield size={24} /> : <UserCog size={24} />}
                      </div>
                      <div>
                        <h3 className="text-[#111111] dark:text-white font-bold text-lg">{s.email}</h3>
                        <div className="flex items-center gap-2 mt-1">
                          <span className={`text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full ${rc.badge}`}>{s.role}</span>
                          {s.role === "MARKETING" && s.referralCode && (
                            <span className="text-xs text-slate-500 dark:text-gray-400 flex items-center gap-1">
                              <LinkIcon size={12} /> {s.referralCode} ({s.commissionRate}%)
                            </span>
                          )}
                          {s.location && (
                            <span className="text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 dark:bg-[#222] dark:text-slate-400">
                              {s.location}
                            </span>
                          )}
                        </div>
                        {s.lastLoginAt && (
                          <div className="text-[11px] text-slate-400 dark:text-gray-500 mt-2 flex items-center gap-1 font-normal">
                            <span>🌐 Login: <strong className="text-slate-600 dark:text-gray-300">{s.lastLoginIp}</strong> ({s.lastLoginDevice || 'Unknown'}) at {new Date(s.lastLoginAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })} {new Date(s.lastLoginAt).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })}</span>
                          </div>
                        )}
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      {/* Allow editing any staff member */}
                      <button onClick={() => openEdit(s)}
                        className="p-2 text-slate-400 dark:text-gray-500 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition-colors" title="Edit Staff">
                        <Edit2 size={18} />
                      </button>
                      
                      {/* Allow deleting other staff members except the currently logged-in account */}
                      {currentUser && currentUser.email !== s.email && currentUser.staffId !== s.id ? (
                        <button onClick={() => handleDelete(s.id)}
                          className="p-2 text-slate-400 dark:text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors" title="Delete Staff">
                          <Trash2 size={18} />
                        </button>
                      ) : (
                        currentUser && (s.id === currentUser.staffId || s.email === currentUser.email) ? (
                          <span className="text-xs text-slate-400 dark:text-gray-500 italic px-2">Current User</span>
                        ) : null
                      )}
                    </div>
                  </div>
                  {/* Show granted permissions */}
                  {s.role !== "SUPERADMIN" && permLabels.length > 0 && (
                    <div className="mt-4 pt-4 border-t border-slate-200 dark:border-[#333333] flex flex-wrap gap-2">
                      {permLabels.map(label => (
                        <span key={label} className="text-xs bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-300 px-3 py-1 rounded-full border border-[#444444] flex items-center gap-1">
                          <Check size={12} className="text-green-400" /> {label}
                        </span>
                      ))}
                    </div>
                  )}
                  {s.role !== "SUPERADMIN" && permLabels.length === 0 && (
                    <div className="mt-4 pt-4 border-t border-slate-200 dark:border-[#333333]">
                      <span className="text-xs text-slate-400 dark:text-gray-500 italic">No permissions granted. Click Edit to assign dashboard tabs.</span>
                    </div>
                  )}
                </div>
              );
            });
          })()}
        </div>
      )}

      {/* Create / Edit Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div className="p-6 border-b border-slate-200 dark:border-[#333333] flex justify-between items-center sticky top-0 bg-white dark:bg-[#1A1A1A] z-10 rounded-t-2xl">
              <h2 className="text-xl font-bold text-[#111111] dark:text-white">{editingStaff ? "Edit Staff" : "Create New Staff"}</h2>
              <button onClick={() => { setIsModalOpen(false); resetForm(); }} className="text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"><X size={20} /></button>
            </div>
            
            <form onSubmit={handleSubmit} className="p-6 space-y-5">
              <div className="space-y-2">
                <label className="text-sm text-slate-500 dark:text-gray-400">Email Address</label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-gray-500" size={18} />
                  <input type="email" required value={email} onChange={(e) => setEmail(e.target.value)}
                    className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] rounded-xl pl-10 pr-4 py-2.5 text-[#111111] dark:text-white focus:ring-1 focus:ring-[#E53935] focus:outline-none" />
                </div>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between items-center">
                  <label className="text-sm text-slate-500 dark:text-gray-400">{editingStaff ? "New Password (leave blank to keep current)" : "Password"}</label>
                  <button
                    type="button"
                    onClick={() => {
                      const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
                      let genPassword = "";
                      for (let i = 0; i < 12; i++) {
                        genPassword += chars.charAt(Math.floor(Math.random() * chars.length));
                      }
                      setPassword(genPassword);
                      setShowPassword(true);
                    }}
                    className="text-xs text-[#E53935] hover:text-red-600 font-bold hover:underline focus:outline-none"
                  >
                    Generate Password
                  </button>
                </div>
                <div className="relative">
                  <Key className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-gray-500" size={18} />
                  <input type={showPassword ? "text" : "password"} required={!editingStaff} value={password} onChange={(e) => setPassword(e.target.value)}
                    placeholder={editingStaff ? "Leave blank to keep current" : ""}
                    className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] rounded-xl pl-10 pr-20 py-2.5 text-[#111111] dark:text-white focus:ring-1 focus:ring-[#E53935] focus:outline-none" />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 hover:text-slate-800 dark:text-gray-400 dark:hover:text-white font-semibold"
                  >
                    {showPassword ? "Hide" : "Show"}
                  </button>
                </div>
              </div>

              <div className="space-y-2">
                <label className="text-sm text-slate-500 dark:text-gray-400">Role</label>
                <select 
                  value={role} 
                  onChange={(e) => {
                    const newRole = e.target.value;
                    setRole(newRole);
                    // Automatically pre-select dashboard permissions checklist based on selected role
                    if (newRole === "SUPERADMIN" || newRole === "ADMIN") {
                      setPermissions(ALL_TABS.map(t => t.id));
                    } else if (newRole === "SALES") {
                      setPermissions(["dashboard", "inbox", "orders", "clients", "billing"]);
                    } else if (newRole === "SUPPORT") {
                      setPermissions(["dashboard", "inbox", "clients", "hardware", "faq", "support"]);
                    } else if (newRole === "MARKETING") {
                      setPermissions(["dashboard", "clients", "client-logos"]);
                    }
                  }}
                  className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] rounded-xl px-4 py-2.5 text-[#111111] dark:text-white focus:ring-1 focus:ring-[#E53935] focus:outline-none cursor-pointer"
                >
                  <option value="SUPERADMIN">Superadmin</option>
                  <option value="ADMIN">Admin</option>
                  <option value="SALES">Sales</option>
                  <option value="SUPPORT">Support</option>
                  <option value="MARKETING">Marketing (Sub-Dealer)</option>
                </select>
              </div>

              {role !== "SUPERADMIN" && role !== "ADMIN" && (
                <div className="space-y-3 pt-2">
                  <label className="text-sm text-slate-500 dark:text-gray-400 font-medium">Dashboard Permissions</label>
                  <p className="text-xs text-slate-400 dark:text-gray-500">Toggle which admin pages this staff member can access in their portal.</p>
                  <div className="space-y-2 bg-slate-100 dark:bg-[#222222] p-4 rounded-xl border border-slate-200 dark:border-[#333333]">
                    {ALL_TABS.map(tab => (
                      <label key={tab.id} className="flex items-center gap-3 cursor-pointer py-1">
                        <input type="checkbox" checked={permissions.includes(tab.id)} onChange={() => togglePermission(tab.id)}
                          className="w-4 h-4 rounded border-[#444444] bg-slate-50 dark:bg-[#111111] text-[#E53935] focus:ring-[#E53935]" />
                        <span className="text-slate-600 dark:text-gray-300 text-sm">{tab.label}</span>
                      </label>
                    ))}
                  </div>
                </div>
              )}

              {role === "MARKETING" && (
                <div className="grid grid-cols-2 gap-4 pt-2">
                  <div className="space-y-2">
                    <label className="text-sm text-slate-500 dark:text-gray-400">Referral Code</label>
                    <div className="relative">
                      <LinkIcon className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-gray-500" size={18} />
                      <input type="text" required value={referralCode} onChange={(e) => setReferralCode(e.target.value.toUpperCase())}
                        placeholder="E.g. RAM20"
                        className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] rounded-xl pl-10 pr-4 py-2.5 text-[#111111] dark:text-white focus:ring-1 focus:ring-[#E53935] uppercase focus:outline-none" />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm text-slate-500 dark:text-gray-400">Commission Rate (%)</label>
                    <div className="relative">
                      <Percent className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-gray-500" size={18} />
                      <input type="number" required value={commissionRate} onChange={(e) => setCommissionRate(e.target.value)}
                        className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] rounded-xl pl-10 pr-4 py-2.5 text-[#111111] dark:text-white focus:ring-1 focus:ring-[#E53935] focus:outline-none" />
                    </div>
                  </div>
                </div>
              )}

              {role !== "SUPERADMIN" && role !== "ADMIN" && (
                <div className="space-y-2 pt-2">
                  <label className="text-sm text-slate-500 dark:text-gray-400">Dealer Location</label>
                  <select value={location} onChange={(e) => setLocation(e.target.value)}
                    className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] rounded-xl px-4 py-2.5 text-[#111111] dark:text-white focus:ring-1 focus:ring-[#E53935] focus:outline-none cursor-pointer">
                    <option value="">Select Location (Optional)</option>
                    {NEPAL_CITIES.map(city => (
                      <option key={city} value={city}>{city}</option>
                    ))}
                    {!NEPAL_CITIES.includes(location) && location !== "" && (
                      <option value={location}>{location}</option>
                    )}
                  </select>
                </div>
              )}

              <div className="pt-4 flex gap-3">
                <button type="button" onClick={() => { setIsModalOpen(false); resetForm(); }}
                  className="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-[#333333] text-slate-600 dark:text-gray-300 hover:bg-slate-200 dark:bg-[#333333] transition-colors">Cancel</button>
                <button type="submit" disabled={submitting}
                  className="flex-1 px-4 py-2.5 rounded-xl bg-[#E53935] text-white font-medium hover:bg-red-600 transition-colors disabled:opacity-70 flex justify-center items-center">
                  {submitting ? <Loader2 className="w-5 h-5 animate-spin" /> : editingStaff ? "Save Changes" : "Create Staff"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
