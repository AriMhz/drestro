import { NextResponse } from 'next/server';
import { prisma } from '@/src/lib/prisma';
import { getSession } from '@/src/lib/auth';
import { syncHardwareOrder } from '@/src/lib/nepalEbilling';

async function checkAuth() {
  const session = await getSession();
  if (!session || (session.role !== 'SUPERADMIN' && session.role !== 'ADMIN')) return false;
  return true;
}

export async function GET() {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const orders = await prisma.hardwareOrder.findMany({
      orderBy: { createdAt: 'desc' },
    });
    return NextResponse.json(orders);
  } catch (error) {
    return NextResponse.json({ error: "Failed to fetch orders" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const { fullName, email, phone, address, items, status = "COMPLETED", discount = 0 } = await req.json();

    if (!fullName || !phone || !address || !items || !Array.isArray(items) || items.length === 0) {
      return NextResponse.json({ error: "Missing required fields or items array is empty" }, { status: 400 });
    }

    // Process and validate items list
    let subtotal = 0;
    const itemsPayload = items.map((item: any, idx: number) => {
      const price = Number(item.price) || 0;
      const quantity = Number(item.quantity) || 1;
      subtotal += price * quantity;
      
      return {
        id: item.id || `manual-${Date.now()}-${idx}`,
        name: item.name || "Hardware Item",
        price: price,
        quantity: quantity,
        image: item.image || "/images/hardware-placeholder.png"
      };
    });

    const discountAmount = Number(discount) || 0;
    const totalPrice = Math.max(0, subtotal - discountAmount);

    const order = await prisma.hardwareOrder.create({
      data: {
        fullName,
        email,
        phone,
        address,
        items: JSON.stringify(itemsPayload),
        discount: discountAmount,
        totalPrice,
        status,
      },
    });

    // If marked as COMPLETED, auto-trigger e-billing synchronization
    if (status === "COMPLETED") {
      try {
        await syncHardwareOrder(order.id);
      } catch (e) {
        console.error("Auto e-billing sync for manual order failed:", e);
      }
    }

    const finalOrder = await prisma.hardwareOrder.findUnique({
      where: { id: order.id }
    });

    return NextResponse.json({ success: true, order: finalOrder || order });
  } catch (error: any) {
    console.error("Failed to create manual order:", error);
    return NextResponse.json({ error: error.message || "Failed to create manual order" }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const { searchParams } = new URL(req.url);
    const id = searchParams.get('id');
    if (!id) return NextResponse.json({ error: "Missing ID" }, { status: 400 });

    await prisma.hardwareOrder.delete({ where: { id } });
    return NextResponse.json({ success: true });
  } catch (error) {
    return NextResponse.json({ error: "Failed to delete order" }, { status: 500 });
  }
}

