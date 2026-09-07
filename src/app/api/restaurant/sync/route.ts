import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";
import crypto from "crypto";

export const dynamic = "force-dynamic";
export const runtime = "nodejs";

export async function POST(req: Request) {
  try {
    const { payload, token } = await req.json();

    if (!payload || !token) {
      return NextResponse.json(
        { success: false, message: "Missing payload or token." },
        { status: 400 }
      );
    }

    const {
      id,
      name,
      tagline,
      address,
      ward,
      city,
      phone,
      email,
      currency,
      tax_percent,
      service_charge_percent,
      pan_number,
      date_calendar_type
    } = payload;

    // Validate token signature
    const signData = `${id}|${name}|${phone}`;
    const expectedToken = crypto
      .createHmac("sha256", "DrestroPOS_Secure_Key_2026_X9P2")
      .update(signData)
      .digest("hex");

    if (token !== expectedToken) {
      return NextResponse.json(
        { success: false, message: "Unauthorized request signature." },
        { status: 401 }
      );
    }

    // Find the restaurant in the database
    const restaurant = await prisma.restaurant.findUnique({
      where: { id },
    });

    if (!restaurant) {
      return NextResponse.json(
        { success: false, message: "Restaurant not found." },
        { status: 404 }
      );
    }

    // Update restaurant settings in Next.js
    const updatedRestaurant = await prisma.restaurant.update({
      where: { id },
      data: {
        name,
        tagline,
        address,
        ward,
        city,
        phone,
        email,
        currency: currency || "Rs.",
        taxPercent: tax_percent !== undefined ? parseFloat(tax_percent) : 0,
        serviceChargePercent: service_charge_percent !== undefined ? parseFloat(service_charge_percent) : 0,
        panNumber: pan_number,
        dateCalendarType: date_calendar_type || "AD",
      },
    });

    // Also sync to matching Client record if offlineLicenseKey is set!
    if (updatedRestaurant.offlineLicenseKey) {
      const existingClient = await prisma.client.findUnique({
        where: { licenseKey: updatedRestaurant.offlineLicenseKey }
      });

      const trialEndsAt = new Date();
      trialEndsAt.setDate(trialEndsAt.getDate() + 14);

      if (existingClient) {
        await prisma.client.update({
          where: { id: existingClient.id },
          data: {
            restaurantName: name,
            contactNumber: phone,
            location: address || "",
          }
        });
      } else {
        await prisma.client.create({
          data: {
            restaurantName: name,
            contactNumber: phone,
            location: address || "",
            planLabel: "Premium",
            licenseKey: updatedRestaurant.offlineLicenseKey,
            status: "Active",
            expiryDate: trialEndsAt,
            tableLimit: 20,
            staffLimit: 5,
            dishLimit: 500,
            roomLimit: 10,
          }
        });
      }
    }

    return NextResponse.json({ success: true, message: "Restaurant settings synced successfully." });
  } catch (error) {
    console.error("Restaurant Sync Error:", error);
    return NextResponse.json(
      { success: false, error: "Internal server error" },
      { status: 500 }
    );
  }
}
