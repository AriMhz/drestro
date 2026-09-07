"use client";

import React, { useState } from "react";
import Link from "next/link";
import { Tag, Edit, Package, Trash2 } from "lucide-react";
import { ComboForm } from "./ComboForm";
import { DynamicIcon } from "@/src/components/DynamicIcon";

interface PricingAdminClientProps {
  plans: any[];
  comparisonTable: any[];
  combos: any[];
  saveComboAction: (formData: FormData) => Promise<void>;
  deleteComboAction: (formData: FormData) => Promise<void>;
  deletePlanAction: (formData: FormData) => Promise<void>;
}

export default function PricingAdminClient({ plans, comparisonTable, combos, saveComboAction, deleteComboAction, deletePlanAction }: PricingAdminClientProps) {
  const [activeTab, setActiveTab] = useState<'software' | 'combo'>('software');
  const [editingCombo, setEditingCombo] = useState<any>(null);

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 className="text-3xl font-bold text-[#111111] dark:text-white tracking-tight flex items-center gap-3">
            {activeTab === 'software' ? <Tag className="text-[#E53935]" /> : <Package className="text-emerald-500" />}
            Pricing & Packages
          </h1>
          <p className="text-slate-500 dark:text-gray-400 mt-2">Manage your software plans, combo packages, and feature comparisons.</p>
        </div>
        {activeTab === 'software' && (
          <Link
            href="/admin/pricing/new"
            className="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#E53935] hover:bg-red-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/10 transition-all cursor-pointer"
          >
            Add New Plan
          </Link>
        )}
      </div>

      {/* Tabs */}
      <div className="inline-flex bg-slate-100 dark:bg-[#222222] p-1 rounded-full mb-4 border border-slate-200 dark:border-[#333333]">
        <button 
          onClick={() => setActiveTab('software')}
          className={`px-8 py-2.5 rounded-full text-sm font-bold transition-all ${activeTab === 'software' ? 'bg-[#E53935] text-white shadow-sm' : 'text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white'}`}
        >
          Software Plans
        </button>
        <button 
          onClick={() => setActiveTab('combo')}
          className={`px-8 py-2.5 rounded-full text-sm font-bold transition-all ${activeTab === 'combo' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white'}`}
        >
          Combo Packages
        </button>
      </div>

      {activeTab === 'software' && (
        <div className="space-y-8 mt-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
          <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {plans.map((plan) => (
              <div key={plan.id} className={`bg-white dark:bg-[#1A1A1A] border ${plan.isPopular ? 'border-[#E53935]/50' : 'border-slate-200 dark:border-[#333333]'} p-6 rounded-2xl relative flex flex-col`}>
                {plan.isPopular && (
                  <span className="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#E53935] text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">
                    Popular
                  </span>
                )}
                
                <div className="mb-6 flex-1">
                  <h3 className="text-xl font-bold text-[#111111] dark:text-white mb-2">{plan.name}</h3>
                  <div className="text-sm text-slate-500 dark:text-gray-400 h-10">{plan.description}</div>
                  
                  <div className="mt-4">
                    <div className="text-xs text-slate-400 dark:text-gray-500 uppercase tracking-widest font-bold mb-1">Yearly</div>
                    <div className="text-2xl font-black text-[#111111] dark:text-white">Rs. {plan.priceYearly.toLocaleString()}</div>
                  </div>
                </div>

                <div className="space-y-2 mb-6">
                  <div className="text-xs font-bold text-slate-500 dark:text-gray-400 uppercase">Features: {plan.features.length} included</div>
                </div>

                <div className="flex gap-2 w-full mt-auto">
                  <Link href={`/admin/pricing/${plan.id}`} className="flex-1 bg-slate-100 dark:bg-[#222222] hover:bg-slate-200 dark:hover:bg-[#333333] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <Edit size={14} /> Edit Plan
                  </Link>
                  <form action={deletePlanAction} onSubmit={(e) => { if(!confirm(`Are you sure you want to delete the plan "${plan.name}"?`)) e.preventDefault(); }} className="shrink-0">
                    <input type="hidden" name="id" value={plan.id} />
                    <button type="submit" className="p-2.5 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500/20 transition-colors flex items-center justify-center cursor-pointer" title="Delete Plan">
                      <Trash2 size={14} />
                    </button>
                  </form>
                </div>
              </div>
            ))}
          </div>

          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden mt-8">
            <div className="p-6 border-b border-slate-200 dark:border-[#333333] flex justify-between items-center bg-slate-100 dark:bg-[#222222]">
              <div>
                <h2 className="text-lg font-bold text-[#111111] dark:text-white">Feature Comparison Table</h2>
                <p className="text-sm text-slate-500 dark:text-gray-400">Manage the {comparisonTable.length} features shown on the public pricing page.</p>
              </div>
              <Link href="/admin/pricing/comparison" className="bg-[#E53935] hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors flex items-center gap-2">
                <Edit size={16} /> Edit Table Data
              </Link>
            </div>
          </div>
        </div>
      )}

      {activeTab === 'combo' && (
        <div className="space-y-10 mt-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
          <ComboForm 
            saveAction={saveComboAction} 
            editingCombo={editingCombo} 
            onCancelEdit={() => setEditingCombo(null)} 
            plans={plans}
          />

          {/* Existing Combos */}
          <div>
            <h3 className="text-[11px] font-bold text-slate-400 dark:text-gray-500 tracking-[0.2em] uppercase mb-4 pl-2">Current Packages</h3>
            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
              {combos.length === 0 && (
                <div className="col-span-full py-12 text-center text-slate-400 dark:text-gray-500 bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#333333]">
                  <Package size={32} className="mx-auto mb-3 opacity-20" />
                  No combos created yet.
                </div>
              )}
              
              {combos.map(combo => (
                <div key={combo.id} className={`bg-white dark:bg-[#1A1A1A] border ${combo.isPopular ? 'border-emerald-500/50 shadow-[0_0_15px_rgba(16,185,129,0.1)]' : 'border-slate-200 dark:border-[#333333]'} rounded-2xl p-6 relative flex flex-col`}>
                  {combo.isPopular && (
                    <div className="absolute top-0 right-0 bg-emerald-500 text-white text-[9px] font-bold px-3 py-1 rounded-bl-lg uppercase tracking-wider">
                      Most Popular
                    </div>
                  )}
                  
                  <div className="mb-4">
                    <h4 className="text-xl font-bold text-[#111111] dark:text-white mb-1">{combo.name}</h4>
                    <p className="text-xs text-slate-500 dark:text-gray-400 mb-2">{combo.subtitle}</p>
                    {combo.planId && (
                      <div className="flex items-center gap-1.5 text-[11px] mt-2">
                        <span className="text-slate-400 dark:text-gray-500">Bundled Plan:</span>
                        <span className="px-2 py-0.5 rounded bg-red-500/10 text-[#E53935] font-bold uppercase tracking-wider text-[9px] border border-red-500/20">
                          {combo.planId}
                        </span>
                      </div>
                    )}
                  </div>
                  
                  <div className="bg-slate-50 dark:bg-[#111111] p-3 rounded-xl border border-slate-200 dark:border-[#333333] mb-4">
                    <div className="text-2xl font-bold text-[#111111] dark:text-white tracking-tight">Rs. {combo.price.toLocaleString()}</div>
                    <div className="flex gap-2 text-xs mt-1">
                      <span className="line-through text-slate-400 dark:text-gray-500">Rs. {combo.originalPrice.toLocaleString()}</span>
                      <span className="text-emerald-400 font-bold">Save Rs. {combo.savings.toLocaleString()}</span>
                    </div>
                  </div>
                  
                  <div className="flex-grow">
                    <p className="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider mb-2">Features Included</p>
                    <ul className="space-y-2 mb-6">
                      {combo.features.map((f: any) => (
                        <li key={f.id} className="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300">
                          <DynamicIcon name={f.iconName} size={14} className="text-emerald-500 shrink-0" />
                          <span className="truncate">{f.text}</span>
                        </li>
                      ))}
                    </ul>
                  </div>
                  
                  <div className="flex gap-2 pt-4 border-t border-slate-200 dark:border-[#333333]">
                    <button 
                      onClick={() => {
                        setEditingCombo(combo);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                      }}
                      className="flex-1 py-2 rounded-lg bg-blue-500/10 text-blue-500 text-xs font-bold hover:bg-blue-500/20 transition-colors flex items-center justify-center gap-1 cursor-pointer"
                    >
                      <Edit size={14} /> Edit
                    </button>
                    <form action={deleteComboAction} className="flex-1">
                      <input type="hidden" name="id" value={combo.id} />
                      <button type="submit" className="w-full py-2 rounded-lg bg-red-500/10 text-red-500 text-xs font-bold hover:bg-red-500/20 transition-colors flex items-center justify-center gap-1 cursor-pointer">
                        <Trash2 size={14} /> Delete
                      </button>
                    </form>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
