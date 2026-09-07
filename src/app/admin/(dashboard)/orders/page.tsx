import { prisma } from "@/src/lib/prisma";
import OrdersClient from "./OrdersClient";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Hardware Orders - DRestro Admin",
};

export default async function HardwareOrdersPage() {
  const orders = await prisma.hardwareOrder.findMany({
    orderBy: { createdAt: "desc" },
  });

  const hardwareList = await prisma.hardware.findMany({
    orderBy: { name: "asc" },
  });

  // Fetch users with emails and phones
  const dbUsers = await prisma.user.findMany({
    select: { name: true, email: true, phone: true }
  });

  // Fetch clients
  const dbClients = await prisma.client.findMany({
    select: { restaurantName: true, contactNumber: true, location: true }
  });

  // Fetch restaurants
  const dbRestaurants = await prisma.restaurant.findMany({
    select: { name: true, phone: true, email: true, address: true }
  });

  const contactsMap = new Map<string, { name: string; email: string; phone: string; address: string }>();

  // Helper to normalize phone/email key
  const normalizeKey = (phone: string, email: string) => {
    return `${phone || ''}-${email || ''}`.toLowerCase().trim();
  };

  // Add users
  dbUsers.forEach(u => {
    if (u.phone || u.email) {
      const key = normalizeKey(u.phone || '', u.email || '');
      contactsMap.set(key, {
        name: u.name || '',
        email: u.email || '',
        phone: u.phone || '',
        address: ''
      });
    }
  });

  // Add clients
  dbClients.forEach(c => {
    if (c.contactNumber) {
      const existingKey = Array.from(contactsMap.keys()).find(k => k.startsWith(`${c.contactNumber.toLowerCase().trim()}-`));
      if (existingKey) {
        const existing = contactsMap.get(existingKey)!;
        contactsMap.set(existingKey, {
          ...existing,
          name: existing.name || c.restaurantName,
          address: existing.address || c.location || ''
        });
      } else {
        const key = normalizeKey(c.contactNumber, '');
        contactsMap.set(key, {
          name: c.restaurantName,
          email: '',
          phone: c.contactNumber,
          address: c.location || ''
        });
      }
    }
  });

  // Add restaurants
  dbRestaurants.forEach(r => {
    if (r.phone || r.email) {
      const existingKey = Array.from(contactsMap.keys()).find(k => 
        (r.phone && k.startsWith(`${r.phone.toLowerCase().trim()}-`)) || 
        (r.email && k.endsWith(`-${r.email.toLowerCase().trim()}`))
      );
      if (existingKey) {
        const existing = contactsMap.get(existingKey)!;
        contactsMap.set(existingKey, {
          name: existing.name || r.name,
          email: existing.email || r.email || '',
          phone: existing.phone || r.phone || '',
          address: existing.address || r.address || ''
        });
      } else {
        const key = normalizeKey(r.phone || '', r.email || '');
        contactsMap.set(key, {
          name: r.name,
          email: r.email || '',
          phone: r.phone || '',
          address: r.address || ''
        });
      }
    }
  });

  const customerList = Array.from(contactsMap.values()).map(c => ({
    name: c.name || 'Merchant',
    email: c.email || '',
    phone: c.phone || '',
    address: c.address || ''
  }));

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-[#111111] dark:text-white">Hardware Orders</h1>
        <p className="text-sm text-slate-500 dark:text-gray-400 mt-1">View and manage customer orders placed via the Hardware Store checkout.</p>
      </div>

      <OrdersClient initialOrders={orders} hardwareList={hardwareList} customerList={customerList} />
    </div>
  );
}
