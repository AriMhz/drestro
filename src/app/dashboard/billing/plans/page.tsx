import { auth } from "../../../../../auth";
import { prisma } from "@/src/lib/prisma";
import PlansClient from "./PlansClient";

export const dynamic = "force-dynamic";

export default async function PlansPage() {
  const session = await auth();

  const plans = await prisma.plan.findMany({
    include: {
      features: true,
      notIncluded: true
    },
    orderBy: { priceYearly: 'asc' }
  });

  const restaurant = await prisma.restaurant.findFirst({
    where: { userId: session?.user?.id },
    include: { subscriptions: true }
  });

  const sub = restaurant?.subscriptions[0];
  
  let currentPlanName = "Premium"; // Default fallback
  if (sub?.planId) {
    if (sub.planId.toLowerCase().includes("free")) {
      currentPlanName = "Free";
    } else if (sub.planId.toLowerCase().includes("basic")) {
      currentPlanName = "Basic";
    } else if (sub.planId.toLowerCase().includes("premium")) {
      currentPlanName = "Premium";
    } else if (sub.planId.toLowerCase().includes("platinum")) {
      currentPlanName = "Platinum";
    }
  }

  return <PlansClient initialPlans={plans} currentPlanName={currentPlanName} />;
}
