"use client";

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Facebook, Twitter, Instagram, Linkedin, Mail, Phone, MapPin, MessageCircle } from 'lucide-react';

import LoyaltyBanner from '../LoyaltyBanner';

export default function Footer() {
  const pathname = usePathname();
  const [settings, setSettings] = useState<any>({});

  useEffect(() => {
    fetch('/api/settings/public')
      .then(res => res.json())
      .then(data => setSettings(data))
      .catch(err => console.error(err));
  }, []);

  // Hide Footer on Admin, Login, and Register pages
  if (pathname.startsWith("/admin") || pathname.startsWith("/login") || pathname.startsWith("/register")) {
    return null;
  }

  const socialLinks = [
    { 
      icon: <Facebook size={18} />, 
      href: settings.facebook_url || "#",
      color: "text-[#1877F2] hover:bg-[#1877F2] hover:text-white hover:border-[#1877F2] hover:shadow-[0_0_15px_rgba(24,119,242,0.5)]"
    },
    { 
      icon: <Twitter size={18} />, 
      href: settings.twitter_url || "#",
      color: "text-[#1DA1F2] hover:bg-[#1DA1F2] hover:text-white hover:border-[#1DA1F2] hover:shadow-[0_0_15px_rgba(29,161,242,0.5)]"
    },
    { 
      icon: <Instagram size={18} />, 
      href: settings.instagram_url || "#",
      color: "text-[#E1306C] hover:bg-[#E1306C] hover:text-white hover:border-[#E1306C] hover:shadow-[0_0_15px_rgba(225,48,108,0.5)]"
    },
    { 
      icon: <Linkedin size={18} />, 
      href: settings.linkedin_url || "#",
      color: "text-[#0077B5] hover:bg-[#0077B5] hover:text-white hover:border-[#0077B5] hover:shadow-[0_0_15px_rgba(0,119,181,0.5)]"
    }
  ].filter(link => link.href && link.href !== "#");

  return (
    <>
      <LoyaltyBanner />
      <footer className="relative bg-[#0a0a0a] text-white pt-20 pb-24 lg:pb-8 border-t border-[#222222] overflow-hidden">
        {/* Subtle background glow */}
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[1px] bg-gradient-to-r from-transparent via-[#E53935]/50 to-transparent"></div>
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-[#E53935]/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div className="max-w-[1024px] mx-auto px-10 relative z-10">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
          {/* Brand */}
          <div className="space-y-6">
            <Link href="/" className="flex items-center inline-block">
              <img src="/logos/logo-light.svg" alt="DRestro Logo" className="h-9 md:h-11 w-auto transition-transform hover:scale-105 duration-300 drop-shadow-[0_0_15px_rgba(255,255,255,0.1)]" />
            </Link>
            <p className="text-slate-500 dark:text-gray-400 text-[15px] leading-relaxed pr-4 font-medium">
              All-in-One Restaurant Management Software. Manage orders, billing, inventory & customers in one place.
            </p>
            {settings.whatsapp_number && (
              <a 
                href={`https://wa.me/${String(settings.whatsapp_number).replace(/[^0-9]/g, '')}`} 
                target="_blank" 
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-4 py-2 bg-[#25D366]/10 text-[#25D366] border border-[#25D366]/20 rounded-xl hover:bg-[#25D366] hover:text-[#111111] dark:text-white transition-all duration-300 group mt-2"
              >
                <svg viewBox="0 0 24 24" width="18" height="18" className="group-hover:animate-pulse" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                </svg>
                <span className="font-semibold text-sm">Chat on WhatsApp</span>
              </a>
            )}
            <div className="flex space-x-3 pt-2">
              {socialLinks.map((social, i) => (
                <a key={i} href={social.href} target="_blank" rel="noopener noreferrer" className={`flex items-center justify-center w-10 h-10 rounded-full bg-[#1a1a1a] border border-slate-200 dark:border-[#333333] transition-all duration-300 -translate-y-0 hover:-translate-y-1 ${social.color}`}>
                  {social.icon}
                </a>
              ))}
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="font-bold text-lg mb-6 text-white tracking-wide">Quick Links</h3>
            <ul className="space-y-4">
              {[
                { name: 'Online Software', path: '/software' },
                { name: 'Offline POS', path: '/software' },
                { name: 'Pricing Plans', path: '/pricing' },
                { name: 'Hardware Store', path: '/products' },
                { name: 'Combo Packages', path: '/pricing?tab=combo' }
              ].map((link, i) => (
                <li key={i}>
                  <Link href={link.path} className="group flex items-center text-slate-500 dark:text-gray-400 hover:text-white transition-colors text-[15px] font-medium">
                    <span className="w-1.5 h-1.5 rounded-full bg-[#E53935] mr-2 opacity-0 group-hover:opacity-100 transform -translate-x-2 group-hover:translate-x-0 transition-all duration-300"></span>
                    <span className="transform group-hover:translate-x-1 transition-transform duration-300">{link.name}</span>
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Company */}
          <div>
            <h3 className="font-bold text-lg mb-6 text-white tracking-wide">Company</h3>
            <ul className="space-y-4">
              {[
                { name: 'About Us', path: '/about' },
                { name: 'Contact Us', path: '/contact' },
                { name: 'Careers', path: '/careers' },
                { name: 'FAQs', path: '/faq' },
                { name: 'Privacy Policy', path: '/privacy' },
                { name: 'Terms of Service', path: '/terms' },
                { name: 'Refund Policy', path: '/refund' }
              ].map((link, i) => (
                <li key={i}>
                  <Link href={link.path} className="group flex items-center text-slate-500 dark:text-gray-400 hover:text-white transition-colors text-[15px] font-medium">
                    <span className="w-1.5 h-1.5 rounded-full bg-[#E53935] mr-2 opacity-0 group-hover:opacity-100 transform -translate-x-2 group-hover:translate-x-0 transition-all duration-300"></span>
                    <span className="transform group-hover:translate-x-1 transition-transform duration-300">{link.name}</span>
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="font-bold text-lg mb-6 text-white tracking-wide">Contact</h3>
            <ul className="space-y-5">
              <li className="flex items-start space-x-4 group cursor-pointer">
                <div className="w-10 h-10 rounded-lg bg-[#1a1a1a] border border-slate-200 dark:border-[#333333] flex items-center justify-center group-hover:bg-[#E53935]/10 group-hover:border-[#E53935]/30 transition-colors shrink-0">
                  <MapPin size={18} className="text-[#E53935]" />
                </div>
                <div className="flex flex-col pt-1">
                  <span className="text-white text-sm font-semibold mb-0.5">Location</span>
                  <span className="text-slate-500 dark:text-gray-400 text-sm group-hover:text-slate-600 dark:text-gray-300 transition-colors">{settings.contact_location || "Kathmandu, Nepal"}</span>
                </div>
              </li>
              <li className="flex items-start space-x-4 group cursor-pointer">
                <div className="w-10 h-10 rounded-lg bg-[#1a1a1a] border border-slate-200 dark:border-[#333333] flex items-center justify-center group-hover:bg-[#E53935]/10 group-hover:border-[#E53935]/30 transition-colors shrink-0">
                  <Phone size={18} className="text-[#E53935]" />
                </div>
                <div className="flex flex-col pt-1">
                  <span className="text-white text-sm font-semibold mb-0.5">Phone</span>
                  <span className="text-slate-500 dark:text-gray-400 text-sm group-hover:text-slate-600 dark:text-gray-300 transition-colors">{settings.contact_phone || "+977 9865029558"}</span>
                </div>
              </li>
              <li className="flex items-start space-x-4 group cursor-pointer">
                <div className="w-10 h-10 rounded-lg bg-[#1a1a1a] border border-slate-200 dark:border-[#333333] flex items-center justify-center group-hover:bg-[#E53935]/10 group-hover:border-[#E53935]/30 transition-colors shrink-0">
                  <Mail size={18} className="text-[#E53935]" />
                </div>
                <div className="flex flex-col pt-1">
                  <span className="text-white text-sm font-semibold mb-0.5">Email</span>
                  <span className="text-slate-500 dark:text-gray-400 text-sm group-hover:text-slate-600 dark:text-gray-300 transition-colors">{settings.contact_email || "info@drestro.com"}</span>
                </div>
              </li>
            </ul>
          </div>
        </div>

        {/* Android App Download Banner */}
        <div className="mb-10 rounded-2xl bg-gradient-to-r from-[#1a1a1a] to-[#111111] border border-[#333333] p-6 flex flex-col sm:flex-row items-center justify-between gap-5">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 rounded-2xl bg-[#E53935]/10 border border-[#E53935]/20 flex items-center justify-center shrink-0">
              <svg viewBox="0 0 24 24" width="28" height="28" fill="#E53935">
                <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19.01 7.38 20 6.18 20C4.98 20 4 19.01 4 17.82a2.18 2.18 0 0 1 2.18-2.18M17.82 15.64a2.18 2.18 0 0 1 2.18 2.18C20 19.01 19.01 20 17.82 20a2.18 2.18 0 0 1-2.18-2.18a2.18 2.18 0 0 1 2.18-2.18M17.82 10.45a2.18 2.18 0 0 1 2.18 2.18a2.18 2.18 0 0 1-2.18 2.18a2.18 2.18 0 0 1-2.18-2.18a2.18 2.18 0 0 1 2.18-2.18M6.18 10.45a2.18 2.18 0 0 1 2.18 2.18a2.18 2.18 0 0 1-2.18 2.18A2.18 2.18 0 0 1 4 12.63a2.18 2.18 0 0 1 2.18-2.18M14.27 6.32l1.12-2.18c.04-.07.01-.16-.06-.2c-.07-.04-.15-.01-.2.06l-1.14 2.2C13.26 5.87 12.65 5.71 12 5.71s-1.27.16-1.99.49L8.87 4c-.05-.07-.13-.1-.2-.06c-.07.04-.1.13-.06.2l1.12 2.18C8.24 6.98 7.34 8.04 7.3 9.27h9.4c-.04-1.23-.94-2.29-2.43-2.95z"/>
              </svg>
            </div>
            <div>
              <p className="text-white font-bold text-base mb-0.5">Get DRestro on Android</p>
              <p className="text-gray-400 text-sm">Download our app and manage your restaurant anywhere.</p>
            </div>
          </div>
          <a
            href="/downloads/drestro.apk"
            download
            className="flex items-center gap-2.5 px-6 py-3 bg-[#E53935] hover:bg-[#D32F2F] text-white font-bold text-sm rounded-xl transition-all duration-300 hover:shadow-[0_4px_20px_rgba(229,57,53,0.4)] hover:-translate-y-0.5 shrink-0"
          >
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
              <path d="M5 20h14v-2H5v2zm7-18L5.33 8h3.84v4h5.66V8h3.84L12 2z" transform="rotate(180 12 11)"/>
              <path d="M19 9h-4V3H9v6H5l7 7 7-7z"/>
            </svg>
            Download APK
          </a>
        </div>

        <div className="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <p className="text-slate-400 dark:text-gray-500 text-[13px] font-medium tracking-wide">
            @2026 - A product of skylink solution Pvt ltd. All right are reserved
          </p>
        </div>
      </div>
    </footer>
    </>
  );
}

