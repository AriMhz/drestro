"use client";

import { useState, useEffect } from "react";
import { Save, Loader2, Link as LinkIcon, FileText, Phone } from "lucide-react";
import toast from "react-hot-toast";

export default function AdminFooterPage() {
  const [settings, setSettings] = useState({
    facebook_url: "",
    twitter_url: "",
    instagram_url: "",
    linkedin_url: "",
    whatsapp_number: "",
    contact_location: "",
    contact_phone: "",
    contact_email: "",
    contact_map_iframe: "",
    privacy_policy: "",
    terms_conditions: "",
    refund_policy: ""
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetch('/api/admin/settings')
      .then(res => res.json())
      .then(data => {
        if (!data.error) {
          setSettings(prev => ({ ...prev, ...data }));
        }
      })
      .catch(() => toast.error("Failed to load settings"))
      .finally(() => setLoading(false));
  }, []);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    const toastId = toast.loading("Saving settings...");

    try {
      const res = await fetch('/api/admin/settings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings)
      });

      if (res.ok) {
        toast.success("Settings saved successfully!", { id: toastId });
      } else {
        toast.error("Failed to save settings", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-[60vh]">
        <Loader2 className="animate-spin text-slate-500 dark:text-gray-400" size={32} />
      </div>
    );
  }

  return (
    <div className="p-6 max-w-5xl mx-auto pb-24">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-[#111111] dark:text-white">Footer & Policies</h1>
          <p className="text-slate-400 dark:text-gray-500 text-sm mt-1">Manage social links, contact info, and legal pages.</p>
        </div>
        <button 
          onClick={handleSave}
          disabled={saving}
          className="flex items-center gap-2 px-6 py-2.5 bg-black text-[#111111] dark:text-white rounded-lg hover:bg-gray-800 transition-colors shadow-sm disabled:opacity-50"
        >
          {saving ? <Loader2 size={18} className="animate-spin" /> : <Save size={18} />}
          <span className="font-medium">Save Changes</span>
        </button>
      </div>

      <div className="space-y-8">
        {/* Contact Information */}
        <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-gray-200 dark:border-[#333333] overflow-hidden">
          <div className="bg-gray-50 dark:bg-[#222222] px-6 py-4 border-b border-gray-200 dark:border-[#333333] flex items-center gap-2">
            <Phone size={18} className="text-slate-400 dark:text-gray-500" />
            <h2 className="text-lg font-semibold text-gray-800 dark:text-white">Contact Information</h2>
          </div>
          <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">WhatsApp Number</label>
              <input 
                type="text" name="whatsapp_number" value={settings.whatsapp_number} onChange={handleChange}
                placeholder="e.g. +977 9865029558"
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
              <p className="text-xs text-slate-500 dark:text-gray-500 mt-1">Used for the footer WhatsApp button.</p>
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Contact Phone</label>
              <input 
                type="text" name="contact_phone" value={settings.contact_phone} onChange={handleChange}
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Contact Email</label>
              <input 
                type="email" name="contact_email" value={settings.contact_email} onChange={handleChange}
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Location</label>
              <input 
                type="text" name="contact_location" value={settings.contact_location} onChange={handleChange}
                placeholder="e.g. Kathmandu, Nepal"
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Google Maps Embed Map (iframe HTML or URL)</label>
              <textarea 
                name="contact_map_iframe" value={settings.contact_map_iframe || ""} onChange={handleChange}
                placeholder="Paste Google Maps <iframe> code or embed URL here..."
                rows={3}
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 font-mono text-xs"
              />
              <p className="text-xs text-slate-500 dark:text-gray-500 mt-1">
                Go to Google Maps → Share → Embed a map, copy the HTML, and paste it here. Or just enter the embed URL.
              </p>
            </div>
          </div>
        </div>

        {/* Social Links */}
        <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-gray-200 dark:border-[#333333] overflow-hidden">
          <div className="bg-gray-50 dark:bg-[#222222] px-6 py-4 border-b border-gray-200 dark:border-[#333333] flex items-center gap-2">
            <LinkIcon size={18} className="text-slate-400 dark:text-gray-500" />
            <h2 className="text-lg font-semibold text-gray-800 dark:text-white">Social Links</h2>
          </div>
          <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Facebook URL</label>
              <input 
                type="url" name="facebook_url" value={settings.facebook_url} onChange={handleChange}
                placeholder="https://facebook.com/..."
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Instagram URL</label>
              <input 
                type="url" name="instagram_url" value={settings.instagram_url} onChange={handleChange}
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Twitter (X) URL</label>
              <input 
                type="url" name="twitter_url" value={settings.twitter_url} onChange={handleChange}
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">LinkedIn URL</label>
              <input 
                type="url" name="linkedin_url" value={settings.linkedin_url} onChange={handleChange}
                className="w-full px-4 py-2 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
          </div>
        </div>

        {/* Policies */}
        <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-gray-200 dark:border-[#333333] overflow-hidden">
          <div className="bg-gray-50 dark:bg-[#222222] px-6 py-4 border-b border-gray-200 dark:border-[#333333] flex items-center gap-2">
            <FileText size={18} className="text-slate-400 dark:text-gray-500" />
            <h2 className="text-lg font-semibold text-gray-800 dark:text-white">Legal Policies</h2>
          </div>
          <div className="p-6 space-y-6">
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Privacy Policy</label>
              <textarea 
                name="privacy_policy" 
                value={settings.privacy_policy} 
                onChange={handleChange}
                rows={8}
                className="w-full px-4 py-3 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 font-mono text-sm leading-relaxed resize-y"
                placeholder="Enter privacy policy text (supports plain text and simple line breaks)..."
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Terms & Conditions</label>
              <textarea 
                name="terms_conditions" 
                value={settings.terms_conditions} 
                onChange={handleChange}
                rows={8}
                className="w-full px-4 py-3 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 font-mono text-sm leading-relaxed resize-y"
                placeholder="Enter terms and conditions text..."
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Refund Policy</label>
              <textarea 
                name="refund_policy" 
                value={settings.refund_policy || ""} 
                onChange={handleChange}
                rows={8}
                className="w-full px-4 py-3 bg-white dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 font-mono text-sm leading-relaxed resize-y"
                placeholder="Enter refund policy text..."
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
