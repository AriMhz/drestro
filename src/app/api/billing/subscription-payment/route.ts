import { NextResponse } from "next/server";
import { getSession } from "@/src/lib/auth";
import { prisma } from "@/src/lib/prisma";
import { processAndSaveImage } from "@/src/lib/upload";
import { Resend } from "resend";
import { auth } from "../../../../../auth";

const resend = process.env.RESEND_API_KEY ? new Resend(process.env.RESEND_API_KEY) : null;

export const dynamic = "force-dynamic";
export const runtime = "nodejs";

export async function POST(req: Request) {
  try {
    let userId = null;

    // Check for NextAuth customer session first
    const nextSession = await auth();
    if (nextSession?.user?.id) {
      userId = nextSession.user.id;
    } else {
      // Fallback to admin staff session
      const adminSession = await getSession();
      if (adminSession?.staffId) {
        userId = adminSession.staffId;
      }
    }

    if (!userId) {
      return NextResponse.json({ error: "Unauthorized access." }, { status: 401 });
    }

    const formData = await req.formData();
    const planId = formData.get("planId") as string;
    const billingCycle = formData.get("billingCycle") as string;
    const amountStr = formData.get("amount") as string;
    const paymentMethod = formData.get("paymentMethod") as string;
    const screenshotFile = formData.get("screenshot") as File | null;

    if (!planId || !billingCycle || !amountStr || !paymentMethod) {
      return NextResponse.json({ error: "Missing parameters." }, { status: 400 });
    }

    const amount = parseFloat(amountStr);

    // Save screenshot if uploaded
    let screenshotUrl = null;
    if (screenshotFile && screenshotFile.size > 0) {
      screenshotUrl = await processAndSaveImage(screenshotFile, "payments");
    }

    // 1. Fetch user and their first restaurant
    const user = await prisma.user.findUnique({
      where: { id: userId },
      include: {
        restaurants: {
          include: {
            subscriptions: true,
          },
        },
      },
    });

    if (!user || user.restaurants.length === 0) {
      return NextResponse.json({ error: "User has no restaurant set up." }, { status: 404 });
    }

    const restaurant = user.restaurants[0];

    // Generate sequential paymentCode
    const paymentCount = await prisma.subscriptionPayment.count();
    let paymentCode = `TXN-${100001 + paymentCount}`;
    while (true) {
      const existing = await prisma.subscriptionPayment.findFirst({ where: { paymentCode } });
      if (!existing) break;
      const num = parseInt(paymentCode.split('-')[1]) + 1;
      paymentCode = `TXN-${num}`;
    }

    // 2. Create SubscriptionPayment log with PENDING status
    await prisma.subscriptionPayment.create({
      data: {
        paymentCode,
        userId: user.id,
        restaurantId: restaurant.id,
        restaurantName: restaurant.name,
        planId: planId.toLowerCase(),
        billingCycle,
        amount,
        paymentMethod,
        screenshotUrl,
        status: "PENDING",
      },
    });

    // Send email notification using Resend
    if (resend) {
      const customerEmail = user.email || "";
      const customerCode = user.customerCode || "N/A";
      const planLabel = planId.charAt(0).toUpperCase() + planId.slice(1).toLowerCase();

      // Email to billing@drestro.com
      try {
        await resend.emails.send({
          from: 'DRestro Billing <info@drestro.com>',
          to: 'billing@drestro.com',
          subject: `Pending Payment Verification: Cust ID ${customerCode}`,
          html: `
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
              <h2 style="color: #111; border-bottom: 2px solid #E53935; padding-bottom: 10px;">New Payment Receipt Received</h2>
              <p>A customer has uploaded a billing receipt slip for verification. Details are below:</p>
              <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; width: 150px; color: #555;">Customer Name:</td>
                  <td style="padding: 8px 0; color: #111;">${user.name || 'N/A'}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Customer ID (6-digit):</td>
                  <td style="padding: 8px 0; color: #111; font-weight: bold;">${customerCode}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Restaurant Name:</td>
                  <td style="padding: 8px 0; color: #111;">${restaurant.name}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Selected Plan:</td>
                  <td style="padding: 8px 0; color: #111; font-weight: bold; color: #E53935;">${planLabel}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Billing Cycle:</td>
                  <td style="padding: 8px 0; color: #111; text-transform: capitalize;">${billingCycle}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Amount Paid:</td>
                  <td style="padding: 8px 0; color: #111; font-weight: bold;">Rs. ${amount.toLocaleString()}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Payment Method:</td>
                  <td style="padding: 8px 0; color: #111; text-transform: uppercase;">${paymentMethod}</td>
                </tr>
              </table>
              ${screenshotUrl ? `
                <div style="margin-top: 25px;">
                  <p style="font-weight: bold; color: #555;">Uploaded Payment Slip Screenshot:</p>
                  <a href="${screenshotUrl}" target="_blank">
                    <img src="${screenshotUrl}" alt="Payment Slip" style="max-width: 100%; border: 1px solid #ddd; border-radius: 8px; max-height: 300px; object-fit: contain;" />
                  </a>
                </div>
              ` : ''}
              <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;" />
              <p style="color: #999; font-size: 11px; text-align: center;">Please log in to your SaaS Admin Billing dashboard to approve or reject this payment.</p>
            </div>
          `,
        });
      } catch (mailError) {
        console.error("Failed to send email to billing@drestro.com:", mailError);
      }

      // Email to Customer
      if (customerEmail) {
        try {
          await resend.emails.send({
            from: 'DRestro Billing <info@drestro.com>',
            to: customerEmail,
            subject: `Payment Slip Received - Pending Approval (Cust ID ${customerCode})`,
            html: `
              <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
                <h2 style="color: #111; border-bottom: 2px solid #E53935; padding-bottom: 10px;">We Received Your Payment Slip!</h2>
                <p>Hello ${user.name || 'Valued Customer'},</p>
                <p>Thank you for submitting your payment screenshot. Our billing department is currently verifying the transaction. Once verified, your package will be automatically activated.</p>
                
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #f4f4f5; border-radius: 8px; padding: 15px; background: #fafafa;">
                  <tr>
                    <td style="padding: 8px; font-weight: bold; width: 180px; color: #555;">Customer ID:</td>
                    <td style="padding: 8px; color: #111; font-weight: bold;">${customerCode}</td>
                  </tr>
                  <tr>
                    <td style="padding: 8px; font-weight: bold; color: #555;">Restaurant Outlet:</td>
                    <td style="padding: 8px; color: #111;">${restaurant.name}</td>
                  </tr>
                  <tr>
                    <td style="padding: 8px; font-weight: bold; color: #555;">Requested Plan:</td>
                    <td style="padding: 8px; color: #111; font-weight: bold; color: #E53935;">${planLabel}</td>
                  </tr>
                  <tr>
                    <td style="padding: 8px; font-weight: bold; color: #555;">Billing Cycle:</td>
                    <td style="padding: 8px; color: #111; text-transform: capitalize;">${billingCycle}</td>
                  </tr>
                  <tr>
                    <td style="padding: 8px; font-weight: bold; color: #555;">Amount Transferred:</td>
                    <td style="padding: 8px; color: #111; font-weight: bold;">Rs. ${amount.toLocaleString()}</td>
                  </tr>
                  <tr>
                    <td style="padding: 8px; font-weight: bold; color: #555;">Payment Method:</td>
                    <td style="padding: 8px; color: #111; text-transform: uppercase;">${paymentMethod}</td>
                  </tr>
                </table>
                
                <p style="margin-top: 20px; color: #444; font-size: 13px; line-height: 1.6;">
                  If you have any questions or require immediate support, please contact us via WhatsApp at <strong>+977 9865029558</strong>.
                </p>
                <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;" />
                <p style="color: #999; font-size: 11px; text-align: center;">DRestro POS Nepal. All rights reserved.</p>
              </div>
            `,
          });
        } catch (mailError) {
          console.error("Failed to send verification email to customer:", mailError);
        }
      }
    }

    return NextResponse.json({ success: true, message: "Receipt uploaded successfully. Waiting for admin approval!" });
  } catch (error: any) {
    console.error("Subscription payment upload error:", error);
    return NextResponse.json({ error: error.message || "Internal server error" }, { status: 500 });
  }
}
