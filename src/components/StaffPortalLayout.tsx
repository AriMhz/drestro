"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useState, useEffect } from "react";
import {
  LayoutDashboard, Users, MonitorSmartphone, Package,
  Tag, LogOut, HelpCircle, PanelBottom, ImageIcon, Menu, X,
  Inbox, ShoppingCart, Receipt, LifeBuoy
} from "lucide-react";
import ThemeToggle from "@/src/components/ThemeToggle";

const TAB_CONFIG: Record<string, { label: string; icon: any; path: string }> = {
  "inbox": { label: "Inbox", icon: Inbox, path: "/inbox" },
  "orders": { label: "Orders", icon: ShoppingCart, path: "/orders" },
  "clients": { label: "Clients & Licenses", icon: Users, path: "/clients" },
  "hardware": { label: "Hardware Store", icon: MonitorSmartphone, path: "/hardware" },
  "pricing": { label: "Pricing & Plans", icon: Tag, path: "/pricing" },
  "faq": { label: "FAQs", icon: HelpCircle, path: "/faq" },
  "client-logos": { label: "Client Logos", icon: ImageIcon, path: "/client-logos" },
  "footer": { label: "Footer Settings", icon: PanelBottom, path: "/footer" },
  "billing": { label: "Billing", icon: Receipt, path: "/billing" },
  "support": { label: "Support Tickets", icon: LifeBuoy, path: "/support" },
};

const PORTAL_COLORS: Record<string, { accent: string; shadow: string }> = {
  support: { accent: "bg-green-500", shadow: "shadow-green-500/20" },
  sales: { accent: "bg-blue-500", shadow: "shadow-blue-500/20" },
  marketing: { accent: "bg-orange-500", shadow: "shadow-orange-500/20" },
};

