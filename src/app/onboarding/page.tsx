import { auth } from "../../../auth";
import { redirect } from "next/navigation";
import { prisma } from "../../lib/prisma";
import { LogOut } from "lucide-react";
import Link from "next/link";
import crypto from "crypto";
import { OnboardingForm } from "./OnboardingForm";

export const dynamic = "force-dynamic";

export default async function OnboardingPage(props: { searchParams?: Promise<{ new?: string }> }) {
  const session = await auth();
  if (!session) redirect("/login");

  const resolvedParams = props.searchParams ? await props.searchParams : {};
  const isNewBranch = resolvedParams.new === 'true';

  const restaurants = await prisma.restaurant.findMany({
    where: { userId: session.user?.id || "" },
    include: { subscriptions: true }
  });

  const getPlanRank = (planId: string) => {
    const p = (planId || "").toLowerCase();
    if (p.includes("platinum") || p.includes("plat")) return 4;
    if (p.includes("premium")) return 3;
    if (p.includes("basic")) return 2;
    if (p.includes("trial")) return 1;
    return 0;
  };

  const highestSub = restaurants
    .flatMap(r => r.subscriptions)
    .sort((a, b) => getPlanRank(b.planId || "") - getPlanRank(a.planId || ""))[0];

  const isPlatinum = highestSub?.planId && (highestSub.planId.toLowerCase().includes("platinum") || highestSub.planId.toLowerCase().includes("plat"));

  if (restaurants.length > 0) {
    // If they are NOT on the Platinum plan OR did not pass ?new=true, redirect to the first restaurant SSO
    if (!isPlatinum || !isNewBranch) {
      const existingRestaurant = restaurants[0];
      const baseUrl = process.env.NODE_ENV === "production" 
        ? "https://portal.drestro.com" 
        : (process.env.NEXT_PUBLIC_PORTAL_URL || "http://localhost:8000");
      
      const sub = existingRestaurant.subscriptions.sort((a, b) => getPlanRank(b.planId || "") - getPlanRank(a.planId || ""))[0];
      const planId = sub?.planId || "premium_trial";
      
      const ssoExpiresAt = sub?.currentPeriodEnd 
        ? sub.currentPeriodEnd.toISOString() 
        : (sub?.trialEndsAt ? sub.trialEndsAt.toISOString() : "");
      
      const limits = JSON.stringify({
        tables: sub?.tableLimit || 0,
        users: sub?.staffLimit || 0,
        items: sub?.dishLimit || 0,
        rooms: sub?.roomLimit || 0,
        allowedMenus: sub?.allowedMenus || (
          (existingRestaurant.type?.toLowerCase().includes("hotel") || existingRestaurant.type?.toLowerCase().includes("resort"))
            ? "pos,menu,tables,rooms,kot,reports,expenses,staff,inventory,customers,settings"
            : "pos,menu,tables,kot,reports,expenses,staff,inventory,customers,settings"
        )
      });
      
      const email = session?.user?.email || "";
      const name = session?.user?.name || "";
      const licenseKey = existingRestaurant.offlineLicenseKey || "";
      const restName = existingRestaurant.name || "";
      const restAddress = existingRestaurant.address || "";
      const restPhone = existingRestaurant.phone || "";

      // Cryptographic signature token using same payload format
      const payload = `${email}|${planId}`;
      const token = crypto.createHmac('sha256', 'DrestroPOS_Secure_Key_2026_X9P2').update(payload).digest('hex');

      const ssoUrl = `${baseUrl}/sso/login?slug=${encodeURIComponent(existingRestaurant.id)}&email=${encodeURIComponent(email)}&name=${encodeURIComponent(name)}&plan=${encodeURIComponent(planId)}&expires_at=${encodeURIComponent(ssoExpiresAt)}&limits=${encodeURIComponent(limits)}&license_key=${encodeURIComponent(licenseKey)}&restaurant_name=${encodeURIComponent(restName)}&restaurant_address=${encodeURIComponent(restAddress)}&restaurant_phone=${encodeURIComponent(restPhone)}&restaurant_pan=${encodeURIComponent(existingRestaurant.panNumber || "")}&restaurant_tagline=${encodeURIComponent(existingRestaurant.tagline || "")}&restaurant_ward=${encodeURIComponent(existingRestaurant.ward || "")}&restaurant_city=${encodeURIComponent(existingRestaurant.city || "")}&restaurant_email=${encodeURIComponent(existingRestaurant.email || email)}&restaurant_currency=${encodeURIComponent(existingRestaurant.currency || "Rs.")}&restaurant_tax=${encodeURIComponent(String(existingRestaurant.taxPercent || 0))}&restaurant_service_charge=${encodeURIComponent(String(existingRestaurant.serviceChargePercent || 0))}&restaurant_calendar=${encodeURIComponent(existingRestaurant.dateCalendarType || "AD")}&token=${token}`;
      
      redirect(ssoUrl);
    }
  }

  const dbUser = await prisma.user.findUnique({
    where: { id: session.user?.id || "" }
  });

  return (
    <div className="min-h-screen bg-[#F8F9FA] dark:bg-[#0a0a0a] bg-grid-pattern dark:bg-grid-dark flex flex-col items-center justify-center p-6 py-12 relative">
      
      <div className="flex justify-center mb-8">
        <div className="flex items-center gap-2">
          <img src="/logos/logo.svg" alt="DRestro" className="h-10 w-auto object-contain dark:hidden" />
          <img src="/logos/logo-light.svg" alt="DRestro" className="h-10 w-auto object-contain hidden dark:block" />
        </div>
      </div>

      <div className="w-full max-w-2xl bg-white dark:bg-[#0d0d0d]/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.02)] dark:shadow-[0_8px_35px_rgba(0,0,0,0.3)] border border-gray-100 dark:border-neutral-800 p-8 md:p-12">
        <div className="mb-10">
          <h1 className="text-[28px] font-bold text-gray-900 dark:text-white mb-2">Create New Restaurant</h1>
          <p className="text-gray-500 dark:text-neutral-400">Set up your business profile to start your Free Lifetime account.</p>
        </div>

        <OnboardingForm defaultEmail={dbUser?.email || ""} defaultPhone={dbUser?.phone || ""} />
      </div>

      <div className="w-full max-w-2xl mt-8 flex justify-between items-center text-sm font-medium text-gray-500 dark:text-neutral-400">
        <Link href="/contact" className="hover:text-gray-900 dark:hover:text-white transition-colors">Join a Restaurant?</Link>
        <Link href="/api/auth/signout" className="flex items-center gap-2 hover:text-gray-900 dark:hover:text-white transition-colors">
          <LogOut size={16} /> Logout
        </Link>
      </div>
    </div>
  );
}
