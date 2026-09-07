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

  const settings = await prisma.siteSetting.findMany({
    where: {
      key: {
        in: [
          'whatsapp_number',
          'facebook_url',
          'twitter_url',
          'instagram_url',
          'linkedin_url',
          'contact_location',
          'contact_phone',
          'contact_email',
          'careers_email',
          'contact_map_iframe',
          'privacy_policy',
          'terms_conditions',
          'refund_policy',
          'nepal_ebilling_enabled',
          'nepal_ebilling_api_key',
          'nepal_ebilling_environment',
          'nepal_ebilling_seller_pan',
          'nepal_ebilling_subdomain',
          'payment_qr_fonepay',
          'payment_qr_esewa',
          'payment_qr_nepalpay',
          'payment_qr_khalti'
        ]
      }
    }
  });

  const settingsMap = settings.reduce((acc: any, setting) => {
    let val: any = setting.value;
    try {
      const parsed = JSON.parse(val);
      if (typeof parsed === 'string' || typeof parsed === 'number' || typeof parsed === 'boolean') {
        val = parsed;
      }
    } catch(e) {}
    if (typeof val === 'string') {
      val = val.trim();
      if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith("'") && val.endsWith("'"))) {
        val = val.slice(1, -1).trim();
      }
    }
    acc[setting.key] = val;
    return acc;
  }, {});

  return NextResponse.json(settingsMap);
}

export async function POST(req: Request) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const data = await req.json();
    
    // Update or create each setting
    for (const [key, value] of Object.entries(data)) {
      if (value !== undefined) {
        const strValue = typeof value === 'string' ? value : JSON.stringify(value);
        await prisma.siteSetting.upsert({
          where: { key },
          update: { value: strValue },
          create: { key, value: strValue }
        });
      }
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    return NextResponse.json({ error: "Failed to update settings" }, { status: 500 });
  }
}
