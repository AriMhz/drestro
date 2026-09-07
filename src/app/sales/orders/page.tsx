import { getSession } from "@/src/lib/auth";
import { redirect } from "next/navigation";

export const dynamic = "force-dynamic";

export default async function SalesOrdersPage() {
  const session = await getSession();
  if (!session) redirect("/admin/login");
  
  const AdminPage = (await import("@/src/app/admin/(dashboard)/orders/page")).default;
  return <AdminPage />;
}
