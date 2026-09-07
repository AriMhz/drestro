import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";
import bcrypt from "bcryptjs";

export async function POST(req: Request) {
  try {
    const { token, email, newPassword } = await req.json(); // email is treated as identifier (email or phone)

    if (!token || !email || !newPassword) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    if (newPassword.length < 6) {
      return NextResponse.json({ error: "Password must be at least 6 characters" }, { status: 400 });
    }

    // Clean check for phone number
    let cleanIdentifier = email;
    let isPhone = false;
    let cleanPhone = email.replace(/[^0-9]/g, "");
    if (cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }
    if (cleanPhone.length === 10) {
      cleanIdentifier = cleanPhone;
      isPhone = true;
    }

    // 1. Find the token
    const verificationToken = await prisma.verificationToken.findUnique({
      where: {
        identifier_token: {
          identifier: cleanIdentifier,
          token: token,
        },
      },
    });

    if (!verificationToken) {
      return NextResponse.json({ error: "Invalid or expired reset code/token" }, { status: 400 });
    }

    // 2. Check if expired
    if (new Date(verificationToken.expires) < new Date()) {
      // Delete expired token
      await prisma.verificationToken.delete({
        where: {
          identifier_token: { identifier: cleanIdentifier, token },
        },
      });
      return NextResponse.json({ error: "Reset token has expired" }, { status: 400 });
    }

    // 3. Hash new password
    const hashedPassword = await bcrypt.hash(newPassword, 10);

    // 4. Update user
    if (isPhone) {
      await prisma.user.update({
        where: { phone: cleanIdentifier },
        data: { password: hashedPassword },
      });
    } else {
      await prisma.user.update({
        where: { email: cleanIdentifier },
        data: { password: hashedPassword },
      });
    }

    // 5. Delete the token so it can't be reused
    await prisma.verificationToken.delete({
      where: {
        identifier_token: { identifier: cleanIdentifier, token },
      },
    });

    return NextResponse.json({ message: "Password updated successfully" });
  } catch (error) {
    console.error("Reset password error:", error);
    return NextResponse.json(
      { error: "Something went wrong" },
      { status: 500 }
    );
  }
}
