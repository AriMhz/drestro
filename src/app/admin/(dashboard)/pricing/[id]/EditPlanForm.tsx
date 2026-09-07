"use client";

import React, { useState } from "react";
import { Save, ArrowLeft, Check, X, Plus } from "lucide-react";
import Link from "next/link";

interface EditPlanFormProps {
  plan: any;
  updatePlan: (formData: FormData) => Promise<void>;
}

const COMMON_FEATURES = [
  "Dashboard Overview",
  "Menu Manager",
  "Restaurant Tables",
  "Hotel Room Manager",
  "Inventory Management",
  "Staff Panels",
  "Take Restaurant Order",
  "Take Room Service",
  "Hotel Reception Cashier",
  "Cashier Dashboard",
  "Waiter Dashboard",
  "Kitchen Display (KOT)",
  "Bar Display (BOT)",
  "Reports & Analytics",
  "Priority Support",
  "System Settings",
  "License Manager",
  "Cloud Sync & Backup"
];

export default function EditPlanForm({ plan, updatePlan }: EditPlanFormProps) {
  const [features, setFeatures] = useState(
    plan.features.map((f: any) => f.text).join("\n")
  );
  const [notIncluded, setNotIncluded] = useState(
    plan.notIncluded.map((f: any) => f.text).join("\n")
  );

  const getLines = (text: string) => {
    return text.split("\n").map(line => line.trim()).filter(line => line.length > 0);
  };

  const hasFeature = (listText: string, feature: string) => {
    return getLines(listText).some(line => line.toLowerCase() === feature.toLowerCase());
  };

  const handleIncludeToggle = (feature: string) => {
    const includedLines = getLines(features);
    const excludedLines = getLines(notIncluded);

    // If it is currently included, remove it
    if (hasFeature(features, feature)) {
      const updated = includedLines.filter(line => line.toLowerCase() !== feature.toLowerCase());
      setFeatures(updated.join("\n"));
    } else {
      // Add to included, remove from excluded if present
      const updatedIncluded = [...includedLines, feature];
      const updatedExcluded = excludedLines.filter(line => line.toLowerCase() !== feature.toLowerCase());
      setFeatures(updatedIncluded.join("\n"));
      setNotIncluded(updatedExcluded.join("\n"));
    }
  };

  const handleExcludeToggle = (feature: string) => {
    const includedLines = getLines(features);
    const excludedLines = getLines(notIncluded);

    // If it is currently excluded, remove it
    if (hasFeature(notIncluded, feature)) {
      const updated = excludedLines.filter(line => line.toLowerCase() !== feature.toLowerCase());
      setNotIncluded(updated.join("\n"));
    } else {
      // Add to excluded, remove from included if present
      const updatedExcluded = [...excludedLines, feature];
      const updatedIncluded = includedLines.filter(line => line.toLowerCase() !== feature.toLowerCase());
      setNotIncluded(updatedExcluded.join("\n"));
      setFeatures(updatedIncluded.join("\n"));
    }
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-5xl pb-20">
      <div className="flex items-center gap-4">
        <Link href="/admin/pricing" className="w-10 h-10 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-xl flex items-center justify-center text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white hover:bg-slate-100 dark:bg-[#222222] transition-colors">
          <ArrowLeft size={18} />
        </Link>
        <div>
          <h1 className="text-3xl font-bold text-[#111111] dark:text-white tracking-tight">
            {plan.id === 'new' ? 'Add New Software Plan' : `Edit Plan: ${plan.name}`}
          </h1>
        </div>
      </div>

      <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] p-6 md:p-8 rounded-2xl shadow-xl">
        <form action={updatePlan} className="space-y-8">
          <input type="hidden" name="id" value={plan.id} />
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-500 dark:text-gray-400">Plan Name</label>
              <input name="name" defaultValue={plan.name} required className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]" />
            </div>
            
            <div className="space-y-2 flex flex-col justify-end">
              <label className="flex items-center gap-3 cursor-pointer p-3.5 rounded-xl border border-slate-200 dark:border-[#333333] bg-slate-50 dark:bg-[#222222] hover:bg-slate-100 dark:hover:bg-[#2c2c2c] transition-colors">
                <input type="checkbox" name="isPopular" value="true" defaultChecked={plan.isPopular} className="w-5 h-5 rounded border-slate-300 dark:border-gray-600 text-[#E53935] focus:ring-[#E53935] bg-white dark:bg-gray-700" />
                <span className="text-sm font-semibold text-[#111111] dark:text-white">Mark as "Most Popular" badge</span>
              </label>
            </div>

            <div className="space-y-2 md:col-span-2">
              <label className="text-sm font-semibold text-slate-500 dark:text-gray-400">Description</label>
              <input name="description" defaultValue={plan.description} required className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]" />
            </div>

            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-500 dark:text-gray-400">Yearly Price (Rs)</label>
              <input name="priceYearly" type="number" defaultValue={plan.priceYearly} required className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]" />
            </div>

            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-500 dark:text-gray-400">Old Yearly Price (Optional Strike-through)</label>
              <input name="oldPriceYearly" type="number" defaultValue={plan.oldPriceYearly || ''} className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]" />
            </div>

            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-500 dark:text-gray-400">6 Months Price (Rs) *</label>
              <input name="priceHalfYearly" type="number" defaultValue={plan.priceHalfYearly} required className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]" />
            </div>

            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-500 dark:text-gray-400">Old 6 Months Price (Optional Strike-through)</label>
              <input name="oldPriceHalfYearly" type="number" defaultValue={plan.oldPriceHalfYearly || ''} className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]" />
            </div>

            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-500 dark:text-gray-400">Button Text</label>
              <input name="buttonText" defaultValue={plan.buttonText} required className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]" />
            </div>
            
            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-500 dark:text-gray-400">Button Style</label>
              <select name="buttonVariant" defaultValue={plan.buttonVariant} className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935] appearance-none">
                <option value="outline">Outline (White/Gray)</option>
                <option value="primary">Primary (Red Fill)</option>
              </select>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-semibold text-emerald-400">Included Features (One per line)</label>
              <textarea 
                name="features" 
                rows={8} 
                value={features}
                onChange={(e) => setFeatures(e.target.value)}
                className="w-full bg-slate-50 dark:bg-[#222222] border border-emerald-900/40 text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 font-mono text-sm leading-relaxed" 
                placeholder="Unlimited Tables&#10;Unlimited Users..." 
              />
            </div>

            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-400 dark:text-gray-500">Not Included Features (One per line)</label>
              <textarea 
                name="notIncluded" 
                rows={8} 
                value={notIncluded}
                onChange={(e) => setNotIncluded(e.target.value)}
                className="w-full bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500 font-mono text-sm leading-relaxed" 
                placeholder="Inventory Management&#10;Custom Roles..." 
              />
            </div>
          </div>

          {/* Interactive Common Features Helper Checklist */}
          <div className="bg-slate-50 dark:bg-[#222222]/30 p-6 rounded-2xl border border-slate-200 dark:border-[#333333] space-y-4">
            <div>
              <h3 className="text-base font-bold text-[#111111] dark:text-white flex items-center gap-2">
                <span className="text-emerald-500">✅</span> Common Features Checklist Helper
              </h3>
              <p className="text-xs text-slate-500 dark:text-gray-400 mt-1">
                Click `+ Include` to add a feature to the **Included** list, or `+ Exclude` to add it to the **Not Included** list. Highlighted items are already added.
              </p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              {COMMON_FEATURES.map((feature) => {
                const isIncluded = hasFeature(features, feature);
                const isExcluded = hasFeature(notIncluded, feature);

                return (
                  <div 
                    key={feature} 
                    className="flex items-center justify-between p-3 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-xl hover:border-slate-300 dark:hover:border-neutral-700 transition-colors"
                  >
                    <span className="text-xs font-bold text-slate-800 dark:text-gray-300 truncate mr-2">{feature}</span>
                    <div className="flex items-center gap-1.5 shrink-0">
                      {/* Included Action Button */}
                      {isIncluded ? (
                        <button
                          type="button"
                          onClick={() => handleIncludeToggle(feature)}
                          className="bg-emerald-500 text-white text-[10px] font-black px-2.5 py-1.5 rounded-lg border border-emerald-600 shadow-sm shadow-emerald-500/10 flex items-center gap-1"
                        >
                          <Check size={10} strokeWidth={3} /> Included
                        </button>
                      ) : (
                        <button
                          type="button"
                          onClick={() => handleIncludeToggle(feature)}
                          className="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-500 text-[10px] font-bold px-2.5 py-1.5 rounded-lg border border-emerald-500/20 flex items-center gap-1 transition-colors"
                        >
                          <Plus size={10} /> Include
                        </button>
                      )}

                      {/* Excluded Action Button */}
                      {isExcluded ? (
                        <button
                          type="button"
                          onClick={() => handleExcludeToggle(feature)}
                          className="bg-slate-600 text-white text-[10px] font-black px-2.5 py-1.5 rounded-lg border border-slate-700 shadow-sm flex items-center gap-1"
                        >
                          <X size={10} strokeWidth={3} /> Excluded
                        </button>
                      ) : (
                        <button
                          type="button"
                          onClick={() => handleExcludeToggle(feature)}
                          className="bg-slate-500/10 hover:bg-slate-500/20 text-slate-400 text-[10px] font-bold px-2.5 py-1.5 rounded-lg border border-slate-500/20 flex items-center gap-1 transition-colors"
                        >
                          <Plus size={10} /> Exclude
                        </button>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          <div className="flex justify-end pt-6 border-t border-slate-200 dark:border-[#333333]">
            <button type="submit" className="bg-[#E53935] hover:bg-red-650 text-white px-8 py-3.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-red-500/20">
              <Save size={18} /> {plan.id === 'new' ? 'Create Plan' : 'Save Changes'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
