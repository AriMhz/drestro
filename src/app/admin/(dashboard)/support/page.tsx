import { prisma } from "@/src/lib/prisma";
import SupportClient from "./SupportClient";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Support Tickets - DRestro Admin",
};

export default async function SupportPage() {
  // Authentication is handled by middleware.ts

  // Fetch all support tickets with the restaurant details
  const tickets = await prisma.supportTicket.findMany({
    include: {
      restaurant: true,
      assignedTo: true,
    },
    orderBy: { createdAt: "desc" },
  });

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-[#111111] dark:text-white">Support Tickets</h1>
        <p className="text-sm text-slate-500 dark:text-gray-400 mt-1">Manage and respond to client support requests.</p>
      </div>

      <SupportClient initialTickets={tickets} />
    </div>
  );
}
