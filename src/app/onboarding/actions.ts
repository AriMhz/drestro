"use server";

import { auth } from "../../../auth";
import { redirect } from "next/navigation";
import { prisma } from "../../lib/prisma";
import crypto from "crypto";
import bcrypt from "bcryptjs";

export async function saveRestaurant(formData: FormData) {
  const session = await auth();
  if (!session?.user?.id) return redirect("/login");

  const restaurants = await prisma.restaurant.findMany({
    where: { userId: session.user.id },
    include: { subscriptions: true }
  });

  if (restaurants.length > 0) {
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

    if (!isPlatinum) {
      // Redirect back to dashboard if they are trying to create another restaurant but are not Platinum
      return redirect("/dashboard");
    }
  }

  const name = formData.get("name") as string;
  const phone = formData.get("phone") as string;
  const email = formData.get("email") as string;
  const password = formData.get("password") as string;
  const panNumber = formData.get("panNumber") as string;
  const address = formData.get("address") as string;
  const type = formData.get("type") as string;

  if (!name || !phone) return;

  let cleanPhone = phone ? phone.replace(/[^0-9]/g, "") : "";
  if (cleanPhone && cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
    cleanPhone = cleanPhone.substring(3);
  }

  if (cleanPhone && (cleanPhone.length < 9 || cleanPhone.length > 10)) {
    throw new Error("Phone number must be exactly 9 or 10 digits (e.g. 98XXXXXXXX)");
  }

  // Hash branch password if provided
  let hashedPassword = null;
  if (password && password.trim().length > 0) {
    hashedPassword = await bcrypt.hash(password.trim(), 10);
  }

  // Generate an offline license key automatically (e.g. DRESTRO-XXXX-XXXX)
  const keySegment = () => crypto.randomBytes(2).toString("hex").toUpperCase();
  const offlineLicenseKey = `DR-${keySegment()}-${keySegment()}-${keySegment()}`;

  const hasHotelFeature = type ? (type.toLowerCase().includes("hotel") || type.toLowerCase().includes("resort")) : false;
  const allowedMenus = hasHotelFeature
    ? "pos,menu,tables,rooms,kot,reports,expenses,staff,inventory,customers,settings"
    : "pos,menu,tables,kot,reports,expenses,staff,inventory,customers,settings";

  const newRestaurant = await prisma.restaurant.create({
    data: {
      userId: session.user.id,
      name,
      phone: cleanPhone || phone,
      email: email || null,
      password: hashedPassword,
      panNumber: panNumber || null,
      address,
      type,
      offlineLicenseKey,
      subscriptions: {
        create: {
          planId: "free",
          status: "active",
          trialEndsAt: null,
          tableLimit: 10,
          staffLimit: 1,
          dishLimit: 100,
          roomLimit: 5,
          allowedMenus
        }
      }
    }
  });

  // Create Client record to track license key for offline app and show in SaaS Admin Client list
  await prisma.client.create({
    data: {
      restaurantName: name,
      contactNumber: phone,
      location: address || "",
      planLabel: "Free Plan",
      licenseKey: offlineLicenseKey,
      status: "Active",
      expiryDate: null,
      tableLimit: 10,
      staffLimit: 1,
      dishLimit: 100,
      roomLimit: 5,
    }
  });

  // Generate Secure SSO Token for newly created restaurant
  const baseUrl = process.env.NODE_ENV === "production" 
    ? "https://portal.drestro.com" 
    : (process.env.NEXT_PUBLIC_PORTAL_URL || "http://localhost:8000");
  
  const planId = "free";
  const ssoExpiresAt = "";
  const limits = JSON.stringify({
    tables: 10,
    users: 1,
    items: 100,
    rooms: 5,
    allowedMenus
  });

  const payload = `${session?.user?.email || ""}|${planId}`;
  const token = crypto.createHmac('sha256', 'DrestroPOS_Secure_Key_2026_X9P2').update(payload).digest('hex');
  const ssoUrl = `${baseUrl}/sso/login?slug=${encodeURIComponent(newRestaurant.id)}&email=${encodeURIComponent(session?.user?.email || "")}&name=${encodeURIComponent(session?.user?.name || "")}&plan=${encodeURIComponent(planId)}&expires_at=${encodeURIComponent(ssoExpiresAt)}&limits=${encodeURIComponent(limits)}&license_key=${encodeURIComponent(offlineLicenseKey)}&restaurant_name=${encodeURIComponent(name)}&restaurant_address=${encodeURIComponent(address)}&restaurant_phone=${encodeURIComponent(phone)}&restaurant_pan=${encodeURIComponent(panNumber || "")}&restaurant_tagline=&restaurant_ward=&restaurant_city=&restaurant_email=${encodeURIComponent(email || "")}&restaurant_currency=Rs.&restaurant_tax=0&restaurant_service_charge=0&restaurant_calendar=AD&token=${token}`;
  
  redirect(ssoUrl);
}
