import { jwtVerify, SignJWT } from "jose";
import { cookies } from "next/headers";

const secretKey = "drestro-super-secret-key-change-in-production";
const key = new TextEncoder().encode(secretKey);

export async function encrypt(payload: any) {
  return await new SignJWT(payload)
    .setProtectedHeader({ alg: "HS256" })
    .setIssuedAt()
    .setExpirationTime("1h")
    .sign(key);
}

export async function decrypt(input: string): Promise<any> {
  const { payload } = await jwtVerify(input, key, {
    algorithms: ["HS256"],
  });
  return payload;
}

export async function getSession() {
  const cookieStore = await cookies();
  const session = cookieStore.get("drestro_admin_session")?.value;
  if (!session) return null;
  return await decrypt(session);
}

export async function createSession(staffId: string, role: string, permissions: string, email?: string) {
  const expires = new Date(Date.now() + 60 * 60 * 1000); // 1 hour
  const session = await encrypt({ staffId, role, permissions, email, expires });
  const cookieStore = await cookies();
  const isProd = process.env.NODE_ENV === "production";
  
  cookieStore.set("drestro_admin_session", session, {
    expires,
    httpOnly: true,
    secure: isProd,
    sameSite: "lax",
    path: "/",
    domain: isProd ? ".drestro.com" : undefined,
  });
}

export async function destroySession() {
  const cookieStore = await cookies();
  const isProd = process.env.NODE_ENV === "production";
  cookieStore.set("drestro_admin_session", "", {
    expires: new Date(0),
    path: "/",
    domain: isProd ? ".drestro.com" : undefined,
  });
}
