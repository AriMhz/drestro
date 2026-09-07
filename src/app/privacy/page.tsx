import { prisma } from '@/src/lib/prisma';
import Navbar from '@/src/components/layout/Navbar';
import Footer from '@/src/components/layout/Footer';
import { Shield, Eye, Lock, FileText, Globe } from 'lucide-react';

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Privacy Policy | DRestro",
  description: "Privacy Policy for DRestro Restaurant POS.",
};

export default async function PrivacyPage() {
  const setting = await prisma.siteSetting.findUnique({
    where: { key: 'privacy_policy' }
  });

  const content = setting?.value;

  // Premium default legal text if database is empty
  const defaultSections = [
    {
      id: "intro",
      title: "1. Introduction & Cloudless Core",
      icon: <Globe className="w-5 h-5 text-[#E53935]" />,
      text: "Welcome to DRestro. We are committed to protecting your business operations. Unlike traditional restaurant systems, DRestro operates primarily on a cloudless, offline local-subnet network architecture. This means the vast majority of your active restaurant operations—including employee logs, kitchen orders, billing history, and recipe sheets—are securely kept inside your physical building on your local machines rather than being uploaded to third-party cloud servers."
    },
    {
      id: "collect",
      title: "2. Information We Collect",
      icon: <Eye className="w-5 h-5 text-[#E53935]" />,
      text: "We collect account registration data (your restaurant name, official phone number, physical address, and owner email) to manage license activations. However, raw operational records (dine-in guest table plans, KOT live updates, daybook ledger values, and staff access credentials) remain strictly on your local POS cashier server hardware and are never gathered, inspected, or processed by DRestro's cloud infrastructure."
    },
    {
      id: "security",
      title: "3. Local Network Security & Router Duty",
      icon: <Lock className="w-5 h-5 text-[#E53935]" />,
      text: "Because DRestro runs concurrently over your local Wi-Fi router, data safety against web leaks is naturally secured. However, you are solely responsible for securing your restaurant's physical router and network subnet with strong password mechanisms (WPA3 recommended) to prevent unauthorized local terminal connections."
    },
    {
      id: "payments",
      title: "4. Third-Party Billing Gateways",
      icon: <Shield className="w-5 h-5 text-[#E53935]" />,
      text: "Subscription purchases and cashier checkout simulations are processed securely via leading payment processors (Fonepay, eSewa, Khalti). DRestro does not store, access, or log any credit card details, API keys, or financial credentials on our databases."
    },
    {
      id: "contact",
      title: "5. Legal & Data Privacy Contacts",
      icon: <FileText className="w-5 h-5 text-[#E53935]" />,
      text: "For questions regarding localized license keys, backups, or security compliance, you can contact our security and legal desk in Kathmandu directly at legal@drestro.com or via phone support at +977 9865029558."
    }
  ];

  return (
    <div className="bg-[#0a0a0a] font-sans flex flex-col text-white">
        
        {/* Modern Header Hero */}
        <div className="relative py-20 bg-[#111111] border-b border-neutral-800 overflow-hidden">
          {/* Subtle glowing element */}
          <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[500px] h-[150px] bg-[#E53935]/5 rounded-full blur-[80px] pointer-events-none"></div>
          
          <div className="max-w-6xl mx-auto px-6 text-center relative z-10">
            <span className="text-[#E53935] text-[10px] font-black uppercase tracking-widest bg-red-500/10 border border-red-500/25 px-3 py-1 rounded-md">
              Legal Guidelines
            </span>
            <h1 className="text-4xl md:text-5xl font-black tracking-tight mt-4 mb-4 text-white">
              Privacy Policy
            </h1>
            <p className="text-neutral-500 dark:text-neutral-400 font-bold text-sm tracking-wide">
              Last updated: {new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
            </p>
          </div>
        </div>

        {/* Dynamic Double Column Layout */}
        <div className="max-w-6xl mx-auto px-6 py-16 md:py-24">
          <div className="flex flex-col lg:flex-row gap-12">
            
            {/* Left Column: Premium Interactive Index */}
            <aside className="w-full lg:w-1/4 lg:sticky lg:top-[120px] h-fit">
              <div className="bg-[#111111] border border-neutral-800 rounded-2xl p-5 space-y-4 shadow-md">
                <h3 className="text-xs font-black text-white uppercase tracking-widest pb-3 border-b border-neutral-800">
                  Quick Index
                </h3>
                <nav className="flex flex-col gap-2.5">
                  {content ? (
                    <a href="#content" className="flex items-center gap-2.5 text-xs font-bold text-[#E53935] hover:text-[#E53935] transition-colors">
                      <FileText className="w-4 h-4 shrink-0" />
                      <span>Custom Policy Text</span>
                    </a>
                  ) : (
                    defaultSections.map((sec) => (
                      <a 
                        key={sec.id}
                        href={`#${sec.id}`}
                        className="flex items-center gap-2.5 text-xs font-bold text-neutral-400 hover:text-white transition-colors"
                      >
                        {sec.icon}
                        <span className="truncate">{sec.title.split(". ")[1]}</span>
                      </a>
                    ))
                  )}
                </nav>
              </div>
            </aside>

            {/* Right Column: Premium Legal Container */}
            <section className="w-full lg:w-3/4">
              <div className="bg-[#111111] border border-neutral-800 rounded-3xl p-8 md:p-12 shadow-sm max-w-none">
                
                {content ? (
                  // Custom policy rendered cleanly from database
                  <div id="content" className="whitespace-pre-wrap text-neutral-300 text-sm leading-relaxed font-semibold">
                    {content}
                  </div>
                ) : (
                  // Gorgeous fallback legal layout
                  <div className="space-y-12">
                    {defaultSections.map((sec) => (
                      <div key={sec.id} id={sec.id} className="scroll-mt-[140px] space-y-4">
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-xl bg-red-500/10 flex items-center justify-center border border-red-500/20 shrink-0">
                            {sec.icon}
                          </div>
                          <h2 className="text-lg md:text-xl font-black text-white tracking-tight">
                            {sec.title}
                          </h2>
                        </div>
                        <p className="text-neutral-400 text-sm leading-relaxed font-medium pl-12">
                          {sec.text}
                        </p>
                      </div>
                    ))}
                  </div>
                )}
                
              </div>
            </section>

          </div>
        </div>
    </div>
  );
}
