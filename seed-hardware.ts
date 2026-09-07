import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

const FALLBACK_PRODUCTS = [
  {
    name: 'Thermal Printer - xPrinter TQ-80',
    description: 'High-quality Thermal Paper Roll designed for smooth and clear printing in billing and receipt printers. With 80m length, 80mm width, and 70 gsm thickness, it ensures durability, crisp print quality, and long-lasting results. Perfect for restaurants, retail stores, supermarkets, and all businesses using thermal printers.',
    price: 12500,
    imageUrl: '/images/products/printer.png',
    stockStatus: 'In Stock'
  },
  {
    name: 'Thermal Paper Roll (80m)',
    description: 'High-quality Thermal Paper Roll designed for smooth and clear printing in billing and receipt printers. With 80m length, 80mm width, and 70 gsm thickness, it ensures durability, crisp print quality, and long-lasting results. Perfect for restaurants, retail stores, supermarkets, and all businesses using thermal printers.',
    price: 150,
    imageUrl: '/images/products/paper.png',
    stockStatus: 'In Stock'
  },
  {
    name: 'Nizi Power Backup (4hrs)',
    description: 'Nizi Mini Router Powerbank is designed to provide backup power to electronic devices during power outages.',
    price: 2000,
    imageUrl: '/images/products/power_bank.png',
    stockStatus: 'In Stock'
  },
  {
    name: 'Nizi Dynamic QR',
    description: 'Discover the key specs, customization options, and warranty details of the Nizi POS B30 - designed to fit your business needs perfectly.',
    price: 6000,
    imageUrl: '/images/products/qr.png',
    stockStatus: 'In Stock'
  },
  {
    name: 'Ethernet Wire - 5 Mtr',
    description: 'High-Performance Category 6 Ethernet Cable designed for fast, reliable, and secure network connectivity. Built with durable materials and gold-plated copper connectors, it ensures maximum conductivity, minimal data loss, and long-term performance.',
    price: 170,
    imageUrl: '/images/products/ethernet.png',
    stockStatus: 'In Stock'
  },
  {
    name: 'Nizi Power Backup (8hrs)',
    description: 'Nizi Mini Router Powerbank is designed to provide backup power to electronic devices during power outages.',
    price: 2500,
    imageUrl: '/images/products/power_bank.png',
    stockStatus: 'In Stock'
  }
];

async function main() {
  console.log('Checking database...');
  const existingCount = await prisma.hardware.count();
  
  if (existingCount === 0) {
    console.log('Database is empty. Seeding hardware products...');
    for (const product of FALLBACK_PRODUCTS) {
      await prisma.hardware.create({
        data: product
      });
      console.log(`Created: ${product.name}`);
    }
    console.log('Seeding finished.');
  } else {
    console.log(`Database already has ${existingCount} products. Skipping seed.`);
  }
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
