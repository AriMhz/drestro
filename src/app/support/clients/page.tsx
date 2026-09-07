import { prisma } from "@/src/lib/prisma";
import { revalidatePath } from "next/cache";
import ClientsClient from "@/src/app/admin/(dashboard)/clients/ClientsClient";
import { getSession } from "@/src/lib/auth";
import { redirect } from "next/navigation";

export const dynamic = "force-dynamic";

export default async function SupportClientsPage() {
  const session = await getSession();
  if (!session) redirect("/admin/login");

  const clients = await prisma.client.findMany({ orderBy: { createdAt: "desc" } });
  const users = await prisma.user.findMany({
    include: { restaurants: { include: { subscriptions: true } } },
    orderBy: { createdAt: "desc" }
  });

  async function generateLicenseKey(formData: FormData) {
    "use server";
    const restaurantName = formData.get("restaurantName") as string;
    const contactNumber = formData.get("contactNumber") as string;
    const location = formData.get("location") as string;
    const machineId = formData.get("machineId") as string;
    const planLabel = formData.get("planLabel") as string;
    const expiryDays = parseInt(formData.get("expiryDays") as string);
    const tableLimit = parseInt(formData.get("tableLimit") as string) || 0;
    const staffLimit = parseInt(formData.get("staffLimit") as string) || 0;
    const dishLimit = parseInt(formData.get("dishLimit") as string) || 0;
    const roomLimit = parseInt(formData.get("roomLimit") as string) || 0;
    if (!restaurantName || !contactNumber || !expiryDays || !machineId) return;
    const randomStr = () => Math.random().toString(36).substring(2, 6).toUpperCase();
    const machinePrefix = machineId ? machineId.substring(0, 4).toUpperCase() : randomStr();
    const licenseKey = `DR-${machinePrefix}-${randomStr()}-${randomStr()}-${randomStr()}`;
    const expiryDate = new Date();
    if (expiryDays === 36500) expiryDate.setFullYear(expiryDate.getFullYear() + 100);
    else expiryDate.setDate(expiryDate.getDate() + expiryDays);
    await prisma.client.create({
      data: { restaurantName, contactNumber, location: location || null, machineId: machineId || null, planLabel: planLabel || "Premium", licenseKey, expiryDate, status: "Active", tableLimit, staffLimit, dishLimit, roomLimit }
    });
    revalidatePath("/support/clients");
  }

  async function deleteClientById(id: string) {
    "use server";
    await prisma.client.delete({ where: { id } });
    revalidatePath("/support/clients");
  }

  async function updateSubscription(formData: FormData) {
    "use server";
    const subscriptionId = formData.get("subscriptionId") as string;
    const planId = formData.get("planId") as string;
    const status = formData.get("status") as string;
    const currentPeriodEnd = formData.get("currentPeriodEnd") as string;
    const tableLimit = parseInt(formData.get("tableLimit") as string) || 0;
    const staffLimit = parseInt(formData.get("staffLimit") as string) || 0;
    const dishLimit = parseInt(formData.get("dishLimit") as string) || 0;
    const roomLimit = parseInt(formData.get("roomLimit") as string) || 0;
    await prisma.subscription.update({
      where: { id: subscriptionId },
      data: { planId, status, currentPeriodEnd: currentPeriodEnd ? new Date(currentPeriodEnd) : null, tableLimit, staffLimit, dishLimit, roomLimit }
    });
    revalidatePath("/support/clients");
  }

  async function resetMachineId(formData: FormData) {
    "use server";
    const clientId = formData.get("clientId") as string;
    if (!clientId) return;
    await prisma.client.update({ where: { id: clientId }, data: { machineId: null } });
    revalidatePath("/support/clients");
  }

  async function updateRestaurantDetails(formData: FormData) {
    "use server";
    const restaurantId = formData.get("restaurantId") as string;
    const name = formData.get("name") as string;
    const phone = formData.get("phone") as string;
    const type = formData.get("type") as string;
    const address = formData.get("address") as string;
    const ward = formData.get("ward") as string;
    const city = formData.get("city") as string;
    const tagline = formData.get("tagline") as string;
    const email = formData.get("email") as string;

    if (!restaurantId || !name || !phone) return;

    await prisma.restaurant.update({
      where: { id: restaurantId },
      data: {
        name,
        phone,
        type,
        address: address || null,
        ward: ward || null,
        city: city || null,
        tagline: tagline || null,
        email: email || null
      }
    });
    revalidatePath("/support/clients");
  }

  async function updateClientDetails(formData: FormData) {
    "use server";
    const clientId = formData.get("clientId") as string;
    const restaurantName = formData.get("restaurantName") as string;
    const contactNumber = formData.get("contactNumber") as string;
    const location = formData.get("location") as string;

    if (!clientId || !restaurantName || !contactNumber) return;

    await prisma.client.update({
      where: { id: clientId },
      data: {
        restaurantName,
        contactNumber,
        location: location || null
      }
    });

    revalidatePath("/support/clients");
  }

  async function updateUserDetails(formData: FormData) {
    "use server";
    const userId = formData.get("userId") as string;
    const name = formData.get("name") as string;
    const email = formData.get("email") as string;
    const phone = formData.get("phone") as string;

    if (!userId || !name || !email) return;

    let cleanPhone = phone ? phone.replace(/[^0-9]/g, "") : null;
    if (cleanPhone && cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }

    await prisma.user.update({
      where: { id: userId },
      data: {
        name,
        email,
        phone: cleanPhone || null
      }
    });

    revalidatePath("/support/clients");
  }

  const staffLocation = session?.location || "";
  const staffRole = session?.role || "";

  return (
    <ClientsClient 
      clients={clients} 
      users={users} 
      generateLicenseKey={generateLicenseKey} 
      deleteClient={deleteClientById} 
      updateSubscription={updateSubscription} 
      resetMachineId={resetMachineId}
      updateRestaurantDetails={updateRestaurantDetails}
      updateClientDetails={updateClientDetails}
      updateUserDetails={updateUserDetails}
      staffLocation={staffLocation}
      staffRole={staffRole}
    />
  );
}
