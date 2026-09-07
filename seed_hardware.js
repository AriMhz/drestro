import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

const products = [
  {
    name: 'Thermal Printer - xPrinter TQ-80',
    description: 'Upgrade your billing experience with a sleek, high-performance printer built for retail stores, restaurants, supermarkets, and more.',
    price: 12500,
    imageUrl: '/images/products/printer.png',
    stockStatus: 'In Stock'
  },
  {
    name: 'Thermal Paper Roll (80m)',
    description: 'Print variable data on-demand roll labels for direct thermal, thermal transfer, and inkjet roll printers.',
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
  console.log('Clearing existing hardware...');
  await prisma.hardware.deleteMany();
  
  console.log('Seeding dummy hardware products...');
  for (const product of products) {
    await prisma.hardware.create({
      data: product
    });
    console.log(`Created: ${product.name}`);
  }
  console.log('Done!');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
