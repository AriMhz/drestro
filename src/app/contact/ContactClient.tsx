"use client";

import { MapPin, Phone, Mail, Clock, MessageSquare, ArrowRight } from 'lucide-react';
import { motion } from 'motion/react';
import { useState } from 'react';
import toast from 'react-hot-toast';

interface ContactSettings {
  whatsapp_number?: string;
  contact_phone?: string;
  contact_email?: string;
  contact_location?: string;
  contact_map_iframe?: string;
}

export default function ContactClient({ contactSettings }: { contactSettings: ContactSettings }) {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    subject: "",
    message: ""
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name || !formData.email || !formData.subject || !formData.message) {
      toast.error("Please fill in all fields.");
      return;
    }

    setIsSubmitting(true);
    const toastId = toast.loading("Sending message...");
    try {
      const res = await fetch("/api/contact", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });

      if (res.ok) {
        toast.success("Message sent successfully!", { id: toastId });
        setFormData({ name: "", email: "", phone: "", subject: "", message: "" });
      } else {
        const data = await res.json();
        toast.error(data.error || "Failed to send message.", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred. Please try again.", { id: toastId });
    } finally {
      setIsSubmitting(false);
    }
  };

  // Extract iframe src from code embed or accept plain URL
  const getMapSrc = (embedInput?: string) => {
    if (!embedInput) return "";
    const trimmed = embedInput.trim();
    if (trimmed.startsWith("http")) return trimmed;
    const match = trimmed.match(/src="([^"]+)"/);
    return match ? match[1] : "";
  };

  const mapSrc = getMapSrc(contactSettings.contact_map_iframe);
  const phoneVal = contactSettings.contact_phone || "+977 9865029558";
  let emailVal = contactSettings.contact_email || "info@drestro.com";
  if (emailVal) {
    emailVal = emailVal.trim();
    if ((emailVal.startsWith('"') && emailVal.endsWith('"')) || (emailVal.startsWith("'") && emailVal.endsWith("'"))) {
      emailVal = emailVal.slice(1, -1).trim();
    }
  }
  const locationVal = contactSettings.contact_location || "Thamel, Kathmandu\nBagmati Province, Nepal";
  const whatsappVal = contactSettings.whatsapp_number || "9779865029558";
  
  // Clean phone / whatsapp values for anchor tags
  const cleanPhone = phoneVal.replace(/[^0-9+]/g, '');
  const cleanWhatsapp = whatsappVal.replace(/[^0-9]/g, '');

  return (
    <div className="bg-slate-50 dark:bg-[#0a0a0a] min-h-screen pt-12 pb-24 text-neutral-900 dark:text-white transition-colors duration-300">
      <div className="max-w-6xl mx-auto px-6 lg:px-8">
        
        {/* Header Hero */}
        <div className="text-center max-w-3xl mx-auto mb-16 pt-8 relative">
          {/* Soft background glow */}
          <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[350px] h-[120px] bg-[#E53935]/5 rounded-full blur-[80px] pointer-events-none"></div>
          
          <span className="text-[#E53935] text-xs font-bold uppercase tracking-widest bg-red-500/10 border border-red-500/20 px-3.5 py-1.5 rounded-full">
            Connect With Us
          </span>
          <h1 className="text-4xl md:text-5xl font-black text-gray-950 dark:text-white tracking-tight mt-5 mb-6">
            Get in Touch
          </h1>
          <p className="text-lg text-neutral-500 dark:text-neutral-400 leading-relaxed max-w-xl mx-auto">
            Have questions about pricing, hardware setup, or subnet offline sync? Our dedicated Nepalese team is here to assist.
          </p>
        </div>

        <div className="grid lg:grid-cols-12 gap-8 items-stretch mb-16">
          
          {/* Left Column: Direct Info Cards (Span 5) */}
          <div className="lg:col-span-5 flex flex-col gap-5 justify-between">
            
            {/* Card 1: Call Us */}
            <motion.div 
              initial={{ opacity: 0, y: 15 }} 
              animate={{ opacity: 1, y: 0 }} 
              className="bg-white dark:bg-[#111111] rounded-3xl p-6 border border-neutral-200 dark:border-neutral-800/80 shadow-[0_4px_20px_rgba(0,0,0,0.01)] hover:border-[#E53935]/30 hover:shadow-lg transition-all duration-300 flex items-start space-x-4 flex-1"
            >
              <div className="w-11 h-11 bg-red-50 dark:bg-red-500/10 rounded-2xl flex items-center justify-center shrink-0 border border-red-100 dark:border-red-500/20 shadow-sm">
                <Phone className="text-[#E53935] w-5 h-5" />
              </div>
              <div>
                <h3 className="font-extrabold text-gray-950 dark:text-white text-base mb-1">Call Us Support</h3>
                <p className="text-xs text-neutral-500 dark:text-neutral-400 mb-3 leading-relaxed">Mon-Sat from 9am to 6pm.</p>
                <a href={`tel:${cleanPhone}`} className="text-[#E53935] font-black text-sm tracking-wide hover:opacity-85 hover:underline flex items-center gap-1.5">
                  {phoneVal}
                  <ArrowRight size={13} />
                </a>
              </div>
            </motion.div>

            {/* Card 2: WhatsApp */}
            <motion.div 
              initial={{ opacity: 0, y: 15 }} 
              animate={{ opacity: 1, y: 0 }} 
              transition={{ delay: 0.08 }} 
              className="bg-white dark:bg-[#111111] rounded-3xl p-6 border border-neutral-200 dark:border-neutral-800/80 shadow-[0_4px_20px_rgba(0,0,0,0.01)] hover:border-[#E53935]/30 hover:shadow-lg transition-all duration-300 flex items-start space-x-4 flex-1"
            >
              <div className="w-11 h-11 bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-500/20 shadow-sm">
                <MessageSquare className="text-emerald-500 w-5 h-5" />
              </div>
              <div>
                <h3 className="font-extrabold text-gray-950 dark:text-white text-base mb-1">WhatsApp Live Chat</h3>
                <p className="text-xs text-neutral-500 dark:text-neutral-400 mb-3 leading-relaxed">Fastest way to get dynamic subnet support.</p>
                <a href={`https://wa.me/${cleanWhatsapp}`} target="_blank" rel="noopener noreferrer" className="text-emerald-500 dark:text-emerald-400 font-black text-sm tracking-wide hover:opacity-85 hover:underline flex items-center gap-1.5">
                  Message Us Live
                  <ArrowRight size={13} />
                </a>
              </div>
            </motion.div>

            {/* Card 3: Email */}
            <motion.div 
              initial={{ opacity: 0, y: 15 }} 
              animate={{ opacity: 1, y: 0 }} 
              transition={{ delay: 0.16 }} 
              className="bg-white dark:bg-[#111111] rounded-3xl p-6 border border-neutral-200 dark:border-neutral-800/80 shadow-[0_4px_20px_rgba(0,0,0,0.01)] hover:border-[#E53935]/30 hover:shadow-lg transition-all duration-300 flex items-start space-x-4 flex-1"
            >
              <div className="w-11 h-11 bg-red-50 dark:bg-red-500/10 rounded-2xl flex items-center justify-center shrink-0 border border-red-100 dark:border-red-500/20 shadow-sm">
                <Mail className="text-[#E53935] w-5 h-5" />
              </div>
              <div>
                <h3 className="font-extrabold text-gray-950 dark:text-white text-base mb-1">Email Inquiries</h3>
                <p className="text-xs text-neutral-500 dark:text-neutral-400 mb-3 leading-relaxed">We typically reply within 2 working hours.</p>
                <a href={`mailto:${emailVal}`} className="text-[#E53935] font-black text-sm tracking-wide hover:opacity-85 hover:underline flex items-center gap-1.5">
                  {emailVal}
                  <ArrowRight size={13} />
                </a>
              </div>
            </motion.div>

            {/* Card 4: Office Location */}
            <motion.div 
              initial={{ opacity: 0, y: 15 }} 
              animate={{ opacity: 1, y: 0 }} 
              transition={{ delay: 0.24 }} 
              className="bg-white dark:bg-[#111111] rounded-3xl p-6 border border-neutral-200 dark:border-neutral-800/80 shadow-[0_4px_20px_rgba(0,0,0,0.01)] hover:border-[#E53935]/30 hover:shadow-lg transition-all duration-300 flex items-start space-x-4 flex-1"
            >
              <div className="w-11 h-11 bg-red-50 dark:bg-red-500/10 rounded-2xl flex items-center justify-center shrink-0 border border-red-100 dark:border-red-500/20 shadow-sm">
                <MapPin className="text-[#E53935] w-5 h-5" />
              </div>
              <div>
                <h3 className="font-extrabold text-gray-950 dark:text-white text-base mb-1">Corporate HQ</h3>
                <p className="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed font-semibold whitespace-pre-line">
                  {locationVal}
                </p>
              </div>
            </motion.div>

          </div>

          {/* Right Column: Premium Contact Form (Span 7) */}
          <div className="lg:col-span-7 flex">
            <motion.div 
              initial={{ opacity: 0, scale: 0.98 }} 
              animate={{ opacity: 1, scale: 1 }} 
              className="bg-white dark:bg-[#111111] rounded-3xl p-8 md:p-10 shadow-sm border border-neutral-200 dark:border-neutral-800/80 flex flex-col justify-between w-full"
            >
              <div>
                <h2 className="text-2xl font-black text-gray-950 dark:text-white tracking-tight mb-6">
                  Send us a message
                </h2>
                
                <form onSubmit={handleSubmit} className="space-y-5">
                  <div className="grid md:grid-cols-2 gap-5">
                    <div>
                      <label className="block text-xs font-black uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-1.5">Name</label>
                      <input 
                        type="text" 
                        required
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        className="w-full bg-neutral-50 dark:bg-[#1a1a1a] border border-neutral-200 dark:border-neutral-800/80 focus:border-[#E53935] text-neutral-900 dark:text-white rounded-xl py-3 px-4 focus:ring-1 focus:ring-[#E53935]/20 focus:outline-none transition-all placeholder-neutral-400 dark:placeholder-neutral-600 text-sm font-semibold" 
                        placeholder="Jane Doe" 
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-black uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-1.5">Email</label>
                      <input 
                        type="email" 
                        required
                        value={formData.email}
                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                        className="w-full bg-neutral-50 dark:bg-[#1a1a1a] border border-neutral-200 dark:border-neutral-800/80 focus:border-[#E53935] text-neutral-900 dark:text-white rounded-xl py-3 px-4 focus:ring-1 focus:ring-[#E53935]/20 focus:outline-none transition-all placeholder-neutral-400 dark:placeholder-neutral-600 text-sm font-semibold" 
                        placeholder="jane@example.com" 
                      />
                    </div>
                  </div>
                  
                  <div className="grid md:grid-cols-2 gap-5">
                    <div>
                      <label className="block text-xs font-black uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-1.5">Phone Number</label>
                      <input 
                        type="tel" 
                        value={formData.phone}
                        onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                        className="w-full bg-neutral-50 dark:bg-[#1a1a1a] border border-neutral-200 dark:border-neutral-800/80 focus:border-[#E53935] text-neutral-900 dark:text-white rounded-xl py-3 px-4 focus:ring-1 focus:ring-[#E53935]/20 focus:outline-none transition-all placeholder-neutral-400 dark:placeholder-neutral-600 text-sm font-semibold" 
                        placeholder="e.g. 98XXXXXXXX / +977..." 
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-black uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-1.5">Subject</label>
                      <input 
                        type="text" 
                        required
                        value={formData.subject}
                        onChange={(e) => setFormData({ ...formData, subject: e.target.value })}
                        className="w-full bg-neutral-50 dark:bg-[#1a1a1a] border border-neutral-200 dark:border-neutral-800/80 focus:border-[#E53935] text-neutral-900 dark:text-white rounded-xl py-3 px-4 focus:ring-1 focus:ring-[#E53935]/20 focus:outline-none transition-all placeholder-neutral-400 dark:placeholder-neutral-600 text-sm font-semibold" 
                        placeholder="How can we help?" 
                      />
                    </div>
                  </div>
                  <div>
                    <label className="block text-xs font-black uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-1.5">Message</label>
                    <textarea 
                      rows={5} 
                      required
                      value={formData.message}
                      onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                      className="w-full bg-neutral-50 dark:bg-[#1a1a1a] border border-neutral-200 dark:border-neutral-800/80 focus:border-[#E53935] text-neutral-900 dark:text-white rounded-xl py-3 px-4 focus:ring-1 focus:ring-[#E53935]/20 focus:outline-none transition-all placeholder-neutral-400 dark:placeholder-neutral-600 text-sm font-semibold resize-none" 
                      placeholder="Tell us more about your inquiry..."
                    ></textarea>
                  </div>
                  
                  <div className="pt-2 flex justify-end">
                    <button 
                      type="submit" 
                      disabled={isSubmitting}
                      className="bg-[#E53935] hover:bg-red-600 disabled:bg-red-500/50 text-white font-extrabold py-3.5 px-8 rounded-xl shadow-[0_4px_12px_rgba(229,57,53,0.2)] dark:shadow-none hover:scale-[1.02] active:scale-[0.98] transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto"
                    >
                      {isSubmitting ? "Sending..." : "Send Message"}
                      <ArrowRight size={13} />
                    </button>
                  </div>
                </form>
              </div>
            </motion.div>
          </div>

        </div>

        {/* Map Embed Container */}
        <div className="w-full h-[400px] bg-white dark:bg-[#111111] border border-neutral-200 dark:border-neutral-800/80 rounded-3xl overflow-hidden relative flex items-center justify-center shadow-[0_4px_20px_rgba(0,0,0,0.01)] hover:shadow-md transition-all duration-300">
          {mapSrc ? (
            <iframe
              src={mapSrc}
              width="100%"
              height="100%"
              style={{ border: 0 }}
              allowFullScreen
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              className="w-full h-full grayscale dark:invert dark:grayscale"
            />
          ) : (
            <div className="text-center p-6">
              <div className="w-12 h-12 bg-red-50 dark:bg-red-500/10 rounded-2xl flex items-center justify-center border border-red-100 dark:border-red-500/20 shadow-sm mx-auto mb-4">
                <MapPin className="w-5 h-5 text-[#E53935]" />
              </div>
              <h3 className="font-extrabold text-gray-950 dark:text-white text-base mb-1">Our Location</h3>
              <p className="text-xs text-neutral-500 dark:text-neutral-400 max-w-xs mx-auto leading-relaxed whitespace-pre-line">
                {locationVal}
              </p>
            </div>
          )}
        </div>

      </div>
    </div>
  );
}
