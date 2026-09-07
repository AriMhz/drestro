import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";
import bcrypt from "bcryptjs";
import { sendWelcomeEmail } from "@/src/lib/email";

export async function POST(req: Request) {
  try {
    const { name, email, phone, password, otp, clientIpv4, ref } = await req.json();

    if (!email || !password || !phone || !otp) {
      return NextResponse.json(
        { error: "All fields (Name, Email, Phone, Password, OTP) are required" },
        { status: 400 }
      );
    }

    // Clean phone number (digits only)
    let cleanPhone = phone.replace(/[^0-9]/g, "");
    if (cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }

    if (cleanPhone.length < 9 || cleanPhone.length > 10) {
      return NextResponse.json(
        { error: "Phone number must be exactly 9 or 10 digits (e.g. 98XXXXXXXX)" },
        { status: 400 }
      );
    }

    // 1. Verify OTP
    const verificationToken = await prisma.verificationToken.findUnique({
      where: {
        identifier_token: {
          identifier: cleanPhone,
          token: otp,
        },
      },
    });

    if (!verificationToken) {
      return NextResponse.json({ error: "Invalid OTP code" }, { status: 400 });
    }

    if (new Date(verificationToken.expires) < new Date()) {
      await prisma.verificationToken.delete({
        where: {
          identifier_token: { identifier: cleanPhone, token: otp },
        },
      });
      return NextResponse.json({ error: "OTP code has expired" }, { status: 400 });
    }

    // 2. Check if user already exists with this email
    const existingEmailUser = await prisma.user.findUnique({
      where: { email },
    });

    if (existingEmailUser) {
      return NextResponse.json(
        { error: "User already exists with this email" },
        { status: 400 }
      );
    }

    // 3. Check if user already exists with this phone number
    const existingPhoneUser = await prisma.user.findUnique({
      where: { phone: cleanPhone },
    });

    if (existingPhoneUser) {
      return NextResponse.json(
        { error: "User already exists with this phone number" },
        { status: 400 }
      );
    }

    // Resolve referredById from ref code if present
    let referredById: string | null = null;
    if (ref) {
      const staff = await prisma.adminStaff.findUnique({
        where: { referralCode: ref.toUpperCase() },
      });
      if (staff) {
        referredById = staff.id;
      }
    }

    const rawIp = clientIpv4 || req.headers.get("cf-pseudo-ipv4") || req.headers.get("x-forwarded-for")?.split(",")[0] || req.headers.get("x-real-ip") || "127.0.0.1";
    const ip = rawIp.startsWith("::ffff:") ? rawIp.substring(7) : (rawIp === "::1" ? "127.0.0.1" : rawIp);
    const ua = req.headers.get("user-agent") || "";
    
    let device = "Unknown Device";
    const lower = ua.toLowerCase();
    if (lower.includes("windows")) device = "Windows PC";
    else if (lower.includes("macintosh") || lower.includes("mac os")) device = "Mac PC";
    else if (lower.includes("android")) device = "Android Device";
    else if (lower.includes("iphone") || lower.includes("ipad")) device = "iOS Device";
    else if (lower.includes("linux")) device = "Linux Device";
    else device = ua.split(" ")[0] || "Unknown Device";

    const hashedPassword = await bcrypt.hash(password, 10);

    const userCount = await prisma.user.count();
    let codeNum = 100000 + userCount + 1;
    let customerCode = String(codeNum);
    while (true) {
      const collision = await prisma.user.findFirst({ where: { customerCode } });
      if (!collision) break;
      codeNum++;
      customerCode = String(codeNum);
    }

    const user = await prisma.user.create({
      data: {
        name: name || null,
        email,
        phone: cleanPhone,
        customerCode,
        password: hashedPassword,
        lastLoginIp: ip,
        lastLoginAt: new Date(),
        lastLoginDevice: device,
        referredById,
      },
    });

    // 4. Delete the OTP token so it can't be reused
    await prisma.verificationToken.delete({
      where: {
        identifier_token: { identifier: cleanPhone, token: otp },
      },
    });

    // 5. Send Welcome Email Notification
    try {
      sendWelcomeEmail(user.email, name || "").catch(err => console.error("Welcome Email error:", err));
    } catch (e) {
      console.error("Failed to initiate Welcome Email:", e);
    }

    return NextResponse.json({
      message: "User registered successfully",
      user: { id: user.id, email: user.email, phone: user.phone },
    });
  } catch (error) {
    console.error("Registration error:", error);
    return NextResponse.json(
      { error: "Something went wrong" },
      { status: 500 }
    );
  }
}
