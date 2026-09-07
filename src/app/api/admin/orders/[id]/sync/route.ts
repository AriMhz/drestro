import { NextResponse } from 'next/server';
import { getSession } from '@/src/lib/auth';
import { syncHardwareOrder } from '@/src/lib/nepalEbilling';
import { prisma } from '@/src/lib/prisma';

async function checkAuth() {
  const session = await getSession();
  if (!session || (session.role !== 'SUPERADMIN' && session.role !== 'ADMIN')) return false;
  return true;
}

export async function POST(
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

    const result = await syncHardwareOrder(id);

    if (result.success) {
      const order = await prisma.hardwareOrder.findUnique({
        where: { id },
        select: { nepalEbillingInvoiceId: true }
      });
      return NextResponse.json({ success: true, invoiceId: order?.nepalEbillingInvoiceId });
    } else {
      return NextResponse.json({ success: false, error: result.error || "Sync failed" }, { status: 400 });
    }
  } catch (error: any) {
    console.error("Manual e-billing sync error:", error);
    return NextResponse.json({ success: false, error: error.message || "Failed to trigger sync" }, { status: 500 });
  }
}
