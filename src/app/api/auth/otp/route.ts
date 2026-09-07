import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";
import { sendSMS } from "@/src/lib/sms";

export async function POST(req: Request) {
  try {
    const { phone, purpose } = await req.json();

    if (!phone) {
      return NextResponse.json({ error: "Phone number is required" }, { status: 400 });
    }

    // Clean phone number (digits only)
    let cleanPhone = phone.replace(/[^0-9]/g, "");
    if (cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }

    if (cleanPhone.length !== 10) {
      return NextResponse.json({ error: "Invalid phone number. Must be a 10-digit number." }, { status: 400 });
    }

    // 1. Check user existence based on purpose
    const existingUser = await prisma.user.findFirst({
      where: { phone: cleanPhone },
    });

    if (purpose === "register" && existingUser) {
      return NextResponse.json({ error: "An account is already registered with this phone number." }, { status: 400 });
    }

    if (purpose === "reset" && !existingUser) {
      return NextResponse.json({ error: "No user account found with this phone number." }, { status: 400 });
    }

    // 2. Generate 6-digit OTP code
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expires = new Date(Date.now() + 5 * 60 * 1000); // 5 minutes expiration

    // 3. Clear existing tokens for this phone number
    await prisma.verificationToken.deleteMany({
      where: { identifier: cleanPhone },
    });

    // 4. Save new OTP in VerificationToken table
    await prisma.verificationToken.create({
      data: {
        identifier: cleanPhone,
        token: otp,
        expires,
      },
    });

    // 5. Send OTP via SMS
    const message = `Your DRestro OTP code is: ${otp}. Valid for 5 minutes.`;
    const smsSent = await sendSMS(cleanPhone, message);

    if (!smsSent) {
      return NextResponse.json({ error: "Failed to send SMS OTP. Please try again." }, { status: 500 });
    }

    return NextResponse.json({ success: true, message: "OTP sent successfully" });
  } catch (error) {
    console.error("OTP route error:", error);
    return NextResponse.json({ error: "Something went wrong" }, { status: 500 });
  }
}
