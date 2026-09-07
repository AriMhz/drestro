import { getSession } from "@/src/lib/auth";
import { redirect } from "next/navigation";

export const dynamic = "force-dynamic";

export default async function SupportClientLogosPage() {
  const session = await getSession();
  if (!session) redirect("/admin/login");
  
  // Import and render the admin page component
  const AdminPage = (await import("@/src/app/admin/(dashboard)/client-logos/page")).default;
  return <AdminPage />;
}
