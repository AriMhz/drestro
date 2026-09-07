import { NextResponse } from 'next/server';
import { prisma } from '@/src/lib/prisma';
import { getSession } from '@/src/lib/auth';
import { syncHardwareOrder } from '@/src/lib/nepalEbilling';

async function checkAuth() {
  const session = await getSession();
  if (!session || (session.role !== 'SUPERADMIN' && session.role !== 'ADMIN')) return false;
  return true;
}

export async function PUT(
  req: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const { id } = await params;
    const body = await req.json();

    if (!id) {
      return NextResponse.json({ error: "Missing ID" }, { status: 400 });
    }

    const data: any = {};
    if (body.status !== undefined) data.status = body.status;
    if (body.fullName !== undefined) data.fullName = body.fullName;
    if (body.phone !== undefined) data.phone = body.phone;
    if (body.address !== undefined) data.address = body.address;
    if (body.items !== undefined) {
      data.items = typeof body.items === 'string' ? body.items : JSON.stringify(body.items);
    }
    if (body.totalPrice !== undefined) data.totalPrice = Number(body.totalPrice);
    if (body.discount !== undefined) data.discount = Number(body.discount);

    // If we edit/re-complete, reset sync status so it can be re-synced
    if (body.status === 'COMPLETED') {
      data.nepalEbillingSynced = false;
      data.nepalEbillingError = null;
    }

    const order = await prisma.hardwareOrder.update({
      where: { id },
      data,
    });

    if (body.status === 'COMPLETED') {
      try {
        await syncHardwareOrder(id);
      } catch (e) {
        console.error("Auto e-billing sync failed:", e);
      }
    }

    // Return the updated order with latest e-billing state
    const latestOrder = await prisma.hardwareOrder.findUnique({ where: { id } });
    return NextResponse.json(latestOrder || order);
  } catch (error) {
    console.error("Failed to update order:", error);
    return NextResponse.json({ error: "Failed to update order" }, { status: 500 });
  }
}

export async function DELETE(
  req: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const { id } = await params;
    if (!id) {
      return NextResponse.json({ error: "Missing ID" }, { status: 400 });
    }

    await prisma.hardwareOrder.delete({
      where: { id },
    });

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("Failed to delete order:", error);
    return NextResponse.json({ error: "Failed to delete order" }, { status: 500 });
  }
}
