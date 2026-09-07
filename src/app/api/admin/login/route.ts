import { NextResponse } from "next/server";
import { createSession } from "../../../../lib/auth";
import { prisma } from "../../../../lib/prisma";
import bcrypt from "bcryptjs";

export async function POST(req: Request) {
  try {
    const { email, password, location, clientIpv4 } = await req.json();

    if (!email || !password || !location) {
      return NextResponse.json(
        { error: "Email, password, and location are required" },
        { status: 400 }
      );
    }

    const staff = await prisma.adminStaff.findUnique({
      where: { email },
    });

    if (!staff) {
      return NextResponse.json(
        { error: "Invalid credentials" },
        { status: 401 }
      );
    }

    const passwordMatch = await bcrypt.compare(password, staff.password);

    if (passwordMatch) {
      // Location Validation
      if (staff.role === "SUPERADMIN" || staff.role === "ADMIN") {
        // Superadmin and Admin have global access. Allow any location selection.
      } else {
        const staffLoc = staff.location || "";
        if (staffLoc) {
          if (staffLoc.toLowerCase() !== location.toLowerCase()) {
            return NextResponse.json(
              { error: `Location mismatch: This account is registered to ${staffLoc}.` },
              { status: 401 }
            );
          }
        } else {
          if (location.toLowerCase() === "admin") {
            return NextResponse.json(
              { error: "Staff/Dealers cannot log in under the 'Admin' location." },
              { status: 401 }
            );
          }
        }
      }

      await createSession(staff.id, staff.role, staff.permissions, staff.email);

      try {
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
        else if (lower.includes("curl") || lower.includes("postman")) device = "API Client";
        else device = ua.split(" ")[0] || "Unknown Device";

        await prisma.adminStaff.update({
          where: { id: staff.id },
          data: {
            lastLoginIp: ip,
            lastLoginAt: new Date(),
            lastLoginDevice: device
          }
        });
      } catch (e) {
        console.error("Failed to update staff login logs:", e);
      }
      
      // Determine redirect URL based on role
      let redirectUrl = "/admin/clients";
      if (staff.role === "SALES") redirectUrl = "/sales";
      if (staff.role === "SUPPORT") redirectUrl = "/support";
      if (staff.role === "MARKETING") redirectUrl = "/marketing";

      return NextResponse.json({ success: true, redirectUrl });
    }

    return NextResponse.json(
      { error: "Invalid credentials" },
      { status: 401 }
    );
  } catch (error) {
    console.error("Login error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
