"use client";

import { useState } from "react";
import { Mail, Trash2, Inbox, Clock, User, MessageSquare, Search, Send, ArrowLeft, Settings, Phone, MapPin, Globe, MessageCircle } from "lucide-react";
import toast from "react-hot-toast";

export default function ContactClient({ 
  initialSubmissions, 
  initialSettings = {} 
}: { 
  initialSubmissions: any[]; 
  initialSettings?: Record<string, string>;
}) {
  const [submissions, setSubmissions] = useState(initialSubmissions);
  const [selectedSubmission, setSelectedSubmission] = useState<any | null>(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [isDeleting, setIsDeleting] = useState(false);
  
  // Tab and Settings state
  const [activeTab, setActiveTab] = useState<"messages" | "settings">("messages");
  const [whatsappNumber, setWhatsappNumber] = useState(initialSettings.whatsapp_number || "9779865029558");
  const [contactPhone, setContactPhone] = useState(initialSettings.contact_phone || "+977 9865029558");
  const [contactEmail, setContactEmail] = useState(initialSettings.contact_email || "info@drestro.com");
  const [contactLocation, setContactLocation] = useState(initialSettings.contact_location || "Thamel, Kathmandu\nBagmati Province, Nepal");
  const [contactMapIframe, setContactMapIframe] = useState(initialSettings.contact_map_iframe || "");
  const [isSaving, setIsSaving] = useState(false);

  const handleSaveSettings = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    const toastId = toast.loading("Saving settings...");
    try {
      const res = await fetch("/api/admin/settings", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          whatsapp_number: whatsappNumber,
          contact_phone: contactPhone,
          contact_email: contactEmail,
          contact_location: contactLocation,
          contact_map_iframe: contactMapIframe,
        }),
      });

      if (res.ok) {
        toast.success("Contact settings saved successfully", { id: toastId });
      } else {
        const errData = await res.json();
        toast.error(errData.error || "Failed to save settings", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    } finally {
      setIsSaving(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this contact submission?")) return;
    
    setIsDeleting(true);
    const toastId = toast.loading("Deleting submission...");
    try {
      const res = await fetch(`/api/admin/contact?id=${id}`, {
        method: "DELETE",
      });

      if (res.ok) {
        toast.success("Submission deleted", { id: toastId });
        setSubmissions(submissions.filter(s => s.id !== id));
        if (selectedSubmission && selectedSubmission.id === id) {
          setSelectedSubmission(null);
        }
      } else {
        toast.error("Failed to delete submission", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    } finally {
      setIsDeleting(false);
    }
  };

  const [dateFilter, setDateFilter] = useState<string>("all");
  const [customStartDate, setCustomStartDate] = useState<string>("");
  const [customEndDate, setCustomEndDate] = useState<string>("");

  const filteredSubmissions = submissions.filter(s => {
    // 1. Text Search Filter
    const q = searchQuery.toLowerCase();
    const matchesSearch = s.name.toLowerCase().includes(q) ||
      s.email.toLowerCase().includes(q) ||
      (s.phone && s.phone.includes(q)) ||
      s.subject.toLowerCase().includes(q) ||
      s.message.toLowerCase().includes(q);

    if (!matchesSearch) return false;

    // 2. Date Filter
    if (dateFilter === "all") return true;
    const itemDate = new Date(s.createdAt);
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
      if (customStartDate && new Date(s.createdAt) < new Date(customStartDate)) return false;
      if (customEndDate) {
        const end = new Date(customEndDate);
        end.setHours(23, 59, 59, 999);
        if (new Date(s.createdAt) > end) return false;
      }
    }
    return true;
  });

  return (
    <div className="space-y-6">
      {/* Tab Switcher */}
      <div className="flex border-b border-slate-200 dark:border-[#222222]">
        <button
          onClick={() => setActiveTab("messages")}
          className={`pb-3 text-sm font-bold border-b-2 transition-all mr-6 flex items-center gap-2 ${
            activeTab === "messages"
              ? "border-red-500 text-red-500"
              : "border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
          }`}
        >
          <Mail className="w-4 h-4" />
          Messages Queue ({submissions.length})
        </button>
        <button
          onClick={() => setActiveTab("settings")}
          className={`pb-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2 ${
            activeTab === "settings"
              ? "border-red-500 text-red-500"
              : "border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
          }`}
        >
          <Settings className="w-4 h-4" />
          Contact & Map Settings
        </button>
      </div>

      {activeTab === "messages" ? (
        <div className="grid grid-cols-1 xl:grid-cols-3 gap-8 min-h-[calc(100vh-140px)]">
      {/* Submissions List */}
      <div className={`xl:col-span-2 bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] overflow-hidden flex flex-col ${selectedSubmission ? "hidden xl:flex" : "flex"}`}>
        
        {/* Search and Header */}
        <div className="p-6 bg-slate-50/50 dark:bg-[#141414]/50 border-b border-slate-200/60 dark:border-[#222222]">
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h2 className="text-lg font-black text-slate-800 dark:text-white tracking-tight">Messages Queue</h2>
              <p className="text-xs text-slate-400 mt-0.5">Inbox for all messages sent via the contact page.</p>
            </div>
            
            <div className="flex flex-wrap items-center gap-3 w-full md:w-auto">
              <div className="relative flex-1 md:w-56">
                <span className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <Search className="h-4 w-4 text-slate-400" />
                </span>
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Search name, email, text..."
                  className="w-full pl-9 pr-4 py-2 text-xs font-medium bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2a2a2a]/50 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-red-500"
                />
              </div>

              {/* Date Filter Dropdown */}
              <div className="flex items-center gap-2">
                <select
                  value={dateFilter}
                  onChange={(e) => setDateFilter(e.target.value)}
                  className="px-3 py-2 text-xs font-bold bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/50 dark:border-[#2a2a2a]/50 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-red-500 cursor-pointer"
                >
                  <option value="all">📅 All Time</option>
                  <option value="today">Today</option>
                  <option value="yesterday">Yesterday</option>
                  <option value="7days">Last 7 Days</option>
                  <option value="30days">Last 30 Days</option>
                  <option value="custom">Custom Date Range...</option>
                </select>

                {dateFilter === "custom" && (
                  <div className="flex items-center gap-1.5 animate-in fade-in duration-200">
                    <input
                      type="date"
                      value={customStartDate}
                      onChange={(e) => setCustomStartDate(e.target.value)}
                      className="px-2 py-1.5 text-xs bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] rounded-lg text-slate-800 dark:text-white focus:outline-none"
                    />
                    <span className="text-xs text-slate-400">to</span>
                    <input
                      type="date"
                      value={customEndDate}
                      onChange={(e) => setCustomEndDate(e.target.value)}
                      className="px-2 py-1.5 text-xs bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200 dark:border-[#2a2a2a] rounded-lg text-slate-800 dark:text-white focus:outline-none"
                    />
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Submissions Table / List */}
        <div className="flex-1 overflow-y-auto">
          {filteredSubmissions.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 px-6">
              <div className="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/30 dark:border-[#2a2a2a] flex items-center justify-center text-slate-400 mb-3">
                <Inbox className="w-6 h-6" />
              </div>
              <p className="text-sm font-bold text-slate-600 dark:text-slate-300">No submissions found</p>
              <p className="text-xs text-slate-400 mt-1">There are no messages matching your search or inbox is empty.</p>
            </div>
          ) : (
            <div className="divide-y divide-slate-100/60 dark:divide-[#222222]/80">
              {filteredSubmissions.map((sub) => (
                <div 
                  key={sub.id} 
                  onClick={() => setSelectedSubmission(sub)}
                  className={`p-6 flex items-start gap-4 hover:bg-slate-50/50 dark:hover:bg-[#151515]/30 cursor-pointer transition-all duration-200 border-l-4 ${selectedSubmission?.id === sub.id ? "bg-slate-50/80 dark:bg-[#151515]/50 border-l-red-500" : "border-l-transparent"}`}
                >
                  <div className="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-[#181818] border border-slate-200/40 dark:border-slate-800 flex items-center justify-center font-black text-xs text-slate-600 dark:text-slate-400 shrink-0 select-none">
                    {sub.name.substring(0, 2).toUpperCase()}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center justify-between gap-2">
                      <div className="flex items-center gap-2">
                        <h3 className="text-sm font-black text-slate-800 dark:text-slate-100 truncate">{sub.name}</h3>
                        <span className="px-1.5 py-0.5 bg-red-500/10 text-[#E53935] text-[9px] font-extrabold rounded-md">{sub.submissionCode || 'MSG-PENDING'}</span>
                      </div>
                      <span className="text-[10px] font-semibold text-slate-400 whitespace-nowrap">
                        {new Date(sub.createdAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}
                      </span>
                    </div>
                    <p className="text-xs font-semibold text-red-500/80 truncate mt-0.5">{sub.subject}</p>
                    <p className="text-xs text-slate-400 dark:text-neutral-400 line-clamp-2 mt-1.5 leading-relaxed">{sub.message}</p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Submission Details Panel */}
      <div className={`xl:col-span-1 ${selectedSubmission ? "block animate-fadeIn" : "hidden xl:block"}`}>
        {selectedSubmission ? (
          <div className="bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] p-6 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] sticky top-6">
            <button 
              onClick={() => setSelectedSubmission(null)}
              className="xl:hidden mb-5 px-3 py-2 bg-slate-100 dark:bg-[#1a1a1a] hover:bg-slate-200 dark:hover:bg-[#252525] text-slate-500 dark:text-slate-400 rounded-xl flex items-center gap-2 text-xs font-bold transition-all"
            >
              <ArrowLeft size={14} /> Back to Messages
            </button>
            
            <div className="flex justify-between items-start gap-4 mb-5 pb-5 border-b border-slate-100 dark:border-[#222222]">
              <div>
                <div className="flex items-center gap-2 mb-2">
                  <span className="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider bg-red-500/10 text-red-500 border border-red-500/20">
                    Inbox Message
                  </span>
                  <span className="px-2 py-0.5 bg-red-500/10 text-[#E53935] text-[9px] font-extrabold rounded-lg">{selectedSubmission.submissionCode || 'MSG-PENDING'}</span>
                </div>
                <h2 className="text-lg font-black text-slate-800 dark:text-white leading-snug tracking-tight">{selectedSubmission.subject}</h2>
                <div className="flex items-center gap-1.5 mt-2">
                  <User className="w-3.5 h-3.5 text-slate-400" />
                  <p className="text-xs font-semibold text-slate-400">From <span className="font-bold text-slate-700 dark:text-slate-300">{selectedSubmission.name}</span></p>
                </div>
              </div>
            </div>

            {/* Submitter Metadata */}
            <div className="mb-6 space-y-3 bg-slate-50/50 dark:bg-[#141414]/50 border border-slate-200/40 dark:border-[#222222]/80 p-4.5 rounded-2xl">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1">
                <Mail className="w-3.5 h-3.5 text-slate-400" /> Sender Information
              </h3>
              
              <div className="grid grid-cols-1 gap-2.5 text-xs">
                <div className="flex items-center gap-2">
                  <span className="text-slate-400 shrink-0">Email:</span>
                  <a href={`mailto:${selectedSubmission.email}`} className="font-bold text-red-500 hover:underline truncate" title={selectedSubmission.email}>
                    {selectedSubmission.email}
                  </a>
                </div>
                <div className="flex items-center gap-2">
                  <span className="text-slate-400 shrink-0">Phone:</span>
                  {selectedSubmission.phone ? (
                    <a href={`tel:${selectedSubmission.phone}`} className="font-bold text-slate-800 dark:text-white hover:text-red-500 transition-colors flex items-center gap-1">
                      <span>📞</span> {selectedSubmission.phone}
                    </a>
                  ) : (
                    <span className="text-slate-400 italic">Not provided</span>
                  )}
                </div>
                <div className="flex items-center gap-2">
                  <span className="text-slate-400 shrink-0">Submitted:</span>
                  <span className="font-bold text-slate-700 dark:text-slate-300">
                    {new Date(selectedSubmission.createdAt).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })} on {new Date(selectedSubmission.createdAt).toLocaleDateString()}
                  </span>
                </div>
              </div>
            </div>

            {/* Message Body */}
            <div className="mb-6">
              <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                <MessageSquare className="w-3.5 h-3.5 text-slate-400" /> Message Text
              </h3>
              <div className="p-4 bg-slate-50 dark:bg-[#161616] rounded-2xl border border-slate-200/40 dark:border-slate-800/80 min-h-[140px] max-h-[300px] overflow-y-auto">
                <p className="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap font-medium leading-relaxed">{selectedSubmission.message}</p>
              </div>
            </div>

            {/* Actions */}
            <div className="space-y-3 pt-4 border-t border-slate-100 dark:border-[#222222]">
              <a 
                href={`mailto:${selectedSubmission.email}?subject=Re: ${selectedSubmission.subject}`}
                className="w-full bg-[#E53935] hover:bg-red-700 text-[#111111] dark:text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-[0_8px_20px_rgba(229,57,53,0.2)] active:scale-[0.98] flex items-center justify-center gap-2"
              >
                <Send className="w-4 h-4" />
                <span>Reply by Email</span>
              </a>

              <button 
                disabled={isDeleting}
                onClick={() => handleDelete(selectedSubmission.id)}
                className="w-full bg-slate-100 hover:bg-red-500/10 dark:bg-[#202020] dark:hover:bg-red-500/15 border border-slate-200/50 dark:border-[#2b2b2b] text-slate-600 dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 py-3.5 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
              >
                <Trash2 className="w-4 h-4 shrink-0" />
                <span>Delete Submission</span>
              </button>
            </div>
          </div>
        ) : (
          <div className="bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] p-6 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] flex flex-col items-center justify-center text-center h-[340px] xl:h-full">
            <div className="w-14 h-14 rounded-3xl bg-slate-100 dark:bg-[#1a1a1a] border border-slate-200/30 dark:border-[#2a2a2a] flex items-center justify-center text-slate-400 dark:text-slate-500 mb-4 animate-pulse">
              <Mail className="w-7 h-7" />
            </div>
            <p className="text-sm font-bold text-slate-700 dark:text-slate-300">Select a Message</p>
            <p className="text-xs text-slate-400 max-w-[200px] mt-1 mx-auto leading-relaxed">Choose a submission from the list to view its complete details and reply.</p>
          </div>
        )}
      </div>
      </div>
      ) : (
        <div className="bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/60 dark:border-[#222222] p-6 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.03)] dark:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.2)] max-w-4xl">
          <form onSubmit={handleSaveSettings} className="space-y-6">
            <div className="border-b border-slate-100 dark:border-[#222222] pb-4">
              <h2 className="text-lg font-bold text-[#111111] dark:text-white">Contact Page Settings</h2>
              <p className="text-xs text-slate-500 dark:text-gray-400 mt-0.5">Customize the contact details and map embed shown on the public contact page.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Phone */}
              <div className="space-y-2">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                  <Phone className="w-3.5 h-3.5 text-slate-400" /> Phone Number
                </label>
                <input
                  type="text"
                  value={contactPhone}
                  onChange={(e) => setContactPhone(e.target.value)}
                  className="w-full bg-slate-50 dark:bg-[#161616] border border-slate-200 dark:border-[#2b2b2b] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 text-slate-800 dark:text-slate-100 font-medium transition-all"
                  placeholder="+977 9865029558"
                  required
                />
              </div>

              {/* Email */}
              <div className="space-y-2">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                  <Mail className="w-3.5 h-3.5 text-slate-400" /> Email Address
                </label>
                <input
                  type="email"
                  value={contactEmail}
                  onChange={(e) => setContactEmail(e.target.value)}
                  className="w-full bg-slate-50 dark:bg-[#161616] border border-slate-200 dark:border-[#2b2b2b] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 text-slate-800 dark:text-slate-100 font-medium transition-all"
                  placeholder="info@drestro.com"
                  required
                />
              </div>

              {/* WhatsApp */}
              <div className="space-y-2">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                  <MessageCircle className="w-3.5 h-3.5 text-slate-400" /> WhatsApp Number (including country code)
                </label>
                <input
                  type="text"
                  value={whatsappNumber}
                  onChange={(e) => setWhatsappNumber(e.target.value)}
                  className="w-full bg-slate-50 dark:bg-[#161616] border border-slate-200 dark:border-[#2b2b2b] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 text-slate-800 dark:text-slate-100 font-medium transition-all"
                  placeholder="9779865029558"
                  required
                />
              </div>

              {/* Location */}
              <div className="space-y-2 md:col-span-2">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                  <MapPin className="w-3.5 h-3.5 text-slate-400" /> Physical Address
                </label>
                <textarea
                  value={contactLocation}
                  onChange={(e) => setContactLocation(e.target.value)}
                  rows={3}
                  className="w-full bg-slate-50 dark:bg-[#161616] border border-slate-200 dark:border-[#2b2b2b] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 text-slate-800 dark:text-slate-100 font-medium transition-all resize-none"
                  placeholder="Thamel, Kathmandu"
                  required
                />
              </div>

              {/* Google Map Embed Code */}
              <div className="space-y-2 md:col-span-2">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                  <Globe className="w-3.5 h-3.5 text-slate-400" /> Google Map Embed (Iframe Code or URL)
                </label>
                <textarea
                  value={contactMapIframe}
                  onChange={(e) => setContactMapIframe(e.target.value)}
                  rows={4}
                  className="w-full bg-slate-50 dark:bg-[#161616] border border-slate-200 dark:border-[#2b2b2b] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 text-slate-800 dark:text-slate-100 font-mono text-xs transition-all"
                  placeholder='e.g. <iframe src="https://www.google.com/maps/embed?pb=..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>'
                />
                <p className="text-[11px] text-slate-400 dark:text-gray-500">Go to Google Maps &rarr; Share &rarr; Embed a map, copy the HTML code, and paste it here.</p>
              </div>
            </div>

            <div className="pt-4 border-t border-slate-100 dark:border-[#222222] flex justify-end">
              <button
                type="submit"
                disabled={isSaving}
                className="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-[0_4px_12px_rgba(239,68,68,0.2)] active:scale-[0.98] disabled:opacity-50"
              >
                {isSaving ? "Saving..." : "Save Changes"}
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
