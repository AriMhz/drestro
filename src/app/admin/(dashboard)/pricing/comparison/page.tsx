"use client";

import React, { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { Save, ArrowLeft, AlertCircle, Plus, Trash2, GripVertical } from "lucide-react";
import Link from "next/link";

export default function EditComparisonPage() {
  const router = useRouter();
  const [data, setData] = useState<any[]>([]);
  const [isSaving, setIsSaving] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetch("/api/admin/pricing-comparison")
      .then(res => res.json())
      .then(resData => {
        setData(resData);
        setIsLoading(false);
      })
      .catch(err => {
        console.error(err);
        setError("Failed to load current comparison data");
        setIsLoading(false);
      });
  }, []);

  const handleSave = async () => {
    setError("");
    setSuccess(false);
    setIsSaving(true);
    
    try {
      // Clean up data before saving: convert string "true"/"false" back to booleans if desired, 
      // but the public UI expects boolean or string. We can just keep them as strings or parse them.
      const cleanedData = data.map(row => {
        const cleanRow = { ...row };
        ['free', 'basic', 'premium', 'plat'].forEach(plan => {
          if (cleanRow[plan] === 'true') cleanRow[plan] = true;
          if (cleanRow[plan] === 'false') cleanRow[plan] = false;
        });
        return cleanRow;
      });

      const res = await fetch("/api/admin/pricing-comparison", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ data: cleanedData }),
      });

      if (!res.ok) throw new Error("Failed to save");
      
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
      router.refresh();
    } catch (e: any) {
      setError(e.message);
    } finally {
      setIsSaving(false);
    }
  };

  const addRow = () => {
    setData([...data, { category: "New Category", name: "New Feature", free: false, basic: true, premium: true, plat: true }]);
  };

  const removeRow = (index: number) => {
    const newData = [...data];
    newData.splice(index, 1);
    setData(newData);
  };

  const updateRow = (index: number, field: string, value: any) => {
    const newData = [...data];
    newData[index] = { ...newData[index], [field]: value };
    setData(newData);
  };

  const moveRow = (index: number, direction: 'up' | 'down') => {
    if (direction === 'up' && index === 0) return;
    if (direction === 'down' && index === data.length - 1) return;
    
    const newData = [...data];
    const swapIndex = direction === 'up' ? index - 1 : index + 1;
    
    const temp = newData[index];
    newData[index] = newData[swapIndex];
    newData[swapIndex] = temp;
    
    setData(newData);
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-7xl mx-auto">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center gap-4">
          <Link href="/admin/pricing" className="w-10 h-10 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-xl flex items-center justify-center text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white hover:bg-slate-100 dark:bg-[#222222] transition-colors">
            <ArrowLeft size={18} />
          </Link>
          <div>
            <h1 className="text-3xl font-bold text-[#111111] dark:text-white tracking-tight">Edit Comparison Table</h1>
            <p className="text-slate-500 dark:text-gray-400 mt-1 text-sm">Add, remove, or edit features in the comparison grid.</p>
          </div>
        </div>
        <button 
          onClick={handleSave}
          disabled={isSaving || isLoading}
          className="bg-[#E53935] hover:bg-red-600 text-white px-8 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2 disabled:opacity-50"
        >
          {isSaving ? <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span> : <Save size={18} />}
          {isSaving ? "Saving..." : "Save Table"}
        </button>
      </div>

      {error && (
        <div className="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl flex items-center gap-3">
          <AlertCircle size={20} />
          <span className="text-sm font-medium">{error}</span>
        </div>
      )}
      
      {success && (
        <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-4 rounded-xl flex items-center gap-3">
          <AlertCircle size={20} />
          <span className="text-sm font-medium">Successfully saved!</span>
        </div>
      )}

      <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden shadow-2xl">
        <div className="overflow-x-auto">
          {isLoading ? (
            <div className="p-12 text-center text-slate-400 dark:text-gray-500">Loading data...</div>
          ) : (
            <table className="w-full text-left text-sm whitespace-nowrap">
              <thead className="bg-slate-100 dark:bg-[#222222] text-slate-500 dark:text-gray-400 text-xs uppercase tracking-widest font-bold">
                <tr>
                  <th className="px-4 py-4 w-10"></th>
                  <th className="px-4 py-4">Category</th>
                  <th className="px-4 py-4">Feature Name</th>
                  <th className="px-4 py-4 text-center">Free</th>
                  <th className="px-4 py-4 text-center">Basic</th>
                  <th className="px-4 py-4 text-center">Premium</th>
                  <th className="px-4 py-4 text-center">Platinum</th>
                  <th className="px-4 py-4 w-10 text-center">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-[#333333]">
                {data.map((row, index) => (
                  <tr key={index} className="hover:bg-slate-100 dark:bg-[#222222]/50 group">
                    <td className="px-4 py-3 text-center">
                      <div className="flex flex-col items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onClick={() => moveRow(index, 'up')} disabled={index === 0} className="text-slate-400 dark:text-gray-500 hover:text-[#111111] dark:text-white disabled:opacity-30">▲</button>
                        <button onClick={() => moveRow(index, 'down')} disabled={index === data.length - 1} className="text-slate-400 dark:text-gray-500 hover:text-[#111111] dark:text-white disabled:opacity-30">▼</button>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <input 
                        type="text" 
                        value={row.category} 
                        onChange={(e) => updateRow(index, 'category', e.target.value)}
                        className="bg-transparent border border-transparent hover:border-slate-200 dark:border-[#333333] focus:border-[#E53935] focus:bg-[#0a0a0a] text-[#111111] dark:text-white px-2 py-1.5 rounded w-full transition-all"
                      />
                    </td>
                    <td className="px-4 py-3">
                      <input 
                        type="text" 
                        value={row.name} 
                        onChange={(e) => updateRow(index, 'name', e.target.value)}
                        className="bg-transparent border border-transparent hover:border-slate-200 dark:border-[#333333] focus:border-[#E53935] focus:bg-[#0a0a0a] text-[#111111] dark:text-white px-2 py-1.5 rounded w-full transition-all font-bold"
                      />
                    </td>
                    <td className="px-4 py-3 text-center">
                      <input 
                        type="text" 
                        value={row.free === true ? 'true' : row.free === false ? 'false' : row.free} 
                        onChange={(e) => updateRow(index, 'free', e.target.value)}
                        className="bg-transparent border border-transparent hover:border-slate-200 dark:border-[#333333] focus:border-[#E53935] focus:bg-[#0a0a0a] text-slate-600 dark:text-gray-300 px-2 py-1.5 rounded w-28 text-center transition-all"
                        placeholder="true/false or text"
                      />
                    </td>
                    <td className="px-4 py-3 text-center">
                      <input 
                        type="text" 
                        value={row.basic === true ? 'true' : row.basic === false ? 'false' : row.basic} 
                        onChange={(e) => updateRow(index, 'basic', e.target.value)}
                        className="bg-transparent border border-transparent hover:border-slate-200 dark:border-[#333333] focus:border-[#E53935] focus:bg-[#0a0a0a] text-slate-600 dark:text-gray-300 px-2 py-1.5 rounded w-28 text-center transition-all"
                        placeholder="true/false or text"
                      />
                    </td>
                    <td className="px-4 py-3 text-center">
                      <input 
                        type="text" 
                        value={row.premium === true ? 'true' : row.premium === false ? 'false' : row.premium} 
                        onChange={(e) => updateRow(index, 'premium', e.target.value)}
                        className="bg-transparent border border-transparent hover:border-slate-200 dark:border-[#333333] focus:border-[#E53935] focus:bg-[#0a0a0a] text-slate-600 dark:text-gray-300 px-2 py-1.5 rounded w-28 text-center transition-all"
                        placeholder="true/false or text"
                      />
                    </td>
                    <td className="px-4 py-3 text-center">
                      <input 
                        type="text" 
                        value={row.plat === true ? 'true' : row.plat === false ? 'false' : row.plat} 
                        onChange={(e) => updateRow(index, 'plat', e.target.value)}
                        className="bg-transparent border border-transparent hover:border-slate-200 dark:border-[#333333] focus:border-[#E53935] focus:bg-[#0a0a0a] text-slate-600 dark:text-gray-300 px-2 py-1.5 rounded w-28 text-center transition-all"
                        placeholder="true/false or text"
                      />
                    </td>
                    <td className="px-4 py-3 text-center">
                      <button 
                        onClick={() => removeRow(index)}
                        className="text-slate-400 dark:text-gray-500 hover:text-red-500 transition-colors p-2"
                        title="Remove feature"
                      >
                        <Trash2 size={16} />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
        
        <div className="p-4 border-t border-slate-200 dark:border-[#333333] bg-slate-100 dark:bg-[#222222]">
          <button 
            onClick={addRow}
            className="text-sm font-bold text-emerald-500 hover:text-emerald-400 flex items-center gap-2 transition-colors px-4 py-2"
          >
            <Plus size={16} /> Add New Feature Row
          </button>
        </div>
      </div>
      
      <div className="text-slate-400 dark:text-gray-500 text-sm flex items-center gap-2 pb-12">
        <AlertCircle size={16} />
        <span>Tip: Type <strong>true</strong> or <strong>false</strong> to show a checkmark (✓) or cross (✕) on the public pricing page. Or type text like "100/month".</span>
      </div>
    </div>
  );
}
