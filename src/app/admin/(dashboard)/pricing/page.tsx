import { prisma } from "@/src/lib/prisma";
import { revalidatePath } from "next/cache";
import PricingAdminClient from "./PricingAdminClient";

export const dynamic = "force-dynamic";

async function saveComboAction(formData: FormData) {
  "use server";
  
  const id = formData.get("id") as string;
  const name = formData.get("name") as string;
  const subtitle = formData.get("subtitle") as string;
  const price = parseInt(formData.get("price") as string);
  const originalPrice = parseInt(formData.get("originalPrice") as string);
  const savings = parseInt(formData.get("savings") as string);
  const isPopular = formData.get("isPopular") === "on";
  
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
        name, subtitle, price, originalPrice, savings, isPopular,
        features: {
          deleteMany: {},
          create: features
        }
      }
    });
  } else {
    await prisma.comboPackage.create({
      data: {
        name, subtitle, price, originalPrice, savings, isPopular,
        features: {
          create: features
        }
      }
    });
  }

  revalidatePath("/admin/pricing");
  revalidatePath("/pricing");
}

async function deleteComboAction(formData: FormData) {
  "use server";
  const id = formData.get("id") as string;
  if (id) {
    await prisma.comboPackage.delete({ where: { id } });
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

export default async function AdminPricingPage() {
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

  return (
    // @ts-ignore
    <PricingAdminClient 
      plans={plans} 
      comparisonTable={comparisonTable} 
      combos={combos} 
      saveComboAction={saveComboAction} 
      deleteComboAction={deleteComboAction} 
      deletePlanAction={deletePlanAction}
    />
  );
}
