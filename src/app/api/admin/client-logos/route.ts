import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/src/lib/auth';
import { prisma } from '@/src/lib/prisma';
import fs from 'fs';
import path from 'path';
import sharp from 'sharp';

export async function GET(req: NextRequest) {
  try {
    const session = await getSession();
    if (!session || (session.role !== 'SUPERADMIN' && session.role !== 'ADMIN')) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const logos = await prisma.clientLogo.findMany({
      orderBy: { order: 'asc' },
    });

    return NextResponse.json(logos);
  } catch (error) {
    console.error('Error fetching client logos:', error);
    return NextResponse.json({ error: 'Failed to fetch logos' }, { status: 500 });
  }
}

export async function POST(req: NextRequest) {
  try {
    const session = await getSession();
    if (!session || (session.role !== 'SUPERADMIN' && session.role !== 'ADMIN')) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const formData = await req.formData();
    const file = formData.get('image') as File | null;
    const name = formData.get('name') as string | null;

    if (!file) {
      return NextResponse.json({ error: 'Image file is required' }, { status: 400 });
    }

    // Ensure uploads directory exists
    const uploadsDir = path.join(process.cwd(), 'public', 'uploads', 'client-logos');
    if (!fs.existsSync(uploadsDir)) {
      fs.mkdirSync(uploadsDir, { recursive: true });
    }

    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);

    // Generate unique filename with .webp extension
    const uniqueSuffix = `${Date.now()}-${Math.round(Math.random() * 1e9)}`;
    const filename = `logo-${uniqueSuffix}.webp`;
    const filepath = path.join(uploadsDir, filename);

    // Process and compress image to webp using sharp
    await sharp(buffer)
      .resize({ width: 400, withoutEnlargement: true }) // Max width 400px for logos
      .webp({ quality: 80 }) // Compress to webp
      .toFile(filepath);

    // Save to database
    const logoUrl = `/uploads/client-logos/${filename}`;
    
    // Get highest order
    const lastLogo = await prisma.clientLogo.findFirst({
      orderBy: { order: 'desc' },
    });
    const nextOrder = (lastLogo?.order || 0) + 1;

    const newLogo = await prisma.clientLogo.create({
      data: {
        url: logoUrl,
        name: name || file.name,
        order: nextOrder,
      },
    });

    return NextResponse.json(newLogo, { status: 201 });
  } catch (error) {
    console.error('Error uploading client logo:', error);
    return NextResponse.json({ error: 'Failed to upload logo' }, { status: 500 });
  }
}
