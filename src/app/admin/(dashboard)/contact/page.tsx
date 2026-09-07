import { prisma } from "@/src/lib/prisma";
import ContactClient from "./ContactClient";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Contact Submissions - DRestro Admin",
};

export default async function ContactSubmissionsPage() {
  const submissions = await prisma.contactSubmission.findMany({
    orderBy: { createdAt: "desc" },
  });

  const settings = await prisma.siteSetting.findMany({
    where: {
      key: {
        in: [
          'whatsapp_number',
          'contact_location',
          'contact_phone',
          'contact_email',
          'contact_map_iframe'
        ]
      }
    }
  });

  const settingsMap = settings.reduce((acc: any, setting) => {
    let val = setting.value;
    try {
      const parsed = JSON.parse(val);
      if (typeof parsed === "string") {
        val = parsed;
      }
    } catch (e) {}
    val = val.trim();
    if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith("'") && val.endsWith("'"))) {
      val = val.slice(1, -1).trim();
    }
    acc[setting.key] = val;
    return acc;
  }, {});

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-[#111111] dark:text-white">Contact & Submissions</h1>
        <p className="text-sm text-slate-500 dark:text-gray-400 mt-1">Manage contact page details and view customer submissions.</p>
      </div>

      <ContactClient initialSubmissions={submissions} initialSettings={settingsMap} />
    </div>
  );
}
