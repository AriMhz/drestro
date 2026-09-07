import { prisma } from "@/src/lib/prisma";
import ContactClient from "./ContactClient";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Contact Us - DRestroPOS",
  description: "Get in touch with our dedicated Nepalese support team. Have questions about pricing, hardware setup, or offline sync?",
};

export default async function ContactPage() {
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

  return <ContactClient contactSettings={settingsMap} />;
}
