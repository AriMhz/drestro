"use client";

import { motion } from 'motion/react';
import { Cloud, Laptop, Server, Smartphone, Zap, ShieldCheck, PieChart, Users } from 'lucide-react';
import Link from 'next/link';

export default function Software() {
  return (
    <div className="bg-[#FFFFFF] dark:bg-[#0a0a0a] min-h-screen pt-24 pb-0 border-t border-[#E2E2E7] dark:border-neutral-800">
      
      {/* Hero section */}
      <section className="pt-20 pb-16 bg-[#FAFAFA] dark:bg-[#111111] border-b border-[#E2E2E7] dark:border-neutral-800">
        <div className="max-w-[1024px] mx-auto px-10 text-center">
          <h1 className="text-4xl md:text-[54px] font-bold text-[#111111] dark:text-white mb-6 tracking-[-0.02em] leading-tight">Software built for the modern restaurant</h1>
          <p className="text-xl text-[#666666] max-w-[800px] mx-auto mb-10 leading-[1.6]">
            Choose between our lightning-fast Offline POS for ultimate reliability, or our Cloud-based platform for managing your restaurant from anywhere.
          </p>
        </div>
      </section>

      {/* Online vs Offline Details */}
      <section className="py-24">
        <div className="max-w-[1024px] mx-auto px-10">
          
          {/* Cloud Based */}
          <div className="grid md:grid-cols-2 gap-16 items-center mb-32">
            <motion.div initial={{ opacity: 0, x: -20 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }}>
              <div className="inline-flex items-center space-x-2 bg-[rgba(59,130,246,0.1)] text-[#3B82F6] px-3 py-1.5 rounded-full text-xs font-bold mb-6 uppercase tracking-[1px]">
                <Cloud size={16} />
                <span>Cloud Edition</span>
              </div>
              <h2 className="text-3xl lg:text-[40px] tracking-[-0.02em] font-bold text-[#111111] dark:text-white mb-6 leading-tight">Manage your restaurant from anywhere.</h2>
              <p className="text-[#666666] mb-8 text-lg leading-[1.6]">
                The cloud edition gives you full access to your restaurant's data on any device with an internet connection. See live sales, update menus remotely, and manage multiple branches seamlessly.
              </p>
              
              <ul className="space-y-6">
                {[
                  { icon: <Smartphone className="text-[#3B82F6]" />, title: "Any Device Access", desc: "Works on Windows, Mac, iOS, and Android." },
                  { icon: <PieChart className="text-[#3B82F6]" />, title: "Live Analytics", desc: "View real-time sales and inventory reports from anywhere." },
                  { icon: <Users className="text-[#3B82F6]" />, title: "Multi-Outlet Management", desc: "Control all your branches from a single unified dashboard." }
                ].map((item, i) => (
                  <li key={i} className="flex">
                    <div className="mr-4 mt-1 bg-[rgba(59,130,246,0.1)] w-10 h-10 rounded-lg flex items-center justify-center shrink-0">
                      {item.icon}
                    </div>
                    <div>
                      <h4 className="text-[18px] font-bold text-[#111111] dark:text-white mb-1">{item.title}</h4>
                      <p className="text-[#666666] text-sm leading-[1.6]">{item.desc}</p>
                    </div>
                  </li>
                ))}
              </ul>
              
              <div className="mt-10">
                <Link href="/pricing" className="text-[#3B82F6] font-semibold text-sm hover:underline inline-flex items-center transition-colors">
                  View Cloud Pricing plan &rarr;
                </Link>
              </div>
            </motion.div>
            
            <motion.div initial={{ opacity: 0, scale: 0.95 }} whileInView={{ opacity: 1, scale: 1 }} viewport={{ once: true }}>
              <div className="bg-[#FAFAFA] dark:bg-[#111111] border border-[#E2E2E7] dark:border-neutral-800 rounded-[24px] p-4 shadow-sm relative overflow-hidden">
                <div className="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] opacity-30"></div>
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=1200" alt="Cloud Dashboard" className="rounded-[16px] shadow-[0_20px_40px_rgba(0,0,0,0.1)] w-full relative z-10 border border-[#E2E2E7] dark:border-neutral-800" />
              </div>
            </motion.div>
          </div>

          {/* Offline POS */}
          <div className="grid md:grid-cols-2 gap-16 items-center">
             <motion.div initial={{ opacity: 0, scale: 0.95 }} whileInView={{ opacity: 1, scale: 1 }} viewport={{ once: true }} className="order-2 md:order-1">
              <div className="bg-[#111111] rounded-[24px] p-4 shadow-[0_20px_40px_rgba(0,0,0,0.2)] relative border border-[#333333]">
                <img src="https://images.unsplash.com/photo-1556742044-3c52d6e88c62?auto=format&fit=crop&q=80&w=1200" alt="Offline POS" className="rounded-[16px] shadow-2xl w-full border border-[#444444] opacity-80 mix-blend-luminosity" />
                <div className="absolute -bottom-6 -right-6 bg-[#E53935] text-white p-6 rounded-[16px] shadow-[0_8px_24px_rgba(229,57,53,0.3)]">
                  <div className="flex items-center space-x-3 mb-2">
                    <Zap className="text-white" />
                    <span className="font-bold">Lightning Fast</span>
                  </div>
                  <p className="text-sm text-red-100 max-w-[200px]">0.1s response time for offline billing.</p>
                </div>
              </div>
            </motion.div>

            <motion.div initial={{ opacity: 0, x: 20 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }} className="order-1 md:order-2">
              <div className="inline-flex items-center space-x-2 bg-[rgba(229,57,53,0.1)] text-[#E53935] px-3 py-1.5 rounded-full text-xs font-bold mb-6 uppercase tracking-[1px]">
                <Laptop size={16} />
                <span>Offline POS Edition</span>
              </div>
              <h2 className="text-3xl lg:text-[40px] tracking-[-0.02em] font-bold text-[#111111] dark:text-white mb-6 leading-tight">Unbreakable reliability. No internet needed.</h2>
              <p className="text-[#666666] mb-8 text-lg leading-[1.6]">
                Internet down? No problem. Our offline POS software is installed directly onto your billing machine. It guarantees 100% uptime for your billing operations.
              </p>
              
              <ul className="space-y-6">
                {[
                  { icon: <ShieldCheck className="text-[#E53935]" />, title: "100% Uptime", desc: "Keep billing customers even during internet outages." },
                  { icon: <Zap className="text-[#E53935]" />, title: "Instant Response", desc: "No loading screens. Every click registers instantly." },
                  { icon: <Server className="text-[#E53935]" />, title: "Local Data Control", desc: "All your data stays on your local machine with automated local backups." }
                ].map((item, i) => (
                  <li key={i} className="flex">
                    <div className="mr-4 mt-1 bg-[rgba(229,57,53,0.1)] w-10 h-10 rounded-lg flex items-center justify-center shrink-0">
                      {item.icon}
                    </div>
                    <div>
                      <h4 className="text-[18px] font-bold text-[#111111] dark:text-white mb-1">{item.title}</h4>
                      <p className="text-[#666666] text-sm leading-[1.6]">{item.desc}</p>
                    </div>
                  </li>
                ))}
              </ul>
              
              <div className="mt-10">
                <Link href="/pricing" className="text-[#E53935] font-semibold text-sm hover:underline inline-flex items-center transition-colors">
                  View Offline POS Pricing &rarr;
                </Link>
              </div>
            </motion.div>
          </div>

        </div>
      </section>

      {/* Shared Features Grid */}
      <section className="py-24 bg-[#111111] text-white">
        <div className="max-w-[1024px] mx-auto px-10 text-center">
          <h2 className="text-3xl lg:text-[40px] tracking-[-0.02em] font-bold text-white mb-16">Standard Features on Both Editions</h2>
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            {[
              "Inventory & Recipe Management", "Table & KOT Management", 
              "Staff Roles & Permissions", "Customer Database (CRM)", 
              "Tax & Service Charge Config", "Daily Sales Analytics", 
              "Detailed Cash Drawer Management", "Discount & Offer Rules"
            ].map((feature, i) => (
               <div key={i} className="bg-[#222222] border border-[#333333] p-6 rounded-[16px] hover:bg-[#333333] transition-colors shadow-sm">
                 <h4 className="font-semibold text-sm leading-[1.6]">{feature}</h4>
               </div>
            ))}
          </div>
        </div>
      </section>

    </div>
  );
}
