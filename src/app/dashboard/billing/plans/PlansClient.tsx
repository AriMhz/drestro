"use client";

import React, { useState } from 'react';
import { motion } from 'motion/react';
import { Check, X, ArrowLeft } from 'lucide-react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';

interface PlanFeature {
  id: string;
  text: string;
}

interface PlanNotIncluded {
  id: string;
  text: string;
}

interface DBPlan {
  id: string;
  name: string;
  description: string;
  priceYearly: number;
  priceHalfYearly: number;
  oldPriceYearly: number | null;
  oldPriceHalfYearly: number | null;
  isPopular: boolean;
  buttonText: string;
  buttonVariant: string;
  features: PlanFeature[];
  notIncluded: PlanNotIncluded[];
}

interface PlansClientProps {
  initialPlans: DBPlan[];
  currentPlanName?: string;
}

const planStyles: Record<string, any> = {
  free: {
    borderColor: 'border-slate-300 dark:border-slate-50',
    headerBg: 'bg-transparent',
    headerText: 'text-blue-600 dark:text-[#3B82F6]',
    priceColor: 'text-blue-600 dark:text-[#3B82F6]',
    buttonStyle: 'bg-[#0a0a0a] text-white border border-white hover:bg-[#1a1a1a]',
    tagActiveBg: '',
    tagActiveText: '',
  },
  basic: {
    borderColor: 'border-[#7d93b3]',
    headerBg: 'bg-[#5e6b7d]/30',
    headerText: 'text-blue-600 dark:text-[#5C8AE6]',
    priceColor: 'text-blue-600 dark:text-[#5C8AE6]',
    buttonStyle: 'bg-[#0a0a0a] text-white border border-white hover:bg-[#1a1a1a]',
    tagActiveBg: 'bg-white',
    tagActiveText: 'text-[#5C8AE6]',
  },
  premium: {
    borderColor: 'border-green-500 shadow-[0_0_25px_rgba(34,197,94,0.3)] z-10 scale-[1.02]',
    headerBg: 'bg-[#517a60]/30',
    headerText: 'text-green-600 dark:text-[#2EAA64]',
    priceColor: 'text-green-600 dark:text-[#2EAA64]',
    buttonStyle: 'bg-[#0a0a0a] text-[#2EAA64] border border-[#2EAA64] hover:bg-[#2EAA64]/10',
    tagActiveBg: 'bg-[#DCFCE7]',
    tagActiveText: 'text-[#2EAA64]',
  },
  platinum: {
    borderColor: 'border-[#a380b5]',
    headerBg: 'bg-[#765e82]/30',
    headerText: 'text-purple-600 dark:text-[#A05FD9]',
    priceColor: 'text-purple-600 dark:text-[#A05FD9]',
    buttonStyle: 'bg-[#0a0a0a] text-white border border-white hover:bg-[#1a1a1a]',
    tagActiveBg: 'bg-white',
    tagActiveText: 'text-[#A05FD9]',
  }
};

const getPlanStyle = (name: string) => {
  const key = name.toLowerCase();
  if (planStyles[key]) return planStyles[key];
  return {
    borderColor: 'border-neutral-700',
    headerBg: 'bg-neutral-800/30',
    headerText: 'text-white',
    priceColor: 'text-white',
    buttonStyle: 'bg-[#0a0a0a] text-white border border-white hover:bg-[#1a1a1a]',
    tagActiveBg: 'bg-white',
    tagActiveText: 'text-black',
  };
};

