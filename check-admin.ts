import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
  console.log("Checking for existing admins...");
  const admins = await prisma.adminStaff.findMany();
  
  if (admins.length > 0) {
    console.log("Existing admins found:");
    admins.forEach(a => console.log(`- ${a.email} (${a.role})`));
  } else {
    console.log("No admins found. Creating default Superadmin...");
  }

  const hashedPassword = await bcrypt.hash('password123', 10);
  
  await prisma.adminStaff.upsert({
    where: { email: 'admin@drestro.com' },
    update: { password: hashedPassword, role: 'SUPERADMIN' },
    create: { email: 'admin@drestro.com', password: hashedPassword, role: 'SUPERADMIN', permissions: '[]' }
  });
  
  console.log("\n✅ Superadmin account is ready!");
  console.log("Email: admin@drestro.com");
  console.log("Password: password123");
}

main()
  .catch(console.error)
  .finally(() => prisma.$disconnect());
