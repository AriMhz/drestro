"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import {
  Users, Server, AlertTriangle, TrendingUp, Building2,
  UserCog, CreditCard, Crown, Loader2, ArrowUpRight
} from "lucide-react";

export default function DashboardOverview({ basePath = "/admin" }: { basePath?: string }) {
  const [stats, setStats] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch("/api/admin/dashboard")
      .then(res => res.json())
      .then(data => { setStats(data); setLoading(false); })
      .catch(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center p-24">
        <Loader2 className="w-10 h-10 text-[#E53935] animate-spin" />
      </div>
    );
  }

  if (!stats) return <p className="text-slate-500 dark:text-gray-400 p-8">Failed to load dashboard data.</p>;

  const statCards = [
    { label: "Total Web Users", value: stats.totalWebUsers, sub: `${stats.totalRestaurants} restaurants`, icon: Users, color: "blue" },
    { label: "Active Offline Licenses", value: stats.activeClients, sub: `${stats.totalClients} total licenses`, icon: CreditCard, color: "emerald" },
    { label: "Expiring in 30 Days", value: stats.expiringClients, sub: "licenses expiring soon", icon: AlertTriangle, color: "amber" },
    { label: "Active Subscriptions", value: stats.totalSubscriptions, sub: `across ${stats.totalRestaurants} restaurants`, icon: TrendingUp, color: "purple" },
    { label: "Hardware Products", value: stats.totalHardware, sub: "listed on store", icon: Server, color: "green" },
    { label: "Staff Members", value: stats.totalStaff, sub: "admin, sales, support", icon: UserCog, color: "red" },
  ];

  const colorMap: Record<string, { bg: string; border: string; text: string }> = {
    blue: { bg: "bg-blue-500/10", border: "border-blue-500/20", text: "text-blue-400" },
    emerald: { bg: "bg-emerald-500/10", border: "border-emerald-500/20", text: "text-emerald-400" },
    amber: { bg: "bg-amber-500/10", border: "border-amber-500/20", text: "text-amber-400" },
    purple: { bg: "bg-purple-500/10", border: "border-purple-500/20", text: "text-purple-400" },
    green: { bg: "bg-green-500/10", border: "border-green-500/20", text: "text-green-400" },
    red: { bg: "bg-red-500/10", border: "border-red-500/20", text: "text-red-400" },
  };

  const planColors: Record<string, string> = {
    free: "bg-gray-500/20 text-slate-600 dark:text-gray-300",
    basic: "bg-blue-500/20 text-blue-300",
    premium_trial: "bg-orange-500/20 text-orange-300",
    premium: "bg-red-500/20 text-red-300",
    platinum: "bg-purple-500/20 text-purple-300",
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div>
        <h1 className="text-3xl font-bold text-[#111111] dark:text-white tracking-tight">Dashboard Overview</h1>
        <p className="text-slate-500 dark:text-gray-400 mt-2">Real-time overview of your DRestro platform.</p>
      </div>

      {/* Stat Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {statCards.map((card) => {
          const c = colorMap[card.color];
          return (
            <div key={card.label} className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] p-5 rounded-2xl flex items-center justify-between hover:border-[#444444] transition-colors">
              <div>
                <p className="text-slate-500 dark:text-gray-400 text-xs font-medium uppercase tracking-wider mb-1">{card.label}</p>
                <h3 className="text-3xl font-black text-[#111111] dark:text-white">{card.value}</h3>
                <p className="text-slate-400 dark:text-gray-500 text-xs mt-1">{card.sub}</p>
              </div>
              <div className={`w-12 h-12 ${c.bg} rounded-xl flex items-center justify-center border ${c.border}`}>
                <card.icon size={22} className={c.text} />
              </div>
            </div>
          );
        })}
      </div>

      {/* Plan Breakdown + Recent Users */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Plan Breakdown */}
        <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6">
          <div className="flex items-center justify-between mb-5">
            <h2 className="text-lg font-bold text-[#111111] dark:text-white flex items-center gap-2">
              <Crown size={20} className="text-amber-400" /> Subscription Plans
            </h2>
          </div>
          {Object.keys(stats.planBreakdown).length > 0 ? (
            <div className="space-y-3">
              {Object.entries(stats.planBreakdown).map(([plan, count]) => {
                const total = stats.totalSubscriptions || 1;
                const pct = Math.round(((count as number) / total) * 100);
                return (
                  <div key={plan}>
                    <div className="flex items-center justify-between mb-1.5">
                      <span className={`text-xs font-bold px-2.5 py-0.5 rounded-full uppercase ${planColors[plan] || "bg-gray-500/20 text-slate-600 dark:text-gray-300"}`}>
                        {plan.replace("_", " ")}
                      </span>
                      <span className="text-sm font-bold text-[#111111] dark:text-white">{count as number} <span className="text-slate-400 dark:text-gray-500 font-normal text-xs">({pct}%)</span></span>
                    </div>
                    <div className="w-full h-2 bg-slate-100 dark:bg-[#222222] rounded-full overflow-hidden">
                      <div
                        className="h-full bg-gradient-to-r from-[#E53935] to-red-400 rounded-full transition-all duration-700"
                        style={{ width: `${pct}%` }}
                      />
                    </div>
                  </div>
                );
              })}
            </div>
          ) : (
            <p className="text-slate-400 dark:text-gray-500 text-sm">No subscriptions yet.</p>
          )}
        </div>

        {/* Recent Users */}
        <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6">
          <div className="flex items-center justify-between mb-5">
            <h2 className="text-lg font-bold text-[#111111] dark:text-white flex items-center gap-2">
              <Users size={20} className="text-blue-400" /> Recent Signups
            </h2>
            <Link href={`${basePath}/clients`} className="text-xs text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white flex items-center gap-1 transition-colors">
              View All <ArrowUpRight size={14} />
            </Link>
          </div>
          {stats.recentUsers && stats.recentUsers.length > 0 ? (
            <div className="space-y-3">
              {stats.recentUsers.map((user: any) => (
                <div key={user.id} className="flex items-center gap-3 p-3 bg-slate-100 dark:bg-[#222222] rounded-xl border border-slate-200 dark:border-[#333333]">
                  <div className="w-9 h-9 rounded-full bg-slate-200 dark:bg-[#333333] flex items-center justify-center text-[#111111] dark:text-white font-bold text-sm border border-[#444444]">
                    {user.name ? user.name.charAt(0).toUpperCase() : "U"}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="font-semibold text-[#111111] dark:text-white text-sm truncate">{user.name || "Unknown"}</div>
                    <div className="text-slate-400 dark:text-gray-500 text-xs truncate">{user.email}</div>
                  </div>
                  <div className="text-slate-400 dark:text-gray-500 text-[11px] whitespace-nowrap">
                    {new Date(user.createdAt).toLocaleDateString(undefined, { month: "short", day: "numeric" })}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-slate-400 dark:text-gray-500 text-sm">No users yet.</p>
          )}
        </div>
      </div>

      {/* Quick Action Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Link href={`${basePath}/clients`} className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] hover:border-[#E53935]/50 p-6 rounded-2xl flex items-center gap-5 transition-all group">
          <div className="w-14 h-14 bg-[#E53935]/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <Users size={28} className="text-[#E53935]" />
          </div>
          <div>
            <h2 className="text-lg font-bold text-[#111111] dark:text-white group-hover:text-[#E53935] transition-colors">Manage Clients</h2>
            <p className="text-slate-500 dark:text-gray-400 text-sm">Generate licenses, manage cloud subscriptions</p>
          </div>
          <ArrowUpRight size={20} className="text-gray-600 group-hover:text-[#E53935] ml-auto transition-colors" />
        </Link>

        <Link href={`${basePath}/hardware`} className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] hover:border-emerald-500/50 p-6 rounded-2xl flex items-center gap-5 transition-all group">
          <div className="w-14 h-14 bg-emerald-500/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <Server size={28} className="text-emerald-500" />
          </div>
          <div>
            <h2 className="text-lg font-bold text-[#111111] dark:text-white group-hover:text-emerald-400 transition-colors">Hardware Store</h2>
            <p className="text-slate-500 dark:text-gray-400 text-sm">Add or update POS hardware devices</p>
          </div>
          <ArrowUpRight size={20} className="text-gray-600 group-hover:text-emerald-400 ml-auto transition-colors" />
        </Link>
      </div>
    </div>
  );
}
