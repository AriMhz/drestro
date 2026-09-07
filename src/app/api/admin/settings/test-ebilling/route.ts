import { NextResponse } from 'next/server';
import { getSession } from '@/src/lib/auth';
import {
  formatNepalEbillingApiError,
  getNepalEbillingHeaders,
  getNepalEbillingSalesInvoiceUrl,
} from '@/src/lib/nepalEbillingApi';

async function checkAuth() {
  const session = await getSession();
  if (!session || (session.role !== 'SUPERADMIN' && session.role !== 'ADMIN')) return false;
  return true;
}

export async function POST(req: Request) {
  const isAuth = await checkAuth();
  if (!isAuth) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  try {
    const { apiKey: rawApiKey, environment = 'staging', subdomain: rawSubdomain } = await req.json();
    const apiKey = (rawApiKey || '').trim();
    const subdomain = (rawSubdomain || '').trim().toLowerCase();

    if (!apiKey) {
      return NextResponse.json({ success: false, error: "API key is required" }, { status: 400 });
    }

    if (!subdomain) {
      return NextResponse.json({
        success: false,
        error: "Tenant subdomain is required. Use the subdomain from your Nepal E-Billing login URL (e.g. mycompany from https://mycompany.staging.nepalebilling.com).",
      }, { status: 400 });
    }

    const invoiceUrl = getNepalEbillingSalesInvoiceUrl(subdomain, environment);

    const response = await fetch(invoiceUrl, {
      method: 'GET',
      headers: getNepalEbillingHeaders(apiKey),
    });

    const text = await response.text();
    const contentType = response.headers.get('content-type');

    if (response.status === 401 || response.status === 403) {
      return NextResponse.json({
        success: false,
        error: formatNepalEbillingApiError(response.status, text, contentType, subdomain, environment),
      });
    }

    // 405 means the endpoint exists but rejects GET — API key reached the backend.
    if (response.ok || response.status === 405) {
      return NextResponse.json({ success: true });
    }

    return NextResponse.json({
      success: false,
      error: formatNepalEbillingApiError(response.status, text, contentType, subdomain, environment),
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: `Connection failed: ${error.message}` }, { status: 500 });
  }
}
