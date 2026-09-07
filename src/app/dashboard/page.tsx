import { auth } from "../../../auth";
import { prisma } from "../../lib/prisma";
import { redirect } from "next/navigation";
import Link from "next/link";
import LicenseKey from "@/src/components/LicenseKey";
import { Building2, MapPin, Phone, ArrowRight, Plus, LogOut, CheckCircle2, AlertTriangle, Receipt, HelpCircle, Mail } from "lucide-react";

export const dynamic = "force-dynamic";

export default async function DashboardPage() {
  const session = await auth();
  
  if (!session?.user?.id) {
    redirect("/login");
  }

  // Fetch all restaurants owned by the current user
  const outletId = (session.user as any).outletId;
  const restaurants = await prisma.restaurant.findMany({
    where: { 
      userId: session.user.id,
      ...(outletId ? { id: outletId } : {})
    },
    include: { subscriptions: true }
  });

  const dbUser = await prisma.user.findUnique({
    where: { id: session.user.id }
  });

  // Find the highest/best subscription of the user
  const getPlanRank = (planId: string) => {
    const p = (planId || "").toLowerCase();
    if (p.includes("platinum") || p.includes("plat")) return 4;
    if (p.includes("premium")) return 3;
    if (p.includes("basic")) return 2;
    if (p.includes("trial")) return 1;
    return 0;
  };

  const highestSub = restaurants
    .flatMap(r => r.subscriptions)
    .sort((a, b) => getPlanRank(b.planId || "") - getPlanRank(a.planId || ""))[0];

  const isPlatinum = highestSub?.planId && (highestSub.planId.toLowerCase().includes("platinum") || highestSub.planId.toLowerCase().includes("plat"));

  const email = session.user.email || "";
  const name = session.user.name || "";

  async function getSsoUrl(restaurant: any) {
    const sub = highestSub || restaurant.subscriptions[0];
    const isPaidActive = sub?.currentPeriodEnd && new Date(sub.currentPeriodEnd) > new Date() && !sub.planId.includes("trial") && sub.planId !== "free";
    const planId = isPaidActive ? sub.planId : "free";
    const ssoExpiresAt = isPaidActive && sub?.currentPeriodEnd ? sub.currentPeriodEnd.toISOString() : "";
    
    const limits = JSON.stringify({
      tables: sub?.tableLimit || 0,
      users: sub?.staffLimit || 0,
      items: sub?.dishLimit || 0,
      rooms: sub?.roomLimit || 0,
      allowedMenus: sub?.allowedMenus || (
        (restaurant.type?.toLowerCase().includes("hotel") || restaurant.type?.toLowerCase().includes("resort"))
          ? "pos,menu,tables,rooms,kot,reports,expenses,staff,inventory,customers,settings"
          : "pos,menu,tables,kot,reports,expenses,staff,inventory,customers,settings"
      )
    });
    
    const licenseKey = restaurant.offlineLicenseKey || "";
    const restName = restaurant.name || "";
    const restAddress = restaurant.address || "";
    const restPhone = restaurant.phone || dbUser?.phone || "";
    const restEmail = restaurant.email || email;
    let restCity = restaurant.city || "";
    if (!restCity && restAddress && restAddress.includes(",")) {
      const parts = restAddress.split(",").map((s: string) => s.trim());
      if (parts.length >= 2) restCity = parts[parts.length - 1];
    }

    const payload = `${email}|${planId}`;
    
    const encoder = new TextEncoder();
    const keyData = encoder.encode('DrestroPOS_Secure_Key_2026_X9P2');
    const cryptoKey = await globalThis.crypto.subtle.importKey(
      'raw',
      keyData,
      { name: 'HMAC', hash: 'SHA-256' },
      false,
      ['sign']
    );
    const signature = await globalThis.crypto.subtle.sign('HMAC', cryptoKey, encoder.encode(payload));
    const token = Array.from(new Uint8Array(signature))
      .map(b => b.toString(16).padStart(2, '0'))
      .join('');
    
    return `${process.env.NODE_ENV === "production" ? "https://portal.drestro.com" : "http://localhost:8080"}/sso/login?slug=${encodeURIComponent(restaurant.id)}&email=${encodeURIComponent(email)}&name=${encodeURIComponent(name)}&plan=${encodeURIComponent(planId)}&expires_at=${encodeURIComponent(ssoExpiresAt)}&limits=${encodeURIComponent(limits)}&license_key=${encodeURIComponent(licenseKey)}&restaurant_name=${encodeURIComponent(restName)}&restaurant_address=${encodeURIComponent(restAddress)}&restaurant_phone=${encodeURIComponent(restPhone)}&restaurant_pan=${encodeURIComponent(restaurant.panNumber || "")}&restaurant_tagline=${encodeURIComponent(restaurant.tagline || "")}&restaurant_ward=${encodeURIComponent(restaurant.ward || "")}&restaurant_city=${encodeURIComponent(restCity)}&restaurant_email=${encodeURIComponent(restEmail)}&restaurant_currency=${encodeURIComponent(restaurant.currency || "Rs.")}&restaurant_tax=${encodeURIComponent(String(restaurant.taxPercent || 0))}&restaurant_service_charge=${encodeURIComponent(String(restaurant.serviceChargePercent || 0))}&restaurant_calendar=${encodeURIComponent(restaurant.dateCalendarType || "AD")}&token=${token}`;
  }

  // Build mapped restaurants list with SSO links
  const branchList = await Promise.all(
    restaurants.map(async (rest) => {
      const ssoUrl = await getSsoUrl(rest);
      const sub = rest.subscriptions[0];
      return {
        ...rest,
        ssoUrl,
        planId: sub?.planId || "Free",
        status: sub?.status || "trialing",
        expiresAt: sub?.currentPeriodEnd || sub?.trialEndsAt || null
      };
    })
  );

  // If logged in directly as an Outlet, bypass dashboard and launch branch immediately
  if (outletId && branchList.length === 1) {
    if (branchList[0].status === "active" || branchList[0].status === "trialing") {
      redirect(branchList[0].ssoUrl);
    }
  }

  const allSuspended = branchList.length > 0 && branchList.every(b => b.status === "canceled" || b.status === "suspended" || b.status === "past_due");

  return (
    <div className="min-h-screen bg-[#F8F9FA] dark:bg-[#0a0a0a] text-slate-800 dark:text-slate-200">
      
      {/* Top Header */}
      <header className="border-b border-slate-200 dark:border-neutral-900 bg-white/70 dark:bg-[#111]/70 backdrop-blur-md sticky top-0 z-30">
        <div className="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <img src="/logos/logo.svg" alt="DRestro" className="h-8 dark:hidden" />
            <img src="/logos/logo-light.svg" alt="DRestro" className="h-8 hidden dark:block" />
            <span className="text-[10px] font-black uppercase bg-[#E53935] text-white px-2 py-0.5 rounded tracking-wider">Portal</span>
          </div>
          <div className="flex items-center gap-4">
            <div className="hidden md:flex items-center gap-4 mr-2">
              <Link href="/dashboard/billing" className="text-xs font-bold text-slate-600 dark:text-neutral-350 hover:text-[#E53935] transition-colors flex items-center gap-1">
                <Receipt size={14} /> <span>Transactions</span>
              </Link>
              <Link href="/faq" className="text-xs font-bold text-slate-600 dark:text-neutral-350 hover:text-[#E53935] transition-colors flex items-center gap-1">
                <HelpCircle size={14} /> <span>Support</span>
              </Link>
              <Link href="/contact" className="text-xs font-bold text-slate-600 dark:text-neutral-350 hover:text-[#E53935] transition-colors flex items-center gap-1">
                <Mail size={14} /> <span>Contact Us</span>
              </Link>
              <div className="w-[1px] h-4 bg-slate-200 dark:bg-neutral-800"></div>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-full bg-slate-100 dark:bg-neutral-800 border border-slate-200 dark:border-neutral-700 flex items-center justify-center font-bold text-slate-700 dark:text-slate-300">
                {name.charAt(0).toUpperCase()}
              </div>
              <span className="text-sm font-bold hidden sm:inline">{name}</span>
            </div>
            <Link href="/api/auth/signout" className="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold transition-all">
              <LogOut size={14} /> <span>Logout</span>
            </Link>
          </div>
        </div>
      </header>

      {/* Main Dashboard Section */}
      <main className="max-w-6xl mx-auto px-6 py-12">
        <div className="mb-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 className="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
              Restaurant & Branch Dashboard
            </h1>
            <p className="text-slate-500 dark:text-neutral-400 mt-2 text-sm">
              Launch, configure, and manage all your restaurant branches under one centralized account.
            </p>
          </div>
          {dbUser?.customerCode && (
            <div className="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-2xl px-5 py-3 flex flex-col items-start md:items-end justify-center shadow-sm">
              <span className="text-[10px] uppercase font-bold text-slate-400 dark:text-neutral-500 tracking-wider">Customer Unique ID</span>
              <div className="text-lg font-extrabold text-[#E53935] tracking-widest mt-0.5">{dbUser.customerCode}</div>
            </div>
          )}
        </div>
        {/* Quick Actions Shortcuts */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
          <Link href="/dashboard/billing" className="bg-white dark:bg-[#111] border border-slate-200 dark:border-neutral-850 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div className="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/20 text-[#E53935] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
              <Receipt size={22} />
            </div>
            <div>
              <h4 className="font-bold text-slate-900 dark:text-white text-sm">Transaction Logs</h4>
              <p className="text-xs text-slate-500 dark:text-neutral-400 mt-0.5">Manage licenses & invoices</p>
            </div>
          </Link>

          <Link href="/faq" className="bg-white dark:bg-[#111] border border-slate-200 dark:border-neutral-850 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div className="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/20 text-blue-500 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
              <HelpCircle size={22} />
            </div>
            <div>
              <h4 className="font-bold text-slate-900 dark:text-white text-sm">Help & Support</h4>
              <p className="text-xs text-slate-500 dark:text-neutral-400 mt-0.5">Frequently asked questions</p>
            </div>
          </Link>

          <Link href="/contact" className="bg-white dark:bg-[#111] border border-slate-200 dark:border-neutral-850 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
            <div className="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/20 text-purple-500 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
              <Mail size={22} />
            </div>
            <div>
              <h4 className="font-bold text-slate-900 dark:text-white text-sm">Contact Us</h4>
              <p className="text-xs text-slate-500 dark:text-neutral-400 mt-0.5">Reach out to our local team</p>
            </div>
          </Link>
        </div>

        {branchList.length === 0 ? (
          <div className="bg-white dark:bg-[#111] border border-slate-200 dark:border-neutral-850 rounded-2xl p-10 text-center shadow-sm flex flex-col items-center justify-center space-y-6">
            <div className="w-16 h-16 bg-red-50 dark:bg-red-950/20 text-[#E53935] rounded-full flex items-center justify-center">
              <Building2 size={32} />
            </div>
            <div className="space-y-2">
              <h3 className="text-lg font-bold text-slate-800 dark:text-white">No Branches Registered</h3>
              <p className="text-sm text-slate-500 dark:text-neutral-400 max-w-sm mx-auto">
                Set up your first branch to start using DRestro's cloud ordering and local billing systems.
              </p>
            </div>
            <Link 
              href="/onboarding"
              className="inline-flex items-center gap-2 bg-[#E53935] hover:bg-red-700 text-white font-black px-6 py-3.5 rounded-xl shadow-md transition-all text-sm"
            >
              <Plus size={16} /> Create First Branch
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            {/* Branches List */}
            {branchList.map((branch) => (
              <div 
                key={branch.id} 
                className="bg-white dark:bg-[#111] border border-slate-200 dark:border-neutral-850 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group relative overflow-hidden"
              >
                <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#E53935] to-red-500"></div>
                
                <div>
                  {/* Title & Badge */}
                  <div className="flex justify-between items-start mb-4">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-xl bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 flex items-center justify-center text-slate-500 dark:text-neutral-400 shrink-0">
                        <Building2 size={20} />
                      </div>
                      <div>
                        <h3 className="font-bold text-slate-900 dark:text-white text-base truncate max-w-[150px]" title={branch.name}>
                          {branch.name}
                        </h3>
                        <span className="text-[10px] text-slate-400 dark:text-neutral-500 capitalize">{branch.type}</span>
                      </div>
                    </div>
                    <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border
                      ${branch.planId.includes('premium') ? 'bg-[#E53935] text-white border-transparent' : 
                        branch.planId.includes('basic') ? 'bg-blue-500/10 text-blue-500 border-blue-500/20' :
                        branch.planId.includes('plat') ? 'bg-purple-500/10 text-purple-500 border-purple-500/20' :
                        'bg-gray-500/10 text-slate-500 border-gray-500/20'}`}
                    >
                      {branch.planId.toUpperCase()}
                    </span>
                  </div>

                  {/* Branch Stats/Details */}
                  <div className="space-y-2 border-t border-slate-100 dark:border-neutral-900 pt-4 mb-6">
                    {branch.address && (
                      <div className="flex items-center gap-2 text-xs text-slate-500 dark:text-neutral-400">
                        <MapPin size={13} className="shrink-0 text-slate-400" />
                        <span className="truncate">{branch.address}</span>
                      </div>
                    )}
                    <div className="flex items-center gap-2 text-xs text-slate-500 dark:text-neutral-400">
                      <Phone size={13} className="shrink-0 text-slate-400" />
                      <span>{branch.phone}</span>
                    </div>
                    
                    {/* Expiry Label */}
                    <div className="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-neutral-400 mt-2 bg-slate-50 dark:bg-neutral-900/50 p-2 rounded-lg border border-slate-100 dark:border-neutral-850 w-full">
                      {branch.status === "active" ? (
                        <CheckCircle2 size={13} className="text-emerald-500" />
                      ) : (
                        <AlertTriangle size={13} className="text-amber-500" />
                      )}
                      <span className="truncate">
                        {branch.expiresAt ? (
                          <>Expires: {new Date(branch.expiresAt).toLocaleDateString()}</>
                        ) : (
                          "Lifetime Plan"
                        )}
                      </span>
                    </div>
                  </div>
                </div>

                {/* Bottom Actions */}
                <div className="space-y-3">
                  
                  {/* SSO launch or Reactivate Button */}
                  {branch.status === "active" || branch.status === "trialing" ? (
                    <a 
                      href={branch.ssoUrl}
                      className="w-full inline-flex items-center justify-center gap-2 bg-[#E53935] hover:bg-red-700 text-white font-black py-3 rounded-xl transition-all shadow-sm group-hover:scale-[1.01]"
                    >
                      <span>Launch Branch</span> <ArrowRight size={14} />
                    </a>
                  ) : (
                    <div className="space-y-2">
                      <div className="text-center text-xs text-rose-500 font-bold uppercase tracking-wider bg-rose-500/10 border border-rose-500/20 py-2.5 rounded-xl">
                        Subscription {branch.status}
                      </div>
                      <a 
                        href={`https://wa.me/9865029558?text=Hello%20DRestro%2520Support,%2520I%2520need%252520to%2520reactivate%2520my%2520branch%2520"${encodeURIComponent(branch.name)}"%2520(Customer%2520ID:%2520${dbUser?.customerCode || "N/A"})`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="w-full inline-flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#1EBE5D] text-white font-black py-3 rounded-xl transition-all shadow-sm text-sm"
                      >
                        <span>Reactivate on WhatsApp</span>
                      </a>
                    </div>
                  )}



                </div>

              </div>
            ))}

            {/* Add New Branch Card */}
            {isPlatinum ? (
              <Link 
                href="/onboarding?new=true"
                className="border-2 border-dashed border-slate-200 dark:border-neutral-800 hover:border-red-500 dark:hover:border-red-500 bg-white/40 dark:bg-neutral-900/10 hover:bg-white dark:hover:bg-neutral-900/40 rounded-2xl p-6 flex flex-col items-center justify-center text-center space-y-3 transition-all min-h-[300px] cursor-pointer group"
              >
                <div className="w-12 h-12 rounded-full border border-dashed border-slate-350 dark:border-neutral-750 flex items-center justify-center text-slate-400 group-hover:text-red-500 group-hover:border-red-500 transition-colors">
                  <Plus size={24} />
                </div>
                <div>
                  <h3 className="font-bold text-slate-800 dark:text-white group-hover:text-red-500 transition-colors">Register New Branch</h3>
                  <p className="text-xs text-slate-500 dark:text-neutral-500 mt-1 max-w-[180px] mx-auto">
                    Add another restaurant outlet under this central account.
                  </p>
                </div>
              </Link>
            ) : (
              <div 
                className="border-2 border-dashed border-slate-200 dark:border-neutral-800 bg-slate-50/50 dark:bg-neutral-950/20 rounded-2xl p-6 flex flex-col items-center justify-center text-center space-y-4 min-h-[300px] relative group overflow-hidden"
              >
                {/* Floating upgrade badge */}
                <div className="absolute top-3 right-3 bg-amber-500 text-white text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                  Platinum Only
                </div>
                
                <div className="w-12 h-12 rounded-full bg-slate-100 dark:bg-neutral-800/80 flex items-center justify-center text-slate-400">
                  <Building2 size={22} className="opacity-60" />
                </div>
                <div>
                  <h3 className="font-bold text-slate-400 dark:text-neutral-550">Multi-Outlet (Locked)</h3>
                  <p className="text-[11px] text-slate-450 dark:text-neutral-500 mt-1 max-w-[180px] mx-auto">
                    Creating multiple branches is exclusive to the **Platinum** plan.
                  </p>
                </div>
                <Link 
                  href="/dashboard/billing/checkout"
                  className="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-sm transition-all"
                >
                  Upgrade to Platinum
                </Link>
              </div>
            )}

          </div>
        )}
      </main>

      {allSuspended && (
        <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
          <div className="bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-3xl p-8 max-w-md w-full shadow-2xl text-center space-y-6 animate-in zoom-in-95 duration-200">
            <div className="w-20 h-20 bg-rose-500/10 text-rose-500 rounded-full flex items-center justify-center mx-auto border border-rose-500/20">
              <AlertTriangle size={40} className="animate-bounce" />
            </div>
            
            <div className="space-y-2">
              <h2 className="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Subscription Inactive</h2>
              <p className="text-slate-500 dark:text-neutral-400 text-sm leading-relaxed">
                Your branch subscription status is currently <strong className="text-rose-500 uppercase">{branchList[0].status}</strong>. Access to your dashboard and POS portals is temporarily locked.
              </p>
            </div>

            <div className="bg-slate-50 dark:bg-neutral-950/50 p-4 rounded-2xl border border-slate-150 dark:border-neutral-850 text-left space-y-3">
              <div className="text-xs font-bold text-slate-400 dark:text-neutral-500 uppercase tracking-wider">How to Reactivate:</div>
              <p className="text-xs text-slate-500 dark:text-neutral-400 leading-relaxed">
                Please contact our billing & support team to renew your subscription or verify your manual payment slip.
              </p>
            </div>

            <div className="flex flex-col gap-2 pt-2">
              <a 
                href={`https://wa.me/9865029558?text=Hello%20DRestro%2520Support,%2520I%2520need%252520to%2520reactivate%2520my%2520branches.%2520(Customer%2520ID:%2520${dbUser?.customerCode || "N/A"})`}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full inline-flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#1EBE5D] text-white font-black py-3.5 rounded-xl transition-all shadow-md shadow-emerald-500/10 text-sm"
              >
                <span>Chat on WhatsApp</span>
              </a>
              <a 
                href="mailto:support@drestro.com"
                className="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-slate-700 dark:text-slate-200 font-bold py-3 rounded-xl transition-all text-xs"
              >
                <span>Email: billing@drestro.com</span>
              </a>
            </div>

            <div className="text-[10px] text-slate-450 dark:text-neutral-500 border-t border-slate-100 dark:border-neutral-850 pt-4 flex justify-between items-center">
              <span>Customer ID: <strong>{dbUser?.customerCode || "N/A"}</strong></span>
              <a href="/api/auth/signout" className="hover:underline text-[#E53935] font-bold">Logout Account</a>
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
