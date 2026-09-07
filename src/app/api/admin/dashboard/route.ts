import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";
import { getSession } from "@/src/lib/auth";

export async function GET() {
  try {
    const session = await getSession();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const [
      totalClients,
      activeClients,
      totalWebUsers,
      totalRestaurants,
      totalStaff,
      totalHardware,
      subscriptions,
      expiringClients,
      recentUsers,
    ] = await Promise.all([
      prisma.client.count(),
      prisma.client.count({ where: { status: "Active" } }),
      prisma.user.count(),
      prisma.restaurant.count(),
      prisma.adminStaff.count(),
      prisma.hardware.count(),
      prisma.subscription.findMany({ select: { planId: true, status: true } }),
      prisma.client.count({
        where: {
          expiryDate: { lte: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000), gt: new Date() },
          status: "Active"
        }
      }),
      prisma.user.findMany({
        take: 5,
        orderBy: { createdAt: "desc" },
        select: { id: true, name: true, email: true, createdAt: true },
      }),
    ]);

    // Plan breakdown
    const planBreakdown: Record<string, number> = {};
    const statusBreakdown: Record<string, number> = {};
    subscriptions.forEach((s: any) => {
      planBreakdown[s.planId] = (planBreakdown[s.planId] || 0) + 1;
      statusBreakdown[s.status] = (statusBreakdown[s.status] || 0) + 1;
    });

    return NextResponse.json({
      totalClients,
      activeClients,
      expiringClients,
      totalWebUsers,
      totalRestaurants,
      totalStaff,
      totalHardware,
      totalSubscriptions: subscriptions.length,
      planBreakdown,
      statusBreakdown,
      recentUsers,
    });
  } catch (error) {
    console.error("Dashboard stats error:", error);
    return NextResponse.json({ error: "Failed to fetch stats" }, { status: 500 });
  }
}
