import { NextResponse } from 'next/server';
import { prisma } from '@/src/lib/prisma';

export async function GET() {
  try {
    const logos = await prisma.clientLogo.findMany({
      orderBy: { order: 'asc' },
    });

    return NextResponse.json(logos);
  } catch (error) {
    console.error('Error fetching client logos:', error);
    return NextResponse.json({ error: 'Failed to fetch logos' }, { status: 500 });
  }
}
