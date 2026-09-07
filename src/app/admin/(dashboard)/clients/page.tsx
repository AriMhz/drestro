import { prisma } from "@/src/lib/prisma";
import { revalidatePath } from "next/cache";
import { getSession } from "@/src/lib/auth";
import ClientsClient from "./ClientsClient";
import crypto from "crypto";

export const dynamic = "force-dynamic";

export default async function ClientsPage() {
  const clients = await prisma.client.findMany({
    include: {
      createdBy: true,
      updatedBy: true
    },
    orderBy: { createdAt: "desc" }
  });

  const plans = await prisma.plan.findMany({
    orderBy: { priceYearly: "asc" }
  });

  const combos = await prisma.comboPackage.findMany({
    orderBy: { price: "asc" }
  });

  const marketingStaff = await prisma.adminStaff.findMany({
    select: { id: true, email: true, role: true, location: true },
    orderBy: { email: "asc" }
  });

  const users = await prisma.user.findMany({
    include: {
      referredBy: true,
      restaurants: {
        include: {
          subscriptions: {
            include: {
              updatedBy: true
            }
          }
        }
      }
    },
    orderBy: { createdAt: "desc" }
  });

  async function generateLicenseKey(formData: FormData) {
    "use server";
    
    const session = await getSession();
    const createdById = session?.staffId || null;
    const updatedById = session?.staffId || null;
    
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
    if (expiryDays === 36500) { 
      expiryDate.setFullYear(expiryDate.getFullYear() + 100);
    } else {
      expiryDate.setDate(expiryDate.getDate() + expiryDays);
    }

    await prisma.client.create({
      data: {
        restaurantName,
        contactNumber,
        location: location || null,
        machineId: machineId || null,
        planLabel: planLabel || "Premium",
        licenseKey,
        expiryDate,
        status: "Active",
        tableLimit,
        staffLimit,
        dishLimit,
        roomLimit,
        createdById,
        updatedById
      }
    });

    revalidatePath("/admin/clients");
  }

  async function deleteClientById(id: string) {
    "use server";
    try {
      const client = await prisma.client.findUnique({ where: { id } });
      if (client && client.licenseKey) {
        // Find matching restaurant
        const restaurant = await prisma.restaurant.findFirst({
          where: { offlineLicenseKey: client.licenseKey }
        });
        if (restaurant) {
          try {
            const action = "delete";
            const signData = `${restaurant.id}|${action}`;
            const token = crypto
              .createHmac("sha256", "DrestroPOS_Secure_Key_2026_X9P2")
              .update(signData)
              .digest("hex");

            await fetch("https://portal.drestro.com/api/restaurant/admin-action", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
              },
              body: JSON.stringify({ slug: restaurant.id, action, token }),
              cache: "no-store",
            });
          } catch (err) {
            console.error(`Failed to delete POS restaurant ${restaurant.id}:`, err);
          }
        }
      }
      await prisma.client.delete({ where: { id } });
    } catch (e) {
      console.error("Delete client error:", e);
    }
    revalidatePath("/admin/clients");
  }

  async function updateSubscription(formData: FormData) {
    "use server";
    const session = await getSession();
    const updatedById = session?.staffId || null;

    const subscriptionId = formData.get("subscriptionId") as string;
    const planId = formData.get("planId") as string;
    const status = formData.get("status") as string;
    const currentPeriodEnd = formData.get("currentPeriodEnd") as string;
    
    // Limits
    const tableLimit = parseInt(formData.get("tableLimit") as string) || 0;
    const staffLimit = parseInt(formData.get("staffLimit") as string) || 0;
    const dishLimit = parseInt(formData.get("dishLimit") as string) || 0;
    const roomLimit = parseInt(formData.get("roomLimit") as string) || 0;
    const allowedMenus = (formData.getAll("allowedMenus") as string[]).join(",");
    
    console.log("UPDATE SUBSCRIPTION CALLED", {
      subscriptionId,
      planId,
      status,
      currentPeriodEnd,
      tableLimit,
      staffLimit,
      dishLimit,
      roomLimit,
      allowedMenus
    });
    
    const sub = await prisma.subscription.update({
      where: { id: subscriptionId },
      data: {
        planId,
        status,
        currentPeriodEnd: currentPeriodEnd ? new Date(currentPeriodEnd) : null,
        tableLimit,
        staffLimit,
        dishLimit,
        roomLimit,
        allowedMenus,
        updatedById
      },
      include: { restaurant: true }
    });

    if (sub && sub.restaurant && sub.restaurant.offlineLicenseKey) {
      // Find the plan model to get friendly plan name (e.g. Premium)
      const dbPlan = await prisma.plan.findUnique({
        where: { id: planId }
      });
      const planLabel = dbPlan ? dbPlan.name : planId;

      await prisma.client.updateMany({
        where: { licenseKey: sub.restaurant.offlineLicenseKey },
        data: {
          planLabel,
          expiryDate: currentPeriodEnd ? new Date(currentPeriodEnd) : new Date(Date.now() + 365*24*60*60*1000),
          tableLimit,
          staffLimit,
          dishLimit,
          roomLimit
        }
      });
    }
    
    revalidatePath("/admin/clients");
  }

  async function resetMachineId(formData: FormData) {
    "use server";
    const clientId = formData.get("clientId") as string;
    if (!clientId) return;
    const session = await getSession();
    const updatedById = session?.staffId || null;

    await prisma.client.update({
      where: { id: clientId },
      data: { machineId: null, updatedById }
    });
    revalidatePath("/admin/clients");
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
    const panNumber = formData.get("panNumber") as string;
    const featureHotel = formData.get("featureHotel") === "on";
    const featureInventory = formData.get("featureInventory") === "on";
    const featureReports = formData.get("featureReports") === "on";
    const featureDaybook = formData.get("featureDaybook") === "on";
    const featureEbilling = formData.get("featureEbilling") === "on";
    const featureMultiUser = formData.get("featureMultiUser") === "on";

    if (!restaurantId || !name || !phone) return;

    let cleanPhone = phone ? phone.replace(/[^0-9]/g, "") : "";
    if (cleanPhone && cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }

    if (cleanPhone && (cleanPhone.length < 9 || cleanPhone.length > 10)) {
      throw new Error("Phone number must be exactly 9 or 10 digits (e.g. 98XXXXXXXX)");
    }

    const rest = await prisma.restaurant.update({
      where: { id: restaurantId },
      data: {
        name,
        phone: cleanPhone || phone,
        type,
        address: address || null,
        ward: ward || null,
        city: city || null,
        tagline: tagline || null,
        email: email || null,
        panNumber: panNumber || null,
        featureHotel,
        featureInventory,
        featureReports,
        featureDaybook,
        featureEbilling,
        featureMultiUser
      }
    });

    if (rest.userId && cleanPhone) {
      await prisma.user.update({
        where: { id: rest.userId },
        data: { phone: cleanPhone }
      });
    }

    // Sync to Client table (for license keys / clients lists)
    if (rest.offlineLicenseKey) {
      await prisma.client.updateMany({
        where: { licenseKey: rest.offlineLicenseKey },
        data: {
          restaurantName: name,
          contactNumber: cleanPhone || phone,
          location: address || null
        }
      });
    }

    // Live Push updated details & feature access flags to DRestroPOS Cloud Portal (portal.drestro.com)!
    try {
      const signData = `${rest.id}|update_details`;
      const token = crypto
        .createHmac("sha256", "DrestroPOS_Secure_Key_2026_X9P2")
        .update(signData)
        .digest("hex");

      const posUrl = process.env.NODE_ENV === "production"
        ? "https://portal.drestro.com/api/restaurant/admin-action"
        : "http://localhost:8000/api/restaurant/admin-action";

      await fetch(posUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          slug: rest.id,
          action: "update_details",
          token,
          name,
          phone: cleanPhone || phone,
          pan_number: panNumber || null,
          tagline: tagline || null,
          address: address || null,
          ward: ward || null,
          city: city || null,
          email: email || null,
          feature_hotel: featureHotel,
          feature_inventory: featureInventory,
          feature_reports: featureReports,
          feature_daybook: featureDaybook,
          feature_ebilling: featureEbilling,
          feature_multi_user: featureMultiUser
        })
      });
    } catch (e) {
      console.error("Failed to sync updated restaurant details & features to POS:", e);
    }

    revalidatePath("/admin/clients");
  }

  async function updateClientDetails(formData: FormData) {
    "use server";
    const clientId = formData.get("clientId") as string;
    const restaurantName = formData.get("restaurantName") as string;
    const contactNumber = formData.get("contactNumber") as string;
    const location = formData.get("location") as string;

    if (!clientId || !restaurantName || !contactNumber) return;

    let cleanPhone = contactNumber ? contactNumber.replace(/[^0-9]/g, "") : "";
    if (cleanPhone && cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }

    if (cleanPhone && (cleanPhone.length < 9 || cleanPhone.length > 10)) {
      throw new Error("Phone number must be exactly 9 or 10 digits (e.g. 98XXXXXXXX)");
    }

    const client = await prisma.client.update({
      where: { id: clientId },
      data: {
        restaurantName,
        contactNumber: cleanPhone || contactNumber,
        location: location || null
      }
    });

    // Sync to Restaurant table
    if (client.licenseKey) {
      await prisma.restaurant.updateMany({
        where: { offlineLicenseKey: client.licenseKey },
        data: {
          name: restaurantName,
          phone: cleanPhone || contactNumber,
          address: location || null
        }
      });

      // Also update matching User owner's phone number
      const matchingRest = await prisma.restaurant.findFirst({
        where: { offlineLicenseKey: client.licenseKey }
      });
      if (matchingRest && matchingRest.userId && cleanPhone) {
        await prisma.user.update({
          where: { id: matchingRest.userId },
          data: { phone: cleanPhone }
        });
      }
    }

    revalidatePath("/admin/clients");
  }

  async function updateUserDetails(formData: FormData) {
    "use server";
    const userId = formData.get("userId") as string;
    const name = formData.get("name") as string;
    const email = formData.get("email") as string;
    const phone = formData.get("phone") as string;
    const assignedLocation = formData.get("assignedLocation") as string;
    const referredById = formData.get("referredById") as string;

    if (!userId || !name || !email) return;

    let cleanPhone = phone ? phone.replace(/[^0-9]/g, "") : null;
    if (cleanPhone && cleanPhone.length > 10 && cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }

    if (cleanPhone && (cleanPhone.length < 9 || cleanPhone.length > 10)) {
      throw new Error("Phone number must be exactly 9 or 10 digits (e.g. 98XXXXXXXX)");
    }

    await prisma.user.update({
      where: { id: userId },
      data: {
        name,
        email,
        phone: cleanPhone || null,
        assignedLocation: assignedLocation || null,
        referredById: referredById || null
      }
    });

    if (cleanPhone) {
      await prisma.restaurant.updateMany({
        where: { userId },
        data: { phone: cleanPhone }
      });
    }

    revalidatePath("/admin/clients");
  }

  async function deleteUserById(userId: string) {
    "use server";
    try {
      // Find all restaurants owned by this user
      const userRestaurants = await prisma.restaurant.findMany({
        where: { userId }
      });

      // Call Laravel POS API to cascadingly delete each restaurant
      for (const restaurant of userRestaurants) {
        try {
          const action = "delete";
          const signData = `${restaurant.id}|${action}`;
          const token = crypto
            .createHmac("sha256", "DrestroPOS_Secure_Key_2026_X9P2")
            .update(signData)
            .digest("hex");

          await fetch("https://portal.drestro.com/api/restaurant/admin-action", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
            },
            body: JSON.stringify({ slug: restaurant.id, action, token }),
            cache: "no-store",
          });
        } catch (err) {
          console.error(`Failed to delete POS restaurant ${restaurant.id}:`, err);
        }
      }

      await prisma.user.delete({ where: { id: userId } });
    } catch (e) {
      console.error("Delete user error:", e);
    }
    revalidatePath("/admin/clients");
  }

  const session = await getSession();
  const staffLocation = session?.location || "";
  const staffRole = session?.role || "";
  const staffId = session?.staffId || "";

  return (
    <ClientsClient 
      clients={clients} 
      users={users} 
      generateLicenseKey={generateLicenseKey} 
      deleteClient={deleteClientById} 
      deleteUser={deleteUserById}
      updateSubscription={updateSubscription}
      resetMachineId={resetMachineId}
      updateRestaurantDetails={updateRestaurantDetails}
      updateClientDetails={updateClientDetails}
      updateUserDetails={updateUserDetails}
      staffLocation={staffLocation}
      staffRole={staffRole}
      staffId={staffId}
      plans={plans}
      combos={combos}
      marketingStaff={marketingStaff}
    />
  );
}
