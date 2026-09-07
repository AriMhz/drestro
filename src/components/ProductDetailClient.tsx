"use client";

import React, { useState } from 'react';
import { Minus, Plus, ShoppingBag } from 'lucide-react';
import { useCart } from '../context/CartContext';

interface ProductDetailClientProps {
  product: {
    id: string;
    name: string;
    price: number | string;
    image: string;
  };
}

export default function ProductDetailClient({ product }: ProductDetailClientProps) {
  const [quantity, setQuantity] = useState(1);
  const { addToCart } = useCart();

  const handleDecrease = () => {
    if (quantity > 1) {
      setQuantity(q => q - 1);
    }
  };

  const handleIncrease = () => {
    setQuantity(q => q + 1);
  };

  const handleOrderNow = () => {
    const priceRaw = typeof product.price === 'string' ? product.price.replace(/,/g, '') : product.price;
    const priceNum = parseFloat(priceRaw as string) || 0;

    addToCart({
      id: product.id,
      name: product.name,
      price: priceNum,
      image: product.image,
      quantity: quantity
    });
  };

  return (
    <div className="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 border-t border-b border-gray-100 dark:border-neutral-800 py-6">
      <div className="flex flex-col gap-2">
        <label className="text-sm font-semibold text-gray-700 dark:text-neutral-300">Quantity</label>
        <div className="flex items-center bg-white dark:bg-[#111] rounded-lg border border-gray-200 dark:border-neutral-700">
          <button 
            onClick={handleDecrease} 
            className="p-3 text-slate-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-[#111111] dark:text-white transition-colors"
          >
            <Minus className="w-4 h-4" />
          </button>
          <span className="w-12 text-center text-sm font-semibold text-gray-900 dark:text-[#111111] dark:text-white">{quantity}</span>
          <button 
            onClick={handleIncrease} 
            className="p-3 text-slate-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-[#111111] dark:text-white transition-colors"
          >
            <Plus className="w-4 h-4" />
          </button>
        </div>
      </div>
      
      <div className="flex-1 w-full sm:w-auto self-end">
        <button 
          onClick={handleOrderNow}
          className="w-full sm:w-auto bg-[#E53935] hover:bg-red-700 text-[#111111] dark:text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-[0_8px_20px_rgba(229,57,53,0.3)] active:scale-[0.98] flex items-center justify-center gap-2 h-[46px]"
        >
          <ShoppingBag className="w-5 h-5" /> Order Now
        </button>
      </div>
    </div>
  );
}
