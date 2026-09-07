import { prisma } from "@/src/lib/prisma";
import BillingClient from "./BillingClient";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "E-Billing Management - DRestro Admin",
};

export default async function BillingPage() {
  // 1. Fetch E-Billing Settings
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
    let val = s.value;
    try {
      const parsed = JSON.parse(val);
      if (typeof parsed === "string") {
        val = parsed;
      }
    } catch (e) {}
    val = val.trim();
    if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith("'") && val.endsWith("'"))) {
      val = val.slice(1, -1).trim();
    }
    acc[s.key] = val;
    return acc;
  }, {
    nepal_ebilling_enabled: 'false',
    nepal_ebilling_api_key: '',
    nepal_ebilling_environment: 'staging',
    nepal_ebilling_seller_pan: '',
    nepal_ebilling_subdomain: ''
  });

  // 2. Fetch Hardware Orders for invoice logs
  const orders = await prisma.hardwareOrder.findMany({
    orderBy: { createdAt: "desc" }
  });

  // 3. Fetch Clients for Auto-Fill autocomplete
  const clients = await prisma.client.findMany({
    orderBy: { restaurantName: "asc" }
  });

  // 4. Fetch Users and Restaurants for Auto-Fill autocomplete
  const users = await prisma.user.findMany({
    include: {
      restaurants: true
    },
    orderBy: { name: "asc" }
  });

  // 5. Fetch Plans, Combos, and Hardware for billing autocomplete
  const plans = await prisma.plan.findMany({
    orderBy: { name: "asc" }
  });

  const combos = await prisma.comboPackage.findMany({
    orderBy: { name: "asc" }
  });

  const hardware = await prisma.hardware.findMany({
    orderBy: { name: "asc" }
  });

  // Backfill missing customer codes
  const usersWithoutCode = await prisma.user.findMany({
    where: { customerCode: null }
  });

  if (usersWithoutCode.length > 0) {
    let currentCount = await prisma.user.count();
    for (const u of usersWithoutCode) {
      let codeNum = 100000 + currentCount + 1;
      let code = String(codeNum);
      while (true) {
        const collision = await prisma.user.findFirst({ where: { customerCode: code } });
        if (!collision) break;
        codeNum++;
        code = String(codeNum);
      }
      try {
        await prisma.user.update({
          where: { id: u.id },
          data: { customerCode: code }
        });
        currentCount++;
      } catch (err) {}
    }
  }

  // Backfill missing ticket codes
  const ticketsWithoutCode = await prisma.supportTicket.findMany({
    where: { ticketCode: null }
  });
  if (ticketsWithoutCode.length > 0) {
    let currentTicketCount = await prisma.supportTicket.count();
    for (const t of ticketsWithoutCode) {
      let code = `TKT-${100000 + currentTicketCount + 1}`;
      while (true) {
        const collision = await prisma.supportTicket.findFirst({ where: { ticketCode: code } });
        if (!collision) break;
        currentTicketCount++;
        code = `TKT-${100000 + currentTicketCount + 1}`;
      }
      try {
        await prisma.supportTicket.update({
          where: { id: t.id },
          data: { ticketCode: code }
        });
        currentTicketCount++;
      } catch (err) {}
    }
  }

  // Backfill missing contact submission codes
  const submissionsWithoutCode = await prisma.contactSubmission.findMany({
    where: { submissionCode: null }
  });
  if (submissionsWithoutCode.length > 0) {
    let currentSubmissionsCount = await prisma.contactSubmission.count();
    for (const s of submissionsWithoutCode) {
      let code = `MSG-${100000 + currentSubmissionsCount + 1}`;
      while (true) {
        const collision = await prisma.contactSubmission.findFirst({ where: { submissionCode: code } });
        if (!collision) break;
        currentSubmissionsCount++;
        code = `MSG-${100000 + currentSubmissionsCount + 1}`;
      }
      try {
        await prisma.contactSubmission.update({
          where: { id: s.id },
          data: { submissionCode: code }
        });
        currentSubmissionsCount++;
      } catch (err) {}
    }
  }

  // Backfill missing payment codes
  const paymentsWithoutCode = await prisma.subscriptionPayment.findMany({
    where: { paymentCode: null }
  });
  if (paymentsWithoutCode.length > 0) {
    let currentPaymentsCount = await prisma.subscriptionPayment.count();
    for (const p of paymentsWithoutCode) {
      let code = `TXN-${100000 + currentPaymentsCount + 1}`;
      while (true) {
        const collision = await prisma.subscriptionPayment.findFirst({ where: { paymentCode: code } });
        if (!collision) break;
        currentPaymentsCount++;
        code = `TXN-${100000 + currentPaymentsCount + 1}`;
      }
      try {
        await prisma.subscriptionPayment.update({
          where: { id: p.id },
          data: { paymentCode: code }
        });
        currentPaymentsCount++;
      } catch (err) {}
    }
  }

  const subscriptionPayments = await prisma.subscriptionPayment.findMany({
    include: {
      user: true
    },
    orderBy: { createdAt: "desc" }
  });

  return (
    <BillingClient
      initialSettings={settingsMap}
      initialOrders={orders}
      clients={clients}
      users={users}
      plans={plans}
      combos={combos}
      hardware={hardware}
      subscriptionPayments={subscriptionPayments}
    />
  );
}
