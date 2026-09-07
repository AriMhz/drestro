import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";
import { decrypt } from "./lib/auth";

export async function middleware(request: NextRequest) {
  const pathname = request.nextUrl.pathname;

  const isAdminPath = pathname.startsWith("/admin") && !pathname.startsWith("/admin/login");
  const isSalesPath = pathname.startsWith("/sales");
  const isSupportPath = pathname.startsWith("/support");
  const isMarketingPath = pathname.startsWith("/marketing");
  const isProtectedApi = pathname.startsWith("/api/admin") && !pathname.startsWith("/api/admin/login") && !pathname.startsWith("/api/admin/logout");

  if (isAdminPath || isSalesPath || isSupportPath || isMarketingPath || isProtectedApi) {
    const sessionCookie = request.cookies.get("drestro_admin_session")?.value;

    if (!sessionCookie) {
      if (isProtectedApi) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
      return NextResponse.redirect(new URL("/admin/login", request.url));
    }

    try {
      const session = await decrypt(sessionCookie);

      // Superadmin and Admin can access everything
      if (session.role === "SUPERADMIN" || session.role === "ADMIN") {
        return NextResponse.next();
      }

      // API Access Control — staff API is superadmin only
      if (isProtectedApi) {
        if (pathname.startsWith("/api/admin/staff") && session.role !== "SUPERADMIN") {
          return NextResponse.json({ error: "Unauthorized" }, { status: 403 });
        }
        return NextResponse.next();
      }

      // Admin routes — only superadmin
      if (isAdminPath) {
        return NextResponse.redirect(new URL("/admin/login", request.url));
      }

      // Portal routes — check role matches
      if (isSalesPath && session.role !== "SALES") {
        return NextResponse.redirect(new URL("/admin/login", request.url));
      }
      if (isSupportPath && session.role !== "SUPPORT") {
        return NextResponse.redirect(new URL("/admin/login", request.url));
      }
      if (isMarketingPath && session.role !== "MARKETING") {
        return NextResponse.redirect(new URL("/admin/login", request.url));
      }

      // For portal pages, check permissions
      const portalBase = isSalesPath ? "/sales" : isSupportPath ? "/support" : "/marketing";
      const subPath = pathname.replace(portalBase, "").replace(/^\//, "").split("/")[0]; // e.g., "clients"
      
      let perms: string[] = [];
      try { perms = JSON.parse(session.permissions || "[]"); } catch {}
      
      if (!subPath) {
        // Accessing the portal index (e.g. /sales)
        if (!perms.includes("dashboard")) {
          // Find first allowed sub-page
          const firstAllowed = perms.find(p => p !== "dashboard");
          if (firstAllowed) {
            return NextResponse.redirect(new URL(`${portalBase}/${firstAllowed}`, request.url));
          } else {
            // No permissions allowed, redirect to login
            return NextResponse.redirect(new URL("/admin/login", request.url));
          }
        }
      } else {
        // Accessing a sub-page (e.g. /sales/clients)
        if (!perms.includes(subPath)) {
          // Check if they have dashboard permission to fall back to, else first allowed sub-page
          if (perms.includes("dashboard")) {
            return NextResponse.redirect(new URL(portalBase, request.url));
          } else {
            const firstAllowed = perms.find(p => p !== "dashboard");
            if (firstAllowed) {
              return NextResponse.redirect(new URL(`${portalBase}/${firstAllowed}`, request.url));
            } else {
              return NextResponse.redirect(new URL("/admin/login", request.url));
            }
          }
        }
      }

      return NextResponse.next();
    } catch (error) {
      if (isProtectedApi) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
      return NextResponse.redirect(new URL("/admin/login", request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/admin/:path*", "/api/admin/:path*", "/sales/:path*", "/support/:path*", "/marketing/:path*"],
};
