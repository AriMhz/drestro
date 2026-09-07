import { prisma } from '@/src/lib/prisma';
import Navbar from '@/src/components/layout/Navbar';
import Footer from '@/src/components/layout/Footer';
import { ShieldCheck, HardDrive, ScrollText, BadgeCent, Gavel } from 'lucide-react';

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Terms and Conditions | DRestro",
  description: "Terms and Conditions for DRestro Restaurant POS.",
};

export default async function TermsPage() {
  const setting = await prisma.siteSetting.findUnique({
    where: { key: 'terms_conditions' }
  });

  const content = setting?.value;

  // Premium default Terms text if database is empty
  const defaultSections = [
    {
      id: "grant",
      title: "1. Software License Grant & Hardware limits",
      icon: <ShieldCheck className="w-5 h-5 text-[#E53935]" />,
      text: "DRestro grants you a limited, non-exclusive, non-transferable license to install and concurrently execute our offline POS systems. The concurrent device count limits (cashier billing screens, synchronized waiter mobile terminals, and live bar/kitchen displays) are strictly dictated by your selected subscription plan (Free, Basic, Premium, or Platinum)."
    },
    {
      id: "subnet",
      title: "2. Offline Local-Subnet Operations",
      icon: <HardDrive className="w-5 h-5 text-[#E53935]" />,
      text: "Our restaurant system operates concurrently over your local Wi-Fi router subnet. You are solely responsible for setting up and maintaining proper physical local networking infrastructure, LAN cables, local subnet address pools, and thermal print network sockets."
    },
    {
      id: "backup",
      title: "3. Data Ownership & Backup Duties",
      icon: <ScrollText className="w-5 h-5 text-[#E53935]" />,
      text: "You hold absolute 100% ownership over your localized restaurant logs. DRestro holds no responsibility or liability for server file corruptions, hardware system breaks, or operational details lost on your register machines. We strongly advise that cashiers initiate daily manual database backups through our integrated backup desk."
    },
    {
      id: "billing",
      title: "4. Subscriptions, Payments & Downgrades",
      icon: <BadgeCent className="w-5 h-5 text-[#E53935]" />,
      text: "Plans are charged on 6-month or yearly intervals, and payments are managed securely. Account downgrades automatically transition active restaurant floor profiles to our Free plan and apply feature limitations (100 dishes and 10 tables maximum) immediately."
    },
    {
      id: "law",
      title: "5. Governing Law & Dispute Resolution",
      icon: <Gavel className="w-5 h-5 text-[#E53935]" />,
      text: "This software agreement and operational parameters are strictly governed by and interpreted under the commercial laws of Nepal. Any arising issues, conflicts, or non-compliance parameters shall be brought exclusively before the competent courts in Kathmandu, Nepal."
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
              Terms & Conditions
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
                      <ScrollText className="w-4 h-4 shrink-0" />
                      <span>Custom Terms Text</span>
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
                  // Custom terms rendered cleanly from database
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
