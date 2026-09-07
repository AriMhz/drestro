export const NEPAL_EBILLING_SALES_INVOICE_PATH = '/invoices/sales-invoice-generation/';

export function getNepalEbillingBaseUrl(
  subdomain: string,
  environment: string = 'staging'
): string {
  let cleanInput = subdomain.trim().toLowerCase().replace(/^https?:\/\//i, '').replace(/\/+$/, '');
  if (!cleanInput) {
    throw new Error('Tenant subdomain is required');
  }

  // Extract the first label as the core tenant subdomain (e.g., "sky" from "sky.staging.nepalebilling.com")
  const tenant = cleanInput.split('.')[0];
  if (tenant === 'nepalebilling' || tenant === 'staging' || tenant === 'api') {
    throw new Error('Please enter your specific company subdomain (e.g., sky), not the main Nepal E-Billing domain.');
  }

  const suffix = environment === 'production' ? 'api.nepalebilling.com' : 'api.staging.nepalebilling.com';
  return `https://${tenant}.${suffix}`;
}

export function getNepalEbillingSalesInvoiceUrl(
  subdomain: string,
  environment: string = 'staging'
): string {
  return `${getNepalEbillingBaseUrl(subdomain, environment)}${NEPAL_EBILLING_SALES_INVOICE_PATH}`;
}

function isHtmlResponse(contentType: string | null, body: string): boolean {
  return (
    (contentType?.includes('text/html') ?? false) ||
    body.trimStart().startsWith('<!DOCTYPE') ||
    body.trimStart().startsWith('<!doctype')
  );
}

export function formatNepalEbillingApiError(
  status: number,
  body: string,
  contentType: string | null,
  subdomain: string,
  environment: string
): string {
  if (status === 404 && isHtmlResponse(contentType, body)) {
    const exampleUrl = getNepalEbillingSalesInvoiceUrl(subdomain || 'your-company', environment);
    return [
      `Endpoint not found (404) at ${getNepalEbillingBaseUrl(subdomain, environment)}.`,
      'This usually means the tenant subdomain or environment is wrong.',
      'Use the subdomain from your Nepal E-Billing login URL.',
      `Example: https://your-company.${environment === 'production' ? 'nepalebilling.com' : 'staging.nepalebilling.com'}`,
      `Expected API URL: ${exampleUrl}`,
      'Also confirm API integration is enabled under Dashboard > Settings > API Keys.',
    ].join(' ');
  }

  if (status === 403) {
    return 'Forbidden (403): API integration may be disabled or your subscription may be expired. Enable it in Dashboard > Settings > API Keys.';
  }

  if (status === 401) {
    return 'Unauthorized (401): Invalid API key. Generate a new key from Dashboard > Settings > API Keys.';
  }

  let errorMsg = `API Error (${status})`;
  try {
    const data = JSON.parse(body);
    errorMsg += `: ${data.detail || data.message || JSON.stringify(data)}`;
  } catch {
    if (body) {
      errorMsg += `: ${body.slice(0, 200)}`;
    }
  }

  return errorMsg;
}

export function getNepalEbillingHeaders(apiKey: string): HeadersInit {
  const cleanKey = apiKey.trim();
  return {
    'X-Api-Key': cleanKey,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };
}
