const { PrismaClient } = require("@prisma/client");
const prisma = new PrismaClient();

async function main() {
  const users = await prisma.user.findMany({
    select: { id: true, email: true, name: true }
  });
  console.log("USERS:", JSON.stringify(users, null, 2));

  const restaurants = await prisma.restaurant.findMany({
    select: { id: true, name: true, userId: true, offlineLicenseKey: true, phone: true }
  });
  console.log("RESTAURANTS:", JSON.stringify(restaurants, null, 2));

  const clients = await prisma.client.findMany({
    select: { id: true, restaurantName: true, licenseKey: true, contactNumber: true }
  });
  console.log("CLIENTS:", JSON.stringify(clients, null, 2));
}

main().catch(console.error);
