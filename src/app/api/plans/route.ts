import { NextResponse } from 'next/server';
import { prisma } from '@/src/lib/prisma';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    const plans = await prisma.plan.findMany({
      include: {
        features: true,
        notIncluded: true,
      },
      orderBy: { priceYearly: 'asc' }
    });
    return NextResponse.json(plans);
  } catch (error) {
    console.error("Failed to fetch public plans:", error);
    return NextResponse.json([], { status: 500 });
  }
}