export default function StaffPortalLayout({
  children,
  portalName,
}: {
  children: React.ReactNode;
  portalName: "support" | "sales" | "marketing";
}) {
  const pathname = usePathname();
  const router = useRouter();
  const [permissions, setPermissions] = useState<string[]>([]);
  const [role, setRole] = useState("");
  const [email, setEmail] = useState("");
  const [loaded, setLoaded] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const colors = PORTAL_COLORS[portalName];
  const basePath = `/${portalName}`;

  useEffect(() => {
    fetch("/api/admin/session")
      .then(res => res.json())
      .then(data => {
        if (data.permissions) {
          try { setPermissions(JSON.parse(data.permissions)); } catch { setPermissions([]); }
        }
        setRole(data.role || "");
        setEmail(data.email || "");
        setLoaded(true);
      })
      .catch(() => setLoaded(true));
  }, []);

  const handleLogout = () => {
    if (window.location.hostname.endsWith("drestro.com")) {
      window.location.href = "https://drestro.com/api/admin/logout";
    } else {
      window.location.href = "http://localhost:3000/api/admin/logout";
    }
  };

  // Build nav items from permissions
  const navItems: Array<{ name: string; href: string; icon: any }> = [];
  const isSuper = role === "SUPERADMIN";

  if (isSuper || permissions.includes("dashboard")) {
    navItems.push({ name: "Dashboard", href: basePath, icon: LayoutDashboard });
  }

  if (isSuper) {
    Object.keys(TAB_CONFIG).forEach(key => {
      navItems.push({
        name: TAB_CONFIG[key].label,
        href: `${basePath}${TAB_CONFIG[key].path}`,
        icon: TAB_CONFIG[key].icon,
      });
    });
  } else {
    permissions
      .filter(p => TAB_CONFIG[p] && p !== "dashboard")
      .forEach(p => {
        navItems.push({
          name: TAB_CONFIG[p].label,
          href: `${basePath}${TAB_CONFIG[p].path}`,
          icon: TAB_CONFIG[p].icon,
        });
      });
  }

  const portalLabel = portalName.charAt(0).toUpperCase() + portalName.slice(1);

  return (
    <div className="min-h-screen bg-slate-50 dark:bg-[#111111] flex flex-col md:flex-row text-[#111111] dark:text-white pb-16 md:pb-0">
      <aside className="hidden md:flex w-64 bg-white dark:bg-[#1A1A1A] border-r border-slate-200 dark:border-[#333333] flex-col h-screen fixed left-0 top-0 z-20">
        <div className="p-6 border-b border-slate-200 dark:border-[#333333] flex items-center gap-3">
          <img src="/logos/icon.svg" alt={`DRestro ${portalLabel}`} className="w-10 h-10 object-contain drop-shadow-sm" />
          <div className="flex items-center gap-2">
            <span className="font-bold text-xl tracking-tight text-[#111111] dark:text-white">DRestro</span>
            <span className="text-[10px] font-black uppercase bg-[#E53935] text-white px-2 py-0.5 rounded tracking-wider">{portalLabel}</span>
          </div>
        </div>

        {loaded && (
          <div className="px-4 pt-4">
            <div className="bg-slate-100 dark:bg-[#222222] rounded-xl px-4 py-2.5 text-xs text-slate-500 dark:text-gray-400 truncate">
              {email}
            </div>
          </div>
        )}

        <nav className="flex-1 py-4 px-4 space-y-2 overflow-y-auto">
          {navItems.map((item) => {
            const isActive = pathname === item.href || (pathname.startsWith(item.href) && item.href !== basePath);
            return (
              <Link
                key={item.name}
                href={item.href}
                className={`flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${
                  isActive
                    ? `${colors.accent} text-[#111111] dark:text-white ${colors.shadow} shadow-lg`
                    : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white hover:bg-slate-100 dark:bg-[#222222]"
                }`}
              >
                <item.icon size={20} />
                <span className="font-medium text-sm">{item.name}</span>
              </Link>
            );
          })}
        </nav>

        <div className="p-4 border-t border-slate-200 dark:border-[#333333]">
          <div className="flex items-center justify-between px-4 py-2 mb-2 bg-slate-100 dark:bg-[#222222] rounded-xl">
            <span className="text-sm font-medium text-slate-500 dark:text-gray-400">Appearance</span>
            <ThemeToggle />
          </div>
          <button
            onClick={handleLogout}
            className="flex items-center gap-3 px-4 py-3 w-full rounded-xl text-slate-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-500/10 transition-all text-left"
          >
            <LogOut size={20} />
            <span className="font-medium text-sm">Logout</span>
          </button>
        </div>
      </aside>

      <main className="flex-1 flex flex-col min-h-screen overflow-x-hidden md:pl-64">
        {/* Mobile Top Header Bar with Logout */}
        <div className="md:hidden flex items-center justify-between p-4 bg-white dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#333333] sticky top-0 z-30 shadow-sm">
          <div className="flex items-center gap-2">
            <img src="/logos/icon.svg" alt={`DRestro ${portalLabel}`} className="w-7 h-7 object-contain" />
            <span className="font-bold text-base tracking-tight text-[#111111] dark:text-white">DRestro</span>
            <span className="text-[9px] font-black uppercase bg-[#E53935] text-white px-1.5 py-0.5 rounded tracking-wider">{portalLabel}</span>
          </div>

          <div className="flex items-center gap-2">
            {loaded && (
              <span className="text-[10px] text-slate-500 dark:text-gray-400 max-w-[120px] truncate hidden sm:inline-block">
                {email}
              </span>
            )}
            <button
              onClick={handleLogout}
              className="flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white border border-red-500/20 px-3 py-1.5 rounded-xl text-xs font-black transition-all"
            >
              <LogOut size={14} />
              Logout
            </button>
          </div>
        </div>

        <div className="flex-1 p-4 md:p-10 max-w-7xl mx-auto w-full">
          {children}
        </div>
      </main>

      {/* Mobile Bottom Nav */}
      <nav className="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-[#1A1A1A]/95 backdrop-blur-md border-t border-slate-200 dark:border-[#333333] flex items-center justify-around h-16 px-2 z-40 shadow-[0_-4px_20px_rgba(0,0,0,0.3)]">
        {navItems.slice(0, 3).map((item) => {
          const isActive = pathname === item.href || (pathname.startsWith(item.href) && item.href !== basePath);
          return (
            <Link key={item.name} href={item.href}
              className={`flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors ${isActive ? "text-[#E53935]" : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"}`}>
              <item.icon size={19} className={isActive ? "fill-[#E53935]/20" : ""} />
              <span className="text-[9px] font-bold tracking-tight text-center truncate max-w-[65px]">{item.name}</span>
            </Link>
          );
        })}
        
        {/* "More" Menu Trigger Button */}
        <button
          onClick={() => setIsMobileMenuOpen(true)}
          className={`flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors ${
            isMobileMenuOpen ? "text-[#E53935]" : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"
          }`}
        >
          <Menu size={19} />
          <span className="text-[9px] font-bold tracking-tight text-center">
            More
          </span>
        </button>

        {/* 1-Tap Direct Mobile Sign Out Button */}
        <button
          onClick={handleLogout}
          className="flex flex-col items-center justify-center w-full h-full space-y-1 text-red-500 hover:text-red-600 transition-colors"
          title="Sign Out"
        >
          <LogOut size={19} />
          <span className="text-[9px] font-bold tracking-tight text-center">
            Logout
          </span>
        </button>
      </nav>

      {/* Premium Slide-Up Bottom Drawer Sheet Overlay */}
      {isMobileMenuOpen && (
        <div className="md:hidden fixed inset-0 z-50 flex items-end justify-center bg-black/70 backdrop-blur-sm transition-all duration-300">
          <div className="absolute inset-0" onClick={() => setIsMobileMenuOpen(false)}></div>
          
          <div className="relative w-full max-h-[85vh] bg-white dark:bg-[#1A1A1A] border-t border-slate-200 dark:border-[#333333] rounded-t-3xl p-6 pb-10 z-50 flex flex-col space-y-6 animate-in slide-in-from-bottom duration-300 shadow-[0_-10px_35px_rgba(0,0,0,0.5)] overflow-y-auto">
            
            <div className="flex justify-between items-center border-b border-slate-200 dark:border-[#333333]/50 pb-4">
              <div className="flex items-center gap-2">
                <span className="text-[#E53935] font-black text-sm">●</span>
                <h3 className="text-base font-black text-[#111111] dark:text-white uppercase tracking-wider">All {portalLabel} Menus</h3>
              </div>
              <button 
                onClick={() => setIsMobileMenuOpen(false)}
                className="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] flex items-center justify-center text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white transition-colors"
              >
                <X size={18} />
              </button>
            </div>

            <div className="grid grid-cols-3 gap-3">
              {navItems.slice(4).map((item) => {
                const isActive = pathname === item.href || (pathname.startsWith(item.href) && item.href !== basePath);
                return (
                  <Link
                    key={item.name}
                    href={item.href}
                    onClick={() => setIsMobileMenuOpen(false)}
                    className={`flex flex-col items-center justify-center p-3 rounded-2xl border transition-all aspect-square ${
                      isActive 
                        ? "bg-[#E53935]/10 border-[#E53935]/50 text-[#111111] dark:text-white shadow-sm" 
                        : "bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#333333] text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"
                    }`}
                  >
                    <item.icon size={22} className={`mb-2 ${isActive ? "text-[#E53935]" : "text-slate-500 dark:text-gray-400"}`} />
                    <span className="text-[10px] font-black tracking-tight text-center leading-tight max-w-[80px]">
                      {item.name.replace(" Management", "").replace(" Settings", "").replace(" Store", "")}
                    </span>
                  </Link>
                );
              })}
            </div>

            <hr className="border-slate-200 dark:border-[#333333]/50" />

            <div className="flex flex-col gap-3">
              <div className="flex items-center justify-between px-4 py-3 bg-slate-100 dark:bg-[#222222] rounded-2xl">
                <span className="text-sm font-black tracking-tight text-slate-500 dark:text-gray-400">Appearance</span>
                <ThemeToggle />
              </div>
              <button
                onClick={() => {
                  setIsMobileMenuOpen(false);
                  handleLogout();
                }}
                className="w-full bg-[#E53935]/10 hover:bg-[#E53935] border border-[#E53935]/30 hover:border-red-600 text-red-500 hover:text-[#111111] dark:text-white py-4 rounded-2xl text-sm font-black transition-all flex items-center justify-center gap-2 shadow-sm"
              >
                <LogOut size={18} />
                Sign Out
              </button>
            </div>
            
          </div>
        </div>
      )}
    </div>
  );
}
