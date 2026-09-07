const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
  const restaurants = await prisma.restaurant.findMany();
  for (const r of restaurants) {
    if (!r.slug) {
      // Create a slug from the name
      let slug = r.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
      if (!slug) slug = 'restaurant-' + r.id.substring(0, 4);
      
      try {
        await prisma.restaurant.update({
          where: { id: r.id },
          data: { slug }
        });
        console.log(`Updated ${r.name} with slug: ${slug}`);
      } catch (e) {
        // If slug exists, add random string
        const uniqueSlug = slug + '-' + Math.floor(Math.random() * 1000);
        await prisma.restaurant.update({
          where: { id: r.id },
          data: { slug: uniqueSlug }
        });
        console.log(`Updated ${r.name} with unique slug: ${uniqueSlug}`);
      }
    }
  }
}

main().catch(console.error).finally(() => prisma.$disconnect());
