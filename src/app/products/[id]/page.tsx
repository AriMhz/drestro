import { notFound } from 'next/navigation';
import { prisma } from '@/src/lib/prisma';
import ProductDetailClient from '@/src/components/ProductDetailClient';
import Link from 'next/link';
import { ArrowLeft, CheckCircle2 } from 'lucide-react';
import ProductGrid from '@/src/components/ProductGrid';

const FALLBACK_PRODUCTS = [
  {
    id: '1',
    name: 'Thermal Printer - xPrinter TQ-80',
    description: 'High-quality Thermal Paper Roll designed for smooth and clear printing in billing and receipt printers. With 80m length, 80mm width, and 70 gsm thickness, it ensures durability, crisp print quality, and long-lasting results. Perfect for restaurants, retail stores, supermarkets, and all businesses using thermal printers.',
    oldPrice: '13,500',
    price: 12500,
    save: '1000',
    image: '/images/products/printer.png'
  },
  {
    id: '2',
    name: 'Thermal Paper Roll (80m)',
    description: 'High-quality Thermal Paper Roll designed for smooth and clear printing in billing and receipt printers. With 80m length, 80mm width, and 70 gsm thickness, it ensures durability, crisp print quality, and long-lasting results. Perfect for restaurants, retail stores, supermarkets, and all businesses using thermal printers.',
    oldPrice: '200',
    price: 150,
    save: '50',
    image: '/images/products/paper.png'
  },
  {
    id: '3',
    name: 'Nizi Power Backup (4hrs)',
    description: 'Nizi Mini Router Powerbank is designed to provide backup power to electronic devices during power outages.',
    oldPrice: '2,500',
    price: 2000,
    save: '500',
    image: '/images/products/power_bank.png'
  },
  {
    id: '4',
    name: 'Nizi Dynamic QR',
    description: 'Discover the key specs, customization options, and warranty details of the Nizi POS B30 - designed to fit your business needs perfectly.',
    price: 6000,
    image: '/images/products/qr.png'
  },
  {
    id: '5',
    name: 'Ethernet Wire - 5 Mtr',
    description: 'High-Performance Category 6 Ethernet Cable designed for fast, reliable, and secure network connectivity. Built with durable materials and gold-plated copper connectors, it ensures maximum conductivity, minimal data loss, and long-term performance.',
    price: 170,
    image: '/images/products/ethernet.png'
  },
  {
    id: '6',
    name: 'Nizi Power Backup (8hrs)',
    description: 'Nizi Mini Router Powerbank is designed to provide backup power to electronic devices during power outages.',
    oldPrice: '3,000',
    price: 2500,
    save: '500',
    image: '/images/products/power_bank.png'
  }
];

export const dynamic = 'force-dynamic';

export default async function ProductDetailsPage({ params }: { params: Promise<{ id: string }> }) {
  const resolvedParams = await params;
  const { id } = resolvedParams;

  let product: any = null;
  
  try {
    // Try to find in database
    product = await prisma.hardware.findUnique({
      where: { id: id }
    });
  } catch (error) {
    // Ignore error if id is not a valid UUID
  }

  // If not found in DB, search fallbacks
  if (!product) {
    const fallback = FALLBACK_PRODUCTS.find(p => p.id === id);
    if (fallback) {
      product = {
        ...fallback,
        imageUrl: fallback.image,
        stockStatus: 'In Stock'
      };
    }
  }

  if (!product) {
    notFound();
  }

  // Get related products (just take the first 4 for now)
  let relatedProducts = [];
  try {
    const dbRelated = await prisma.hardware.findMany({ take: 4 });
    if (dbRelated.length > 0) {
      relatedProducts = dbRelated;
    } else {
      relatedProducts = FALLBACK_PRODUCTS.slice(0, 4);
    }
  } catch (error) {
    relatedProducts = FALLBACK_PRODUCTS.slice(0, 4);
  }

  const clientProductData = {
    id: product.id,
    name: product.name,
    price: typeof product.price === 'number' ? product.price : parseFloat(product.price?.toString().replace(/,/g, '') || '0'),
    image: product.imageUrl || product.image || '/images/products/printer.png'
  };

  return (
    <div className="bg-[#FFFFFF] dark:bg-[#0a0a0a] min-h-screen pt-24 pb-24">
      <div className="max-w-[1200px] mx-auto px-6 lg:px-10">
        
        {/* Back Link */}
        <div className="mb-8">
          <Link href="/products" className="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-[#E53935] transition-colors">
            <ArrowLeft className="w-4 h-4 mr-2" /> Back to Products
          </Link>
        </div>

        <div className="flex flex-col lg:flex-row gap-12 lg:gap-24 mb-24">
          {/* Left Column: Image Gallery */}
          <div className="lg:w-1/2 flex flex-col gap-6">
            <div className="bg-[#F8F9FA] dark:bg-[#111] rounded-[32px] p-12 flex items-center justify-center border border-gray-100 dark:border-neutral-800 h-[400px] lg:h-[600px]">
              <img 
                src={clientProductData.image} 
                alt={product.name} 
                className="max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal"
              />
            </div>
          </div>

          {/* Right Column: Product Info */}
          <div className="lg:w-1/2 flex flex-col justify-center">
            
            <h1 className="text-3xl lg:text-4xl font-black text-gray-900 dark:text-white mb-4 leading-tight tracking-tight">
              {product.name}
            </h1>
            
            {product.stockStatus !== 'Out of Stock' && (
              <div className="mb-6 inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-wider">
                <CheckCircle2 className="w-3.5 h-3.5" /> In Stock
              </div>
            )}

            <div className="flex items-end gap-3 mb-8">
              <span className="text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                Rs. {typeof product.price === 'number' ? product.price.toLocaleString() : product.price}
              </span>
              {product.oldPrice && (
                <span className="text-lg font-bold text-gray-400 line-through mb-1">
                  Rs. {product.oldPrice}
                </span>
              )}
            </div>

            {/* Interactive Client Component */}
            <ProductDetailClient product={clientProductData} />

            <div className="mt-8">
              <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-4">Description</h3>
              <div className="prose prose-sm sm:prose-base dark:prose-invert max-w-none text-gray-600 dark:text-neutral-400 whitespace-pre-wrap leading-relaxed">
                {product.description}
              </div>
            </div>

          </div>
        </div>

        {/* Related Products Section */}
        <div className="pt-16 border-t border-gray-100 dark:border-neutral-800">
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-10">Related Products</h2>
          <ProductGrid products={relatedProducts} />
        </div>

      </div>
    </div>
  );
}
