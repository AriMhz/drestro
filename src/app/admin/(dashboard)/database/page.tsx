"use client";

import React, { useState, useEffect } from "react";
import { Database, Download, ShieldCheck, HardDrive, FileJson, Server, RefreshCw, Layers, FileSpreadsheet, FileText } from "lucide-react";

interface DbSummary {
  usersCount: number;
  restaurantsCount: number;
  subscriptionsCount: number;
  clientsCount: number;
  supportTicketsCount: number;
  hardwareOrdersCount: number;
  contactSubmissionsCount: number;
}

export default function AdminDatabasePage() {
  const [downloadingFormat, setDownloadingFormat] = useState<string | null>(null);
  const [loadingStats, setLoadingStats] = useState(true);
  const [stats, setStats] = useState<DbSummary | null>(null);

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    setLoadingStats(true);
    try {
      const res = await fetch("/api/admin/database/download?format=json");
      if (res.ok) {
        const data = await res.json();
        if (data.meta?.summary) {
          setStats(data.meta.summary);
        }
      }
    } catch (e) {
      console.error("Failed to load DB stats:", e);
    } finally {
      setLoadingStats(false);
    }
  };

  const handleDownload = (format: "csv" | "html" | "json" | "db") => {
    setDownloadingFormat(format);

    const extMap: Record<string, string> = {
      csv: "csv",
      html: "html",
      json: "json",
      db: "db",
    };

    const link = document.createElement("a");
    link.href = `/api/admin/database/download?format=${format}`;
    link.setAttribute("download", `drestro_database_${format}.${extMap[format]}`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setTimeout(() => {
      setDownloadingFormat(null);
    }, 2000);
  };

  return (
    <div className="p-6 md:p-10 max-w-6xl mx-auto space-y-8 animate-in fade-in duration-300">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-[#222222] pb-6">
        <div>
          <div className="flex items-center gap-3 mb-1">
            <div className="p-2.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-xl">
              <Database size={26} />
            </div>
            <h1 className="text-2xl md:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
              Database Management & Export Center
            </h1>
          </div>
          <p className="text-sm text-slate-500 dark:text-gray-400">
            Export complete database records A-Z in Excel/CSV, Printable HTML Report, or raw JSON & DB formats.
          </p>
        </div>

        <button
          onClick={fetchStats}
          disabled={loadingStats}
          className="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-[#222222] hover:bg-slate-200 dark:hover:bg-[#2E2E2E] text-slate-700 dark:text-gray-200 text-xs font-bold rounded-xl transition-all self-start sm:self-auto cursor-pointer"
        >
          <RefreshCw size={14} className={loadingStats ? "animate-spin" : ""} />
          Refresh Stats
        </button>
      </div>

      {/* Main Download Options Grid (4 Formats) */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* 1. Excel / CSV Format Card */}
        <div className="bg-white dark:bg-[#1A1A1A] border-2 border-emerald-500/20 dark:border-emerald-500/30 rounded-2xl p-6 sm:p-8 flex flex-col justify-between shadow-lg relative overflow-hidden group">
          <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <FileSpreadsheet size={140} className="text-emerald-500" />
          </div>

          <div className="space-y-4 relative z-10">
            <div className="flex items-center justify-between">
              <span className="px-3 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-black rounded-lg uppercase tracking-wider">
                Most Popular for Excel
              </span>
              <span className="flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/30 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800/40">
                <ShieldCheck size={13} /> Opens in Excel & Sheets
              </span>
            </div>

            <div>
              <h2 className="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <FileSpreadsheet className="text-emerald-500" size={22} /> Excel & CSV Spreadsheet (.csv)
              </h2>
              <p className="text-xs text-slate-500 dark:text-gray-400 mt-2 leading-relaxed">
                Exports all database records (Users, Outlets, Email, Phone, Plan, Status, Location, Limits, Tickets, etc.) into clean tabular sections ready to open in <strong>Microsoft Excel</strong>, <strong>Google Sheets</strong>, or Numbers.
              </p>
            </div>

            <div className="bg-slate-50 dark:bg-[#111111] p-3.5 rounded-xl border border-slate-200 dark:border-[#333333] space-y-1 text-xs">
              <div className="flex justify-between text-slate-600 dark:text-gray-300">
                <span>Best For:</span>
                <span className="font-bold text-slate-900 dark:text-white">Excel Tables, Filtering & Analysis</span>
              </div>
              <div className="flex justify-between text-slate-600 dark:text-gray-300">
                <span>Format:</span>
                <span className="font-mono font-bold text-emerald-500">.csv (Excel Spreadsheet)</span>
              </div>
            </div>
          </div>

          <div className="pt-6 relative z-10">
            <button
              onClick={() => handleDownload("csv")}
              disabled={downloadingFormat === "csv"}
              className="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-emerald-500/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              {downloadingFormat === "csv" ? (
                <>
                  <RefreshCw size={18} className="animate-spin" /> Preparing Excel CSV...
                </>
              ) : (
                <>
                  <Download size={18} /> Download Excel Spreadsheet (.csv)
                </>
              )}
            </button>
          </div>
        </div>

        {/* 2. Printable HTML Document Report Card */}
        <div className="bg-white dark:bg-[#1A1A1A] border-2 border-indigo-500/20 dark:border-indigo-500/30 rounded-2xl p-6 sm:p-8 flex flex-col justify-between shadow-lg relative overflow-hidden group">
          <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <FileText size={140} className="text-indigo-500" />
          </div>

          <div className="space-y-4 relative z-10">
            <div className="flex items-center justify-between">
              <span className="px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-black rounded-lg uppercase tracking-wider">
                Visual Report / PDF
              </span>
            </div>

            <div>
              <h2 className="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <FileText className="text-indigo-500" size={22} /> Printable Document Report (.html)
              </h2>
              <p className="text-xs text-slate-500 dark:text-gray-400 mt-2 leading-relaxed">
                Generates a beautiful styled document report with styled data tables. Double-click to open in any web browser, print, or save as PDF for client meetings or audit reports.
              </p>
            </div>

            <div className="bg-slate-50 dark:bg-[#111111] p-3.5 rounded-xl border border-slate-200 dark:border-[#333333] space-y-1 text-xs">
              <div className="flex justify-between text-slate-600 dark:text-gray-300">
                <span>Best For:</span>
                <span className="font-bold text-slate-900 dark:text-white">Reading, Printing & PDF Save</span>
              </div>
              <div className="flex justify-between text-slate-600 dark:text-gray-300">
                <span>Format:</span>
                <span className="font-mono font-bold text-indigo-500">.html (Visual Document)</span>
              </div>
            </div>
          </div>

          <div className="pt-6 relative z-10">
            <button
              onClick={() => handleDownload("html")}
              disabled={downloadingFormat === "html"}
              className="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-indigo-500/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              {downloadingFormat === "html" ? (
                <>
                  <RefreshCw size={18} className="animate-spin" /> Generating Document...
                </>
              ) : (
                <>
                  <Download size={18} /> Download Document Report (.html)
                </>
              )}
            </button>
          </div>
        </div>

        {/* 3. Full JSON Dump Card */}
        <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 sm:p-8 flex flex-col justify-between shadow-sm relative overflow-hidden group">
          <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <FileJson size={140} className="text-rose-500" />
          </div>

          <div className="space-y-4 relative z-10">
            <div className="flex items-center justify-between">
              <span className="px-3 py-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-black rounded-lg uppercase tracking-wider">
                Developer Export
              </span>
            </div>

            <div>
              <h2 className="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <FileJson className="text-rose-500" size={22} /> Full Database JSON Backup (A-Z)
              </h2>
              <p className="text-xs text-slate-500 dark:text-gray-400 mt-2 leading-relaxed">
                Exports all database models A-Z as a structured JSON file. Fully compatible for server migrations, backups, and programmatic parsing.
              </p>
            </div>

            <div className="bg-slate-50 dark:bg-[#111111] p-3.5 rounded-xl border border-slate-200 dark:border-[#333333] space-y-1 text-xs">
              <div className="flex justify-between text-slate-600 dark:text-gray-300">
                <span>Includes:</span>
                <span className="font-bold text-slate-900 dark:text-white">All 24 Database Tables A-Z</span>
              </div>
              <div className="flex justify-between text-slate-600 dark:text-gray-300">
                <span>Format:</span>
                <span className="font-mono font-bold text-rose-500">.json (Programmatic Dump)</span>
              </div>
            </div>
          </div>

          <div className="pt-6 relative z-10">
            <button
              onClick={() => handleDownload("json")}
              disabled={downloadingFormat === "json"}
              className="w-full py-3 bg-slate-900 hover:bg-slate-800 dark:bg-[#2A2A2A] dark:hover:bg-[#383838] text-white font-bold text-xs rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              {downloadingFormat === "json" ? (
                <>
                  <RefreshCw size={16} className="animate-spin" /> Exporting JSON...
                </>
              ) : (
                <>
                  <Download size={16} /> Download Full JSON (A-Z)
                </>
              )}
            </button>
          </div>
        </div>

        {/* 4. Raw SQLite Database Card */}
        <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 sm:p-8 flex flex-col justify-between shadow-sm relative overflow-hidden group">
          <div className="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <HardDrive size={140} className="text-blue-500" />
          </div>

          <div className="space-y-4 relative z-10">
            <div className="flex items-center justify-between">
              <span className="px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xs font-black rounded-lg uppercase tracking-wider">
                Raw Database File
              </span>
            </div>

            <div>
              <h2 className="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <HardDrive className="text-blue-500" size={22} /> SQLite Binary Database (.db)
              </h2>
              <p className="text-xs text-slate-500 dark:text-gray-400 mt-2 leading-relaxed">
                Downloads the direct raw SQLite database file (<code className="bg-slate-100 dark:bg-[#222] px-1 py-0.5 rounded text-blue-500 font-bold">dev.db</code>). Best for opening in DB Browser for SQLite.
              </p>
            </div>

            <div className="bg-slate-50 dark:bg-[#111111] p-3.5 rounded-xl border border-slate-200 dark:border-[#333333] space-y-1 text-xs">
              <div className="flex justify-between text-slate-600 dark:text-gray-300">
                <span>File Path:</span>
                <span className="font-mono text-slate-700 dark:text-gray-300 font-semibold">prisma/dev.db</span>
              </div>
              <div className="flex justify-between text-slate-600 dark:text-gray-300">
                <span>Format:</span>
                <span className="font-mono font-bold text-blue-500">.db (SQLite Binary)</span>
              </div>
            </div>
          </div>

          <div className="pt-6 relative z-10">
            <button
              onClick={() => handleDownload("db")}
              disabled={downloadingFormat === "db"}
              className="w-full py-3 bg-slate-900 hover:bg-slate-800 dark:bg-[#2A2A2A] dark:hover:bg-[#383838] text-white font-bold text-xs rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              {downloadingFormat === "db" ? (
                <>
                  <RefreshCw size={16} className="animate-spin" /> Fetching SQLite File...
                </>
              ) : (
                <>
                  <Download size={16} /> Download Raw .db File
                </>
              )}
            </button>
          </div>
        </div>
      </div>

      {/* Database Statistics Table Summary */}
      <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-6 shadow-sm space-y-5">
        <div className="flex items-center justify-between border-b border-slate-100 dark:border-[#282828] pb-4">
          <div className="flex items-center gap-2">
            <Layers className="text-rose-500" size={20} />
            <h2 className="font-bold text-base text-slate-900 dark:text-white">Database Table Summary</h2>
          </div>
          <span className="text-xs text-slate-400 font-medium">Real-time Row Counts</span>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div className="p-4 bg-slate-50 dark:bg-[#111111] rounded-xl border border-slate-200/60 dark:border-[#2B2B2B]">
            <p className="text-xs font-semibold text-slate-500 dark:text-gray-400">Users (Main)</p>
            <p className="text-2xl font-black text-slate-900 dark:text-white mt-1">
              {loadingStats ? "..." : stats?.usersCount ?? 0}
            </p>
          </div>

          <div className="p-4 bg-slate-50 dark:bg-[#111111] rounded-xl border border-slate-200/60 dark:border-[#2B2B2B]">
            <p className="text-xs font-semibold text-slate-500 dark:text-gray-400">Restaurants / Outlets</p>
            <p className="text-2xl font-black text-rose-500 mt-1">
              {loadingStats ? "..." : stats?.restaurantsCount ?? 0}
            </p>
          </div>

          <div className="p-4 bg-slate-50 dark:bg-[#111111] rounded-xl border border-slate-200/60 dark:border-[#2B2B2B]">
            <p className="text-xs font-semibold text-slate-500 dark:text-gray-400">POS Clients / Licenses</p>
            <p className="text-2xl font-black text-blue-500 mt-1">
              {loadingStats ? "..." : stats?.clientsCount ?? 0}
            </p>
          </div>

          <div className="p-4 bg-slate-50 dark:bg-[#111111] rounded-xl border border-slate-200/60 dark:border-[#2B2B2B]">
            <p className="text-xs font-semibold text-slate-500 dark:text-gray-400">Active Subscriptions</p>
            <p className="text-2xl font-black text-emerald-500 mt-1">
              {loadingStats ? "..." : stats?.subscriptionsCount ?? 0}
            </p>
          </div>

          <div className="p-4 bg-slate-50 dark:bg-[#111111] rounded-xl border border-slate-200/60 dark:border-[#2B2B2B]">
            <p className="text-xs font-semibold text-slate-500 dark:text-gray-400">Support Tickets</p>
            <p className="text-2xl font-black text-amber-500 mt-1">
              {loadingStats ? "..." : stats?.supportTicketsCount ?? 0}
            </p>
          </div>

          <div className="p-4 bg-slate-50 dark:bg-[#111111] rounded-xl border border-slate-200/60 dark:border-[#2B2B2B]">
            <p className="text-xs font-semibold text-slate-500 dark:text-gray-400">Hardware Orders</p>
            <p className="text-2xl font-black text-indigo-500 mt-1">
              {loadingStats ? "..." : stats?.hardwareOrdersCount ?? 0}
            </p>
          </div>

          <div className="p-4 bg-slate-50 dark:bg-[#111111] rounded-xl border border-slate-200/60 dark:border-[#2B2B2B]">
            <p className="text-xs font-semibold text-slate-500 dark:text-gray-400">Contact Submissions</p>
            <p className="text-2xl font-black text-purple-500 mt-1">
              {loadingStats ? "..." : stats?.contactSubmissionsCount ?? 0}
            </p>
          </div>

          <div className="p-4 bg-slate-50 dark:bg-[#111111] rounded-xl border border-slate-200/60 dark:border-[#2B2B2B] flex flex-col justify-center">
            <p className="text-xs font-semibold text-slate-500 dark:text-gray-400">Database Engine</p>
            <p className="text-xs font-bold text-slate-800 dark:text-gray-200 mt-1 flex items-center gap-1">
              <Server size={14} className="text-emerald-500" /> Prisma ORM / SQLite
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
