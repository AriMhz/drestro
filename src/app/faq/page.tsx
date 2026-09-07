import { prisma } from '@/src/lib/prisma';
import Navbar from '@/src/components/layout/Navbar';
import Footer from '@/src/components/layout/Footer';
import Link from 'next/link';

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Frequently Asked Questions | DRestro",
  description: "Find answers to the most common questions about DRestro POS.",
};

export default async function FaqPage() {
  const faqs = await prisma.faq.findMany({
    orderBy: { order: 'asc' },
  });

  const settings = await prisma.siteSetting.findUnique({
    where: { key: 'whatsapp_number' }
  });

  const whatsappNumber = settings?.value?.replace(/[^0-9]/g, '') || "1234567890";

  return (
    <div className="bg-slate-50 font-sans flex flex-col">
      {/* Header Section */}
      <div className="bg-[#111111] text-white py-20 relative overflow-hidden">
        <div className="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay"></div>
        <div className="max-w-4xl mx-auto px-6 relative z-10 text-center">
          <h1 className="text-4xl md:text-5xl font-black tracking-tight mb-4">Frequently Asked Questions</h1>
          <p className="text-lg md:text-xl text-gray-400 font-medium max-w-2xl mx-auto">
            Everything you need to know about DRestro POS and our services.
          </p>
        </div>
      </div>

      {/* FAQs List Section */}
      <div className="max-w-4xl mx-auto px-6 py-16 md:py-24">
        {faqs.length === 0 ? (
          <div className="text-center text-gray-500 py-10">
            <p className="text-lg">No FAQs available yet. Please check back later.</p>
          </div>
        ) : (
          <div className="space-y-6">
            {faqs.map((faq) => (
              <div key={faq.id} className="bg-white dark:bg-[#0a0a0a] rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <h3 className="text-xl font-bold text-[#111111] dark:text-white mb-3">{faq.question}</h3>
                <div className="text-gray-600 dark:text-neutral-400 leading-relaxed whitespace-pre-wrap">{faq.answer}</div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* More Questions CTA */}
      <div className="bg-gray-100 dark:bg-[#111111] border-t border-gray-200 dark:border-neutral-800 py-16">
        <div className="max-w-3xl mx-auto px-6 text-center">
          <h2 className="text-2xl md:text-3xl font-bold text-[#111111] dark:text-white mb-4">Still have more questions?</h2>
          <p className="text-gray-600 dark:text-neutral-400 mb-8 max-w-lg mx-auto text-lg">
            Can't find the answer you're looking for? Please chat to our friendly team on WhatsApp.
          </p>
          <a 
            href={`https://wa.me/${whatsappNumber}`} 
            target="_blank" 
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 px-8 py-4 bg-[#25D366] text-white font-bold rounded-xl hover:bg-[#20bd5a] hover:-translate-y-1 hover:shadow-lg hover:shadow-[#25D366]/30 transition-all duration-300"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
            <span>Chat with us on WhatsApp</span>
          </a>
        </div>
      </div>
    </div>
  );
}
