import { auth } from "../../../../auth";
import { prisma } from "../../../lib/prisma";
import { CheckCircle2, AlertCircle, ArrowRight, Zap, Repeat } from "lucide-react";
import Link from "next/link";
import { redirect } from "next/navigation";

export const dynamic = "force-dynamic";

export default async function BillingPage() {
  const session = await auth();
  if (!session?.user?.id) redirect("/login");
  
  const restaurant = await prisma.restaurant.findFirst({
    where: { userId: session.user.id },
    include: { subscriptions: true }
  });

  if (!restaurant) redirect("/onboarding");

  // Fetch the subscription payments of this user
  const payments = await prisma.subscriptionPayment.findMany({
    where: { userId: session.user.id },
    orderBy: { createdAt: "desc" }
  });

  const sub = restaurant.subscriptions[0];
  const isTrial = sub?.status === "trialing";

  // Dynamically resolve plan name and description
  let planName = "Premium Plan";
  let planDesc = "Includes full access to Online Web POS, Waiter Sync, and Kitchen Display System.";
  
  if (sub?.planId) {
    const idLower = sub.planId.toLowerCase();
    if (idLower.includes("free")) {
      planName = "Free Plan";
      planDesc = "Digitize your kitchen with basic ordering, dishes, and income tracking.";
    } else if (idLower.includes("basic")) {
      planName = "Basic Plan";
      planDesc = "Perfect for standard order management, table space, and KOT display.";
    } else if (idLower.includes("premium")) {
      planName = "Premium Plan";
      planDesc = "Includes full access to Online Web POS, Waiter Sync, and Kitchen Display System.";
    } else if (idLower.includes("platinum")) {
      planName = "Platinum Plan";
      planDesc = "Enterprise level system with unlimited departments, multi-outlet, and 24/7 VIP support.";
    }
  }

  // Format dates elegantly: e.g. June 26, 2026
  const formatDate = (date: Date | null | undefined) => {
    if (!date) return "";
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  };

  return (
    <div className="max-w-4xl mx-auto py-6">
      
      <div className="mb-10">
        <h1 className="text-3xl font-black text-gray-900 dark:text-white mb-2">Billing & Licenses</h1>
        <p className="text-neutral-500 dark:text-neutral-400 text-sm">Manage your subscription, view invoices, and upgrade your plan.</p>
      </div>

      {/* Current Plan Card */}
      <div className="bg-white dark:bg-[#0a0a0a] border border-neutral-200 dark:border-neutral-800 rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-none relative overflow-hidden mb-8 transition-all duration-300">
        <div className="absolute top-0 left-0 w-[4px] h-full bg-gradient-to-b from-[#E53935] to-orange-500"></div>
        
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
          <div className="flex-1">
            <div className="flex items-center gap-3 mb-2.5">
              <h2 className="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{planName}</h2>
              <span className="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 text-[10px] font-black rounded-lg uppercase tracking-wider border border-emerald-200/50 dark:border-emerald-900/30">
                {isTrial ? "Free Plan" : "Active Plan"}
              </span>
            </div>
            
            <p className="text-neutral-500 dark:text-neutral-400 text-sm leading-relaxed mb-6 max-w-xl">
              {planDesc}
            </p>
            
            {sub?.currentPeriodEnd ? (
              <div className="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 px-4 py-2.5 rounded-xl border border-emerald-200/50 dark:border-emerald-900/30 inline-flex shadow-sm uppercase tracking-wider">
                <CheckCircle2 size={15} className="text-emerald-500 shrink-0" />
                Active until {formatDate(sub.currentPeriodEnd)}
              </div>
            ) : (
              <div className="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 px-4 py-2.5 rounded-xl border border-emerald-200/50 dark:border-emerald-900/30 inline-flex shadow-sm uppercase tracking-wider">
                <CheckCircle2 size={15} className="text-emerald-500 shrink-0" />
                Lifetime Free Active (1 User Account)
              </div>
            )}
          </div>

          <div className="flex flex-col sm:flex-row md:flex-col gap-3.5 w-full md:w-auto">
            <Link 
              href="/dashboard" 
              className="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3.5 px-8 rounded-xl shadow-[0_4px_15px_rgba(16,185,129,0.2)] dark:shadow-none hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 text-sm whitespace-nowrap"
            >
              <Zap size={16} />
              Launch POS (Sync)
            </Link>

            <Link 
              href="/dashboard/billing/plans" 
              className="bg-[#E53935] hover:bg-red-600 text-white font-bold py-3.5 px-8 rounded-xl shadow-[0_4px_15px_rgba(229,57,53,0.2)] dark:shadow-none hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 text-sm whitespace-nowrap"
            >
              {isTrial ? <Zap size={16} /> : <Repeat size={16} />}
              {isTrial ? "Upgrade to Full" : "Renew Plan"}
            </Link>
            
            <Link 
              href="/dashboard/billing/plans" 
              className="bg-transparent hover:bg-neutral-50 dark:hover:bg-neutral-900 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-800 font-bold py-3.5 px-8 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 text-sm whitespace-nowrap"
            >
              Change Plan <ArrowRight size={15} />
            </Link>
          </div>
        </div>
      </div>

      {/* Transaction Logs Table */}
      <div className="bg-white dark:bg-[#0a0a0a] border border-neutral-200 dark:border-neutral-800 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-none overflow-hidden">
        <h3 className="text-lg font-black text-gray-900 dark:text-white tracking-tight mb-4">Transaction History</h3>
        
        {payments.length === 0 ? (
          <p className="text-sm text-neutral-500 dark:text-neutral-450 py-4 text-center">No payment transactions recorded yet.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs font-semibold text-slate-700 dark:text-neutral-350">
              <thead>
                <tr className="border-b border-neutral-100 dark:border-neutral-850 text-slate-400 dark:text-neutral-500 text-[10px] uppercase tracking-wider">
                  <th className="pb-3 font-bold">Transaction ID</th>
                  <th className="pb-3 font-bold">Plan</th>
                  <th className="pb-3 font-bold">Cycle</th>
                  <th className="pb-3 font-bold">Amount</th>
                  <th className="pb-3 font-bold">Method</th>
                  <th className="pb-3 font-bold">Status</th>
                  <th className="pb-3 font-bold">Date</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-neutral-100 dark:divide-neutral-850">
                {payments.map((p) => (
                  <tr key={p.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-900/40 transition-colors">
                    <td className="py-4 font-mono font-bold text-[#E53935]">{p.paymentCode || "TXN-PENDING"}</td>
                    <td className="py-4 capitalize font-extrabold text-gray-900 dark:text-white">{p.planId}</td>
                    <td className="py-4 capitalize">{p.billingCycle}</td>
                    <td className="py-4 font-black text-gray-900 dark:text-white">Rs. {p.amount.toLocaleString()}</td>
                    <td className="py-4 uppercase">{p.paymentMethod}</td>
                    <td className="py-4">
                      <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border ${
                        p.status === "COMPLETED" || p.status === "APPROVED"
                          ? "bg-emerald-500/10 text-emerald-500 border-emerald-500/20"
                          : p.status === "PENDING"
                          ? "bg-amber-500/10 text-amber-500 border-amber-500/20"
                          : "bg-rose-500/10 text-rose-500 border-rose-500/20"
                      }`}>
                        {p.status}
                      </span>
                    </td>
                    <td className="py-4 text-neutral-400 dark:text-neutral-500">{new Date(p.createdAt).toLocaleDateString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

    </div>
  );
}
