import { prisma } from "@/src/lib/prisma";
import InboxClient from "./InboxClient";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Inbox - DRestro Admin",
};

export default async function InboxPage() {
  // Fetch all support tickets with the restaurant details
  const tickets = await prisma.supportTicket.findMany({
    include: {
      restaurant: true,
      assignedTo: true,
      resolvedBy: true,
      lastUpdatedBy: true,
    },
    orderBy: { createdAt: "desc" },
  });

  // Fetch contact submissions
  const submissions = await prisma.contactSubmission.findMany({
    orderBy: { createdAt: "desc" },
  });

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-[#111111] dark:text-white">Inbox</h1>
        <p className="text-sm text-slate-500 dark:text-gray-400 mt-1">Manage client support tickets and website contact inquiries.</p>
      </div>

      <InboxClient initialTickets={tickets} initialSubmissions={submissions} />
    </div>
  );
}
