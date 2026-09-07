import ProductGrid from '../../components/ProductGrid';
import { prisma } from '../../lib/prisma';
import { Zap, Shield, ChevronRight } from 'lucide-react';
import Link from 'next/link';

export const dynamic = 'force-dynamic';

export default async function Products() {
  const hardwareItems = await prisma.hardware.findMany({
    orderBy: { createdAt: 'desc' }
  });

  return (
    <div className="bg-[#FFFFFF] dark:bg-[#0a0a0a] min-h-screen pt-24 pb-24 border-t border-[#E2E2E7] dark:border-neutral-800">
      <div className="max-w-[1024px] mx-auto px-10">
        
        {/* Header */}
        <div className="text-center max-w-[800px] mx-auto mb-16">
          <h1 className="text-4xl md:text-[48px] font-bold text-[#111111] dark:text-white mb-6 tracking-[-0.02em] leading-tight">Hardware & Accessories</h1>
          <p className="text-xl text-[#666666] mb-8 leading-[1.6]">Everything you need to set up your billing counter and kitchen.</p>
          <div className="flex flex-wrap justify-center gap-4 text-sm text-[#555555] dark:text-neutral-400 font-semibold pb-8 border-b border-[#E2E2E7] dark:border-neutral-800">
            <div className="flex items-center bg-[#FAFAFA] dark:bg-[#111111] px-4 py-2 border border-[#E2E2E7] dark:border-neutral-800 rounded-full">
              <Zap className="w-4 h-4 text-[#F59E0B] mr-2" /> Same Day Delivery
            </div>
            <div className="flex items-center bg-[#FAFAFA] dark:bg-[#111111] px-4 py-2 border border-[#E2E2E7] dark:border-neutral-800 rounded-full">
              <Shield className="w-4 h-4 text-[#3B82F6] mr-2" /> 1 Year Warranty
            </div>
          </div>
        </div>

        {/* Products Grid */}
        <ProductGrid products={hardwareItems} />

        {/* Promo Banner */}
        <div className="mt-24 bg-[#FAFAFA] dark:bg-[#111111] rounded-[24px] p-8 md:p-12 border border-[#E2E2E7] dark:border-neutral-800 flex flex-col md:flex-row items-center justify-between shadow-sm relative overflow-hidden">
          <div className="absolute top-0 right-0 w-64 h-64 bg-[#E53935] opacity-[0.03] rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
          <div className="mb-6 md:mb-0 md:mr-8 relative z-10">
            <h3 className="text-2xl md:text-[32px] tracking-[-0.02em] font-bold text-[#111111] dark:text-white mb-2 leading-tight">Looking for a complete setup?</h3>
            <p className="text-[#666666] text-lg leading-[1.6]">Save up to 15% when you buy our hardware + software combo packages.</p>
          </div>
          <Link href="/pricing?tab=combo" className="bg-[#E53935] text-white px-[24px] py-[10px] rounded-md font-semibold text-sm hover:opacity-90 transition shadow-[0_4px_12px_rgba(229,57,53,0.2)] flex items-center space-x-2 whitespace-nowrap relative z-10">
            <span>View Combo Packages</span>
            <ChevronRight size={16} />
          </Link>
        </div>

      </div>
    </div>
  );
}
