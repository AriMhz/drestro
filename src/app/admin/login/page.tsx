"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Lock, Mail, AlertCircle, MapPin, ChevronDown } from "lucide-react";

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

const fetchClientIpv4 = async (): Promise<string> => {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 1200); // 1.2s timeout
  try {
    const response = await fetch("https://api4.ipify.org?format=json", {
      signal: controller.signal,
    });
    clearTimeout(timeoutId);
    if (response.ok) {
      const data = await response.json();
      if (data.ip) return data.ip;
    }
  } catch (e) {
    console.warn("ipify failed, trying icanhazip", e);
  }

  // Fallback 1: icanhazip
  const controller2 = new AbortController();
  const timeoutId2 = setTimeout(() => controller2.abort(), 1000);
  try {
    const response = await fetch("https://ipv4.icanhazip.com", {
      signal: controller2.signal,
    });
    clearTimeout(timeoutId2);
    if (response.ok) {
      const ip = (await response.text()).trim();
      if (ip) return ip;
    }
  } catch (e) {
    console.warn("icanhazip failed", e);
  }

  return "";
};

export default function AdminLogin() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [location, setLocation] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      const clientIpv4 = await fetchClientIpv4();

      const res = await fetch("/api/admin/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password, location, clientIpv4 }),
      });

      const data = await res.json();

      if (res.ok) {
        router.push(data.redirectUrl || "/admin/clients");
        router.refresh();
      } else {
        setError(data.error || "Invalid credentials");
      }
    } catch (err) {
      setError("An error occurred during login.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-slate-50 dark:bg-[#111111] flex items-center justify-center p-4 relative overflow-hidden">
      {/* Background Effect */}
      <div className="absolute top-0 right-0 w-[800px] h-[800px] bg-[#E53935]/10 blur-[120px] rounded-full pointer-events-none transform translate-x-1/3 -translate-y-1/3"></div>

      <div className="max-w-md w-full bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#333333] p-8 shadow-2xl relative z-10">
        <div className="flex flex-col items-center justify-center mb-8 gap-2">
          <img src="/logos/logo.svg" alt="DRestro Logo" className="h-8 w-auto dark:hidden" />
          <img src="/logos/logo-light.svg" alt="DRestro Logo" className="h-8 w-auto hidden dark:block" />
          <span className="text-xs font-bold uppercase tracking-widest text-[#E53935]">Staff Portal</span>
        </div>
        
        <div className="text-center mb-8">
          <p className="text-slate-500 dark:text-gray-400 text-sm">Enter your email and password to continue.</p>
        </div>

        {error && (
          <div className="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl flex items-center gap-3 mb-6 text-sm">
            <AlertCircle size={18} />
            <p>{error}</p>
          </div>
        )}

        <form onSubmit={handleLogin} className="space-y-6">
          <div className="space-y-2">
            <label className="text-sm font-medium text-slate-600 dark:text-gray-300 ml-1">Email Address</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Mail size={18} className="text-slate-400 dark:text-gray-500" />
              </div>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl pl-11 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-[#E53935] focus:border-transparent transition-all placeholder:text-gray-600 dark:text-neutral-400"
                placeholder="staff@drestro.com"
                required
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-sm font-medium text-slate-600 dark:text-gray-300 ml-1">Password</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Lock size={18} className="text-slate-400 dark:text-gray-500" />
              </div>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl pl-11 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-[#E53935] focus:border-transparent transition-all placeholder:text-gray-600 dark:text-neutral-400"
                placeholder="••••••••••••"
                required
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-sm font-medium text-slate-600 dark:text-gray-300 ml-1">Location</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <MapPin size={18} className="text-slate-400 dark:text-gray-500" />
              </div>
              <select
                value={location}
                onChange={(e) => setLocation(e.target.value)}
                className={`w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] rounded-xl pl-11 pr-10 py-3.5 focus:outline-none focus:ring-2 focus:ring-[#E53935] focus:border-transparent transition-all cursor-pointer appearance-none ${
                  location ? "text-[#111111] dark:text-white" : "text-gray-500 dark:text-neutral-500"
                }`}
                required
              >
                <option value="" disabled className="text-gray-400 dark:text-neutral-500">Select Location</option>
                <option value="admin">Admin</option>
                {NEPAL_CITIES.map((city) => (
                  <option key={city} value={city}>{city}</option>
                ))}
              </select>
              <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 dark:text-gray-500">
                <ChevronDown size={18} />
              </div>
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-[#E53935] hover:bg-[#D32F2F] text-white py-3.5 rounded-xl font-bold transition-all flex items-center justify-center disabled:opacity-70 shadow-[0_4px_12px_rgba(229,57,53,0.2)]"
          >
            {loading ? "Verifying..." : "Access Portal"}
          </button>
        </form>
      </div>
    </div>
  );
}
