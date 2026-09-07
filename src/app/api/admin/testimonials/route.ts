import { NextResponse } from 'next/server';
import { prisma } from '@/src/lib/prisma';
import { getSession } from '@/src/lib/auth';

async function checkAuth() {
  const session = await getSession();
  if (!session || (session.role !== 'SUPERADMIN' && session.role !== 'ADMIN')) return false;
  return true;
}

export async function GET() {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const testimonials = await prisma.testimonial.findMany({
    orderBy: { order: 'asc' }
  });
  return NextResponse.json(testimonials);
}

export async function POST(req: Request) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const data = await req.json();
    const testimonial = await prisma.testimonial.create({
      data: {
        authorName: data.authorName,
        authorRole: data.authorRole,
        content: data.content,
        rating: data.rating || 5,
        authorInitials: data.authorInitials || 'A',
        bgColor: data.bgColor || 'bg-red-500',
        order: data.order || 0
      }
    });
    return NextResponse.json(testimonial);
  } catch (error) {
    return NextResponse.json({ error: "Failed to create testimonial" }, { status: 500 });
  }
}

export async function PUT(req: Request) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const data = await req.json();
    const { id, ...updateData } = data;
    
    if (!id) return NextResponse.json({ error: "ID is required" }, { status: 400 });

    const testimonial = await prisma.testimonial.update({
      where: { id },
      data: updateData
    });
    return NextResponse.json(testimonial);
  } catch (error) {
    return NextResponse.json({ error: "Failed to update testimonial" }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const url = new URL(req.url);
    const id = url.searchParams.get("id");
    
    if (!id) return NextResponse.json({ error: "ID is required" }, { status: 400 });

    await prisma.testimonial.delete({
      where: { id }
    });
    return NextResponse.json({ success: true });
  } catch (error) {
    return NextResponse.json({ error: "Failed to delete testimonial" }, { status: 500 });
  }
}
