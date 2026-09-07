"use client";

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';

interface Plan {
  id: string;
  name: string;
  priceYearly: number;
}

export default function LoyaltyBanner() {
  const [plans, setPlans] = useState<Plan[]>([
    { id: '1', name: 'Basic', priceYearly: 24000 },
    { id: '2', name: 'Premium', priceYearly: 28000 },
    { id: '3', name: 'Business Plus', priceYearly: 38000 },
    { id: '4', name: 'Platinum', priceYearly: 48000 },
  ]);

  useEffect(() => {
    fetch('/api/plans')
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data) && data.length > 0) {
          const filtered = data.filter((p: Plan) => p.priceYearly > 0);
          if (filtered.length > 0) {
            setPlans(filtered);
          }
        }
      })
      .catch(err => console.error("Failed to fetch dynamic plans:", err));
  }, []);

  const formatPrice = (price: number) => {
    if (price % 1000 === 0) {
      return `${price / 1000}k`;
    }
    return `Rs. ${price.toLocaleString()}`;
  };

  const formatDiscountedPrice = (price: number, discountPercent: number) => {
    const discounted = price * (1 - discountPercent / 100);
    if (discounted % 1000 === 0) {
      return `~${discounted / 1000}k`;
    }
    if ((discounted * 10) % 1000 === 0) {
      return `~${(discounted / 1000).toFixed(1)}k`;
    }
    return `~Rs. ${discounted.toLocaleString()}`;
  };

  return (
    <div className="w-full bg-white dark:bg-[#0a0a0a] py-20 border-t border-gray-100 dark:border-neutral-900">
      <div className="max-w-4xl mx-auto px-6 text-center">
        <div className="inline-flex items-center justify-center gap-2 px-4 py-1.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 font-bold text-xs uppercase tracking-widest mb-8 border border-red-100 dark:border-red-500/20">
          Loyalty
        </div>
        
        <h2 className="text-3xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
          Long-Term Clients Pay Less — Not More
        </h2>
        
        <p className="text-lg md:text-xl text-slate-500 dark:text-gray-400 max-w-2xl mx-auto font-medium mb-12">
          We reward loyalty with significant renewal discounts
        </p>

        {/* Pricing Table */}
        <div className="bg-white dark:bg-[#111111] rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-none border border-gray-100 dark:border-[#222] overflow-hidden mb-8">
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead className="bg-gray-50/50 dark:bg-[#1a1a1a] border-b border-gray-100 dark:border-[#222]">
                <tr>
                  <th className="px-6 py-5 text-sm font-black text-slate-900 dark:text-gray-200">Plan</th>
                  <th className="px-6 py-5 text-sm font-black text-slate-900 dark:text-gray-200 text-center">
                    <div>Year 1</div>
                  </th>
                  <th className="px-6 py-5 text-sm font-black text-slate-900 dark:text-gray-200 text-center">
                    <div>Year 2</div>
                    <div className="text-[10px] font-semibold text-slate-500 dark:text-gray-400 mt-0.5 tracking-wide">10% discount</div>
                  </th>
                  <th className="px-6 py-5 text-sm font-black text-slate-900 dark:text-gray-200 text-center">
                    <div>Year 3+</div>
                    <div className="text-[10px] font-semibold text-slate-500 dark:text-gray-400 mt-0.5 tracking-wide">Up to 25% off</div>
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-50 dark:divide-[#1a1a1a]">
                {plans.map((plan) => (
                  <tr key={plan.id} className="hover:bg-gray-50/50 dark:hover:bg-[#151515] transition-colors">
                    <td className="px-6 py-5 font-bold text-slate-800 dark:text-gray-300">{plan.name}</td>
                    <td className="px-6 py-5 text-slate-500 dark:text-gray-400 text-center font-medium">{formatPrice(plan.priceYearly)}</td>
                    <td className="px-6 py-5 font-bold text-red-600 dark:text-red-400 text-center">{formatDiscountedPrice(plan.priceYearly, 10)}</td>
                    <td className="px-6 py-5 font-bold text-red-600 dark:text-red-400 text-center">{formatDiscountedPrice(plan.priceYearly, 25)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <Link href="/contact" className="inline-flex items-center justify-center gap-2 bg-[#E53935] hover:bg-[#D32F2F] text-white font-bold text-[15px] py-4 px-10 rounded-2xl transition-all shadow-[0_8px_20px_rgba(229,57,53,0.25)] hover:shadow-[0_12px_25px_rgba(229,57,53,0.35)] hover:-translate-y-0.5">
          Become Long-Term Partner <ArrowRight className="w-4 h-4 ml-1" />
        </Link>
      </div>
    </div>
  );
}

