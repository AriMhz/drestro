import NextAuth from "next-auth"
import Credentials from "next-auth/providers/credentials"
import Google from "next-auth/providers/google"
import { PrismaAdapter } from "@auth/prisma-adapter"
import { prisma } from "./src/lib/prisma"
import bcrypt from "bcryptjs"

export const { handlers, signIn, signOut, auth } = NextAuth({
  adapter: PrismaAdapter(prisma),
  session: { strategy: "jwt" },
  providers: [
    Google({
      allowDangerousEmailAccountLinking: true
    }),
    Credentials({
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
        clientIpv4: { label: "Client IPv4", type: "text" }
      },
      async authorize(credentials) {
        if (!credentials?.email || !credentials?.password) return null
        
        const identifier = credentials.email as string;
        let cleanPhone = identifier.replace(/[^0-9]/g, "");
        if (cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
          cleanPhone = cleanPhone.substring(3);
        }

        const user = await prisma.user.findFirst({
          where: {
            OR: [
              { email: identifier },
              { phone: cleanPhone }
            ]
          }
        })
        
        let targetUser = user;
        let outletId = null;

        if (!user || !user.password || !(await bcrypt.compare(credentials.password as string, user.password))) {
          // Check if it's an Outlet/Restaurant manager logging in
          const restaurant = await prisma.restaurant.findFirst({
            where: { email: identifier }
          });
          
          if (restaurant && restaurant.password && await bcrypt.compare(credentials.password as string, restaurant.password)) {
            targetUser = await prisma.user.findUnique({ where: { id: restaurant.userId } });
            outletId = restaurant.id;
          } else {
            return null;
          }
        }
        
        if (!targetUser) return null;

        // If we reached here, login is successful (either as Main Account or Outlet)
        try {
          const { headers } = await import("next/headers");
          const headersList = await headers();
          const rawIp = (credentials.clientIpv4 as string) || headersList.get("cf-pseudo-ipv4") || headersList.get("x-forwarded-for")?.split(",")[0] || headersList.get("x-real-ip") || "127.0.0.1";
          const ip = rawIp.startsWith("::ffff:") ? rawIp.substring(7) : (rawIp === "::1" ? "127.0.0.1" : rawIp);
          const ua = headersList.get("user-agent") || "";
          
          let device = "Unknown Device";
          const lower = ua.toLowerCase();
          if (lower.includes("windows")) device = "Windows PC";
          else if (lower.includes("macintosh") || lower.includes("mac os")) device = "Mac PC";
          else if (lower.includes("android")) device = "Android Device";
          else if (lower.includes("iphone") || lower.includes("ipad")) device = "iOS Device";
          else if (lower.includes("linux")) device = "Linux Device";
          else if (lower.includes("curl") || lower.includes("postman")) device = "API Client";
          else device = ua.split(" ")[0] || "Unknown Device";

          await prisma.user.update({
            where: { id: targetUser.id },
            data: {
              lastLoginIp: ip,
              lastLoginAt: new Date(),
              lastLoginDevice: device
            }
          });
        } catch (e) {
          console.error("Failed to update user login logs:", e);
        }
        
        if (outletId) {
          return Object.assign(targetUser, { outletId });
        }
        return targetUser;
      }
    })
  ],
  pages: {
    signIn: "/login",
  },
  callbacks: {
    async signIn({ user, account }) {
      if (account?.provider === "credentials") {
        return true;
      }
      if (user && user.id) {
        try {
          const { headers } = await import("next/headers");
          const headersList = await headers();
          const rawIp = headersList.get("cf-pseudo-ipv4") || headersList.get("x-forwarded-for")?.split(",")[0] || headersList.get("x-real-ip") || "127.0.0.1";
          const ip = rawIp.startsWith("::ffff:") ? rawIp.substring(7) : (rawIp === "::1" ? "127.0.0.1" : rawIp);
          const ua = headersList.get("user-agent") || "";
          
          let device = "Unknown Device";
          const lower = ua.toLowerCase();
          if (lower.includes("windows")) device = "Windows PC";
          else if (lower.includes("macintosh") || lower.includes("mac os")) device = "Mac PC";
          else if (lower.includes("android")) device = "Android Device";
          else if (lower.includes("iphone") || lower.includes("ipad")) device = "iOS Device";
          else if (lower.includes("linux")) device = "Linux Device";
          else device = ua.split(" ")[0] || "Unknown Device";

          await prisma.user.update({
            where: { id: user.id },
            data: {
              lastLoginIp: ip,
              lastLoginAt: new Date(),
              lastLoginDevice: device
            }
          });
        } catch (e) {
          console.error("Failed to update user login log in signIn callback:", e);
        }
      }
      return true;
    },
    async jwt({ token, user }) {
      if (user) {
        token.id = user.id;
        token.customerCode = (user as any).customerCode;
        if ((user as any).outletId) {
          token.outletId = (user as any).outletId;
        }
      }
      
      if (token.id) {
        try {
          const dbUser = await prisma.user.findUnique({
            where: { id: token.id as string }
          });
          
          if (!dbUser) {
            // User was deleted from the admin panel! Invalidate token
            return {};
          }

          if (!token.customerCode) {
            if (!dbUser.customerCode) {
              let code = "";
              let isUnique = false;
              while (!isUnique) {
                code = String(Math.floor(100000 + Math.random() * 900000));
                const collision = await prisma.user.findFirst({ where: { customerCode: code } });
                if (!collision) isUnique = true;
              }
              const updated = await prisma.user.update({
                where: { id: dbUser.id },
                data: { customerCode: code }
              });
              token.customerCode = updated.customerCode;
            } else {
              token.customerCode = dbUser.customerCode;
            }
          }
        } catch (e) {
          console.error("JWT validation error:", e);
        }
      }
      return token;
    },
    async session({ session, token }) {
      if (!token || !token.id) {
        // If token has been invalidated, return empty/null session to force logout
        return {
          ...session,
          user: null
        } as any;
      }
      if (session.user) {
        if (token.id) session.user.id = token.id as string;
        else if (token.sub) session.user.id = token.sub;
        (session.user as any).customerCode = token.customerCode as string;
        if (token.outletId) {
          (session.user as any).outletId = token.outletId as string;
        }
      }
      return session;
    }
  }
})
