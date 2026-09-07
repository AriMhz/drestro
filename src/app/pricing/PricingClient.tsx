"use client";

import React from 'react';
import { motion } from 'motion/react';
import { Check, X, HelpCircle, ArrowRight, Printer, Box, Zap, Wifi, ShoppingCart } from 'lucide-react';
import Link from 'next/link';

interface PricingClientProps {
  plans: any[];
  combos: any[];
  products: any[];
  compareFeatures: any[];
  initialTab?: 'software' | 'combo';
}

export default function PricingClient({ plans, combos, products, compareFeatures, initialTab = 'software' }: PricingClientProps) {
  const [activeTab, setActiveTab] = React.useState<'software' | 'combo'>(initialTab);

  React.useEffect(() => {
    if (initialTab) {
      setActiveTab(initialTab);
    }
  }, [initialTab]);

  const faqs = [
    { q: "Do you offer a free trial or demo?", a: "Yes, DRestro offers a Free Plan for you to explore the features and functionalities of our software with lifetime access." },
    { q: "Can I switch plans later?", a: "Yes, you can upgrade or downgrade your plan at any time based on your restaurant business needs." },
    { q: "Are there any setup or hidden charges?", a: "There are no hidden fees. Setup and onboarding support are included in your initial subscription plan." },
    { q: "Do I need to buy any extra hardware?", a: "No, DRestro works on Android, iOS, tablets, and desktops. There's no absolute need for additional hardware unless you want specialized receipt printers." },
    { q: "Can I cancel my subscription anytime?", a: "You can cancel your subscription plan at any time. Your account will simply revert to the Free version." },
  ];

  return (
    <div className="bg-[#FFFFFF] dark:bg-[#0a0a0a] min-h-screen pt-24 pb-24">
      {/* 1. Header Section */}
      <div className="max-w-[1024px] mx-auto px-6 lg:px-10 mb-20 text-center pt-8">
        <h1 className="text-4xl md:text-5xl font-black text-[#111111] dark:text-white mb-6 tracking-tight">Pricing & Plans</h1>
        <p className="text-xl text-[#666666] mb-10 leading-relaxed max-w-2xl mx-auto">No credit card required. No hidden fees, what you see is what you pay. Upgrade anytime, or cancel whenever you want.</p>
        
        {/* Tabs for Software and Combos */}
        <div className="inline-flex bg-slate-100 dark:bg-neutral-900 p-1 rounded-full mb-10 border border-slate-200 dark:border-neutral-800">
          <button 
            onClick={() => setActiveTab('software')}
            className={`px-8 py-3 rounded-full text-sm font-black transition-all ${activeTab === 'software' ? 'bg-white dark:bg-[#0a0a0a] text-slate-900 dark:text-white shadow-sm border border-slate-200 dark:border-neutral-700' : 'text-slate-500 hover:text-slate-700 dark:text-neutral-400 dark:hover:text-neutral-200'}`}
          >
            Software Plans
          </button>
          <button 
            onClick={() => setActiveTab('combo')}
            className={`px-8 py-3 rounded-full text-sm font-black transition-all ${activeTab === 'combo' ? 'bg-white dark:bg-[#0a0a0a] text-slate-900 dark:text-white shadow-sm border border-slate-200 dark:border-neutral-700' : 'text-slate-500 hover:text-slate-700 dark:text-neutral-400 dark:hover:text-neutral-200'}`}
          >
            Combo Packages (Software + Hardware)
          </button>
        </div>
      </div>

      {activeTab === 'software' && (
        <>
          {/* 2. Software Plans */}
          <div className="max-w-[1280px] mx-auto px-6 lg:px-10 mb-32">
            <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
              {plans.map((plan, i) => (
                <motion.div 
                  key={plan.name}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: i * 0.1 }}
                  className={`relative rounded-3xl p-8 border ${plan.isPopular ? 'border-[#E53935] shadow-xl shadow-red-100 transform lg:-translate-y-2' : 'border-[#E2E2E7] dark:border-neutral-800 shadow-sm'} bg-white dark:bg-[#0a0a0a] flex flex-col h-full`}
                >
                  {plan.isPopular && (
                    <div className="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                      <span className="bg-[#E53935] text-white text-[11px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-md">
                        Most Popular
                      </span>
                    </div>
                  )}
                  <div className="mb-8">
                    <h3 className="text-2xl font-black text-[#111111] dark:text-white mb-3">{plan.name}</h3>
                    <p className="text-[#666666] text-sm h-12 leading-relaxed">{plan.description}</p>
                    <div className="mt-6">
                      {plan.oldPriceYearly && (
                        <div className="text-sm font-bold text-gray-400 line-through mb-1">
                          Rs.{plan.oldPriceYearly.toLocaleString()}
                        </div>
                      )}
                      <div className="flex items-baseline text-[#111111] dark:text-white">
                        <span className="text-4xl font-black tracking-tight">Rs.{plan.priceYearly.toLocaleString()}</span>
                        {plan.priceYearly > 0 && <span className="ml-1 text-sm font-bold text-[#999999]">/yr</span>}
                      </div>
                      {plan.isPopular && <p className="text-xs text-[#E53935] font-bold mt-2">* 50% off on renewal rate</p>}
                    </div>
                  </div>
                  
                  <Link 
                    href="/login"
                    className={`w-full text-center py-4 rounded-xl font-black text-sm mb-8 transition-all ${
                      plan.buttonVariant === 'primary' 
                        ? 'bg-[#E53935] text-white hover:bg-red-700 shadow-sm' 
                        : 'bg-gray-100 dark:bg-neutral-800 text-[#111111] dark:text-white hover:bg-gray-200 dark:hover:bg-neutral-700'
                    }`}
                  >
                    {plan.buttonText}
                  </Link>

                  <div className="flex-1">
                    <p className="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Included Features</p>
                    <ul className="space-y-4 mb-6">
                      {plan.features.map((feature: any, idx: number) => (
                        <li key={idx} className="flex items-start">
                          <Check className="h-5 w-5 text-emerald-500 shrink-0 mr-3" />
                          <span className="text-[#333] dark:text-neutral-200 text-sm font-medium">{feature.text || feature}</span>
                        </li>
                      ))}
                      {plan.notIncluded && plan.notIncluded.map((feature: any, idx: number) => (
                        <li key={idx} className="flex items-start opacity-50">
                          <X className="h-5 w-5 text-[#999999] shrink-0 mr-3" />
                          <span className="text-[#999999] text-sm">{feature.text || feature}</span>
                        </li>
                      ))}
                    </ul>
                  </div>
                </motion.div>
              ))}
            </div>

            {/* Premium Enterprise Banner */}
            <div className="mt-12 rounded-3xl p-10 lg:p-14 text-white flex flex-col md:flex-row items-center justify-between relative overflow-hidden shadow-[0_20px_40px_rgba(0,0,0,0.15)] bg-gradient-to-br from-[#0a0f1c] via-[#0f172a] to-[#064e3b] dark:from-[#050505] dark:via-[#0a0a0a] dark:to-[#022c22]">
              
              {/* Subtle Decorative elements */}
              <div className="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-500/10 rounded-full blur-[120px] pointer-events-none translate-x-1/3 -translate-y-1/3"></div>
              <div className="absolute bottom-0 left-0 w-[400px] h-[400px] bg-blue-500/10 rounded-full blur-[100px] pointer-events-none -translate-x-1/3 translate-y-1/3"></div>

              <div className="mb-8 md:mb-0 md:mr-8 max-w-3xl relative z-10">
                <div className="flex items-center gap-3 mb-5">
                  <span className="text-emerald-300 font-black tracking-widest uppercase text-xs bg-emerald-400/10 px-4 py-1.5 rounded-full border border-emerald-400/30 shadow-[0_0_15px_rgba(52,211,153,0.15)]">White-Label Enterprise</span>
                </div>
                <h3 className="text-4xl lg:text-5xl font-black mb-5 tracking-tight text-white drop-shadow-sm">Your Name. Your Logo. Your System.</h3>
                <p className="text-slate-300 text-lg lg:text-xl leading-relaxed mb-8 max-w-2xl font-medium">
                  Want our powerful POS software, but with your own branding? We will customize the entire system to use your logo, your colors, and your own website link (like <strong className="text-white">pos.yourrestaurant.com</strong>). Your staff will only see your brand!
                </p>
                <ul className="flex flex-col sm:flex-row gap-5 sm:gap-8 text-base font-bold text-slate-200">
                  <li className="flex items-center gap-2.5"><div className="bg-emerald-500/20 p-1 rounded-full"><Check className="w-4 h-4 text-emerald-400" /></div> Your Own Web Address</li>
                  <li className="flex items-center gap-2.5"><div className="bg-emerald-500/20 p-1 rounded-full"><Check className="w-4 h-4 text-emerald-400" /></div> Your Logo & Colors</li>
                  <li className="flex items-center gap-2.5"><div className="bg-emerald-500/20 p-1 rounded-full"><Check className="w-4 h-4 text-emerald-400" /></div> VIP Setup & Support</li>
                </ul>
              </div>
              
              <Link href="/contact" className="w-full md:w-auto bg-white text-emerald-950 font-black text-base py-4 px-10 rounded-xl hover:bg-emerald-50 hover:scale-105 transition-all whitespace-nowrap text-center relative z-10 shadow-[0_8px_25px_rgba(255,255,255,0.15)] flex flex-col items-center justify-center">
                <span className="block">Contact Sales</span>
                <span className="text-[10px] uppercase tracking-widest text-emerald-700/80 mt-0.5">Let's build together</span>
              </Link>
            </div>
          </div>

          {/* 5. Compare Plans Table */}
          <div className="max-w-[1024px] mx-auto px-6 lg:px-10 mb-32">
            <div className="text-center mb-12">
              <h2 className="text-3xl lg:text-4xl tracking-tight font-black text-[#111111] dark:text-white mb-4">Compare Plans in Detail</h2>
              <p className="text-[#666666] text-lg">Explore all features to find the right fit for your business.</p>
            </div>
            
            <div className="overflow-x-auto rounded-3xl border border-[#E2E2E7] dark:border-neutral-800 bg-white dark:bg-[#0a0a0a] shadow-sm">
              <table className="w-full text-left border-collapse min-w-[800px]">
                <thead>
                  <tr>
                    <th className="w-1/3 p-6 bg-slate-50 dark:bg-neutral-900 border-b border-[#E2E2E7] dark:border-neutral-800 font-black text-slate-400 dark:text-neutral-500 text-[10px] uppercase tracking-widest">Features</th>
                    <th className={`p-6 border-b font-black text-center text-sm ${plans.find(p => p.name === 'Free')?.isPopular ? 'bg-red-50 dark:bg-red-950/30 border-red-100 dark:border-red-900/50 text-[#E53935]' : 'bg-white dark:bg-[#0a0a0a] border-[#E2E2E7] dark:border-neutral-800 text-slate-900 dark:text-white'}`}>Free</th>
                    <th className={`p-6 border-b font-black text-center text-sm ${plans.find(p => p.name === 'Basic')?.isPopular ? 'bg-red-50 dark:bg-red-950/30 border-red-100 dark:border-red-900/50 text-[#E53935]' : 'bg-white dark:bg-[#0a0a0a] border-[#E2E2E7] dark:border-neutral-800 text-slate-900 dark:text-white'}`}>Basic</th>
                    <th className={`p-6 border-b font-black text-center text-sm ${plans.find(p => p.name === 'Premium')?.isPopular ? 'bg-red-50 dark:bg-red-950/30 border-red-100 dark:border-red-900/50 text-[#E53935]' : 'bg-white dark:bg-[#0a0a0a] border-[#E2E2E7] dark:border-neutral-800 text-slate-900 dark:text-white'}`}>Premium</th>
                    <th className={`p-6 border-b font-black text-center text-sm ${plans.find(p => p.name === 'Platinum')?.isPopular ? 'bg-red-50 dark:bg-red-950/30 border-red-100 dark:border-red-900/50 text-[#E53935]' : 'bg-white dark:bg-[#0a0a0a] border-[#E2E2E7] dark:border-neutral-800 text-slate-900 dark:text-white'}`}>Platinum</th>
                  </tr>
                </thead>
                <tbody>
                  {compareFeatures.map((row, i) => {
                    const isNewCategory = i === 0 || compareFeatures[i - 1].category !== row.category;
                    return (
                      <React.Fragment key={i}>
                        {isNewCategory && (
                          <tr>
                            <td colSpan={5} className="bg-slate-50 dark:bg-neutral-900 p-4 text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest border-y border-slate-200 dark:border-neutral-700">
                              {row.category}
                            </td>
                          </tr>
                        )}
                        <tr className="border-b border-slate-100 dark:border-neutral-800 last:border-0 hover:bg-slate-50 dark:hover:bg-neutral-800 dark:bg-neutral-900 transition-colors">
                          <td className="p-5 text-slate-600 dark:text-neutral-300 font-bold text-sm">
                            {row.name}
                          </td>
                          <td className={`p-5 text-center text-sm ${plans.find(p => p.name === 'Free')?.isPopular ? 'text-[#E53935] font-bold bg-red-50/50 dark:bg-red-950/20' : 'text-slate-500 dark:text-neutral-400 font-medium'}`}>
                            {typeof row.free === 'boolean' ? (row.free ? <Check className={`w-5 h-5 mx-auto ${plans.find(p => p.name === 'Free')?.isPopular ? 'text-[#E53935]' : 'text-emerald-500'}`} /> : <X className={`w-4 h-4 mx-auto ${plans.find(p => p.name === 'Free')?.isPopular ? 'text-red-200' : 'text-slate-300 dark:text-neutral-600'}`} />) : row.free}
                          </td>
                          <td className={`p-5 text-center text-sm ${plans.find(p => p.name === 'Basic')?.isPopular ? 'text-[#E53935] font-bold bg-red-50/50 dark:bg-red-950/20' : 'text-slate-500 dark:text-neutral-400 font-medium'}`}>
                            {typeof row.basic === 'boolean' ? (row.basic ? <Check className={`w-5 h-5 mx-auto ${plans.find(p => p.name === 'Basic')?.isPopular ? 'text-[#E53935]' : 'text-emerald-500'}`} /> : <X className={`w-4 h-4 mx-auto ${plans.find(p => p.name === 'Basic')?.isPopular ? 'text-red-200' : 'text-slate-300 dark:text-neutral-600'}`} />) : row.basic}
                          </td>
                          <td className={`p-5 text-center text-sm ${plans.find(p => p.name === 'Premium')?.isPopular ? 'text-[#E53935] font-bold bg-red-50/50 dark:bg-red-950/20' : 'text-slate-500 dark:text-neutral-400 font-medium'}`}>
                            {typeof row.premium === 'boolean' ? (row.premium ? <Check className={`w-5 h-5 mx-auto ${plans.find(p => p.name === 'Premium')?.isPopular ? 'text-[#E53935]' : 'text-emerald-500'}`} /> : <X className={`w-4 h-4 mx-auto ${plans.find(p => p.name === 'Premium')?.isPopular ? 'text-red-200' : 'text-slate-300 dark:text-neutral-600'}`} />) : row.premium}
                          </td>
                          <td className={`p-5 text-center text-sm ${plans.find(p => p.name === 'Platinum')?.isPopular ? 'text-[#E53935] font-bold bg-red-50/50 dark:bg-red-950/20' : 'text-slate-500 dark:text-neutral-400 font-medium'}`}>
                            {typeof row.plat === 'boolean' ? (row.plat ? <Check className={`w-5 h-5 mx-auto ${plans.find(p => p.name === 'Platinum')?.isPopular ? 'text-[#E53935]' : 'text-emerald-500'}`} /> : <X className={`w-4 h-4 mx-auto ${plans.find(p => p.name === 'Platinum')?.isPopular ? 'text-red-200' : 'text-slate-300 dark:text-neutral-600'}`} />) : row.plat}
                          </td>
                        </tr>
                      </React.Fragment>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}

      {activeTab === 'combo' && (
        <>
          {/* 3. Combo Packages */}
          <div className="bg-orange-50 dark:bg-[#0a0a0a] py-32 border-y border-orange-100 dark:border-neutral-800 mb-32">
            <div className="max-w-[1024px] mx-auto px-6 lg:px-10">
              <div className="text-center mb-16">
                <h2 className="text-3xl md:text-5xl font-black text-orange-900 dark:text-orange-500 mb-4 tracking-tight">Don't worry, We have Combo offers for you!</h2>
                <p className="text-xl text-orange-700/80 dark:text-orange-400/80 max-w-2xl mx-auto">Get hardware and software bundled together at an unbeatable price.</p>
              </div>

              <div className="grid md:grid-cols-2 gap-8">
                {combos.map((combo, idx) => (
                  <motion.div 
                    key={idx}
                    initial={{ opacity: 0, y: 20 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    className="bg-white dark:bg-[#0a0a0a] rounded-3xl p-8 lg:p-10 shadow-xl shadow-orange-900/5 dark:shadow-none border border-orange-100 dark:border-neutral-800 relative"
                  >
                    {idx === 0 && (
                      <span className="absolute -top-4 -right-4 bg-orange-500 text-white text-xs font-black px-4 py-2 rounded-xl shadow-lg transform rotate-3">
                        Best Deal
                      </span>
                    )}
                    <h3 className="text-2xl font-black text-slate-900 dark:text-white mb-3">{combo.name}</h3>
                    <p className="text-slate-600 dark:text-neutral-300 mb-6">{combo.description}</p>
                    
                    <div className="mb-8 flex items-baseline">
                      <span className="text-4xl font-black text-[#111111] dark:text-white tracking-tight">Rs.{combo.price?.toLocaleString() || '0'}</span>
                      {combo.oldPrice && (
                        <span className="ml-3 text-lg font-bold text-gray-400 line-through">Rs.{combo.oldPrice.toLocaleString()}</span>
                      )}
                    </div>

                    <div className="bg-slate-50 dark:bg-neutral-900 rounded-2xl p-6 mb-8 border border-slate-100 dark:border-neutral-800">
                      <p className="text-xs font-black text-slate-400 dark:text-neutral-500 uppercase tracking-widest mb-4">Items Included</p>
                      <div className="space-y-6">
                        {combo.items.map((item: any, i: number) => (
                          <div key={i} className="flex gap-4">
                            <div className="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                              {i === 0 ? <Check className="w-5 h-5 text-orange-600" /> : <Printer className="w-5 h-5 text-orange-600" />}
                            </div>
                            <div>
                              <h4 className="font-bold text-slate-800 dark:text-neutral-200">{item.title}</h4>
                              <p className="text-sm text-slate-500 dark:text-neutral-400 mt-1">{item.sub}</p>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>

                    <div className="flex gap-4">
                      <Link href="/contact" className="flex-1 text-center py-4 bg-orange-500 text-white font-black rounded-xl hover:bg-orange-600 transition-all">
                        Contact Sales
                      </Link>
                      <Link 
                        href={`/offer/${combo.id}`}
                        className="px-6 py-4 bg-white dark:bg-[#0a0a0a] border-2 border-slate-200 dark:border-neutral-700 text-slate-600 dark:text-neutral-300 font-black rounded-xl hover:bg-slate-50 dark:hover:bg-neutral-800 dark:bg-neutral-900 transition-all text-center flex items-center justify-center"
                      >
                        Detail
                      </Link>
                    </div>
                  </motion.div>
                ))}
              </div>
            </div>
          </div>

          {/* 4. Hardware Products */}
          <div className="max-w-[1280px] mx-auto px-6 lg:px-10 mb-32">
            <div className="text-center mb-12">
              <h2 className="text-3xl md:text-[40px] font-black text-[#111111] dark:text-white mb-2 tracking-tight">Products</h2>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              {products.map((product, idx) => (
                <div key={idx} className="bg-white dark:bg-[#0a0a0a] border border-[#E2E2E7] dark:border-neutral-800 rounded-3xl p-6 relative group overflow-hidden shadow-[0_4px_12px_rgba(0,0,0,0.02)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] transition-all duration-500 h-[380px] flex flex-col hover:-translate-y-1">
                  
                  {/* Enhanced Pill Save Badge */}
                  {product.save && (
                    <div className="absolute top-4 right-4 bg-emerald-500 text-white text-[10px] font-black px-3 py-1.5 rounded-full z-20 shadow-sm uppercase tracking-widest flex items-center gap-1">
                      <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                      Save Rs.{product.save}
                    </div>
                  )}

                  {/* Premium Image Container with Soft Background */}
                  <div className="flex-shrink-0 h-[160px] bg-slate-50 dark:bg-neutral-900/50 rounded-2xl flex items-center justify-center mb-6 p-4 group-hover:bg-slate-100/50 transition-colors duration-500">
                    <img src={product.image} alt={product.name} className="max-h-[120px] object-contain group-hover:scale-105 transition-transform duration-500" />
                  </div>

                  {/* Upgraded Typography Details */}
                  <div className="flex flex-col flex-grow px-2">
                    <h3 className="text-[17px] font-black text-[#111111] dark:text-white mb-2 leading-snug">{product.name}</h3>
                    <p className="text-[#666666] text-[13px] leading-relaxed line-clamp-2 mb-4">{product.description}</p>
                    <div className="mt-auto flex items-end gap-2.5">
                      <span className="text-2xl font-black text-[#111111] dark:text-white tracking-tight">Rs.{product.price}</span>
                      {product.oldPrice && (
                        <span className="text-[14px] font-bold text-[#999999] line-through mb-[3px]">Rs.{product.oldPrice}</span>
                      )}
                    </div>
                  </div>

                  {/* Ultra-Premium Glassmorphism Overlay */}
                  <div className="absolute inset-0 bg-white dark:bg-[#0a0a0a]/70 backdrop-blur-[8px] opacity-0 group-hover:opacity-100 transition-all duration-500 z-10 flex flex-col items-center justify-center gap-4 rounded-3xl scale-95 group-hover:scale-100">
                    <button className="bg-[#E53935] text-white text-[14px] font-black px-8 py-3.5 rounded-full hover:bg-red-700 transition-all transform hover:scale-105 flex items-center gap-2 shadow-sm">
                      Buy Now
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </button>
                    <button className="bg-white dark:bg-[#0a0a0a] border-2 border-slate-200 dark:border-neutral-700 text-slate-700 text-[14px] font-bold px-8 py-3.5 rounded-full hover:bg-slate-50 dark:hover:bg-neutral-800 dark:bg-neutral-900 hover:border-slate-300 hover:text-[#111111] dark:text-white transition-all flex items-center gap-2 shadow-sm">
                      Know More
                      <ArrowRight className="w-4 h-4" />
                    </button>
                  </div>

                </div>
              ))}
            </div>
            
            <div className="mt-12 flex justify-center">
              <Link href="/products" className="bg-[#E53935] text-white text-[15px] font-bold px-8 py-3.5 rounded-md hover:bg-red-700 transition flex items-center gap-2 shadow-sm">
                See All Products
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
              </Link>
            </div>
          </div>
        </>
      )}

      {/* 6. FAQ Section */}
      <div className="max-w-[800px] mx-auto px-6 lg:px-10">
        <div className="text-center mb-16">
          <h2 className="text-3xl lg:text-4xl tracking-tight font-black text-[#111111] dark:text-white mb-4">Frequently Asked Questions</h2>
        </div>
        <div className="space-y-4">
          {faqs.map((faq, idx) => (
            <div key={idx} className="bg-white dark:bg-[#0a0a0a] border border-slate-200 dark:border-neutral-700 rounded-2xl p-6">
              <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-2">{faq.q}</h3>
              <p className="text-slate-600 dark:text-neutral-300 leading-relaxed">{faq.a}</p>
            </div>
          ))}
        </div>
      </div>

    </div>
  );
}
