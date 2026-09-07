import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/src/lib/auth';
import { prisma } from '@/src/lib/prisma';
import fs from 'fs';
import path from 'path';
import sharp from 'sharp';

export async function POST(req: NextRequest) {
  try {
    const session = await getSession();
    if (!session || (session.role !== 'SUPERADMIN' && session.role !== 'ADMIN')) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const formData = await req.formData();
    const file = formData.get('image') as File | null;
    const method = formData.get('method') as string | null;

    if (!file || !method) {
      return NextResponse.json({ error: 'Image file and method are required' }, { status: 400 });
    }

    const allowedMethods = ['fonepay', 'esewa', 'nepalpay', 'khalti'];
    if (!allowedMethods.includes(method)) {
      return NextResponse.json({ error: 'Invalid payment method' }, { status: 400 });
    }

    // Ensure uploads directory exists
    const uploadsDir = path.join(process.cwd(), 'public', 'uploads', 'qr');
    if (!fs.existsSync(uploadsDir)) {
      fs.mkdirSync(uploadsDir, { recursive: true });
    }

    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);

    // Generate unique filename with .webp extension
    const uniqueSuffix = `${Date.now()}-${Math.round(Math.random() * 1e9)}`;
    const filename = `${method}-${uniqueSuffix}.webp`;
    const filepath = path.join(uploadsDir, filename);

    // Process and compress image to webp using sharp
    await sharp(buffer)
      .resize({ width: 600, withoutEnlargement: true }) // reasonable size for QR
      .webp({ quality: 90 }) // High quality for QR readability
      .toFile(filepath);

    const qrUrl = `/uploads/qr/${filename}`;
    const settingKey = `payment_qr_${method}`;

    // Save to database
    await prisma.siteSetting.upsert({
      where: { key: settingKey },
      update: { value: qrUrl },
      create: { key: settingKey, value: qrUrl }
    });

    return NextResponse.json({ success: true, url: qrUrl, key: settingKey }, { status: 201 });
  } catch (error) {
    console.error('Error uploading QR code:', error);
    return NextResponse.json({ error: 'Failed to upload QR code' }, { status: 500 });
  }
}
