import { NextResponse } from "next/server";
import { prisma } from "../../../../lib/prisma";

export const dynamic = "force-dynamic";
export const runtime = "nodejs";

export async function POST(req: Request) {
  try {
    const { license_key, machine_id, domain } = await req.json();

    if (!license_key && !machine_id) {
      return NextResponse.json(
        { valid: false, message: "License key or Machine ID is required." },
        { status: 400 }
      );
    }

    let client;

    // --- Lookup by license key (primary method) ---
    if (license_key) {
      client = await prisma.client.findUnique({
        where: { licenseKey: license_key },
      });

      if (!client) {
        return NextResponse.json({
          valid: false,
          message: "Invalid license key. Please contact DRestro support at +977 986-5029558."
        });
      }

      // --- Machine ID Binding Logic ---
      if (machine_id) {
        if (!client.machineId) {
          // First activation: lock this key to this machine permanently
          await prisma.client.update({
            where: { id: client.id },
            data: { machineId: machine_id }
          });
          client.machineId = machine_id; // update local reference
        } else if (client.machineId !== machine_id) {
          // Key is already bound to a DIFFERENT machine → reject
          return NextResponse.json({
            valid: false,
            message: "This license key is bound to another machine. Please contact DRestro support at +977 986-5029558 to transfer your license."
          });
        }
        // else: machine matches → proceed
      }

    // --- Lookup by machine_id only (for sync) ---
    } else if (machine_id) {
      client = await prisma.client.findFirst({
        where: { machineId: machine_id },
      });

      if (!client) {
        return NextResponse.json({
          valid: false,
          message: "No license found for this machine. Please enter a valid license key first."
        });
      }
    }

    if (!client) {
      return NextResponse.json({
        valid: false,
        message: "License not found."
      });
    }

    // Update Client POS verification log
    try {
      const rawIp = req.headers.get("cf-pseudo-ipv4") || req.headers.get("x-forwarded-for")?.split(",")[0] || req.headers.get("x-real-ip") || "127.0.0.1";
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

      await prisma.client.update({
        where: { id: client.id },
        data: {
          lastLoginIp: ip,
          lastLoginAt: new Date(),
          lastLoginDevice: device
        }
      });
    } catch (e) {
      console.error("Failed to update client POS login logs:", e);
    }

    // --- Status checks ---
    if (client.status === "Suspended") {
      return NextResponse.json({
        valid: false,
        message: "Your license has been suspended. Please contact DRestro support at +977 986-5029558."
      });
    }

    const today = new Date();
    const expiryDate = client.expiryDate ? new Date(client.expiryDate) : null;

    if (expiryDate && today > expiryDate) {
      return NextResponse.json({
        valid: false,
        message: "Your license has expired. Please contact DRestro support at +977 986-5029558 to renew your subscription."
      });
    }

    // Calculate days remaining
    const diffDays = expiryDate ? Math.ceil(Math.abs(expiryDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24)) : null;

    return NextResponse.json({
      valid: true,
      clientName: client.restaurantName,
      daysRemaining: diffDays,
      expiryDate: client.expiryDate,
      machineId: client.machineId,
      data: {
        plan: client.planLabel || "Free Plan",
        status: "active",
        expires_at: expiryDate ? expiryDate.toISOString() : null,
        activated_at: client.createdAt.toISOString(),
        is_trial: false,
        features: ["all"],
        limits: {
          tables: client.tableLimit || 0,
          users: client.staffLimit || 0,
          items: client.dishLimit || 0,
          rooms: client.roomLimit || 0,
        }
      }
    });
    
  } catch (error) {
    console.error("License Verification Error:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
