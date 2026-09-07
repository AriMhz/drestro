"use client";

import React, { useState } from "react";
import { LifeBuoy, Mail } from "lucide-react";
import SupportClient from "../support/SupportClient";
import ContactClient from "../contact/ContactClient";

interface InboxClientProps {
  initialTickets: any[];
  initialSubmissions: any[];
}

export default function InboxClient({ initialTickets, initialSubmissions }: InboxClientProps) {
  const [activeTab, setActiveTab] = useState<"support" | "contact">("support");

  return (
    <div className="space-y-6">
      {/* Tabs */}
      <div className="flex justify-start border-b border-slate-200 dark:border-[#333333] pb-4">
        <div className="flex p-1 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-xl shadow-sm">
          <button
            onClick={() => setActiveTab("support")}
            className={`flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold transition-all ${
              activeTab === "support"
                ? "bg-[#E53935] text-white shadow-lg"
                : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"
            }`}
          >
            <LifeBuoy size={16} />
            <span>Support Tickets</span>
            <span className={`ml-1.5 px-2 py-0.5 text-xs rounded-full ${
              activeTab === "support"
                ? "bg-white/20 text-white"
                : "bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-400"
            }`}>
              {initialTickets.length}
            </span>
          </button>

          <button
            onClick={() => setActiveTab("contact")}
            className={`flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold transition-all ${
              activeTab === "contact"
                ? "bg-[#E53935] text-white shadow-lg"
                : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"
            }`}
          >
            <Mail size={16} />
            <span>Contact Messages</span>
            <span className={`ml-1.5 px-2 py-0.5 text-xs rounded-full ${
              activeTab === "contact"
                ? "bg-white/20 text-white"
                : "bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-400"
            }`}>
              {initialSubmissions.length}
            </span>
          </button>
        </div>
      </div>

      {/* Tab Contents */}
      <div className="transition-all duration-300">
        {activeTab === "support" ? (
          <SupportClient initialTickets={initialTickets} />
        ) : (
          <ContactClient initialSubmissions={initialSubmissions} />
        )}
      </div>
    </div>
  );
}
