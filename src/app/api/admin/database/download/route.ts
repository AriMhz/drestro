import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";
import { getSession } from "@/src/lib/auth";
import fs from "fs";
import path from "path";

export async function GET(req: Request) {
  try {
    const session = await getSession();
    if (!session || (session.role !== "SUPERADMIN" && session.role !== "ADMIN")) {
      return NextResponse.json({ error: "Unauthorized access" }, { status: 403 });
    }

    const { searchParams } = new URL(req.url);
    const format = searchParams.get("format") || "json";

    // 1. Raw SQLite .db format
    if (format === "db" || format === "sqlite") {
      const dbPath = path.join(process.cwd(), "prisma", "dev.db");
      if (fs.existsSync(dbPath)) {
        const fileBuffer = fs.readFileSync(dbPath);
        return new Response(fileBuffer, {
          headers: {
            "Content-Type": "application/x-sqlite3",
            "Content-Disposition": `attachment; filename="drestro_database_${new Date().toISOString().split("T")[0]}.db"`,
          },
        });
      }
    }

    // Fetch all database tables
    const [
      users,
      restaurants,
      subscriptions,
      subscriptionPayments,
      clients,
      adminStaff,
      commissionLogs,
      supportTickets,
      hardwareOrders,
      hardware,
      testimonials,
      comboPackages,
      comboFeatures,
      plans,
      planFeatures,
      planNotIncluded,
      siteSettings,
      faqs,
      clientLogos,
      contactSubmissions,
      careerPosts,
      accounts,
      sessions,
      verificationTokens
    ] = await Promise.all([
      prisma.user.findMany({ include: { restaurants: true } }).catch(() => []),
      prisma.restaurant.findMany({ include: { user: true, subscriptions: true } }).catch(() => []),
      prisma.subscription.findMany({ include: { restaurant: true } }).catch(() => []),
      prisma.subscriptionPayment.findMany().catch(() => []),
      prisma.client.findMany({ include: { createdBy: true, updatedBy: true } }).catch(() => []),
      prisma.adminStaff.findMany().catch(() => []),
      prisma.commissionLog.findMany().catch(() => []),
      prisma.supportTicket.findMany().catch(() => []),
      prisma.hardwareOrder.findMany().catch(() => []),
      prisma.hardware.findMany().catch(() => []),
      prisma.testimonial.findMany().catch(() => []),
      prisma.comboPackage.findMany().catch(() => []),
      prisma.comboFeature.findMany().catch(() => []),
      prisma.plan.findMany().catch(() => []),
      prisma.planFeature.findMany().catch(() => []),
      prisma.planNotIncluded.findMany().catch(() => []),
      prisma.siteSetting.findMany().catch(() => []),
      prisma.faq.findMany().catch(() => []),
      prisma.clientLogo.findMany().catch(() => []),
      prisma.contactSubmission.findMany().catch(() => []),
      prisma.careerPost.findMany().catch(() => []),
      prisma.account.findMany().catch(() => []),
      prisma.session.findMany().catch(() => []),
      prisma.verificationToken.findMany().catch(() => []),
    ]);

    const dateStr = new Date().toISOString().split("T")[0];

    // 2. CSV / Excel Format (.csv)
    if (format === "csv" || format === "excel") {
      let csvContent = "";

      const appendSection = (title: string, headers: string[], rows: (string | number | null | undefined)[][]) => {
        csvContent += `=== ${title.toUpperCase()} ===\n`;
        csvContent += headers.join(",") + "\n";
        rows.forEach(row => {
          const formattedRow = row.map(cell => {
            if (cell === null || cell === undefined) return '""';
            const str = String(cell).replace(/"/g, '""');
            return `"${str}"`;
          });
          csvContent += formattedRow.join(",") + "\n";
        });
        csvContent += "\n\n";
      };

      // Users & Restaurants Section
      appendSection(
        "Users & Outlets (Main Accounts)",
        ["User ID", "Name", "Email", "Phone", "Created At", "Outlets Count", "Outlets Names"],
        users.map(u => [
          u.id, u.name, u.email, u.phone, u.createdAt ? new Date(u.createdAt).toLocaleString() : "",
          u.restaurants ? u.restaurants.length : 0,
          u.restaurants ? u.restaurants.map(r => r.name).join("; ") : ""
        ])
      );

      // Restaurants / Outlets Section
      appendSection(
        "Restaurants & Outlets (Detailed)",
        ["Restaurant ID", "Owner Email", "Name", "Phone", "Address", "City", "Ward", "Type", "Currency", "PAN Number", "Tax %", "License Key"],
        restaurants.map(r => [
          r.id, r.user?.email || "", r.name, r.phone, r.address, r.city, r.ward, r.type, r.currency, r.panNumber, r.taxPercent, r.offlineLicenseKey
        ])
      );

      // Subscriptions Section
      appendSection(
        "Subscriptions & Plans",
        ["Subscription ID", "Restaurant Name", "Plan ID", "Status", "Trial Ends", "Current Period End", "Table Limit", "Staff Limit", "Allowed Menus"],
        subscriptions.map(s => [
          s.id, s.restaurant?.name || "", s.planId, s.status,
          s.trialEndsAt ? new Date(s.trialEndsAt).toLocaleDateString() : "",
          s.currentPeriodEnd ? new Date(s.currentPeriodEnd).toLocaleDateString() : "",
          s.tableLimit === 0 ? "Unlimited" : s.tableLimit,
          s.staffLimit === 0 ? "Unlimited" : s.staffLimit,
          s.allowedMenus
        ])
      );

      // POS Clients / Licenses Section
      appendSection(
        "POS Offline Clients & Licenses",
        ["Client ID", "Restaurant Name", "Contact Number", "Location", "Plan Label", "License Key", "Status", "Expiry Date", "Machine ID", "Created By", "Table Limit", "Staff Limit", "Dish Limit", "Room Limit", "Last Login IP", "Last Login Date"],
        clients.map(c => [
          c.id, c.restaurantName, c.contactNumber, c.location, c.planLabel, c.licenseKey, c.status,
          c.expiryDate ? new Date(c.expiryDate).toLocaleDateString() : "",
          c.machineId, c.createdBy?.email || "System",
          c.tableLimit === 0 ? "Unlimited" : c.tableLimit,
          c.staffLimit === 0 ? "Unlimited" : c.staffLimit,
          c.dishLimit === 0 ? "Unlimited" : c.dishLimit,
          c.roomLimit === 0 ? "Unlimited" : c.roomLimit,
          c.lastLoginIp, c.lastLoginAt ? new Date(c.lastLoginAt).toLocaleString() : ""
        ])
      );

      // Admin Staff Section
      appendSection(
        "Admin Staff & Role",
        ["Staff ID", "Email", "Role", "Referral Code", "Commission Rate (%)", "Location", "Permissions", "Created At", "Last Login IP", "Last Login Date"],
        adminStaff.map(s => [
          s.id, s.email, s.role, s.referralCode, s.commissionRate, s.location, s.permissions,
          s.createdAt ? new Date(s.createdAt).toLocaleString() : "",
          s.lastLoginIp, s.lastLoginAt ? new Date(s.lastLoginAt).toLocaleString() : ""
        ])
      );

      // Support Tickets Section
      appendSection(
        "Support Tickets",
        ["Ticket Code", "Title", "Priority", "Status", "Description", "Created At"],
        supportTickets.map(t => [
          t.ticketCode || t.id, t.title, t.priority, t.status, t.description,
          t.createdAt ? new Date(t.createdAt).toLocaleString() : ""
        ])
      );

      // Contact Submissions Section
      appendSection(
        "Contact Submissions",
        ["Code", "Name", "Email", "Phone", "Subject", "Message", "Date"],
        contactSubmissions.map(cs => [
          cs.submissionCode || cs.id, cs.name, cs.email, cs.phone, cs.subject, cs.message,
          cs.createdAt ? new Date(cs.createdAt).toLocaleString() : ""
        ])
      );

      return new Response(csvContent, {
        headers: {
          "Content-Type": "text/csv; charset=utf-8",
          "Content-Disposition": `attachment; filename="drestro_database_export_${dateStr}.csv"`,
        },
      });
    }

    // 3. Printable HTML Document Format (.html)
    if (format === "html" || format === "document") {
      const htmlContent = `
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <title>DRestro Database Full Report - ${dateStr}</title>
          <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding: 30px; background: #f8fafc; color: #0f172a; line-height: 1.5; }
            h1 { font-size: 26px; color: #e53935; margin-bottom: 5px; }
            .subtitle { font-size: 13px; color: #64748b; margin-bottom: 25px; }
            .section { margin-bottom: 40px; background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
            .section-title { font-size: 18px; font-weight: bold; color: #1e293b; margin-bottom: 15px; border-bottom: 2px solid #e53935; padding-bottom: 6px; }
            table { width: 100%; border-collapse: collapse; font-size: 12px; }
            th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; word-break: break-word; }
            th { background: #f1f5f9; font-weight: bold; color: #334155; uppercase; }
            tr:hover { background: #f8fafc; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; }
            .badge-green { background: #dcfce7; color: #15803d; }
            .badge-blue { background: #dbeafe; color: #1d4ed8; }
            .badge-red { background: #fee2e2; color: #b91c1c; }
            @media print { body { padding: 0; background: #fff; } .section { shadow: none; border: 1px solid #ccc; page-break-inside: avoid; } }
          </style>
        </head>
        <body>
          <h1>DRestro Database Full Report (A-Z)</h1>
          <p class="subtitle">Generated on: <strong>${new Date().toLocaleString()}</strong> | Requested by: <strong>${session.email}</strong> | Total Records: <strong>${users.length + restaurants.length + clients.length + subscriptions.length + adminStaff.length}</strong></p>

          <!-- Users & Outlets -->
          <div class="section">
            <div class="section-title">1. Users & Outlets (${users.length} Users, ${restaurants.length} Outlets)</div>
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Outlets</th>
                  <th>Created Date</th>
                </tr>
              </thead>
              <tbody>
                ${users.map(u => `
                  <tr>
                    <td><strong>${u.name || "N/A"}</strong></td>
                    <td>${u.email || "N/A"}</td>
                    <td>${u.phone || "N/A"}</td>
                    <td>${u.restaurants && u.restaurants.length > 0 ? u.restaurants.map(r => `<span class="badge badge-blue">${r.name} (${r.type || "Restaurant"})</span>`).join(" ") : "No Outlets"}</td>
                    <td>${u.createdAt ? new Date(u.createdAt).toLocaleDateString() : "N/A"}</td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
          </div>

          <!-- Outlets Detailed -->
          <div class="section">
            <div class="section-title">2. Outlets & Restaurants Details (${restaurants.length})</div>
            <table>
              <thead>
                <tr>
                  <th>Outlet Name</th>
                  <th>Owner Email</th>
                  <th>Phone</th>
                  <th>Address / City</th>
                  <th>PAN / VAT</th>
                  <th>License Key</th>
                </tr>
              </thead>
              <tbody>
                ${restaurants.map(r => `
                  <tr>
                    <td><strong>${r.name}</strong></td>
                    <td>${r.user?.email || "N/A"}</td>
                    <td>${r.phone || "N/A"}</td>
                    <td>${r.address || "N/A"} ${r.city ? ", " + r.city : ""}</td>
                    <td>${r.panNumber || "N/A"}</td>
                    <td><code style="font-[#e53935]">${r.offlineLicenseKey || "N/A"}</code></td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
          </div>

          <!-- Subscriptions -->
          <div class="section">
            <div class="section-title">3. Subscriptions & Plans (${subscriptions.length})</div>
            <table>
              <thead>
                <tr>
                  <th>Restaurant</th>
                  <th>Plan ID</th>
                  <th>Status</th>
                  <th>Trial Ends</th>
                  <th>Period End</th>
                  <th>Limits</th>
                </tr>
              </thead>
              <tbody>
                ${subscriptions.map(s => `
                  <tr>
                    <td><strong>${s.restaurant?.name || "N/A"}</strong></td>
                    <td><span class="badge badge-blue">${s.planId}</span></td>
                    <td><span class="badge ${s.status === "active" ? "badge-green" : "badge-red"}">${s.status.toUpperCase()}</span></td>
                    <td>${s.trialEndsAt ? new Date(s.trialEndsAt).toLocaleDateString() : "N/A"}</td>
                    <td>${s.currentPeriodEnd ? new Date(s.currentPeriodEnd).toLocaleDateString() : "N/A"}</td>
                    <td>Tables: ${s.tableLimit === 0 ? "∞" : s.tableLimit} | Staff: ${s.staffLimit === 0 ? "∞" : s.staffLimit}</td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
          </div>

          <!-- POS Offline Clients -->
          <div class="section">
            <div class="section-title">4. POS Offline Licenses & Clients (${clients.length})</div>
            <table>
              <thead>
                <tr>
                  <th>Restaurant Name</th>
                  <th>Contact Number</th>
                  <th>Location</th>
                  <th>Plan Label</th>
                  <th>License Key</th>
                  <th>Expiry Date</th>
                  <th>Status</th>
                  <th>Created By</th>
                </tr>
              </thead>
              <tbody>
                ${clients.map(c => `
                  <tr>
                    <td><strong>${c.restaurantName}</strong></td>
                    <td>${c.contactNumber}</td>
                    <td>${c.location || "N/A"}</td>
                    <td>${c.planLabel}</td>
                    <td><code>${c.licenseKey}</code></td>
                    <td>${c.expiryDate ? new Date(c.expiryDate).toLocaleDateString() : "N/A"}</td>
                    <td><span class="badge ${c.status === "Active" ? "badge-green" : "badge-red"}">${c.status}</span></td>
                    <td>${c.createdBy?.email || "System"}</td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
          </div>

          <!-- Admin Staff & Team -->
          <div class="section">
            <div class="section-title">5. Admin Staff & Sales Team (${adminStaff.length})</div>
            <table>
              <thead>
                <tr>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Referral Code</th>
                  <th>Commission Rate</th>
                  <th>Location</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                ${adminStaff.map(s => `
                  <tr>
                    <td><strong>${s.email}</strong></td>
                    <td><span class="badge badge-blue">${s.role}</span></td>
                    <td><code>${s.referralCode || "N/A"}</code></td>
                    <td>${s.commissionRate}%</td>
                    <td>${s.location || "N/A"}</td>
                    <td>${s.createdAt ? new Date(s.createdAt).toLocaleDateString() : "N/A"}</td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
          </div>
        </body>
        </html>
      `;

      return new Response(htmlContent, {
        headers: {
          "Content-Type": "text/html; charset=utf-8",
          "Content-Disposition": `attachment; filename="drestro_database_report_${dateStr}.html"`,
        },
      });
    }

    // 4. Default: Full A-Z JSON Dump of all database tables
    const backupData = {
      meta: {
        exportedAt: new Date().toISOString(),
        exportedBy: session.email,
        system: "DRestro SaaS Portal",
        version: "2.0.0",
        summary: {
          usersCount: users.length,
          restaurantsCount: restaurants.length,
          subscriptionsCount: subscriptions.length,
          clientsCount: clients.length,
          supportTicketsCount: supportTickets.length,
          hardwareOrdersCount: hardwareOrders.length,
          contactSubmissionsCount: contactSubmissions.length,
        }
      },
      tables: {
        users,
        restaurants,
        subscriptions,
        subscriptionPayments,
        clients,
        adminStaff,
        commissionLogs,
        supportTickets,
        hardwareOrders,
        hardware,
        testimonials,
        comboPackages,
        comboFeatures,
        plans,
        planFeatures,
        planNotIncluded,
        siteSettings,
        faqs,
        clientLogos,
        contactSubmissions,
        careerPosts,
        accounts,
        sessions,
        verificationTokens,
      }
    };

    const jsonString = JSON.stringify(backupData, null, 2);

    return new Response(jsonString, {
      headers: {
        "Content-Type": "application/json",
        "Content-Disposition": `attachment; filename="drestro_database_full_dump_${dateStr}.json"`,
      },
    });
  } catch (error: any) {
    console.error("Database backup download error:", error);
    return NextResponse.json({ error: "Failed to generate database backup: " + (error?.message || error) }, { status: 500 });
  }
}
