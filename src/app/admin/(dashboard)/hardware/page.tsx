import { prisma } from "@/src/lib/prisma";
import { revalidatePath } from "next/cache";
import { Server, Plus, Trash2, Edit } from "lucide-react";
import { processAndSaveImage, deleteLocalImage } from "@/src/lib/upload";
import Image from "next/image";

import HardwareForm from "./HardwareForm";

export const dynamic = "force-dynamic";

async function addHardware(formData: FormData): Promise<{ success: boolean; error?: string }> {
  "use server";
  
  try {
    const name = formData.get("name") as string;
    const description = formData.get("description") as string;
    const priceStr = formData.get("price") as string;
    const price = parseFloat(priceStr);
    const stockStatus = formData.get("stockStatus") as string;
    const imageFile = formData.get("imageFile") as File | null;
    
    if (!name || isNaN(price)) {
      return { success: false, error: "Name and Price are required and Price must be valid." };
    }

    let imageUrl = "";
    if (imageFile && imageFile.size > 0) {
      const savedUrl = await processAndSaveImage(imageFile, "hardware");
      if (savedUrl) imageUrl = savedUrl;
    }

    await prisma.hardware.create({
      data: { name, description, price, imageUrl, stockStatus }
    });

    revalidatePath("/admin/hardware");
    return { success: true };
  } catch (error: any) {
    console.error("Hardware creation failed:", error);
    return { success: false, error: error.message || "Failed to add hardware to database." };
  }
}

async function deleteHardware(formData: FormData) {
  "use server";
  const id = formData.get("id") as string;
  
  // Find the hardware first to get the image URL
  const hardware = await prisma.hardware.findUnique({ where: { id } });
  
  if (hardware) {
    // Delete the image file from disk
    if (hardware.imageUrl) {
      await deleteLocalImage(hardware.imageUrl);
    }
    
    // Delete from DB
    await prisma.hardware.delete({ where: { id } });
  }
  
  revalidatePath("/admin/hardware");
}

async function updateHardware(formData: FormData): Promise<{ success: boolean; error?: string }> {
  "use server";
  
  try {
    const id = formData.get("id") as string;
    const name = formData.get("name") as string;
    const description = formData.get("description") as string;
    const priceStr = formData.get("price") as string;
    const price = parseFloat(priceStr);
    const stockStatus = formData.get("stockStatus") as string;
    const imageFile = formData.get("imageFile") as File | null;
    
    if (!id || !name || isNaN(price)) {
      return { success: false, error: "ID, Name and Price are required and Price must be valid." };
    }

    let imageUrl;
    if (imageFile && imageFile.size > 0) {
      const savedUrl = await processAndSaveImage(imageFile, "hardware");
      if (savedUrl) imageUrl = savedUrl;
    }

    await prisma.hardware.update({
      where: { id },
      data: { 
        name, 
        description, 
        price, 
        stockStatus,
        ...(imageUrl ? { imageUrl } : {}) // Only update image if a new one was provided
      }
    });

    revalidatePath("/admin/hardware");
    return { success: true };
  } catch (error: any) {
    console.error("Hardware update failed:", error);
    return { success: false, error: error.message || "Failed to update hardware in database." };
  }
}

export default async function HardwarePage({ searchParams }: { searchParams: Promise<{ edit?: string }> }) {
  const resolvedParams = await searchParams;
  const editId = resolvedParams.edit;
  
  // Fetch all hardware for the table
  const hardware = await prisma.hardware.findMany({
    orderBy: { createdAt: "desc" }
  });

  // If editId is provided, find that specific item for the form
  let editData = null;
  if (editId) {
    editData = hardware.find(item => item.id === editId) || null;
  }

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 className="text-3xl font-bold text-[#111111] dark:text-white tracking-tight">Hardware Store</h1>
          <p className="text-slate-500 dark:text-gray-400 mt-2">Manage the POS devices and combo packages listed on your public website.</p>
        </div>
      </div>

      {/* Add/Edit Hardware Form */}
      <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] p-6 rounded-2xl">
        <h2 className="text-lg font-bold text-[#111111] dark:text-white mb-6 flex items-center gap-2">
          {editData ? (
            <><Edit size={20} className="text-emerald-500" /> Edit Product</>
          ) : (
            <><Plus size={20} className="text-emerald-500" /> Add New Product</>
          )}
        </h2>
        
        <HardwareForm 
          addHardwareAction={addHardware} 
          updateHardwareAction={updateHardware}
          initialData={editData} 
        />
      </div>

      {/* Hardware Table */}
      <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-500 dark:text-gray-400">
            <thead className="bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-300 text-xs uppercase font-semibold">
              <tr>
                <th className="px-6 py-4">Image</th>
                <th className="px-6 py-4">Product Name</th>
                <th className="px-6 py-4">Price</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[#333333]">
              {hardware.length === 0 && (
                <tr>
                  <td colSpan={5} className="px-6 py-8 text-center text-slate-400 dark:text-gray-500">No hardware products found. Add one above!</td>
                </tr>
              )}
              {hardware.map((item) => (
                <tr key={item.id} className="hover:bg-slate-100 dark:bg-[#222222]/50 transition-colors">
                  <td className="px-6 py-4">
                    {item.imageUrl ? (
                      <div className="relative w-16 h-12 rounded-lg overflow-hidden border border-slate-200 dark:border-[#333333] bg-slate-50 dark:bg-[#111111]">
                        <Image src={item.imageUrl} alt={item.name} fill className="object-cover" sizes="64px" />
                      </div>
                    ) : (
                      <div className="w-16 h-12 rounded-lg border border-slate-200 dark:border-[#333333] bg-slate-50 dark:bg-[#111111] flex items-center justify-center text-gray-600 dark:text-neutral-400 text-xs">
                        No Img
                      </div>
                    )}
                  </td>
                  <td className="px-6 py-4 font-medium text-[#111111] dark:text-white">{item.name}</td>
                  <td className="px-6 py-4">Rs. {item.price.toLocaleString()}</td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${
                      item.stockStatus === 'In Stock' 
                        ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' 
                        : 'bg-red-500/10 text-red-500 border border-red-500/20'
                    }`}>
                      {item.stockStatus}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-right">
                    <div className="flex items-center justify-end gap-3">
                      <a href={`/admin/hardware?edit=${item.id}`} className="text-slate-400 dark:text-gray-500 hover:text-emerald-500 transition-colors" title="Edit Product">
                        <Edit size={18} />
                      </a>
                      <form action={deleteHardware}>
                        <input type="hidden" name="id" value={item.id} />
                        <button type="submit" className="text-slate-400 dark:text-gray-500 hover:text-red-500 transition-colors" title="Delete Product">
                          <Trash2 size={18} />
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
