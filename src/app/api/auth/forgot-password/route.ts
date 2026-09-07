import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";
import crypto from "crypto";
import { Resend } from "resend";
import { sendSMS } from "@/src/lib/sms";

const resend = process.env.RESEND_API_KEY ? new Resend(process.env.RESEND_API_KEY) : null;

export async function POST(req: Request) {
  try {
    const { email } = await req.json(); // we treat 'email' field as the identifier (can be email or phone)

    if (!email) {
      return NextResponse.json({ error: "Email or phone number is required" }, { status: 400 });
    }

    // Clean check for phone number
    let cleanPhone = email.replace(/[^0-9]/g, "");
    if (cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }
    const isPhone = cleanPhone.length === 10;

    if (isPhone) {
      // 1. Phone Reset Flow
      const user = await prisma.user.findUnique({
        where: { phone: cleanPhone },
      });

      if (!user) {
        return NextResponse.json({ message: "If an account exists, an OTP was sent.", type: "phone", phone: cleanPhone });
      }

      // Generate 6-digit OTP code
      const otp = Math.floor(100000 + Math.random() * 900000).toString();
      const expires = new Date(Date.now() + 5 * 60 * 1000); // 5 minutes expiration

      await prisma.verificationToken.deleteMany({
        where: { identifier: cleanPhone }
      });

      await prisma.verificationToken.create({
        data: {
          identifier: cleanPhone,
          token: otp,
          expires,
        },
      });

      const message = `Your DRestro password reset OTP code is: ${otp}. Valid for 5 minutes.`;
      await sendSMS(cleanPhone, message);

      return NextResponse.json({ message: "If an account exists, an OTP was sent.", type: "phone", phone: cleanPhone });
    } else {
      // 2. Email Reset Flow
      const user = await prisma.user.findUnique({
        where: { email },
      });

      if (!user) {
        return NextResponse.json({ message: "If an account exists, an email was sent.", type: "email" });
      }

      const token = crypto.randomBytes(32).toString("hex");
      const expires = new Date(Date.now() + 1000 * 60 * 60); // 1 hour expiration

      await prisma.verificationToken.deleteMany({
        where: { identifier: email }
      });

      await prisma.verificationToken.create({
        data: {
          identifier: email,
          token,
          expires,
        },
      });

      const baseUrl = new URL(req.url).origin;
      const resetUrl = `${baseUrl}/reset-password?token=${token}&email=${encodeURIComponent(email)}`;

      if (process.env.RESEND_API_KEY) {
        const { error } = await resend!.emails.send({
          from: 'DRestro <info@drestro.com>',
          to: email,
          subject: 'Reset Your DRestro Password',
          html: `
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
              <h2 style="color: #111;">Password Reset Request</h2>
              <p style="color: #555; line-height: 1.5;">We received a request to reset your password for your DRestro account. Click the button below to choose a new password.</p>
              <div style="text-align: center; margin: 30px 0;">
                <a href="${resetUrl}" style="background-color: #E53935; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Reset Password</a>
              </div>
              <p style="color: #555; line-height: 1.5; font-size: 14px;">If you didn't request this, you can safely ignore this email.</p>
              <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;" />
              <p style="color: #999; font-size: 12px; text-align: center;">&copy; ${new Date().getFullYear()} DRestro. All rights reserved.</p>
            </div>
          `,
        });

        if (error) {
          console.error("Resend error:", error);
        }
      } else {
        console.log("\n\n=======================================================");
        console.log("🚨 DEVELOPMENT MODE: EMAIL NOT CONFIGURED 🚨");
        console.log(`Password reset link for ${email}:`);
        console.log(resetUrl);
        console.log("=======================================================\n\n");
      }

      return NextResponse.json({ message: "If an account exists, an email was sent.", type: "email" });
    }
  } catch (error) {
    console.error("Forgot password error:", error);
    return NextResponse.json(
      { error: "Something went wrong" },
      { status: 500 }
    );
  }
}
