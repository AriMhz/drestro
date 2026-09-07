import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";

export async function GET() {
  try {
    // 1. Seed Plans
    const plansData = [
      {
        name: 'Free',
        priceYearly: 0,
        priceHalfYearly: 0,
        description: 'For individuals & starters looking to digitize their kitchen.',
        buttonText: 'Start for Free',
        buttonVariant: 'outline',
        features: ['Limited Dishes (100 max)', '10 Categories', '5 Add-ons', 'Daybook', 'Basic Income & Expenses'],
        notIncluded: ['No Reservation Orders', 'No Customers Orders', 'No Sales & Support'],
      },
      {
        name: 'Basic',
        priceYearly: 12000,
        priceHalfYearly: 7000,
        description: 'Perfect for tracking order management.',
        buttonText: 'Start 14 Days Trial',
        buttonVariant: 'outline',
        features: ['Up to 5 Users Login', 'Up to 20 Tables', 'Up to 500 Dishes', 'Dine-in & Delivery Ordering', 'Digital QR Menu', 'KOT/BOT Management'],
        notIncluded: ['Inventory Management', 'Accounting System', 'Custom User Roles'],
      },
      {
        name: 'Premium',
        isPopular: true,
        oldPriceYearly: 30000,
        oldPriceHalfYearly: 18000,
        priceYearly: 24000,
        priceHalfYearly: 14000,
        description: 'Most Popular. Perfect for growing restaurants looking to scale.',
        buttonText: 'Start 14 Days Trial',
        buttonVariant: 'primary',
        features: ['Everything in Basic', 'Inventory Management', 'Accounting System', 'CRM & Loyalty Points', 'Up to 24 Users Login', 'Up to 50 Tables', 'Up to 1000 Dishes', 'Online Delivery Portal', 'Low Stock Alerts', 'Custom User Roles', 'Daybook Closing Email Alerts'],
        notIncluded: ['Multi-Outlet Management', 'eBilling Setup'],
      },
      {
        name: 'Platinum',
        priceYearly: 60000,
        priceHalfYearly: 35000,
        description: 'For large sized teams with multi kitchen department and service.',
        buttonText: 'Start 14 Days Trial',
        buttonVariant: 'outline',
        features: ['Everything in Premium', 'Multi-Outlet Management', 'Unlimited Users', 'Unlimited Tables', 'Unlimited Dishes', 'Advanced Insights', 'Multi Location Inventory', 'eBilling Setup - No Charge', 'Custom Domain & Branding', 'Unlimited Activity Logs', '24/7 Priority Support'],
        notIncluded: [],
      }
    ];

    // Clear existing plans
    await prisma.plan.deleteMany();

    for (const p of plansData) {
      await prisma.plan.create({
        data: {
          name: p.name,
          description: p.description,
          priceYearly: p.priceYearly,
          priceHalfYearly: p.priceHalfYearly,
          oldPriceYearly: p.oldPriceYearly,
          oldPriceHalfYearly: p.oldPriceHalfYearly,
          isPopular: p.isPopular || false,
          buttonText: p.buttonText,
          buttonVariant: p.buttonVariant,
          features: {
            create: p.features.map(f => ({ text: f }))
          },
          notIncluded: {
            create: p.notIncluded.map(f => ({ text: f }))
          }
        }
      });
    }

    // 2. Seed Comparison Table JSON
    const compareFeatures = [
      { category: 'Orders', name: 'Dine In Orders', free: '100/month', basic: 'Unlimited', premium: 'Unlimited', plat: 'Unlimited' },
      { category: 'Orders', name: 'Delivery Orders', free: '100/month', basic: 'Unlimited', premium: 'Unlimited', plat: 'Unlimited' },
      { category: 'Orders', name: 'Reservation Orders', free: false, basic: true, premium: true, plat: true },
      { category: 'Orders', name: 'Digital Menu Orders', free: false, basic: true, premium: true, plat: true },
      { category: 'Orders', name: 'KOT History', free: false, basic: true, premium: true, plat: true },
      
      { category: 'Menu', name: 'Dishes', free: '100', basic: '500', premium: '1000', plat: 'Unlimited' },
      { category: 'Menu', name: 'Categories', free: '10', basic: '20', premium: '100', plat: '100' },
      { category: 'Menu', name: 'Add-Ons', free: '5', basic: '50', premium: '250', plat: '500' },
      { category: 'Menu', name: 'Submenus', free: '3', basic: '3', premium: '20', plat: '50' },
      { category: 'Menu', name: 'Menusets', free: '1', basic: '1', premium: '5', plat: '10' },
  
      { category: 'Table & Spaces', name: 'Tables', free: '10', basic: '20', premium: '50', plat: 'Unlimited' },
      { category: 'Table & Spaces', name: 'Spaces', free: '5', basic: '10', premium: '50', plat: 'Unlimited' },
  
      { category: 'Finance', name: 'Day Book', free: true, basic: true, premium: true, plat: true },
      { category: 'Finance', name: 'Income & Expense', free: '5/day', basic: '50/day', premium: 'Unlimited', plat: 'Unlimited' },
      { category: 'Finance', name: 'Payment In & Out', free: '5/day', basic: '50/day', premium: 'Unlimited', plat: 'Unlimited' },
      { category: 'Finance', name: 'Payment Accounts', free: '3', basic: '5', premium: '20', plat: '40' },
      { category: 'Finance', name: 'Reports', free: 'Basic', basic: 'Basic', premium: 'Advanced', plat: 'Advanced' },
  
      { category: 'Inventory', name: 'Stock Listings', free: false, basic: '100', premium: '1000', plat: 'Unlimited' },
      { category: 'Inventory', name: 'Automatic Inventory', free: false, basic: false, premium: true, plat: true },
  
      { category: 'Parties', name: 'Staff', free: '2', basic: '5', premium: '24', plat: 'Unlimited' },
  
      { category: 'Other', name: 'Support', free: 'Community', basic: 'Business Hrs', premium: 'Business Hrs', plat: '24/7 Premium' },
      { category: 'Other', name: 'Storage', free: '100 MB', basic: '10 GB', premium: '250 GB', plat: 'Unlimited' },
      { category: 'Other', name: 'Activity Logs', free: '15 days', basic: '30 days', premium: '180 days', plat: '365 days' },
    ];

    await prisma.siteSetting.upsert({
      where: { key: 'comparison_table' },
      update: { value: JSON.stringify(compareFeatures) },
      create: { key: 'comparison_table', value: JSON.stringify(compareFeatures) }
    });

    return NextResponse.json({ message: "Seed successful!" });
  } catch (error: any) {
    console.error("Seed error:", error);
    return NextResponse.json({ error: error.message }, { status: 500 });
  }
}
