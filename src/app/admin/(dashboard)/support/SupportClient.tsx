"use client";

import { useState } from "react";
import { Key, Inbox, Clock, CheckCircle2, AlertTriangle, Shield, User, MapPin, Phone } from "lucide-react";

export default function SupportClient({ initialTickets }: { initialTickets: any[] }) {
  const [tickets, setTickets] = useState(initialTickets);
  const [selectedTicket, setSelectedTicket] = useState<any | null>(null);
  const [isUpdating, setIsUpdating] = useState(false);
  const [activeTab, setActiveTab] = useState<"ALL" | "OPEN" | "IN_PROGRESS" | "RESOLVED" | "CLOSED">("ALL");

  const getStatusColor = (status: string) => {
    switch (status) {
      case "OPEN": return "bg-amber-500/10 text-amber-500 border border-amber-500/20 dark:bg-amber-400/10 dark:text-amber-400 dark:border-amber-400/20";
      case "IN_PROGRESS": return "bg-blue-500/10 text-blue-500 border border-blue-500/20 dark:bg-blue-400/10 dark:text-blue-400 dark:border-blue-400/20";
      case "RESOLVED": return "bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:border-emerald-400/20";
      case "CLOSED": return "bg-slate-200 dark:bg-[#2a2a2a] text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-[#3a3a3a]";
      default: return "bg-slate-200 dark:bg-[#2a2a2a] text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-[#3a3a3a]";
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case "URGENT": return "text-red-600 dark:text-red-400 bg-red-500/10 border border-red-500/20";
      case "HIGH": return "text-orange-600 dark:text-orange-400 bg-orange-500/10 border border-orange-500/20";
      case "MEDIUM": return "text-blue-600 dark:text-blue-400 bg-blue-500/10 border border-blue-500/20";
      case "LOW": return "text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#3a3a3a]";
      default: return "text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#3a3a3a]";
    }
  };

  const updateStatus = async (id: string, newStatus: string) => {
    setIsUpdating(true);
    try {
      const res = await fetch(`/api/admin/support/${id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ status: newStatus }),
      });

      if (res.ok) {
        const { ticket: updatedTicket } = await res.json();
        setTickets(tickets.map(t => t.id === id ? updatedTicket : t));
        if (selectedTicket && selectedTicket.id === id) {
          setSelectedTicket(updatedTicket);
        }
      }
    } catch (err) {
      console.error("Failed to update status:", err);
      alert("Failed to update ticket status.");
    } finally {
      setIsUpdating(false);
    }
  };

  const filteredTickets = tickets.filter(t => {
    if (activeTab === "ALL") return true;
    return t.status === activeTab;
  });

  return (
    <div className="grid grid-cols-1 xl:grid-cols-3 gap-8 min-h-[calc(100vh-140px)]">
      {/* Tickets List */}
      <div className={`xl:col-span-2 bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] overflow-hidden flex flex-col ${selectedTicket ? "hidden xl:flex" : "flex"}`}>
        
        {/* Styled Tabs / Filters Header */}
        <div className="p-6 bg-slate-50/50 dark:bg-[#141414]/50 border-b border-slate-200/60 dark:border-[#222222]">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 className="text-lg font-black text-slate-800 dark:text-white tracking-tight">Support Queues</h2>
              <p className="text-xs text-slate-400 mt-0.5">Manage priorities and tickets submitted from clients.</p>
            </div>
            
            <div className="flex flex-wrap gap-2 p-1 bg-slate-100 dark:bg-[#1a1a1a] rounded-2xl border border-slate-200/50 dark:border-[#2a2a2a]/50">
              {(["ALL", "OPEN", "IN_PROGRESS", "RESOLVED", "CLOSED"] as const).map((tab) => (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className={`px-4 py-2 rounded-xl text-xs font-bold transition-all duration-300 whitespace-nowrap ${
                    activeTab === tab
                      ? "bg-red-500 text-white border-red-500 shadow-lg shadow-red-500/20"
                      : "text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-white/50 dark:hover:bg-white/5"
                  }`}
                >
                  {tab.replace("_", " ")}
                  <span className={`ml-1.5 px-1.5 py-0.5 text-[10px] rounded-md ${activeTab === tab ? "bg-white/20 text-white" : "bg-slate-200 dark:bg-[#2a2a2a] text-slate-600 dark:text-slate-400"}`}>
                    {tickets.filter(t => tab === "ALL" ? true : t.status === tab).length}
                  </span>
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Tickets Table container */}
        <div className="flex-1 overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-slate-200/60 dark:border-[#222222] text-xs uppercase tracking-wider font-extrabold text-slate-400 bg-slate-50/20 dark:bg-[#141414]/10">
                <th className="px-6 py-4.5">Client Restaurant</th>
                <th className="px-6 py-4.5">Ticket Description</th>
                <th className="px-6 py-4.5">Status</th>
                <th className="px-6 py-4.5">Priority</th>
                <th className="px-6 py-4.5">Submitted</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100/60 dark:divide-[#222222]/80">
              {filteredTickets.length === 0 ? (
                <tr>
                  <td colSpan={5} className="px-6 py-20 text-center">
                    <div className="flex flex-col items-center max-w-xs mx-auto">
                      <div className="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/30 dark:border-[#2a2a2a] flex items-center justify-center text-slate-400 mb-3">
                        <Inbox className="w-6 h-6" />
                      </div>
                      <p className="text-sm font-bold text-slate-600 dark:text-slate-300">No active tickets found</p>
                      <p className="text-xs text-slate-400 mt-1">There are no tickets matching the current status tab filters.</p>
                    </div>
                  </td>
                </tr>
              ) : (
                filteredTickets.map((ticket) => (
                  <tr 
                    key={ticket.id} 
                    onClick={() => setSelectedTicket(ticket)}
                    className={`hover:bg-slate-50/50 dark:hover:bg-[#151515]/30 cursor-pointer transition-all duration-200 ${selectedTicket?.id === ticket.id ? "bg-slate-50/80 dark:bg-[#151515]/50 border-l-4 border-l-red-500" : "border-l-4 border-l-transparent"}`}
                  >
                    <td className="px-6 py-5">
                      <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-slate-100 dark:bg-[#181818] border border-slate-200/40 dark:border-slate-800 flex items-center justify-center font-black text-xs text-slate-600 dark:text-slate-400">
                          {ticket.restaurant?.name?.substring(0, 2).toUpperCase() || '?' }
                        </div>
                        <div>
                          <p className="text-sm font-black text-slate-800 dark:text-slate-100">{ticket.restaurant?.name || 'Unknown'}</p>
                          <p className="text-[10px] font-semibold text-slate-400 mt-0.5 flex items-center gap-1.5">
                            <span className="text-[#E53935] font-extrabold">{ticket.ticketCode || 'TKT-PENDING'}</span>
                            <span className="text-slate-300 dark:text-neutral-700">|</span>
                            <Phone className="w-3 h-3 text-slate-400" />
                            {ticket.restaurant?.phone || 'No phone'}
                          </p>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-5">
                      <p className="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate max-w-[240px]">{ticket.title}</p>
                      {ticket.description.includes('[REMOTE ACCESS GRANTED]') ? (
                        <span className="inline-flex items-center gap-1.5 mt-1.5 px-2.5 py-0.5 rounded-lg text-[9px] font-extrabold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:border-emerald-400/20 tracking-wider uppercase">
                          <Key className="w-3 h-3 animate-pulse" /> Remote access granted
                        </span>
                      ) : (
                        <p className="text-xs text-slate-400 truncate max-w-[240px] mt-0.5">{ticket.description}</p>
                      )}
                    </td>
                    <td className="px-6 py-5">
                      <span className={`inline-flex items-center px-3 py-1 rounded-full text-[10px] font-extrabold tracking-widest uppercase ${getStatusColor(ticket.status)}`}>
                        {ticket.status}
                      </span>
                    </td>
                    <td className="px-6 py-5">
                      <span className={`inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-extrabold tracking-wider uppercase ${getPriorityColor(ticket.priority)}`}>
                        {ticket.priority}
                      </span>
                    </td>
                    <td className="px-6 py-5 text-xs font-semibold text-slate-400">
                      {new Date(ticket.createdAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>


      {/* Ticket Details Panel */}
      <div className={`xl:col-span-1 ${selectedTicket ? "block animate-fadeIn" : "hidden xl:block"}`}>
        {selectedTicket ? (
          <div className="bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] p-6 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] sticky top-6">
            <button 
              onClick={() => setSelectedTicket(null)}
              className="xl:hidden mb-5 px-3 py-2 bg-slate-100 dark:bg-[#1a1a1a] hover:bg-slate-200 dark:hover:bg-[#252525] text-slate-500 dark:text-slate-400 rounded-xl flex items-center gap-2 text-xs font-bold transition-all"
            >
              &larr; Back to Tickets List
            </button>
            
            <div className="flex justify-between items-start gap-4 mb-5 pb-5 border-b border-slate-100 dark:border-[#222222]">
              <div>
                <span className={`inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider mb-2 ${getPriorityColor(selectedTicket.priority)}`}>
                  {selectedTicket.priority} Priority
                </span>
                <h2 className="text-lg font-black text-slate-800 dark:text-white leading-snug tracking-tight">{selectedTicket.title}</h2>
                <div className="flex items-center gap-1.5 mt-2">
                  <span className="px-2 py-0.5 bg-red-500/10 text-[#E53935] text-[10px] font-extrabold rounded-md">{selectedTicket.ticketCode || 'TKT-PENDING'}</span>
                  <p className="text-xs font-semibold text-slate-400">from <span className="font-bold text-slate-700 dark:text-slate-300">{selectedTicket.restaurant?.name}</span></p>
                </div>
              </div>
              
              <span className={`inline-flex items-center px-3 py-1 rounded-full text-[10px] font-extrabold tracking-widest uppercase shrink-0 ${getStatusColor(selectedTicket.status)}`}>
                {selectedTicket.status}
              </span>
            </div>

            {/* Client Context Details */}
            <div className="mb-6 space-y-3.5 bg-slate-50/50 dark:bg-[#141414]/50 border border-slate-200/40 dark:border-[#222222]/80 p-4.5 rounded-2xl">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1">
                <Shield className="w-3.5 h-3.5 text-slate-400" /> Restaurant Metadata
              </h3>
              
              <div className="grid grid-cols-1 gap-2.5 text-xs">
                <div className="flex items-center gap-2">
                  <User className="w-4 h-4 text-slate-400 shrink-0" />
                  <span className="text-slate-400">License Key:</span>
                  <span className="font-mono font-bold text-slate-700 dark:text-slate-300 truncate max-w-[140px] bg-slate-100 dark:bg-[#202020] px-1.5 py-0.5 rounded text-[10px]" title={selectedTicket.restaurant?.offlineLicenseKey}>
                    {selectedTicket.restaurant?.offlineLicenseKey || 'None'}
                  </span>
                </div>
                
                <div className="flex items-center gap-2">
                  <Phone className="w-4 h-4 text-slate-400 shrink-0" />
                  <span className="text-slate-400">Phone:</span>
                  <a href={`tel:${selectedTicket.restaurant?.phone}`} className="font-bold text-red-500 hover:underline">{selectedTicket.restaurant?.phone || 'Unknown'}</a>
                </div>
                
                <div className="flex items-start gap-2">
                  <MapPin className="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                  <span className="text-slate-400">Address:</span>
                  <span className="font-bold text-slate-700 dark:text-slate-300 leading-tight">{selectedTicket.restaurant?.address || 'Not specified'}</span>
                </div>
              </div>
            </div>

            {/* Staff Assignment & Activity Details */}
            <div className="mb-6 space-y-3.5 bg-slate-50/50 dark:bg-[#141414]/50 border border-slate-200/40 dark:border-[#222222]/80 p-4.5 rounded-2xl">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1">
                <User className="w-3.5 h-3.5 text-slate-400" /> Staff & Activity Log
              </h3>
              
              <div className="grid grid-cols-1 gap-2.5 text-xs text-slate-600 dark:text-slate-300">
                <div className="flex items-center gap-2">
                  <span className="text-slate-400">Assigned To:</span>
                  {selectedTicket.assignedTo ? (
                    <span className="font-bold text-slate-700 dark:text-slate-200">
                      {selectedTicket.assignedTo.email} <span className="text-[10px] bg-slate-200 dark:bg-[#202020] px-1.5 py-0.5 rounded text-slate-500 uppercase">{selectedTicket.assignedTo.role}</span>
                    </span>
                  ) : (
                    <span className="text-slate-400 italic">Unassigned</span>
                  )}
                </div>
                
                {selectedTicket.lastUpdatedBy && (
                  <div className="flex items-center gap-2">
                    <span className="text-slate-400">Last Action By:</span>
                    <span className="font-bold text-slate-700 dark:text-slate-200">
                      {selectedTicket.lastUpdatedBy.email} <span className="text-[10px] bg-slate-200 dark:bg-[#202020] px-1.5 py-0.5 rounded text-slate-500 uppercase">{selectedTicket.lastUpdatedBy.role}</span>
                    </span>
                  </div>
                )}

                {(selectedTicket.status === "RESOLVED" || selectedTicket.status === "CLOSED") && selectedTicket.resolvedBy && (
                  <div className="flex items-center gap-2">
                    <span className="text-slate-400">Resolved By:</span>
                    <span className="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                      {selectedTicket.resolvedBy.email} <span className="text-[10px] bg-emerald-500/10 text-emerald-500 px-1.5 py-0.5 rounded border border-emerald-500/20 uppercase">{selectedTicket.resolvedBy.role}</span>
                    </span>
                  </div>
                )}
              </div>
            </div>

            {/* Ticket Description */}
            <div className="mb-6">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                <Clock className="w-3.5 h-3.5 text-slate-400" /> Ticket Description
              </h3>
              <div className="p-4 bg-slate-50 dark:bg-[#161616] rounded-2xl border border-slate-200/40 dark:border-slate-800/80 min-h-[120px]">
                <p className="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap font-medium leading-relaxed">{selectedTicket.description}</p>
              </div>
            </div>

            {/* Action controls */}
            <div className="space-y-3 pt-2 border-t border-slate-100 dark:border-[#222222]">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Update Ticket Status</h3>
              
              <div className="grid grid-cols-2 gap-2">
                <button 
                  disabled={isUpdating || selectedTicket.status === "OPEN"}
                  onClick={() => updateStatus(selectedTicket.id, "OPEN")}
                  className={`py-2.5 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center gap-1.5 cursor-pointer ${
                    selectedTicket.status === "OPEN" 
                      ? "bg-amber-500/10 text-amber-500 border border-amber-500/20" 
                      : "bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2b2b2b] text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-[#252525] hover:text-slate-700 dark:hover:text-slate-200"
                  }`}
                >
                  <AlertTriangle className="w-3.5 h-3.5 shrink-0" />
                  <span>Open</span>
                </button>
                
                <button 
                  disabled={isUpdating || selectedTicket.status === "IN_PROGRESS"}
                  onClick={() => updateStatus(selectedTicket.id, "IN_PROGRESS")}
                  className={`py-2.5 px-3 rounded-xl text-xs font-bold transition-all duration-300 flex items-center justify-center gap-1.5 cursor-pointer ${
                    selectedTicket.status === "IN_PROGRESS" 
                      ? "bg-blue-500/10 text-blue-500 border border-blue-500/20" 
                      : "bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2b2b2b] text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-[#252525] hover:text-slate-700 dark:hover:text-slate-200"
                  }`}
                >
                  <Clock className="w-3.5 h-3.5 shrink-0" />
                  <span>In Progress</span>
                </button>
                
                <button 
                  disabled={isUpdating || selectedTicket.status === "RESOLVED"}
                  onClick={() => updateStatus(selectedTicket.id, "RESOLVED")}
                  className={`py-2.5 px-3 rounded-xl text-xs font-bold transition-all duration-300 col-span-2 flex items-center justify-center gap-1.5 cursor-pointer ${
                    selectedTicket.status === "RESOLVED" 
                      ? "bg-emerald-500/10 text-emerald-500 border border-emerald-500/20" 
                      : "bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2b2b2b] text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-[#252525] hover:text-slate-700 dark:hover:text-slate-200"
                  }`}
                >
                  <CheckCircle2 className="w-3.5 h-3.5 shrink-0" />
                  <span>Mark as Resolved</span>
                </button>
              </div>
            </div>
          </div>
        ) : (
          <div className="bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] p-6 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] flex flex-col items-center justify-center text-center h-[340px] xl:h-full">
            <div className="w-14 h-14 rounded-3xl bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/30 dark:border-[#2a2a2a] flex items-center justify-center text-slate-400 dark:text-slate-500 mb-4 animate-pulse">
              <Inbox className="w-7 h-7" />
            </div>
            <p className="text-sm font-bold text-slate-700 dark:text-slate-300">Select a Ticket</p>
            <p className="text-xs text-slate-400 max-w-[200px] mt-1 mx-auto leading-relaxed">Choose a ticket from the left queue list to read metadata and update status.</p>
          </div>
        )}
      </div>
    </div>
  );
}
