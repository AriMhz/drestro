"use client";

import { ArrowRight, ShoppingCart } from 'lucide-react';
import Link from 'next/link';
import { useCart } from '../context/CartContext';

export default function ProductGrid({ products }: { products: any[] }) {
  const { addToCart } = useCart();
  
  // If the database is empty, we show the featured fallback products
  const displayProducts = products.length > 0 ? products : [
    {
      id: '1',
      name: 'Thermal Printer - xPrinter TQ-80',
      description: 'Upgrade your billing experience with a sleek, high-performance printer built for retail stores, restaurants, supermarkets, and more.',
      oldPrice: '13,500',
      price: 12500,
      save: '1000',
      image: '/images/products/printer.png'
    },
    {
      id: '2',
      name: 'Thermal Paper Roll (80m)',
      description: 'Print variable data on-demand roll labels for direct thermal, thermal transfer, and inkjet roll printers.',
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

  const handleBuyNow = (product: any) => {
    // Parse price cleanly, removing any commas if it's a string
    const priceRaw = typeof product.price === 'string' ? product.price.replace(/,/g, '') : product.price;
    const priceNum = parseFloat(priceRaw) || 0;
    
    addToCart({
      id: product.id || product.name, // Use name as fallback ID if none exists
      name: product.name,
      price: priceNum,
      image: product.image || product.imageUrl || '/images/products/printer.png',
      quantity: 1
    });
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      {displayProducts.map((product, idx) => (
        <div key={product.id || idx} className="bg-white dark:bg-[#0a0a0a] border border-[#E2E2E7] dark:border-neutral-800 rounded-3xl p-6 relative group overflow-hidden shadow-[0_4px_12px_rgba(0,0,0,0.02)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] transition-all duration-500 h-[380px] flex flex-col hover:-translate-y-1">
          
          {/* Enhanced Pill Save Badge */}
          {(product.save || (product.mrp && product.mrp > product.price)) && (
            <div className="absolute top-4 right-4 bg-emerald-500 text-[#111111] dark:text-white text-[10px] font-black px-3 py-1.5 rounded-full z-20 shadow-sm uppercase tracking-widest flex items-center gap-1">
              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              Save Rs.{product.mrp && product.mrp > product.price ? (product.mrp - product.price).toLocaleString() : product.save}
            </div>
          )}

          {/* Premium Image Container with Soft Background */}
          <div className="flex-shrink-0 h-[160px] bg-slate-50/50 rounded-2xl flex items-center justify-center mb-6 p-4 group-hover:bg-slate-100/50 transition-colors duration-500">
            <img src={product.image || product.imageUrl || '/images/products/printer.png'} alt={product.name} className="max-h-[120px] object-contain group-hover:scale-105 transition-transform duration-500" />
          </div>

          {/* Upgraded Typography Details */}
          <div className="flex flex-col flex-grow px-2">
            <h3 className="text-[17px] font-black text-[#111111] dark:text-white mb-2 leading-snug">{product.name}</h3>
            <p className="text-[#666666] text-[13px] leading-relaxed line-clamp-2 mb-4">{product.description}</p>
            <div className="mt-auto flex items-end gap-2.5">
              <span className="text-2xl font-black text-[#111111] dark:text-white tracking-tight">Rs.{typeof product.price === 'number' ? product.price.toLocaleString() : product.price}</span>
              {(product.mrp || product.oldPrice) && (
                <span className="text-[14px] font-bold text-[#999999] line-through mb-[3px]">Rs.{product.mrp ? product.mrp.toLocaleString() : product.oldPrice}</span>
              )}
            </div>
          </div>

          {/* Ultra-Premium Glassmorphism Overlay */}
          <div className="absolute inset-0 bg-white dark:bg-[#0a0a0a]/70 backdrop-blur-[8px] opacity-0 group-hover:opacity-100 transition-all duration-500 z-10 flex flex-col items-center justify-center gap-4 rounded-3xl scale-95 group-hover:scale-100">
            <button onClick={() => handleBuyNow(product)} className="bg-[#E53935] text-[#111111] dark:text-white text-[14px] font-black px-8 py-3.5 rounded-full hover:bg-red-700 transition-all transform hover:scale-105 flex items-center gap-2 shadow-[0_8px_20px_rgba(229,57,53,0.3)]">
              Add to Cart
              <ShoppingCart size={18} />
            </button>
            <Link href={`/products/${product.id || product.name}`} className="bg-white dark:bg-[#0a0a0a] border-2 border-slate-200 text-slate-700 text-[14px] font-bold px-8 py-3.5 rounded-full hover:bg-slate-50 hover:border-slate-300 hover:text-[#111111] dark:text-white transition-all flex items-center gap-2 shadow-sm">
              Know More
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>

        </div>
      ))}
    </div>
  );
}
