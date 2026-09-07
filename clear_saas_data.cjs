const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  console.log("Starting SaaS database client flush...");
  
  // Deleting in order to respect foreign key constraints
  console.log("Clearing Sessions...");
  await prisma.session.deleteMany();
  
  console.log("Clearing Accounts...");
  await prisma.account.deleteMany();
  
  console.log("Clearing Verification Tokens...");
  await prisma.verificationToken.deleteMany();
  
  console.log("Clearing Subscriptions...");
  await prisma.subscription.deleteMany();
  
  console.log("Clearing Support Tickets...");
  await prisma.supportTicket.deleteMany();
  
  console.log("Clearing Commission Logs...");
  await prisma.commissionLog.deleteMany();
  
  console.log("Clearing Restaurants...");
  await prisma.restaurant.deleteMany();
  
  console.log("Clearing Users...");
  await prisma.user.deleteMany();
  
  console.log("Clearing Offline Clients/Licenses...");
  await prisma.client.deleteMany();
  
  console.log("Clearing Hardware Orders...");
  await prisma.hardwareOrder.deleteMany();
  
  console.log("SaaS database client flush completed successfully! AdminStaff accounts are intact.");
}

main()
  .catch((e) => {
    console.error("Error during database flush:", e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
