import { prisma } from "@/src/lib/prisma";
import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import EditPlanForm from "./EditPlanForm";

export const dynamic = "force-dynamic";

async function updatePlan(formData: FormData) {
  "use server";
  
  const id = formData.get("id") as string;
  const name = formData.get("name") as string;
  const description = formData.get("description") as string;
  const priceYearly = parseInt(formData.get("priceYearly") as string) || 0;
  const priceHalfYearly = parseInt(formData.get("priceHalfYearly") as string) || 0;
  const oldPriceYearly = formData.get("oldPriceYearly") ? parseInt(formData.get("oldPriceYearly") as string) : null;
  const oldPriceHalfYearly = formData.get("oldPriceHalfYearly") ? parseInt(formData.get("oldPriceHalfYearly") as string) : null;
  const isPopular = formData.get("isPopular") === "true";
  const buttonText = formData.get("buttonText") as string;
  const buttonVariant = formData.get("buttonVariant") as string;

  const featuresText = formData.get("features") as string;
  const notIncludedText = formData.get("notIncluded") as string;

  const featuresList = featuresText.split('\n').map(s => s.trim()).filter(s => s.length > 0);
  const notIncludedList = notIncludedText.split('\n').map(s => s.trim()).filter(s => s.length > 0);

  let targetId = id;
  if (id === "new") {
    const newPlan = await prisma.plan.create({
      data: {
        name, description, priceYearly, priceHalfYearly, oldPriceYearly, oldPriceHalfYearly, isPopular, buttonText, buttonVariant
      }
    });
    targetId = newPlan.id;
  } else {
    // Update plan basic details
    await prisma.plan.update({
      where: { id },
      data: {
        name, description, priceYearly, priceHalfYearly, oldPriceYearly, oldPriceHalfYearly, isPopular, buttonText, buttonVariant
      }
    });
  }

  // Recreate features
  await prisma.planFeature.deleteMany({ where: { planId: targetId } });
  await prisma.planNotIncluded.deleteMany({ where: { planId: targetId } });

  if (featuresList.length > 0) {
    await prisma.planFeature.createMany({
      data: featuresList.map(text => ({ planId: targetId, text }))
    });
  }

  if (notIncludedList.length > 0) {
    await prisma.planNotIncluded.createMany({
      data: notIncludedList.map(text => ({ planId: targetId, text }))
    });
  }

  revalidatePath("/admin/pricing");
  revalidatePath("/pricing");
  revalidatePath("/dashboard/billing/plans");
  redirect("/admin/pricing");
}

export default async function EditPlanPage({ params }: { params: Promise<{ id: string }> }) {
  const resolvedParams = await params;
  
  let plan;
  if (resolvedParams.id === 'new') {
    plan = {
      id: 'new',
      name: '',
      description: '',
      priceYearly: 0,
      priceHalfYearly: 0,
      oldPriceYearly: null,
      oldPriceHalfYearly: null,
      isPopular: false,
      buttonText: 'Start 14 Days Trial',
      buttonVariant: 'outline',
      features: [],
      notIncluded: []
    };
  } else {
    plan = await prisma.plan.findUnique({
      where: { id: resolvedParams.id },
      include: {
        features: true,
        notIncluded: true
      }
    });
  }

  if (!plan) return <div>Plan not found</div>;

  return (
    <EditPlanForm plan={plan} updatePlan={updatePlan} />
  );
}