export default function PlansClient({ initialPlans, currentPlanName = "Premium" }: PlansClientProps) {
  const [billingCycle, setBillingCycle] = useState<'half-yearly' | 'yearly' | '2-yearly' | '3-yearly'>('yearly');
  const router = useRouter();

  // Helper to dynamically calculate button texts based on active user plan
  const getButtonText = (planName: string, currentName: string) => {
    const pName = planName.toLowerCase();
    const cName = currentName.toLowerCase();

    if (pName === cName) {
      return 'Renew Plan';
    }

    const tiers = ['free', 'basic', 'premium', 'platinum'];
    const pIndex = tiers.indexOf(pName);
    const cIndex = tiers.indexOf(cName);

    if (pIndex !== -1 && cIndex !== -1) {
      if (pIndex < cIndex) {
        return pName === 'free' ? 'Downgrade to Free' : 'Downgrade Plan';
      } else {
        return 'Upgrade Plan';
      }
    }

    return pName === 'free' ? 'Downgrade to Free' : 'Upgrade Plan';
  };

  return (
    <div className="max-w-7xl mx-auto py-6 px-6 lg:px-8">
      
      <div className="mb-8">
        <button 
          onClick={() => router.push('/dashboard/billing')} 
          className="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-900 dark:text-white transition-colors mb-6"
        >
          <ArrowLeft size={16} /> Back to Billing
        </button>
      </div>

      {/* Title Section at the top */}
      <div className="text-center max-w-2xl mx-auto mb-16 pt-4">
        <h1 className="text-4xl sm:text-5xl font-black text-[#111111] dark:text-white mb-4 leading-tight tracking-tight">
          Simple and <span className="text-[#E53935]">Flexible</span> Pricing
        </h1>
        <p className="text-gray-500 text-sm sm:text-base leading-relaxed max-w-lg mx-auto">
          No hidden fees, what you see is what you pay. Upgrade anytime, or cancel whenever you want.
        </p>
      </div>

      {/* Pricing Cards occupying full width */}
      <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
        {initialPlans.map((plan, i) => {
          const isFree = plan.priceYearly === 0 || plan.name.toLowerCase() === 'free';
          
          let price = plan.priceYearly;
          if (billingCycle === 'half-yearly') {
            price = plan.priceHalfYearly;
          } else if (billingCycle === '2-yearly') {
            price = Math.round((plan.priceYearly * 2) * 0.90);
          } else if (billingCycle === '3-yearly') {
            price = Math.round((plan.priceYearly * 3) * 0.85);
          }

          const style = getPlanStyle(plan.name);
          const buttonText = getButtonText(plan.name, currentPlanName);

          return (
            <motion.div 
              key={plan.id}
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: i * 0.1 }}
              className={`relative rounded-3xl border-2 ${style.borderColor} bg-[#0a0a0a] flex flex-col h-full`}
            >
              {plan.isPopular && (
                <div className="absolute -top-7 left-1/2 transform -translate-x-1/2 z-20 w-full flex justify-center">
                  <div className="bg-[#E53935] text-white text-[10px] sm:text-xs font-black px-4 py-1.5 rounded-b-xl uppercase tracking-widest text-center leading-tight shadow-md">
                    ★ MOST<br/>POPULAR
                  </div>
                </div>
              )}
              
              {/* Card Header */}
              <div className={`${style.headerBg} p-5 pb-6 text-center rounded-t-[1.35rem] ${plan.isPopular ? 'pt-10' : 'pt-6'}`}>
                
                {!isFree ? (
                  <div className="flex justify-center mb-6">
                    <div className="inline-flex bg-[#111111] p-1 rounded-xl border border-neutral-800 flex-wrap gap-1 justify-center max-w-[280px]">
                      <button
                        onClick={() => setBillingCycle('half-yearly')}
                        className={`px-2 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-colors ${billingCycle === 'half-yearly' ? `${style.tagActiveBg} ${style.tagActiveText}` : 'text-gray-500 hover:text-white'}`}
                      >
                        6 Mo
                      </button>
                      <button
                        onClick={() => setBillingCycle('yearly')}
                        className={`px-2 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-colors ${billingCycle === 'yearly' ? `${style.tagActiveBg} ${style.tagActiveText}` : 'text-gray-500 hover:text-white'}`}
                      >
                        1 Yr
                      </button>
                      <button
                        onClick={() => setBillingCycle('2-yearly')}
                        className={`px-2 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-colors relative ${billingCycle === '2-yearly' ? `${style.tagActiveBg} ${style.tagActiveText}` : 'text-gray-500 hover:text-white'}`}
                      >
                        2 Yrs
                        <span className="absolute -top-2.5 -right-1 bg-emerald-500 text-white text-[6px] font-black px-1 rounded-full uppercase tracking-tight scale-90">10% Off</span>
                      </button>
                      <button
                        onClick={() => setBillingCycle('3-yearly')}
                        className={`px-2 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-colors relative ${billingCycle === '3-yearly' ? `${style.tagActiveBg} ${style.tagActiveText}` : 'text-gray-500 hover:text-white'}`}
                      >
                        3 Yrs
                        <span className="absolute -top-2.5 -right-1 bg-emerald-500 text-white text-[6px] font-black px-1 rounded-full uppercase tracking-tight scale-90">15% Off</span>
                      </button>
                    </div>
                  </div>
                ) : (
                  <div className="h-10 mb-6"></div> /* Spacer for Free plan */
                )}

                <h3 className={`text-xl sm:text-2xl font-black mb-2 ${style.headerText}`}>{plan.name}</h3>
                
                {isFree ? (
                  <p className="text-sm font-bold text-gray-400 mb-6">{plan.description || 'Free Forever'}</p>
                ) : (
                  <div className={`mb-6 ${style.priceColor}`}>
                    <span className="text-2xl sm:text-3xl font-black">Rs {price.toLocaleString()}</span>
                    <span className="text-xs font-bold opacity-70">
                      /{billingCycle === 'yearly' 
                        ? 'Year' 
                        : billingCycle === 'half-yearly' 
                        ? '6mo' 
                        : billingCycle === '2-yearly' 
                        ? '2 Years' 
                        : '3 Years'}
                    </span>
                  </div>
                )}

                <Link 
                  href={isFree ? '#' : `/dashboard/billing/checkout?plan=${plan.id}&billing=${billingCycle}`}
                  className={`block w-full text-center py-3 rounded-xl font-bold text-sm transition-all ${style.buttonStyle}`}
                >
                  {buttonText}
                </Link>
              </div>

              {/* Card Body */}
              <div className="p-5 flex-1 bg-[#0a0a0a] rounded-b-3xl">
                <ul className="space-y-4 mb-6">
                  {plan.features.map((feature, idx) => (
                    <li key={feature.id || idx} className="flex items-start">
                      <Check className="h-4 w-4 text-emerald-500 shrink-0 mr-2.5 mt-0.5" />
                      <span className="text-gray-300 text-xs font-semibold">{feature.text}</span>
                    </li>
                  ))}
                  {plan.notIncluded && plan.notIncluded.map((feature, idx) => (
                    <li key={feature.id || idx} className="flex items-start opacity-40">
                      <X className="h-4 w-4 text-neutral-500 shrink-0 mr-2.5 mt-0.5" />
                      <span className="text-neutral-400 text-xs font-semibold line-through">{feature.text}</span>
                    </li>
                  ))}
                </ul>
              </div>
            </motion.div>
          );
        })}
      </div>
      
      <div className="mt-12 text-center flex flex-col sm:flex-row justify-between items-center text-sm text-gray-500 font-medium gap-4">
        <p>All prices are in NPR and charged per restaurant with applicable taxes added at checkout.</p>
        <Link href="/pricing" className="flex items-center gap-1 hover:text-white border border-neutral-800 rounded-lg px-4 py-2 hover:bg-neutral-900 transition-colors">
          Compare all plans <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" /></svg>
        </Link>
      </div>

    </div>
  );
}
