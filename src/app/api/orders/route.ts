import { NextResponse } from 'next/server';
import { prisma } from '@/src/lib/prisma';

export async function POST(req: Request) {
  try {
    const { fullName, email, phone, address, items, totalPrice } = await req.json();

    if (!fullName || !email || !phone || !address || !items || totalPrice === undefined) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    // items should be a serialized JSON string or an array that we serialize
    const itemsJson = typeof items === 'string' ? items : JSON.stringify(items);

    const order = await prisma.hardwareOrder.create({
      data: {
        fullName,
        email: email || null,
        phone,
        address,
        items: itemsJson,
        totalPrice: Number(totalPrice),
        status: "PENDING",
      },
    });

    return NextResponse.json({ success: true, id: order.id });
  } catch (error) {
    console.error("Error creating hardware order:", error);
    return NextResponse.json({ error: "Failed to create order" }, { status: 500 });
  }
}
