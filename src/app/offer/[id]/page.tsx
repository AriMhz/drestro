import { notFound } from "next/navigation";
import { prisma } from "@/src/lib/prisma";
import Link from "next/link";
import { ArrowLeft, Check, Printer, MessageCircle, Star, Sparkles, AlertCircle } from "lucide-react";

export const dynamic = "force-dynamic";

const slugify = (text: string) => 
  text.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "");

export default async function ComboDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const resolvedParams = await params;
  const { id } = resolvedParams;

  // Fetch all combo packages
  const combos = await prisma.comboPackage.findMany({
    include: { features: true }
  });

  // Find matching combo by ID or slugified name
  const combo = combos.find(c => c.id === id || slugify(c.name) === id);

  if (!combo) {
    notFound();
  }

  // Fetch plans to match features/pricing cards
  const plans = await prisma.plan.findMany({
    include: { features: true, notIncluded: true }
  });

  // Fetch WhatsApp number setting
  const whatsappSetting = await prisma.siteSetting.findUnique({
    where: { key: "whatsapp_number" }
  });
  const whatsappNumber = whatsappSetting?.value || "9779865029558";

  // Try to match a software plan mentioned in the combo package name/features
  const matchedPlan = plans.find(p => 
    combo.name.toLowerCase().includes(p.name.toLowerCase()) ||
    combo.subtitle.toLowerCase().includes(p.name.toLowerCase()) ||
    combo.features.some(f => f.text.toLowerCase().includes(p.name.toLowerCase()))
  );

  // Check if it includes a printer
  const hasPrinter = combo.features.some(f => f.text.toLowerCase().includes("printer"));

  const WHATSAPP_MSG = encodeURIComponent(
    `Hi! I am interested in the ${combo.name} (Rs. ${combo.price.toLocaleString()}). Please tell me more about it.`
  );

  return (
    <div className="bg-[#FFFFFF] dark:bg-[#0a0a0a] min-h-screen pt-28 pb-24 text-slate-800 dark:text-slate-200">
      <div className="max-w-[1200px] mx-auto px-6 lg:px-10">
        
        {/* Back Link */}
        <div className="mb-10">
          <Link 
            href="/pricing?tab=combo" 
            className="inline-flex items-center text-sm font-black text-slate-500 dark:text-neutral-400 hover:text-[#E53935] transition-colors"
          >
            <ArrowLeft className="w-4 h-4 mr-2" /> Back to Packages & Pricing
          </Link>
        </div>

        <div className="flex flex-col lg:flex-row gap-12 lg:gap-16">
          
          {/* Left Column: Visual Package Cards */}
          <div className="lg:w-5/12 flex flex-col gap-8">
            
            {/* Matched Plan Card */}
            {matchedPlan && (
              <div className="bg-white dark:bg-[#111] rounded-3xl p-8 border border-slate-200 dark:border-neutral-800 shadow-md relative overflow-hidden">
                <div className="absolute top-0 right-0 bg-[#E53935] text-white text-[9px] font-black px-3 py-1 rounded-bl-xl uppercase tracking-widest">
                  Software Included
                </div>
                <div className="mb-6">
                  <h3 className="text-xl font-black text-slate-900 dark:text-white mb-1">
                    {matchedPlan.name} Plan
                  </h3>
                  <p className="text-xs text-slate-500 dark:text-neutral-400">
                    {matchedPlan.description}
                  </p>
                  <div className="mt-4 flex items-baseline text-slate-900 dark:text-white">
                    <span className="text-3xl font-black tracking-tight">
                      Rs. {matchedPlan.priceYearly.toLocaleString()}
                    </span>
                    <span className="ml-1 text-xs font-bold text-slate-400 dark:text-neutral-500">/yr value</span>
                  </div>
                </div>
                
                <div className="border-t border-slate-100 dark:border-neutral-800 pt-6">
                  <p className="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-neutral-500 mb-3">
                    Features Included
                  </p>
                  <ul className="space-y-3">
                    {matchedPlan.features.slice(0, 5).map((feature: any, idx: number) => (
                      <li key={idx} className="flex items-start">
                        <Check className="h-4 w-4 text-emerald-500 shrink-0 mr-2.5 mt-0.5" />
                        <span className="text-slate-600 dark:text-neutral-300 text-xs font-semibold">
                          {feature.text}
                        </span>
                      </li>
                    ))}
                    {matchedPlan.features.length > 5 && (
                      <li className="text-[11px] text-slate-400 dark:text-neutral-500 font-bold pl-6">
                        + {matchedPlan.features.length - 5} more features
                      </li>
                    )}
                  </ul>
                </div>
              </div>
            )}

            {/* Printer Card */}
            {hasPrinter && (
              <div className="bg-slate-50 dark:bg-[#111] rounded-3xl p-8 border border-slate-200 dark:border-neutral-800 shadow-sm flex flex-col items-center text-center">
                <div className="w-14 h-14 rounded-2xl bg-orange-100 dark:bg-orange-950/30 flex items-center justify-center mb-4">
                  <Printer className="w-6 h-6 text-orange-600 dark:text-orange-500" />
                </div>
                <h4 className="text-lg font-black text-slate-900 dark:text-white mb-1">
                  Thermal Receipt Printer
                </h4>
                <p className="text-xs text-slate-500 dark:text-neutral-400 mb-6">
                  High-speed billing & kitchen printer (Worth Rs. 13,500)
                </p>
                <div className="relative w-48 h-40 flex items-center justify-center bg-white dark:bg-[#181818] rounded-2xl border border-slate-100 dark:border-neutral-800 p-4 shadow-inner">
                  <img 
                    src="/images/products/printer.png" 
                    alt="Thermal Printer" 
                    className="max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal"
                  />
                </div>
              </div>
            )}

          </div>

          {/* Right Column: Combo Specifications */}
          <div className="lg:w-7/12">
            
            {/* Title / Badges */}
            <div className="mb-6">
              <div className="inline-flex items-center gap-1.5 bg-orange-50 dark:bg-orange-950/20 text-orange-600 dark:text-orange-400 px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-orange-100 dark:border-orange-900/30 mb-4 shadow-sm">
                <Sparkles size={13} className="animate-pulse" /> Super Saver Combo
              </div>
              <h1 className="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-3">
                👑 {combo.name}
              </h1>
              <p className="text-lg text-slate-500 dark:text-neutral-400 font-medium">
                {combo.subtitle}
              </p>
            </div>

            {/* WhatsApp Contact Action */}
            <div className="mb-8">
              <a 
                href={`https://wa.me/${whatsappNumber}?text=${WHATSAPP_MSG}`}
                target="_blank" 
                rel="noopener noreferrer"
                className="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-[#25D366] hover:bg-[#1fb855] text-white font-black px-8 py-4 rounded-2xl shadow-lg shadow-green-500/10 transition-all transform hover:scale-[1.02] cursor-pointer"
              >
                <MessageCircle size={22} />
                <span>Contact Via WhatsApp</span>
              </a>
            </div>

            {/* Overview */}
            <div className="mb-10 pb-8 border-b border-slate-100 dark:border-neutral-800">
              <h3 className="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-neutral-500 mb-3">
                Overview
              </h3>
              <p className="text-slate-600 dark:text-neutral-300 leading-relaxed font-semibold">
                The {combo.name} is designed specifically for cafe owners, restaurants, and lounges looking to fully automate their system. It combines our advanced SaaS subscription with high-performance billing hardware to get you up and running instantly with zero hassle.
              </p>
            </div>

            {/* What's Included & Pricing Math */}
            <div className="mb-10 pb-8 border-b border-slate-100 dark:border-neutral-800">
              <h3 className="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-neutral-500 mb-4">
                What's Included
              </h3>
              
              <ul className="space-y-3 mb-6 pl-1">
                {combo.features.map((feature) => (
                  <li key={feature.id} className="flex items-start text-sm font-bold text-slate-700 dark:text-neutral-300">
                    <span className="text-[#E53935] mr-3 mt-0.5">•</span>
                    {feature.text}
                  </li>
                ))}
              </ul>

              <div className="bg-slate-50 dark:bg-[#111] border border-slate-200 dark:border-neutral-800 rounded-2xl p-6 max-w-md">
                <div className="space-y-3">
                  <div className="flex justify-between text-sm font-bold text-slate-500">
                    <span>Total Value:</span>
                    <span className="line-through">Rs. {combo.originalPrice.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between text-sm font-black text-emerald-600 dark:text-emerald-500">
                    <span>You Save:</span>
                    <span>Rs. {combo.savings.toLocaleString()}</span>
                  </div>
                  <div className="border-t border-slate-200 dark:border-neutral-800 pt-3 flex justify-between items-baseline">
                    <span className="text-sm font-black text-slate-800 dark:text-white">Combo Price:</span>
                    <span className="text-3xl font-black text-[#111] dark:text-white tracking-tight">
                      Rs. {combo.price.toLocaleString()}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            {/* Benefits */}
            <div className="mb-10 pb-8 border-b border-slate-100 dark:border-neutral-800">
              <h3 className="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-neutral-500 mb-4">
                Benefits
              </h3>
              <ul className="space-y-3 pl-1">
                <li className="flex items-start text-sm font-bold text-slate-700 dark:text-neutral-300">
                  <span className="text-emerald-500 mr-3 mt-0.5">✓</span>
                  Get advanced reports & financial insights
                </li>
                <li className="flex items-start text-sm font-bold text-slate-700 dark:text-neutral-300">
                  <span className="text-emerald-500 mr-3 mt-0.5">✓</span>
                  Automate inventory & menu setup
                </li>
                <li className="flex items-start text-sm font-bold text-slate-700 dark:text-neutral-300">
                  <span className="text-emerald-500 mr-3 mt-0.5">✓</span>
                  Scale your restaurant with professional tools
                </li>
                <li className="flex items-start text-sm font-bold text-slate-700 dark:text-neutral-300">
                  <span className="text-emerald-500 mr-3 mt-0.5">✓</span>
                  Printer included for smooth billing
                </li>
              </ul>
            </div>

            {/* Who is it for? */}
            <div className="mb-10 pb-8 border-b border-slate-100 dark:border-neutral-800">
              <h3 className="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-neutral-500 mb-4">
                Who Is It For?
              </h3>
              <ul className="space-y-3 pl-1">
                <li className="flex items-start text-sm font-bold text-slate-700 dark:text-neutral-300">
                  <span className="text-orange-500 mr-3 mt-0.5">▪</span>
                  Mid-sized restaurants with busy operations
                </li>
                <li className="flex items-start text-sm font-bold text-slate-700 dark:text-neutral-300">
                  <span className="text-orange-500 mr-3 mt-0.5">▪</span>
                  Bars & lounges that need team access
                </li>
                <li className="flex items-start text-sm font-bold text-slate-700 dark:text-neutral-300">
                  <span className="text-orange-500 mr-3 mt-0.5">▪</span>
                  Growing cafés expanding their menu & staff
                </li>
              </ul>
            </div>

            {/* Why Choose This Package? */}
            <div>
              <h3 className="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-neutral-500 mb-3">
                ⭐ Why Choose This Package?
              </h3>
              <p className="text-sm text-slate-600 dark:text-neutral-400 leading-relaxed font-semibold">
                Because it's the best value bundle for a restaurant that's growing - saving you money while giving you access to advanced tools and premium pre-configured hardware.
              </p>
            </div>

          </div>

        </div>

      </div>
    </div>
  );
}
