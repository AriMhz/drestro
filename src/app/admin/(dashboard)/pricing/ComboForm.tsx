"use client";

import { useState, useEffect } from "react";
import { Plus, X, Type } from "lucide-react";
import { DynamicIcon } from "@/src/components/DynamicIcon";

const AVAILABLE_ICONS = [
  "check", "monitor", "printer", "package", "wifi", 
  "settings", "shield", "smartphone", "server", "zap", "card", "cloud"
];

interface ComboFormProps {
  saveAction: (formData: FormData) => void;
  editingCombo?: any;
  onCancelEdit?: () => void;
  plans?: any[];
}

export function ComboForm({ saveAction, editingCombo, onCancelEdit, plans = [] }: ComboFormProps) {
  const [name, setName] = useState("");
  const [subtitle, setSubtitle] = useState("");
  const [price, setPrice] = useState<number | "">("");
  const [originalPrice, setOriginalPrice] = useState<number | "">("");
  const [savings, setSavings] = useState<number | "">("");
  const [isPopular, setIsPopular] = useState(false);
  const [planId, setPlanId] = useState("");
  const [features, setFeatures] = useState<{ id: number; text: string; iconName: string }[]>([
    { id: Date.now(), text: "", iconName: "check" }
  ]);

  // Synchronize with editing combo package data
  useEffect(() => {
    if (editingCombo) {
      setName(editingCombo.name || "");
      setSubtitle(editingCombo.subtitle || "");
      setPrice(editingCombo.price ?? "");
      setOriginalPrice(editingCombo.originalPrice ?? "");
      setSavings(editingCombo.savings ?? "");
      setIsPopular(!!editingCombo.isPopular);
      setPlanId(editingCombo.planId || "");
      if (editingCombo.features && editingCombo.features.length > 0) {
        setFeatures(editingCombo.features.map((f: any, i: number) => ({
          id: f.id || Date.now() + i,
          text: f.text || "",
          iconName: f.iconName || "check"
        })));
      } else {
        setFeatures([{ id: Date.now(), text: "", iconName: "check" }]);
      }
    } else {
      setName("");
      setSubtitle("");
      setPrice("");
      setOriginalPrice("");
      setSavings("");
      setIsPopular(false);
      setPlanId("");
      setFeatures([{ id: Date.now(), text: "", iconName: "check" }]);
    }
  }, [editingCombo]);

  const addFeature = () => {
    setFeatures([...features, { id: Date.now(), text: "", iconName: "check" }]);
  };

  const removeFeature = (id: number) => {
    if (features.length > 1) {
      setFeatures(features.filter(f => f.id !== id));
    }
  };

  const updateFeatureIcon = (id: number, iconName: string) => {
    setFeatures(features.map(f => f.id === id ? { ...f, iconName } : f));
  };

  const updateFeatureText = (id: number, text: string) => {
    setFeatures(features.map(f => f.id === id ? { ...f, text } : f));
  };

  return (
    <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl p-8 shadow-2xl relative overflow-hidden">
      <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
      <h3 className="text-[11px] font-bold text-slate-500 dark:text-gray-400 tracking-[0.2em] uppercase mb-6">
        {editingCombo ? `Edit Package: ${editingCombo.name}` : "Create New Package"}
      </h3>
      
      <form 
        action={saveAction} 
        onSubmit={() => {
          // Clear editing state on submission so UI resets
          if (editingCombo && onCancelEdit) {
            setTimeout(onCancelEdit, 100);
          }
        }}
        className="space-y-6"
      >
        {editingCombo && <input type="hidden" name="id" value={editingCombo.id} />}

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="space-y-2">
            <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Package Name</label>
            <input 
              name="name" 
              required 
              type="text" 
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-inner font-bold" 
              placeholder="e.g. Starter Setup" 
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Subtitle / Description</label>
            <input 
              name="subtitle" 
              required 
              type="text" 
              value={subtitle}
              onChange={(e) => setSubtitle(e.target.value)}
              className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-inner font-bold" 
              placeholder="e.g. Perfect for small cafes" 
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Bundled Software Plan</label>
            <select
              name="planId"
              value={planId}
              onChange={(e) => setPlanId(e.target.value)}
              className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-all appearance-none cursor-pointer font-bold"
            >
              <option value="">-- No Software Plan Bundled --</option>
              <option value="free">Free</option>
              <option value="basic">Basic</option>
              <option value="premium">Premium</option>
              <option value="platinum">Platinum</option>
              {plans.length > 0 && plans.filter((p: any) => !["free", "basic", "premium", "platinum"].includes(p.name.toLowerCase())).map((p: any) => (
                <option key={p.id} value={p.name.toLowerCase()}>{p.name}</option>
              ))}
            </select>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="space-y-2">
            <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Selling Price (Rs)</label>
            <input 
              name="price" 
              required 
              type="number" 
              value={price}
              onChange={(e) => setPrice(e.target.value === "" ? "" : Number(e.target.value))}
              className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-inner" 
              placeholder="25000" 
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Original Price (Rs)</label>
            <input 
              name="originalPrice" 
              required 
              type="number" 
              value={originalPrice}
              onChange={(e) => setOriginalPrice(e.target.value === "" ? "" : Number(e.target.value))}
              className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-inner" 
              placeholder="30000" 
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Savings Amount (Rs)</label>
            <input 
              name="savings" 
              required 
              type="number" 
              value={savings}
              onChange={(e) => setSavings(e.target.value === "" ? "" : Number(e.target.value))}
              className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-inner" 
              placeholder="5000" 
            />
          </div>
        </div>

        <div className="flex items-center gap-3 bg-slate-50 dark:bg-[#111111] p-4 rounded-xl border border-slate-200 dark:border-[#333333]">
          <input 
            type="checkbox" 
            name="isPopular" 
            id="isPopular" 
            checked={isPopular}
            onChange={(e) => setIsPopular(e.target.checked)}
            className="w-5 h-5 accent-emerald-500 cursor-pointer" 
          />
          <label htmlFor="isPopular" className="text-sm font-semibold text-slate-600 dark:text-gray-300 cursor-pointer select-none">Mark as "Most Popular" (Highlights the card in green)</label>
        </div>

        <div className="pt-4 border-t border-slate-200 dark:border-[#333333]">
          <div className="flex justify-between items-center mb-4">
            <label className="text-sm font-semibold text-slate-600 dark:text-gray-300">Package Features</label>
            <button type="button" onClick={addFeature} className="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-3 py-1.5 rounded-lg hover:bg-emerald-500/20 transition-colors flex items-center gap-1 cursor-pointer">
              <Plus size={14} /> Add Feature
            </button>
          </div>
          
          <div className="space-y-3">
            {features.map((feature, index) => (
              <div key={feature.id} className="flex items-center gap-3">
                <input type="hidden" name="featureIcon[]" value={feature.iconName} />
                <input type="hidden" name="featureText[]" value={feature.text} />
                
                <div className="relative group">
                  <select 
                    value={feature.iconName}
                    onChange={(e) => updateFeatureIcon(feature.id, e.target.value)}
                    className="appearance-none bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-500 dark:text-gray-400 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:border-emerald-500 transition-all w-[140px] cursor-pointer"
                  >
                    {AVAILABLE_ICONS.map(icon => (
                      <option key={icon} value={icon}>{icon.charAt(0).toUpperCase() + icon.slice(1)}</option>
                    ))}
                  </select>
                  <div className="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-emerald-500">
                    <DynamicIcon name={feature.iconName} size={18} />
                  </div>
                </div>

                <div className="relative flex-grow">
                  <div className="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 dark:text-gray-500">
                    <Type size={16} />
                  </div>
                  <input 
                    type="text" 
                    value={feature.text}
                    onChange={(e) => updateFeatureText(feature.id, e.target.value)}
                    placeholder="e.g. 1x Thermal Printer (80mm)"
                    className="w-full bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:border-emerald-500 transition-all placeholder:text-slate-400 dark:placeholder:text-gray-500 shadow-inner"
                    required={index === 0}
                  />
                </div>

                <button 
                  type="button" 
                  onClick={() => removeFeature(feature.id)}
                  disabled={features.length === 1}
                  className="p-3 bg-red-500/10 text-red-500 rounded-lg hover:bg-red-500/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                  <X size={18} />
                </button>
              </div>
            ))}
          </div>
        </div>

        <div className="flex gap-4 pt-4">
          {editingCombo && (
            <button 
              type="button" 
              onClick={onCancelEdit} 
              className="flex-1 bg-slate-100 hover:bg-slate-200 dark:bg-[#222222] dark:hover:bg-[#333333] text-slate-700 dark:text-gray-200 py-4 rounded-xl font-bold transition-all border border-slate-200 dark:border-[#444444] flex justify-center cursor-pointer"
            >
              Cancel Edit
            </button>
          )}
          <button 
            type="submit" 
            className="flex-1 bg-emerald-500 hover:bg-emerald-400 text-white py-4 rounded-xl font-bold transition-all shadow-[0_4px_15px_rgba(16,185,129,0.3)] flex justify-center cursor-pointer"
          >
            {editingCombo ? "Save Changes" : "Save Combo Package"}
          </button>
        </div>
      </form>
    </div>
  );
}
