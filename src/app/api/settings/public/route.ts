import { NextResponse } from 'next/server';
import { prisma } from '@/src/lib/prisma';

export async function GET() {
  try {
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
            'payment_qr_fonepay',
            'payment_qr_esewa',
            'payment_qr_nepalpay',
            'payment_qr_khalti'
          ]
        }
      }
    });

    const settingsMap = settings.reduce((acc: any, setting) => {
      // The value might be JSON stringified, we'll try to parse it, or just use it as string if it's plain text
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
  } catch (error) {
    console.error("Failed to fetch public settings:", error);
    return NextResponse.json({}, { status: 500 });
  }
}
