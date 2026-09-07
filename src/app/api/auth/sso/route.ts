import { NextResponse } from "next/server";
import { auth } from "../../../../../auth";
import { prisma } from "../../../../lib/prisma";
import crypto from "crypto";

export const dynamic = "force-dynamic";

export async function GET(request: Request) {
  const session = await auth();
  
  if (!session?.user?.id) {
    return NextResponse.redirect(new URL("/login", process.env.NEXTAUTH_URL || request.url));
  }

  const restaurants = await prisma.restaurant.findMany({
    where: { userId: session.user.id },
    include: { subscriptions: true }
  });

  if (restaurants.length > 0) {
    const restaurant = restaurants[0];
    const sub = restaurant.subscriptions[0];
    
    const email = session.user.email || "";
    const name = session.user.name || "";
    const isPaidActive = sub?.currentPeriodEnd && new Date(sub.currentPeriodEnd) > new Date() && !sub.planId.includes("trial") && sub.planId !== "free";
    const planId = isPaidActive ? sub.planId : "free";
    const ssoExpiresAt = isPaidActive && sub?.currentPeriodEnd ? sub.currentPeriodEnd.toISOString() : "";
    
    const limits = JSON.stringify({
      tables: sub?.tableLimit || 0,
      users: sub?.staffLimit || 0,
      items: sub?.dishLimit || 0,
      rooms: sub?.roomLimit || 0
    });
    
    const licenseKey = restaurant.offlineLicenseKey || "";
    
    const payload = `${email}|${planId}`;
    const token = crypto.createHmac('sha256', 'DrestroPOS_Secure_Key_2026_X9P2').update(payload).digest('hex');
    
    const ssoUrl = `${process.env.NODE_ENV === "production" ? "https://portal.drestro.com" : "http://localhost:8080"}/sso/login?slug=${encodeURIComponent(restaurant.id)}&email=${encodeURIComponent(email)}&name=${encodeURIComponent(name)}&plan=${encodeURIComponent(planId)}&expires_at=${encodeURIComponent(ssoExpiresAt)}&limits=${encodeURIComponent(limits)}&license_key=${encodeURIComponent(licenseKey)}&restaurant_name=${encodeURIComponent(restaurant.name || "")}&restaurant_address=${encodeURIComponent(restaurant.address || "")}&restaurant_phone=${encodeURIComponent(restaurant.phone || "")}&restaurant_pan=${encodeURIComponent(restaurant.panNumber || "")}&restaurant_tagline=${encodeURIComponent(restaurant.tagline || "")}&restaurant_ward=${encodeURIComponent(restaurant.ward || "")}&restaurant_city=${encodeURIComponent(restaurant.city || "")}&restaurant_email=${encodeURIComponent(restaurant.email || email)}&restaurant_currency=${encodeURIComponent(restaurant.currency || "Rs.")}&restaurant_tax=${encodeURIComponent(String(restaurant.taxPercent || 0))}&restaurant_service_charge=${encodeURIComponent(String(restaurant.serviceChargePercent || 0))}&restaurant_calendar=${encodeURIComponent(restaurant.dateCalendarType || "AD")}&token=${token}`;

    return NextResponse.redirect(ssoUrl);
  }

  return NextResponse.redirect(new URL("/onboarding", process.env.NEXTAUTH_URL || request.url));
}
