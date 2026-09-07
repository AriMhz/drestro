"use client";

import { motion } from 'motion/react';
import { Target, Lightbulb, Users, Trophy } from 'lucide-react';
import Link from 'next/link';

export default function AboutUs() {
  return (
    <div className="bg-white dark:bg-[#0a0a0a] min-h-screen pt-12 pb-24">
      
      {/* Hero */}
      <section className="mb-20 text-center max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 className="text-4xl md:text-5xl font-bold text-[#111111] dark:text-white mb-6">Empowering restaurants to do more with less.</h1>
        <p className="text-xl text-gray-600 dark:text-neutral-400">
          We built DRestro because we saw restaurant owners struggling with fragmented tools, messy ledgers, and dropped orders. Our mission is to simplify hospitality.
        </p>
      </section>

      {/* Image Strip */}
      <section className="mb-24">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
          <img src="https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&q=80&w=400&h=500" alt="Restaurant Scene" className="rounded-2xl object-cover w-full h-[300px]" />
          <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&q=80&w=400&h=500" alt="Fine Dining" className="rounded-2xl object-cover w-full h-[300px] mt-8" />
          <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&q=80&w=400&h=500" alt="Billing" className="rounded-2xl object-cover w-full h-[300px]" />
          <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80&w=400&h=500" alt="Kitchen" className="rounded-2xl object-cover w-full h-[300px] mt-8" />
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Story */}
        <div className="grid md:grid-cols-2 gap-16 items-center mb-24">
          <motion.div initial={{ opacity: 0, x: -20 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }}>
            <h2 className="text-3xl font-bold text-[#111111] dark:text-white mb-6">Our Story</h2>
            <div className="space-y-4 text-gray-600 dark:text-neutral-400 text-lg">
              <p>Founded in 2020 by a team of ex-restaurateurs and tech enthusiasts, DRestro was born out of frustration. We were tired of using poorly designed, slow, and overpriced POS systems that crashed during rush hours.</p>
              <p>We set out to build an all-in-one ecosystem that spans the entire dining experience—from the moment a customer scans a QR code, to the KOT printing in the kitchen, to the final accounting reconciliation.</p>
              <p>Today, DRestro powers over 500+ restaurants across Nepal, helping them increase table turnover and reduce food waste.</p>
            </div>
          </motion.div>
          <motion.div initial={{ opacity: 0, scale: 0.95 }} whileInView={{ opacity: 1, scale: 1 }} viewport={{ once: true }} className="bg-gray-50 rounded-3xl p-10 border border-gray-100 flex flex-col items-center text-center justify-center h-full">
             <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
                <Trophy className="text-[#E53935] w-8 h-8" />
             </div>
             <h3 className="text-4xl font-extrabold text-[#111111] dark:text-white mb-2">500+</h3>
             <p className="text-gray-500 font-medium">Restaurants Trust Us</p>
          </motion.div>
        </div>

        {/* Values */}
        <div className="text-center mb-16">
          <h2 className="text-3xl font-bold text-[#111111] dark:text-white mb-4">Our Core Values</h2>
        </div>

        <div className="grid md:grid-cols-3 gap-8 mb-24">
          {[
            { icon: <Target className="text-[#E53935]" size={32} />, title: "Merchant First", desc: "Every feature we build is designed to make the merchant's life easier and their business more profitable." },
            { icon: <Lightbulb className="text-[#E53935]" size={32} />, title: "Constant Innovation", desc: "We release updates bi-weekly. If the industry moves, we move faster." },
             { icon: <Users className="text-[#E53935]" size={32} />, title: "Reliability as a Feature", desc: "A restaurant can't stop during service hours. Neither should our software. Uptime is our religion." },
          ].map((val, i) => (
             <motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.1 }} className="bg-white dark:bg-[#0a0a0a] rounded-2xl p-8 shadow-sm border border-gray-100 text-center">
               <div className="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
                 {val.icon}
               </div>
               <h3 className="text-xl font-bold text-[#111111] dark:text-white mb-3">{val.title}</h3>
               <p className="text-gray-600 dark:text-neutral-400">{val.desc}</p>
             </motion.div>
          ))}
        </div>

        {/* CTA */}
         <div className="bg-[#111111] rounded-3xl p-12 text-center text-white relative overflow-hidden">
             <div className="absolute top-0 right-0 w-64 h-64 bg-[#E53935] rounded-full blur-[100px] opacity-20 transform translate-x-1/2 -translate-y-1/2"></div>
             <h2 className="text-3xl font-bold mb-6 position-relative z-10">Join the future of hospitality.</h2>
             <Link href="/contact" className="inline-block bg-[#E53935] text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-red-700 transition relative z-10">
               Work With Us
             </Link>
         </div>

      </div>
    </div>
  );
}
