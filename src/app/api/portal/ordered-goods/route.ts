import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const licenseKey = searchParams.get("licenseKey");

  if (!licenseKey) {
    return NextResponse.json({ error: "licenseKey is required" }, { status: 400 });
  }

  try {
    let phoneToSearch: string | null = null;

    // 1. Try finding a Client by licenseKey
    const client = await prisma.client.findUnique({
      where: { licenseKey },
    });

    if (client && client.contactNumber) {
      phoneToSearch = client.contactNumber;
    } else {
      // 2. Try finding a Restaurant by offlineLicenseKey
      const restaurant = await prisma.restaurant.findUnique({
        where: { offlineLicenseKey: licenseKey },
      });
      if (restaurant && restaurant.phone) {
        phoneToSearch = restaurant.phone;
      }
    }

    if (!phoneToSearch) {
      return NextResponse.json({ orders: [] }); // No phone found
    }

    // 3. Find Hardware Orders by that phone number
    const orders = await prisma.hardwareOrder.findMany({
      where: {
        phone: phoneToSearch,
      },
      orderBy: {
        createdAt: "desc",
      },
    });

    // 4. Parse the JSON items string back to an object array
    const parsedOrders = orders.map(order => {
      let parsedItems = [];
      try {
        parsedItems = JSON.parse(order.items);
      } catch (e) {
        // Fallback if not valid JSON
        parsedItems = [];
      }
      return {
        ...order,
        items: parsedItems,
      };
    });

    return NextResponse.json({ orders: parsedOrders });

  } catch (error) {
    console.error("Error fetching ordered goods:", error);
    return NextResponse.json({ error: "Internal server error" }, { status: 500 });
  }
}
