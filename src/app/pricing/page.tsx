import { prisma } from "@/src/lib/prisma";
import PricingClient from "./PricingClient";

export const dynamic = "force-dynamic";

export default async function PricingPage({ searchParams }: { searchParams: Promise<{ tab?: string }> }) {
  const resolvedParams = await searchParams;
  const initialTab = resolvedParams.tab === 'combo' ? 'combo' : 'software';

  const plans = await prisma.plan.findMany({
    include: {
      features: true,
      notIncluded: true
    },
    orderBy: { priceYearly: 'asc' }
  });

  const combos = await prisma.comboPackage.findMany({
    include: {
      features: true
    },
    orderBy: { price: 'asc' }
  });

  // Map combos to match the structure PricingClient expects
  const mappedCombos = combos.map(combo => ({
    id: combo.id,
    name: combo.name,
    description: combo.subtitle,
    price: combo.price,
    oldPrice: combo.originalPrice,
    items: combo.features.map(f => ({ title: f.text, sub: '' }))
  }));

  const products = await prisma.hardware.findMany({
    orderBy: { createdAt: 'desc' }
  });

  // Map products to match PricingClient expects
  const mappedProducts = products.map(product => ({
    name: product.name,
    description: product.description,
    price: product.price.toLocaleString(),
    oldPrice: null, // You can add oldPrice to Hardware model later if needed
    save: null,
    image: product.imageUrl || '/images/products/printer.png'
  }));

  const siteSetting = await prisma.siteSetting.findUnique({
    where: { key: 'comparison_table' }
  });

  const compareFeatures = siteSetting ? JSON.parse(siteSetting.value) : [];

  return (
    <PricingClient 
      plans={plans} 
      combos={mappedCombos} 
      products={mappedProducts} 
      compareFeatures={compareFeatures} 
      initialTab={initialTab}
    />
  );
}
