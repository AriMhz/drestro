import { prisma } from './prisma';
import {
  formatNepalEbillingApiError,
  getNepalEbillingHeaders,
  getNepalEbillingSalesInvoiceUrl,
} from './nepalEbillingApi';

function getNepaliFiscalYear(date: Date): string {
  const year = date.getFullYear();
  const month = date.getMonth() + 1; // 1-indexed
  const day = date.getDate();

  // Shrawan starts around July 16.
  let isAfterShrawan = false;
  if (month > 7) {
    isAfterShrawan = true;
  } else if (month === 7 && day >= 16) {
    isAfterShrawan = true;
  }

  const bsYear = isAfterShrawan ? year + 57 : year + 56;
  const nextBsYearShort = (bsYear + 1) % 100;
  return `${bsYear}/${nextBsYearShort.toString().padStart(2, '0')}`;
}

export async function syncHardwareOrder(orderId: string): Promise<{ success: boolean; error?: string }> {
  try {
    // 1. Fetch order
    const order = await prisma.hardwareOrder.findUnique({
      where: { id: orderId }
    });

    if (!order) {
      return { success: false, error: "Order not found" };
    }

    // 2. Fetch e-billing settings
    const settings = await prisma.siteSetting.findMany({
      where: {
        key: {
          in: [
            'nepal_ebilling_enabled',
            'nepal_ebilling_api_key',
            'nepal_ebilling_environment',
            'nepal_ebilling_seller_pan',
            'nepal_ebilling_subdomain'
          ]
        }
      }
    });

    const settingsMap = settings.reduce((acc: any, s) => {
      acc[s.key] = s.value;
      return acc;
    }, {});

    const enabled = settingsMap['nepal_ebilling_enabled'] === 'true' || settingsMap['nepal_ebilling_enabled'] === true;
    const apiKey = settingsMap['nepal_ebilling_api_key'];
    const environment = settingsMap['nepal_ebilling_environment'] || 'staging';
    const sellerPan = settingsMap['nepal_ebilling_seller_pan'] || '';
    const subdomain = (settingsMap['nepal_ebilling_subdomain'] || '').trim().toLowerCase();

    if (!enabled) {
      return { success: false, error: "Nepal E-Billing is disabled in settings" };
    }

    if (!apiKey) {
      const errorMsg = "Nepal E-Billing API Key is missing";
      await prisma.hardwareOrder.update({
        where: { id: orderId },
        data: {
          nepalEbillingSynced: false,
          nepalEbillingError: errorMsg
        }
      });
      return { success: false, error: errorMsg };
    }

    if (!subdomain) {
      const errorMsg = "Nepal E-Billing tenant subdomain is missing. Set it in Admin > Billing > API Settings.";
      await prisma.hardwareOrder.update({
        where: { id: orderId },
        data: {
          nepalEbillingSynced: false,
          nepalEbillingError: errorMsg
        }
      });
      return { success: false, error: errorMsg };
    }

    // 3. Prepare values
    const grandTotal = order.totalPrice;
    const subtotal = Math.round((grandTotal / 1.13) * 100) / 100;
    const vat = Math.round((grandTotal - subtotal) * 100) / 100;
    const invoiceDate = new Date(order.createdAt).toISOString().split('T')[0];
    const fiscalYear = getNepaliFiscalYear(new Date(order.createdAt));

    let itemsList: any[] = [];
    try {
      itemsList = JSON.parse(order.items);
    } catch (e) {
      itemsList = [];
    }

    const itemsPayload = itemsList.map((item: any) => {
      const itemPrice = item.price || 0;
      const itemQty = item.quantity || 1;
      const itemUnitPriceExcludingVat = Math.round((itemPrice / 1.13) * 100) / 100;

      return {
        name: item.name || 'Hardware Item',
        quantity: itemQty,
        rate: itemUnitPriceExcludingVat,
        taxable: true
      };
    });

    const payload = {
      customer_name: order.fullName,
      payment_mode: "CA",
      invoice_date: invoiceDate,
      products: itemsPayload,
      reference_number: `HW-${order.id.slice(0, 8).toUpperCase()}`,
      phone_number: order.phone || "",
      address: order.address || "",
      pan_number: ""
    };

    const response = await fetch(getNepalEbillingSalesInvoiceUrl(subdomain, environment), {
      method: 'POST',
      headers: getNepalEbillingHeaders(apiKey),
      body: JSON.stringify(payload)
    });

    if (response.ok) {
      const data = await response.json();
      const remoteInvoiceId = data.invoice_id || data.id || `remote-${Date.now()}`;
      
      await prisma.hardwareOrder.update({
        where: { id: orderId },
        data: {
          nepalEbillingSynced: true,
          nepalEbillingInvoiceId: remoteInvoiceId,
          nepalEbillingError: null
        }
      });
      return { success: true };
    } else {
      let errorMsg = `API Error (${response.status})`;
      try {
        const text = await response.text();
        errorMsg = formatNepalEbillingApiError(
          response.status,
          text,
          response.headers.get('content-type'),
          subdomain,
          environment
        );
      } catch (readErr: any) {
        errorMsg += `: Failed to read response stream (${readErr.message})`;
      }

      await prisma.hardwareOrder.update({
        where: { id: orderId },
        data: {
          nepalEbillingSynced: false,
          nepalEbillingError: errorMsg
        }
      });
      return { success: false, error: errorMsg };
    }
  } catch (error: any) {
    const errorMsg = `Exception: ${error.message}`;
    await prisma.hardwareOrder.update({
      where: { id: orderId },
      data: {
        nepalEbillingSynced: false,
        nepalEbillingError: errorMsg
      }
    });
    return { success: false, error: errorMsg };
  }
}
