"use client";

import { Server, Save, X } from "lucide-react";
import { useRef, useState } from "react";
import toast from "react-hot-toast";
import { useRouter, usePathname } from "next/navigation";

interface HardwareFormProps {
  addHardwareAction: (formData: FormData) => Promise<{ success: boolean; error?: string }>;
  updateHardwareAction: (formData: FormData) => Promise<{ success: boolean; error?: string }>;
  initialData?: any;
}

export default function HardwareForm({ addHardwareAction, updateHardwareAction, initialData }: HardwareFormProps) {
  const ref = useRef<HTMLFormElement>(null);
  const router = useRouter();
  const pathname = usePathname();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (formData: FormData) => {
    setIsSubmitting(true);
    try {
      let result;
      if (initialData) {
        result = await updateHardwareAction(formData);
      } else {
        result = await addHardwareAction(formData);
      }

      if (result.success) {
        toast.success(initialData ? "Product updated!" : "Product added to store!");
        if (!initialData) {
          ref.current?.reset();
        } else {
          router.push(pathname === '/admin/hardware' ? '/admin/hardware' : '/admin/website?tab=hardware');
        }
      } else {
        toast.error(result.error || "Failed to process request.");
      }
    } catch (error) {
      toast.error("An unexpected error occurred.");
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleCancel = () => {
    router.push(pathname === '/admin/hardware' ? '/admin/hardware' : '/admin/website?tab=hardware');
  };

  return (
    <form key={initialData?.id || 'new'} ref={ref} action={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
      {initialData && <input type="hidden" name="id" value={initialData.id} />}
      
      <div className="space-y-2 lg:col-span-2">
        <label className="text-sm font-medium text-slate-500 dark:text-gray-400">Product Name <span className="text-red-500">*</span></label>
        <input name="name" defaultValue={initialData?.name || ''} required type="text" className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500" placeholder="e.g. 15.6' Touch POS Terminal" />
      </div>
      
      <div className="space-y-2 lg:col-span-1">
        <label className="text-sm font-medium text-slate-500 dark:text-gray-400">Price (Rs) <span className="text-red-500">*</span></label>
        <input name="price" defaultValue={initialData?.price || ''} required type="number" step="0.01" className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500" placeholder="e.g. 45000" />
      </div>

      <div className="space-y-2 lg:col-span-1">
        <label className="text-sm font-medium text-slate-500 dark:text-gray-400">MRP (Optional)</label>
        <input name="mrp" defaultValue={initialData?.mrp || ''} type="number" step="0.01" className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500" placeholder="e.g. 50000" />
      </div>
      
      <div className="space-y-2 lg:col-span-1">
        <label className="text-sm font-medium text-slate-500 dark:text-gray-400">Product Image {initialData && "(Optional)"}</label>
        <input name="imageFile" type="file" accept="image/*" className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-emerald-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-500/10 file:text-emerald-500 hover:file:bg-emerald-500/20 cursor-pointer" />
      </div>
      
      <div className="space-y-2 lg:col-span-1">
        <label className="text-sm font-medium text-slate-500 dark:text-gray-400">Stock Status <span className="text-red-500">*</span></label>
        <select name="stockStatus" defaultValue={initialData?.stockStatus || 'In Stock'} className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500 appearance-none">
          <option value="In Stock">In Stock</option>
          <option value="Out of Stock">Out of Stock</option>
        </select>
      </div>
      
      <div className="space-y-2 lg:col-span-6">
        <label className="text-sm font-medium text-slate-500 dark:text-gray-400">Description</label>
        <textarea name="description" defaultValue={initialData?.description || ''} rows={2} className="w-full bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-[#111111] dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:border-emerald-500" placeholder="Brief product description..." />
      </div>

      <div className="lg:col-span-6 flex justify-end gap-3 mt-4">
        {initialData && (
          <button type="button" onClick={handleCancel} disabled={isSubmitting} className="bg-slate-200 dark:bg-[#333333] hover:bg-[#444444] text-[#111111] dark:text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
            <X size={18} /> Cancel
          </button>
        )}
        <button type="submit" disabled={isSubmitting} className="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
          {initialData ? <Save size={18} /> : <Server size={18} />} 
          {isSubmitting ? 'Saving...' : (initialData ? 'Update Product' : 'Add to Store')}
        </button>
      </div>
    </form>
  );
}
