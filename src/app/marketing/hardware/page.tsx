import { getSession } from "@/src/lib/auth";
import { redirect } from "next/navigation";

export const dynamic = "force-dynamic";

export default async function MarketingHardwarePage({ searchParams }: { searchParams: Promise<{ edit?: string }> }) {
  const session = await getSession();
  if (!session) redirect("/admin/login");
  
  // Import and render the admin page component
  const AdminPage = (await import("@/src/app/admin/(dashboard)/hardware/page")).default;
  return <AdminPage searchParams={searchParams} />;
}
