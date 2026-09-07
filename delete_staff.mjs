import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log("Looking for staff@drestro.com in AdminStaff table...");
  try {
    const staff = await prisma.adminStaff.findUnique({
      where: { email: "staff@drestro.com" }
    });

    if (!staff) {
      console.log("staff@drestro.com not found in the AdminStaff table!");
      return;
    }

    console.log(`Found staff: ${staff.email} with role: ${staff.role}. Deleting...`);
    
    await prisma.adminStaff.delete({
      where: { email: "staff@drestro.com" }
    });
    
    console.log("Successfully deleted staff@drestro.com!");
  } catch (error) {
    console.error("Error deleting staff:", error);
  } finally {
    await prisma.$disconnect();
  }
}

main();
