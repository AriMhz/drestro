import { NextResponse } from "next/server";
import { cookies } from "next/headers";
import { destroySession } from "../../../../lib/auth";

export async function POST() {
  await destroySession();
  return NextResponse.json({ success: true });
}

export async function GET(request: Request) {
  await destroySession();
  
  const cookieStore = await cookies();
  const isProd = process.env.NODE_ENV === "production";
  const sessionCookieNames = [
    "authjs.session-token",
    "__Secure-authjs.session-token",
    "next-auth.session-token",
    "__Secure-next-auth.session-token",
    "next-auth.callback-url",
    "next-auth.csrf-token"
  ];
  for (const name of sessionCookieNames) {
    const isSecure = name.startsWith("__Secure-") || isProd;
    cookieStore.set(name, "", {
      expires: new Date(0),
      path: "/",
      secure: isSecure,
    });
    if (isProd) {
      cookieStore.set(name, "", {
        expires: new Date(0),
        path: "/",
        domain: ".drestro.com",
        secure: isSecure,
      });
    }
  }
  
  const proto = request.headers.get("x-forwarded-proto") || "https";
  const host = request.headers.get("x-forwarded-host") || request.headers.get("host") || "drestro.com";
  
  let redirectUrl = "";
  if (host.includes("portal.drestro.com")) {
    redirectUrl = `https://drestro.com/login`;
  } else if (host.includes("localhost:8080")) {
    redirectUrl = `http://localhost:3000/login`;
  } else if (
    host.includes("sales.drestro.com") || 
    host.includes("support.drestro.com") || 
    host.includes("market.drestro.com") || 
    host.includes("admin.drestro.com")
  ) {
    redirectUrl = `https://admin.drestro.com/login`;
  } else if (host.includes("localhost")) {
    const referer = request.headers.get("referer") || "";
    if (
      referer.includes("/sales") || 
      referer.includes("/support") || 
      referer.includes("/marketing") || 
      referer.includes("/admin")
    ) {
      redirectUrl = `${proto}://${host}/admin/login`;
    } else {
      redirectUrl = `${proto}://${host}/login`;
    }
  } else {
    const referer = request.headers.get("referer") || "";
    if (
      referer.includes("/admin") ||
      referer.includes("/sales") ||
      referer.includes("/support") ||
      referer.includes("/marketing")
    ) {
      redirectUrl = `${proto}://${host}/admin/login`;
    } else {
      redirectUrl = `${proto}://${host}/login`;
    }
  }
  
  return NextResponse.redirect(redirectUrl);
}
