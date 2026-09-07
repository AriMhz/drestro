import { prisma } from '@/src/lib/prisma';
import Navbar from '@/src/components/layout/Navbar';
import Footer from '@/src/components/layout/Footer';
import { RefreshCcw, FileText, Ban, Mail } from 'lucide-react';

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Return & Refund Policy | DRestro",
  description: "Return and Refund Policy for DRestro.",
};

export default async function RefundPage() {
  const setting = await prisma.siteSetting.findUnique({
    where: { key: 'refund_policy' }
  });

  const content = setting?.value;

  const defaultSections = [
    {
      id: "eligibility",
      title: "1. Refund Eligibility",
      icon: <RefreshCcw className="w-5 h-5 text-[#E53935]" />,
      text: "If services do not meet requirements, refund requests must be made within 10 days of subscription activation. After 10 days, subscriptions are non-refundable."
    },
    {
      id: "process",
      title: "2. Refund Process",
      icon: <FileText className="w-5 h-5 text-[#E53935]" />,
      text: "Send a written refund request with subscription details to DRestro. Refunds are processed within 7–15 business days."
    },
    {
      id: "limitations",
      title: "3. Limitations",
      icon: <Ban className="w-5 h-5 text-[#E53935]" />,
      text: "Transaction fees from payment gateways are non-refundable. Refunds cover the DRestro subscription only, not the Merchant's own customer orders."
    },
    {
      id: "contact",
      title: "Contact Us",
      icon: <Mail className="w-5 h-5 text-[#E53935]" />,
      text: "For refund requests or questions about this policy, please contact us at:\n\nDRestro Technology Pvt. Ltd.\nEmail: support@drestro.com"
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
              Return & Refund Policy
            </h1>
            <p className="text-neutral-500 dark:text-neutral-400 font-bold text-sm tracking-wide">
              Effective Date: July 10, 2025
            </p>
            <p className="text-neutral-400 font-medium mt-4 max-w-2xl mx-auto">
              We want all our clients to be satisfied with DRestro services.
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
                        <span className="truncate">{sec.title.includes('.') ? sec.title.split(". ")[1] : sec.title}</span>
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
                  <div id="content" className="whitespace-pre-wrap text-neutral-300 text-sm leading-relaxed font-semibold">
                    {content}
                  </div>
                ) : (
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
                        <p className="text-neutral-400 text-sm leading-relaxed font-medium pl-12 whitespace-pre-wrap">
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
