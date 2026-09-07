"use client";

import { useState, useEffect } from "react";
import { ChevronDown } from "lucide-react";
import { motion, AnimatePresence } from "motion/react";

interface Faq {
  id: string;
  question: string;
  answer: string;
  order: number;
}

export default function FaqSection() {
  const [faqs, setFaqs] = useState<Faq[]>([]);
  const [openId, setOpenId] = useState<string | null>(null);

  useEffect(() => {
    fetch('/api/faq')
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data)) {
          setFaqs(data);
        }
      })
      .catch(console.error);
  }, []);

  if (faqs.length === 0) return null;

  return (
    <section className="py-24 bg-[#FAFAFA] dark:bg-[#0a0a0a] border-t border-[#E2E2E7] dark:border-neutral-800">
      <div className="max-w-[800px] mx-auto px-6">
        
        <div className="text-center mb-16">
          <span className="text-[#E53935] text-xs font-bold uppercase tracking-widest border border-[#E53935]/20 bg-[#E53935]/5 px-3.5 py-1.5 rounded-full">
            Got Questions?
          </span>
          <h2 className="text-3xl lg:text-[42px] tracking-[-0.02em] font-extrabold text-[#111111] dark:text-white mt-5 mb-6 leading-tight">
            Frequently Asked Questions
          </h2>
          <p className="text-[#555555] dark:text-neutral-400 text-lg">
            Everything you need to know about DRestro and how it works.
          </p>
        </div>

        <div className="space-y-4">
          {faqs.map((faq) => {
            const isOpen = openId === faq.id;
            return (
              <div 
                key={faq.id} 
                className={`border rounded-2xl overflow-hidden transition-all duration-300 ${
                  isOpen 
                    ? 'bg-white dark:bg-[#1a1a1a] border-[#E53935]/30 shadow-lg shadow-[#E53935]/5' 
                    : 'bg-white dark:bg-[#1a1a1a] border-[#E2E2E7] dark:border-neutral-800 hover:border-gray-300 dark:hover:border-neutral-700'
                }`}
              >
                <button
                  onClick={() => setOpenId(isOpen ? null : faq.id)}
                  className="w-full flex items-center justify-between p-6 text-left focus:outline-none"
                >
                  <span className={`font-semibold text-lg transition-colors duration-200 ${isOpen ? 'text-[#E53935]' : 'text-gray-900 dark:text-white'}`}>
                    {faq.question}
                  </span>
                  <div className={`flex-shrink-0 ml-4 w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 ${
                    isOpen ? 'bg-[#E53935]/10 text-[#E53935] rotate-180' : 'bg-gray-100 dark:bg-neutral-800 text-slate-400 dark:text-gray-500 dark:text-neutral-400'
                  }`}>
                    <ChevronDown size={18} />
                  </div>
                </button>
                
                <AnimatePresence>
                  {isOpen && (
                    <motion.div
                      initial={{ height: 0, opacity: 0 }}
                      animate={{ height: "auto", opacity: 1 }}
                      exit={{ height: 0, opacity: 0 }}
                      transition={{ duration: 0.3, ease: "easeInOut" }}
                    >
                      <div className="px-6 pb-6 text-gray-600 dark:text-neutral-400 leading-relaxed whitespace-pre-wrap">
                        {faq.answer}
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            );
          })}
        </div>

      </div>
    </section>
  );
}
