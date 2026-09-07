import { NextResponse } from "next/server";
import { prisma } from "../../../../lib/prisma";
import bcrypt from "bcryptjs";
import { getSession } from "../../../../lib/auth";
import { Resend } from "resend";

const resend = process.env.RESEND_API_KEY ? new Resend(process.env.RESEND_API_KEY) : null;

export async function GET(req: Request) {
  try {
    const session = await getSession();
    if (!session || (session.role !== "SUPERADMIN" && session.role !== "ADMIN")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    const staff = await prisma.adminStaff.findMany({
      orderBy: { createdAt: "desc" },
      select: {
        id: true,
        email: true,
        role: true,
        permissions: true,
        referralCode: true,
        commissionRate: true,
        location: true,
        createdAt: true,
        lastLoginIp: true,
        lastLoginAt: true,
        lastLoginDevice: true,
      }
    });

    return NextResponse.json(staff);
  } catch (error) {
    return NextResponse.json({ error: "Failed to fetch staff" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const session = await getSession();
    if (!session || (session.role !== "SUPERADMIN" && session.role !== "ADMIN")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    const body = await req.json();
    const { email, password, role, permissions, referralCode, commissionRate, location } = body;

    if (!email || !password || !role) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    if (role === "SUPERADMIN") {
      return NextResponse.json({ error: "Cannot create a Superadmin account" }, { status: 403 });
    }

    const existingStaff = await prisma.adminStaff.findUnique({ where: { email } });
    if (existingStaff) {
      return NextResponse.json({ error: "Email already in use" }, { status: 400 });
    }

    const hashedPassword = await bcrypt.hash(password, 10);

    const newStaff = await prisma.adminStaff.create({
      data: {
        email,
        password: hashedPassword,
        role,
        permissions: JSON.stringify(permissions || []),
        referralCode: referralCode || null,
        commissionRate: parseFloat(commissionRate) || 0.0,
        location: location || null,
      }
    });

    // Send welcome email
    await sendStaffEmail(
      email,
      "Welcome to DRestro - Your Staff Account Has Been Created",
      `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;">
          <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="color: #E53935; margin: 0; font-size: 24px; font-weight: 800; tracking-tight: tight;">D R E S T R O</h2>
            <p style="color: #64748b; font-size: 14px; margin: 5px 0 0 0;">Staff Portal Access</p>
          </div>
          <hr style="border: none; border-top: 1px solid #f1f5f9; margin-bottom: 25px;" />
          <h3 style="color: #0f172a; font-size: 18px; margin: 0 0 15px 0;">Welcome to the DRestro Team!</h3>
          <p style="color: #334155; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
            An account has been created for you on the DRestro Portal. You can now log in and access your dashboard.
          </p>
          <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px; color: #334155;">
              <tr>
                <td style="padding: 6px 0; font-weight: bold; width: 120px;">Email:</td>
                <td style="padding: 6px 0; color: #0f172a;">${email}</td>
              </tr>
              <tr>
                <td style="padding: 6px 0; font-weight: bold;">Password:</td>
                <td style="padding: 6px 0; color: #0f172a; font-family: monospace;">${password}</td>
              </tr>
              <tr>
                <td style="padding: 6px 0; font-weight: bold;">Role:</td>
                <td style="padding: 6px 0; color: #0f172a;"><span style="background-color: #ef4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">${role}</span></td>
              </tr>
              <tr>
                <td style="padding: 6px 0; font-weight: bold;">Location:</td>
                <td style="padding: 6px 0; color: #0f172a;">${location || "Admin / Head Office"}</td>
              </tr>
            </table>
          </div>
          <div style="text-align: center; margin: 30px 0;">
            <a href="https://drestro.com/admin/login" style="background-color: #E53935; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 15px; box-shadow: 0 4px 6px -1px rgba(229, 57, 53, 0.2);">Log In to Portal</a>
          </div>
          <p style="color: #64748b; font-size: 13px; line-height: 1.5; margin: 0 0 20px 0;">
            Please make sure to log in and change your password immediately for security reasons.
          </p>
          <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 25px 0;" />
          <p style="color: #94a3b8; font-size: 11px; text-align: center; margin: 0;">&copy; ${new Date().getFullYear()} DRestro. All rights reserved.</p>
        </div>
      `
    );

    return NextResponse.json({ success: true, staffId: newStaff.id });
  } catch (error) {
    console.error("Failed to create staff:", error);
    return NextResponse.json({ error: "Failed to create staff" }, { status: 500 });
  }
}

export async function PUT(req: Request) {
  try {
    const session = await getSession();
    if (!session || (session.role !== "SUPERADMIN" && session.role !== "ADMIN")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    const body = await req.json();
    const { id, email, password, role, permissions, referralCode, commissionRate, location } = body;

    if (!id) {
      return NextResponse.json({ error: "Staff ID is required" }, { status: 400 });
    }

    const existingStaff = await prisma.adminStaff.findUnique({ where: { id } });
    if (!existingStaff) {
      return NextResponse.json({ error: "Staff member not found" }, { status: 404 });
    }

    if (role === "SUPERADMIN" && existingStaff.role !== "SUPERADMIN") {
      return NextResponse.json({ error: "Cannot upgrade role to Superadmin" }, { status: 403 });
    }

    const updateData: any = {};
    if (email) updateData.email = email;
    if (role) updateData.role = role;
    if (permissions !== undefined) updateData.permissions = JSON.stringify(permissions);
    if (referralCode !== undefined) updateData.referralCode = referralCode || null;
    if (commissionRate !== undefined) updateData.commissionRate = parseFloat(commissionRate) || 0.0;
    if (location !== undefined) updateData.location = location || null;
    if (password) updateData.password = await bcrypt.hash(password, 10);

    await prisma.adminStaff.update({
      where: { id },
      data: updateData,
    });

    // Send update email notification to the staff
    await sendStaffEmail(
      email || existingStaff.email,
      "DRestro - Your Staff Account Details Have Been Updated",
      `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;">
          <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="color: #E53935; margin: 0; font-size: 24px; font-weight: 800; tracking-tight: tight;">D R E S T R O</h2>
            <p style="color: #64748b; font-size: 14px; margin: 5px 0 0 0;">Staff Portal Access</p>
          </div>
          <hr style="border: none; border-top: 1px solid #f1f5f9; margin-bottom: 25px;" />
          <h3 style="color: #0f172a; font-size: 18px; margin: 0 0 15px 0;">Account Details Updated</h3>
          <p style="color: #334155; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
            Your DRestro staff account details have been updated by an administrator. Here are your current active settings:
          </p>
          <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px; color: #334155;">
              <tr>
                <td style="padding: 6px 0; font-weight: bold; width: 120px;">Email:</td>
                <td style="padding: 6px 0; color: #0f172a;">${email || existingStaff.email}</td>
              </tr>
              ${password ? `
              <tr>
                <td style="padding: 6px 0; font-weight: bold;">New Password:</td>
                <td style="padding: 6px 0; color: #0f172a; font-family: monospace;">${password}</td>
              </tr>` : ''}
              <tr>
                <td style="padding: 6px 0; font-weight: bold;">Role:</td>
                <td style="padding: 6px 0; color: #0f172a;"><span style="background-color: #ef4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">${role || existingStaff.role}</span></td>
              </tr>
              <tr>
                <td style="padding: 6px 0; font-weight: bold;">Location:</td>
                <td style="padding: 6px 0; color: #0f172a;">${location !== undefined ? (location || "Admin / Head Office") : (existingStaff.location || "Admin / Head Office")}</td>
              </tr>
            </table>
          </div>
          <div style="text-align: center; margin: 30px 0;">
            <a href="https://drestro.com/admin/login" style="background-color: #E53935; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 15px; box-shadow: 0 4px 6px -1px rgba(229, 57, 53, 0.2);">Log In to Portal</a>
          </div>
          <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 25px 0;" />
          <p style="color: #94a3b8; font-size: 11px; text-align: center; margin: 0;">&copy; ${new Date().getFullYear()} DRestro. All rights reserved.</p>
        </div>
      `
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("Failed to update staff:", error);
    return NextResponse.json({ error: "Failed to update staff" }, { status: 500 });
  }
}

export async function DELETE(req: Request) {
  try {
    const session = await getSession();
    if (!session || (session.role !== "SUPERADMIN" && session.role !== "ADMIN")) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
    }

    const url = new URL(req.url);
    const id = url.searchParams.get("id");

    if (!id) {
      return NextResponse.json({ error: "Staff ID is required" }, { status: 400 });
    }

    const staff = await prisma.adminStaff.findUnique({ where: { id } });
    if (!staff) {
      return NextResponse.json({ error: "Staff member not found" }, { status: 404 });
    }

    if (staff.email === session.email || staff.id === session.staffId) {
      return NextResponse.json({ error: "Cannot delete your own logged-in account" }, { status: 400 });
    }

    await prisma.adminStaff.delete({ where: { id } });

    // Send deletion email notification to the staff
    await sendStaffEmail(
      staff.email,
      "DRestro - Your Staff Account Has Been Deactivated",
      `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;">
          <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="color: #E53935; margin: 0; font-size: 24px; font-weight: 800; tracking-tight: tight;">D R E S T R O</h2>
            <p style="color: #64748b; font-size: 14px; margin: 5px 0 0 0;">Staff Portal Access</p>
          </div>
          <hr style="border: none; border-top: 1px solid #f1f5f9; margin-bottom: 25px;" />
          <h3 style="color: #ef4444; font-size: 18px; margin: 0 0 15px 0;">Account Deactivated</h3>
          <p style="color: #334155; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">Hello,</p>
          <p style="color: #334155; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
            This is to notify you that your staff/dealer account under the email <strong>${staff.email}</strong> has been deleted and deactivated by the administrator.
          </p>
          <p style="color: #334155; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
            You will no longer be able to log into the portal. If you believe this is a mistake or have questions, please contact the support team or your administrator.
          </p>
          <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 25px 0;" />
          <p style="color: #94a3b8; font-size: 11px; text-align: center; margin: 0;">&copy; ${new Date().getFullYear()} DRestro. All rights reserved.</p>
        </div>
      `
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    return NextResponse.json({ error: "Failed to delete staff" }, { status: 500 });
  }
}

async function sendStaffEmail(email: string, subject: string, htmlContent: string) {
  if (!resend) {
    console.log(`\n=======================================================`);
    console.log(`🚨 DEVELOPMENT MODE: EMAIL NOT SENT (NO API KEY) 🚨`);
    console.log(`To: ${email}`);
    console.log(`Subject: ${subject}`);
    console.log(`=======================================================\n`);
    return;
  }
  try {
    await resend.emails.send({
      from: 'DRestro <info@drestro.com>',
      to: email,
      subject,
      html: htmlContent,
    });
  } catch (error) {
    console.error(`Failed to send email to ${email}:`, error);
  }
}
