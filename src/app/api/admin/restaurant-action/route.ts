import { NextResponse } from "next/server";
import { getSession } from "@/src/lib/auth";
import { prisma } from "@/src/lib/prisma";
import crypto from "crypto";

export const dynamic = "force-dynamic";
export const runtime = "nodejs";

export async function POST(req: Request) {
  try {
    const session = await getSession();
    if (!session || (session.role !== "SUPERADMIN" && session.role !== "SUPPORT")) {
      return NextResponse.json({ error: "Unauthorized access." }, { status: 403 });
    }

    const { restaurantId, action } = await req.json();

    if (!restaurantId || !action || !["reset", "delete"].includes(action)) {
      return NextResponse.json({ error: "Invalid parameters." }, { status: 400 });
    }

    // Verify restaurant exists in SaaS DB
    const restaurant = await prisma.restaurant.findUnique({
      where: { id: restaurantId },
    });

    if (!restaurant) {
      return NextResponse.json({ error: "Restaurant not found." }, { status: 404 });
    }

    // Calculate signature token for Laravel POS verification
    const signData = `${restaurantId}|${action}`;
    const token = crypto
      .createHmac("sha256", "DrestroPOS_Secure_Key_2026_X9P2")
      .update(signData)
      .digest("hex");

    // Call local Laravel POS endpoint to apply reset/delete in its SQLite database
    let posSuccess = false;
    let posMessage = "";

    try {
      const posResponse = await fetch("https://portal.drestro.com/api/restaurant/admin-action", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({ slug: restaurantId, action, token }),
        cache: "no-store",
      });

      const posData = await posResponse.json();
      posSuccess = posData.success;
      posMessage = posData.message || "Failed action on POS database.";
    } catch (err: any) {
      console.error("Failed to connect to POS API:", err);
      posMessage = "Could not communicate with POS server API: " + err.message;
    }

    // If POS action succeeded, sync local SaaS database too
    if (posSuccess) {
      if (action === "delete") {
        // Delete restaurant from SaaS Next.js database
        await prisma.restaurant.delete({
          where: { id: restaurantId },
        });
      }
      return NextResponse.json({ success: true, message: `Successfully performed ${action} on restaurant.` });
    } else {
      return NextResponse.json({ error: posMessage }, { status: 500 });
    }
  } catch (error: any) {
    console.error("Restaurant Admin Action Error:", error);
    return NextResponse.json({ error: error.message || "Internal server error" }, { status: 500 });
  }
}
