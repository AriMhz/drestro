import { prisma } from "@/src/lib/prisma";
import { revalidatePath } from "next/cache";
import { processAndSaveImage, deleteLocalImage } from "@/src/lib/upload";
import WebsiteClient from "./WebsiteClient";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Website Management - DRestro Admin",
};

// Pricing Combo Actions
async function saveComboAction(formData: FormData) {
  "use server";
  
  const id = formData.get("id") as string;
  const name = formData.get("name") as string;
  const subtitle = formData.get("subtitle") as string;
  const price = parseInt(formData.get("price") as string);
  const originalPrice = parseInt(formData.get("originalPrice") as string);
  const savings = parseInt(formData.get("savings") as string);
  const isPopular = formData.get("isPopular") === "on";
  
  const planId = formData.get("planId") as string;
  
  const featureTexts = formData.getAll("featureText[]") as string[];
  const featureIcons = formData.getAll("featureIcon[]") as string[];
  
  const features = featureTexts.map((text, i) => ({
    text,
    iconName: featureIcons[i] || "check"
  })).filter(f => f.text.trim() !== "");

  if (!name || !price) return;

  if (id) {
    await prisma.comboPackage.update({
      where: { id },
      data: {
        name, subtitle, price, originalPrice, savings, isPopular, planId,
        features: {
          deleteMany: {},
          create: features
        }
      }
    });
  } else {
    await prisma.comboPackage.create({
      data: {
        name, subtitle, price, originalPrice, savings, isPopular, planId,
        features: {
          create: features
        }
      }
    });
  }

  revalidatePath("/admin/website");
  revalidatePath("/admin/pricing");
  revalidatePath("/pricing");
}

async function deleteComboAction(formData: FormData) {
  "use server";
  const id = formData.get("id") as string;
  if (id) {
    await prisma.comboPackage.delete({ where: { id } });
    revalidatePath("/admin/website");
    revalidatePath("/admin/pricing");
    revalidatePath("/pricing");
  }
}
async function deletePlanAction(formData: FormData) {
  "use server";
  const id = formData.get("id") as string;
  if (id) {
    await prisma.plan.delete({ where: { id } });
    revalidatePath("/admin/website");
    revalidatePath("/admin/pricing");
    revalidatePath("/pricing");
    revalidatePath("/dashboard/billing/plans");
  }
}
// Hardware Actions
async function addHardware(formData: FormData): Promise<{ success: boolean; error?: string }> {
  "use server";
  
  try {
    const name = formData.get("name") as string;
    const description = formData.get("description") as string;
    const priceStr = formData.get("price") as string;
    const price = parseFloat(priceStr);
    const mrpStr = formData.get("mrp") as string;
    const mrp = mrpStr ? parseFloat(mrpStr) : null;
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
      data: { name, description, price, mrp, imageUrl, stockStatus }
    });

    revalidatePath("/admin/website");
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
  
  const hardware = await prisma.hardware.findUnique({ where: { id } });
  
  if (hardware) {
    if (hardware.imageUrl) {
      await deleteLocalImage(hardware.imageUrl);
    }
    await prisma.hardware.delete({ where: { id } });
  }
  
  revalidatePath("/admin/website");
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
    const mrpStr = formData.get("mrp") as string;
    const mrp = mrpStr ? parseFloat(mrpStr) : null;
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
        mrp,
        stockStatus,
        ...(imageUrl ? { imageUrl } : {})
      }
    });

    revalidatePath("/admin/website");
    revalidatePath("/admin/hardware");
    return { success: true };
  } catch (error: any) {
    console.error("Hardware update failed:", error);
    return { success: false, error: error.message || "Failed to update hardware in database." };
  }
}

export default async function WebsitePage({ searchParams }: { searchParams: Promise<{ tab?: string; edit?: string }> }) {
  const resolvedParams = await searchParams;
  const initialTab = resolvedParams.tab || "pricing";
  const editId = resolvedParams.edit;

  // Fetch Pricing Data
  const plans = await prisma.plan.findMany({
    include: {
      features: true,
      notIncluded: true
    },
    orderBy: { priceYearly: 'asc' }
  });

  const siteSetting = await prisma.siteSetting.findUnique({
    where: { key: 'comparison_table' }
  });

  const comparisonTable = siteSetting ? JSON.parse(siteSetting.value) : [];

  const combos = await prisma.comboPackage.findMany({
    include: { features: true },
    orderBy: { createdAt: "desc" }
  });

  // Fetch Hardware Data
  const hardware = await prisma.hardware.findMany({
    orderBy: { createdAt: "desc" }
  });

  // If editId is provided, find that specific item for the form
  let editHardwareData = null;
  if (editId) {
    editHardwareData = hardware.find(item => item.id === editId) || null;
  }

  return (
    <WebsiteClient
      initialTab={initialTab}
      editHardwareData={editHardwareData}
      plans={plans}
      comparisonTable={comparisonTable}
      combos={combos}
      hardware={hardware}
      saveComboAction={saveComboAction}
      deleteComboAction={deleteComboAction}
      addHardwareAction={addHardware}
      deleteHardwareAction={deleteHardware}
      updateHardwareAction={updateHardware}
      deletePlanAction={deletePlanAction}
    />
  );
}
