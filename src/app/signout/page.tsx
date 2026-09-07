"use client";

import { useEffect, useState } from "react";
import { signOut } from "next-auth/react";

export default function SignoutPage() {
  const [status, setStatus] = useState("Signing out...");

  useEffect(() => {
    async function doSignout() {
      try {
        // Step 1: Clear our custom drestro_admin_session cookie via API
        await fetch("/api/admin/logout", { method: "POST" }).catch(() => {});
        setStatus("Clearing session...");
        // Step 2: Properly sign out from NextAuth (clears JWT cookie) → redirect to /login
        await signOut({ callbackUrl: "/login", redirect: true });
      } catch {
        // Fallback: just navigate to login
        window.location.href = "/login";
      }
    }
    doSignout();
  }, []);

  return (
    <div
      style={{
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        height: "100vh",
        fontFamily:
          "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
        background: "#0a0a0a",
        color: "#94a3b8",
      }}
    >
      {/* Spinner */}
      <div
        style={{
          width: 40,
          height: 40,
          border: "3px solid #1e293b",
          borderTopColor: "#10b981",
          borderRadius: "50%",
          animation: "spin 0.8s linear infinite",
          marginBottom: 20,
        }}
      />
      <p style={{ fontSize: 16, fontWeight: 600, color: "#e2e8f0", marginBottom: 4 }}>
        {status}
      </p>
      <p style={{ fontSize: 13, color: "#475569" }}>Please wait…</p>

      <style>{`
        @keyframes spin {
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}
