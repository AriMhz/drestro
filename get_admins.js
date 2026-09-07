const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  const admins = await prisma.adminStaff.findMany();
  console.log("Admin Users:");
  admins.forEach(a => {
    console.log(`- Email: ${a.email} | Role: ${a.role}`);
  });
}

main().catch(console.error).finally(() => prisma.$disconnect());
