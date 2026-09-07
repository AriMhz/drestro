import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log("Seeding Combo Packages...");

  // Delete existing combos to avoid duplicates if they ran it multiple times
  await prisma.comboPackage.deleteMany({});

  const combos = [
    {
      name: 'Premium Combo Package',
      subtitle: 'Get Premium plan + 1 professional printer for 1 year at a special price.',
      price: 35000,
      originalPrice: 40000,
      savings: 5000,
      features: {
        create: [
          { text: 'Premium plan for 1 year' },
          { text: '1pcs of Printer-TQ-80' }
        ]
      }
    },
    {
      name: 'Platinum Combo Package',
      subtitle: 'Get Platinum plan + 2 professional printers for 1 year at a special price.',
      price: 80000,
      originalPrice: 90000,
      savings: 10000,
      features: {
        create: [
          { text: 'Platinum plan for 1 year' },
          { text: '2 pcs of Printer-TQ-80' }
        ]
      }
    }
  ];

  for (const combo of combos) {
    await prisma.comboPackage.create({
      data: combo
    });
    console.log(`Created combo: ${combo.name}`);
  }

  console.log("Combo packages seeded successfully!");
}

main()
  .catch(e => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
