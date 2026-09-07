import { getSession } from "@/src/lib/auth";
import { redirect } from "next/navigation";

export const dynamic = "force-dynamic";

export default async function SalesBillingPage() {
  const session = await getSession();
  if (!session) redirect("/admin/login");
  
  const AdminPage = (await import("@/src/app/admin/(dashboard)/billing/page")).default;
  return <AdminPage />;
}
