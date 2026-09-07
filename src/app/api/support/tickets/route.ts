import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";

async function getOrCreateRestaurant(licenseKey: string) {
  let restaurant = await prisma.restaurant.findUnique({
    where: { offlineLicenseKey: licenseKey },
  });

  if (!restaurant) {
    const client = await prisma.client.findUnique({
      where: { licenseKey: licenseKey },
    });
    if (client) {
      // Find or create a user to own this restaurant
      let user = await prisma.user.findFirst();
      if (!user) {
        user = await prisma.user.create({
          data: {
            name: "System Admin",
            email: "admin@drestro.com",
          }
        });
      }
      restaurant = await prisma.restaurant.create({
        data: {
          userId: user.id,
          name: client.restaurantName,
          phone: client.contactNumber,
          address: client.location || "Offline client location",
          offlineLicenseKey: licenseKey,
        }
      });
    }
  }

  return restaurant;
}

export async function POST(req: Request) {
  try {
    const body = await req.json();
    const { licenseKey, title, description, priority, grantAccess } = body;

    if (!licenseKey || !title || !description || !priority) {
      return NextResponse.json(
        { error: "Missing required fields" },
        { status: 400 }
      );
    }

    // Find or create the restaurant by license key/client key
    const restaurant = await getOrCreateRestaurant(licenseKey);

    if (!restaurant) {
      return NextResponse.json(
        { error: "Invalid license key" },
        { status: 401 }
      );
    }

    // Add access granted flag to description if checked
    let finalDescription = description;
    if (grantAccess) {
      finalDescription = `[REMOTE ACCESS GRANTED]\nThe client has explicitly granted support.drestro remote access to their system to fix this issue.\n\n---\n${description}`;
    }

    // Generate sequential ticketCode
    const ticketCount = await prisma.supportTicket.count();
    let ticketCode = `TKT-${100001 + ticketCount}`;
    while (true) {
      const existing = await prisma.supportTicket.findFirst({ where: { ticketCode } });
      if (!existing) break;
      const num = parseInt(ticketCode.split('-')[1]) + 1;
      ticketCode = `TKT-${num}`;
    }

    // Create the ticket in the central SaaS database
    const ticket = await prisma.supportTicket.create({
      data: {
        ticketCode,
        title,
        description: finalDescription,
        priority,
        status: "OPEN",
        restaurantId: restaurant.id,
      },
    });

    return NextResponse.json({ success: true, ticket });
  } catch (error) {
    console.error("Support ticket creation error:", error);
    return NextResponse.json(
      { error: "Failed to create support ticket" },
      { status: 500 }
    );
  }
}

export async function GET(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const licenseKey = searchParams.get("licenseKey");

    if (!licenseKey) {
      return NextResponse.json(
        { error: "Missing license key" },
        { status: 400 }
      );
    }

    // Find or create the restaurant by license key/client key
    const restaurant = await getOrCreateRestaurant(licenseKey);

    if (!restaurant) {
      return NextResponse.json(
        { error: "Invalid license key" },
        { status: 401 }
      );
    }

    // Return the latest tickets for this restaurant
    const tickets = await prisma.supportTicket.findMany({
      where: { restaurantId: restaurant.id },
      orderBy: { createdAt: "desc" },
    });

    return NextResponse.json({ success: true, tickets });
  } catch (error) {
    console.error("Error fetching support tickets:", error);
    return NextResponse.json(
      { error: "Failed to fetch support tickets" },
      { status: 500 }
    );
  }
}

export async function PUT(req: Request) {
  try {
    const body = await req.json();
    const { licenseKey, title, description, priority, status } = body;
    const { searchParams } = new URL(req.url);
    const titleMatch = searchParams.get("titleMatch"); // Use title to find the ticket

    if (!licenseKey || !titleMatch) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    const restaurant = await getOrCreateRestaurant(licenseKey);

    if (!restaurant) {
      return NextResponse.json({ error: "Invalid license key" }, { status: 401 });
    }

    // Find the specific ticket for this restaurant by title
    const existingTickets = await prisma.supportTicket.findMany({
      where: { restaurantId: restaurant.id, title: titleMatch },
      orderBy: { createdAt: "desc" },
    });
    
    if (existingTickets.length === 0) {
       return NextResponse.json({ error: "Ticket not found" }, { status: 404 });
    }

    const ticket = await prisma.supportTicket.update({
      where: { id: existingTickets[0].id },
      data: {
        ...(title && { title }),
        ...(description && { description }),
        ...(priority && { priority }),
        ...(status && { status }),
      },
    });

    return NextResponse.json({ success: true, ticket });
  } catch (error) {
    console.error("Support ticket update error:", error);
    return NextResponse.json({ error: "Failed to update support ticket" }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const { searchParams } = new URL(req.url);
    const licenseKey = searchParams.get("licenseKey");
    const titleMatch = searchParams.get("titleMatch");

    if (!licenseKey || !titleMatch) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    const restaurant = await getOrCreateRestaurant(licenseKey);

    if (!restaurant) {
      return NextResponse.json({ error: "Invalid license key" }, { status: 401 });
    }

    const existingTickets = await prisma.supportTicket.findMany({
      where: { restaurantId: restaurant.id, title: titleMatch },
      orderBy: { createdAt: "desc" },
    });

    if (existingTickets.length === 0) {
       return NextResponse.json({ error: "Ticket not found" }, { status: 404 });
    }

    await prisma.supportTicket.delete({
      where: { id: existingTickets[0].id },
    });

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("Support ticket deletion error:", error);
    return NextResponse.json({ error: "Failed to delete support ticket" }, { status: 500 });
  }
}

