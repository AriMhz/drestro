import { NextResponse } from "next/server";
import { getSession } from "@/src/lib/auth";
import { prisma } from "@/src/lib/prisma";

export const dynamic = "force-dynamic";
export const runtime = "nodejs";

export async function POST(req: Request) {
  try {
    const session = await getSession();
    if (!session || (session.role !== "SUPERADMIN" && session.role !== "SUPPORT" && session.role !== "SALES")) {
      return NextResponse.json({ error: "Unauthorized access." }, { status: 403 });
    }

    const { paymentId, action } = await req.json();

    if (!paymentId || !action || !["approve", "reject"].includes(action)) {
      return NextResponse.json({ error: "Invalid parameters." }, { status: 400 });
    }

    const payment = await prisma.subscriptionPayment.findUnique({
      where: { id: paymentId }
    });

    if (!payment) {
      return NextResponse.json({ error: "Payment record not found." }, { status: 404 });
    }

    if (payment.status !== "PENDING") {
      return NextResponse.json({ error: "This payment is already processed." }, { status: 400 });
    }

    if (action === "reject") {
      await prisma.subscriptionPayment.update({
        where: { id: paymentId },
        data: { status: "REJECTED" }
      });
      return NextResponse.json({ success: true, message: "Payment receipt rejected." });
    }

    // Approve logic: activate plan & limits
    const PLAN_LIMITS: Record<string, { tableLimit: number, staffLimit: number, dishLimit: number, roomLimit: number }> = {
      free: { tableLimit: 5, staffLimit: 2, dishLimit: 50, roomLimit: 0 },
      basic: { tableLimit: 20, staffLimit: 5, dishLimit: 500, roomLimit: 10 },
      premium: { tableLimit: 50, staffLimit: 24, dishLimit: 1000, roomLimit: 20 },
      platinum: { tableLimit: 0, staffLimit: 0, dishLimit: 0, roomLimit: 0 },
    };

    const limits = PLAN_LIMITS[payment.planId.toLowerCase()] || PLAN_LIMITS.premium;

    // Calculate expiry date
    const expiryDate = new Date();
    if (payment.billingCycle === "yearly") {
      expiryDate.setFullYear(expiryDate.getFullYear() + 1);
    } else {
      expiryDate.setMonth(expiryDate.getMonth() + 6);
    }

    // 1. Fetch restaurant
    const restaurant = await prisma.restaurant.findUnique({
      where: { id: payment.restaurantId },
      include: { subscriptions: true }
    });

    if (!restaurant) {
      return NextResponse.json({ error: "Restaurant not found." }, { status: 404 });
    }

    // 2. Update/create subscription
    let subscription = restaurant.subscriptions[0];
    if (subscription) {
      await prisma.subscription.update({
        where: { id: subscription.id },
        data: {
          planId: payment.planId.toLowerCase(),
          status: "active",
          currentPeriodEnd: expiryDate,
          tableLimit: limits.tableLimit,
          staffLimit: limits.staffLimit,
          dishLimit: limits.dishLimit,
          roomLimit: limits.roomLimit,
        },
      });
    } else {
      await prisma.subscription.create({
        data: {
          restaurantId: restaurant.id,
          planId: payment.planId.toLowerCase(),
          status: "active",
          currentPeriodEnd: expiryDate,
          tableLimit: limits.tableLimit,
          staffLimit: limits.staffLimit,
          dishLimit: limits.dishLimit,
          roomLimit: limits.roomLimit,
        },
      });
    }

    // 3. Update Client record (offline synchronization key)
    if (restaurant.offlineLicenseKey) {
      await prisma.client.updateMany({
        where: { licenseKey: restaurant.offlineLicenseKey },
        data: {
          planLabel: payment.planId.charAt(0).toUpperCase() + payment.planId.slice(1).toLowerCase(),
          status: "Active",
          expiryDate: expiryDate,
          tableLimit: limits.tableLimit,
          staffLimit: limits.staffLimit,
          dishLimit: limits.dishLimit,
          roomLimit: limits.roomLimit,
        }
      });
    }

    // 4. Set payment status to COMPLETED
    await prisma.subscriptionPayment.update({
      where: { id: paymentId },
      data: { status: "COMPLETED" }
    });

    return NextResponse.json({ success: true, message: "Payment verified & subscription activated successfully!" });
  } catch (error: any) {
    console.error("Payment approval error:", error);
    return NextResponse.json({ error: error.message || "Internal server error" }, { status: 500 });
  }
}
