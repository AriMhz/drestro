"use client";

import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import { useSiteSettings } from '../context/SiteSettingsContext';
import { X, Minus, Plus, Trash2, ShoppingCart, Phone, Truck, Mail } from 'lucide-react';
import Image from 'next/image';

export default function HardwareCheckout() {
  const { items, isCartOpen, setIsCartOpen, updateQuantity, removeFromCart, totalItems, totalPrice, clearCart } = useCart();
  const { whatsappNumber } = useSiteSettings();
  const [step, setStep] = useState<1 | 2>(1); // 1: Cart, 2: Shipping Details
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    phone: '',
    address: ''
  });

  if (!isCartOpen) return null;

  const generateOrderDetails = () => {
    const itemList = items.map(item => `- ${item.quantity}x ${item.name} (Rs. ${(item.price * item.quantity).toLocaleString()})`).join('\n');
    return `*Hardware Order Request*\n\n*Items:*\n${itemList}\n\n*Total: Rs. ${totalPrice.toLocaleString()}*`;
  };

  const handleCheckout = async () => {
    if (step === 1) {
      setStep(2);
      return;
    }

    if (!formData.fullName || !formData.email || !formData.phone || !formData.address) {
      // Basic validation, but we can also rely on disabled button
      return;
    }

    // Save order details to our database asynchronously
    try {
      fetch("/api/orders", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          fullName: formData.fullName,
          email: formData.email,
          phone: formData.phone,
          address: formData.address,
          items: items.map(i => ({
            id: i.id,
            name: i.name,
            price: i.price,
            quantity: i.quantity,
            image: i.image,
          })),
          totalPrice: totalPrice,
        }),
      }).catch(err => console.error("Error logging order:", err));
    } catch (e) {
      console.error("Failed to post order details:", e);
    }

    const orderDetails = generateOrderDetails();
    const shippingDetails = `*Shipping Details:*\nName: ${formData.fullName}\nEmail: ${formData.email}\nPhone: ${formData.phone}\nAddress: ${formData.address}`;
    const message = `Hi, I would like to place an order:\n\n${orderDetails}\n\n${shippingDetails}`;

    const encodedMessage = encodeURIComponent(message);
    window.open(`https://wa.me/${whatsappNumber}?text=${encodedMessage}`, '_blank');
    
    // Clear cart and close after a short delay to allow the window to open
    setTimeout(() => {
      clearCart();
      setIsCartOpen(false);
      setStep(1);
      setFormData({ fullName: '', email: '', phone: '', address: '' });
    }, 1000);
  };

  const isFormValid = formData.fullName.trim() !== '' && formData.email.trim() !== '' && formData.phone.trim() !== '' && formData.address.trim() !== '';

  return (
    <>
      {/* Floating Cart Button */}
      {!isCartOpen && totalItems > 0 && (
        <button 
          onClick={() => setIsCartOpen(true)}
          className="fixed bottom-8 right-8 z-[90] bg-[#E53935] text-[#111111] dark:text-white p-4 rounded-full shadow-[0_8px_20px_rgba(229,57,53,0.4)] hover:scale-110 transition-transform flex items-center justify-center animate-in zoom-in"
        >
          <ShoppingCart className="w-6 h-6" />
          <span className="absolute -top-2 -right-2 bg-white text-[#E53935] text-xs font-black w-6 h-6 rounded-full flex items-center justify-center shadow-sm">
            {totalItems}
          </span>
        </button>
      )}

      {isCartOpen && (
        <div className="fixed inset-0 z-[100] flex justify-end">
          {/* Backdrop */}
          <div 
            className="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
            onClick={() => { setIsCartOpen(false); setStep(1); }}
          />

      {/* Drawer */}
      <div className="relative w-full max-w-md bg-white dark:bg-[#0a0a0a] h-full flex flex-col shadow-2xl animate-in slide-in-from-right duration-300">
        
        {/* Header */}
        <div className="flex items-center justify-between p-6 border-b border-gray-200 dark:border-neutral-800">
          <h2 className="text-xl font-bold text-gray-900 dark:text-[#111111] dark:text-white flex items-center gap-2">
            {step === 1 ? (
              <><ShoppingCart className="w-5 h-5 text-[#E53935]" /> Your Cart</>
            ) : (
              <><Truck className="w-5 h-5 text-[#E53935]" /> Shipping Details</>
            )}
          </h2>
          <button 
            onClick={() => { setIsCartOpen(false); setStep(1); }}
            className="p-2 text-slate-400 dark:text-gray-500 hover:text-gray-900 dark:text-neutral-400 dark:hover:text-[#111111] dark:text-white rounded-full hover:bg-gray-100 dark:hover:bg-neutral-800 transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-6">
          {items.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-full text-center text-slate-400 dark:text-gray-500 dark:text-neutral-400">
              <ShoppingCart className="w-16 h-16 mb-4 opacity-20" />
              <p className="text-lg font-semibold text-gray-900 dark:text-[#111111] dark:text-white">Your cart is empty</p>
              <p className="mt-2 text-sm">Add some hardware to get started.</p>
              <button 
                onClick={() => setIsCartOpen(false)}
                className="mt-6 px-6 py-2 bg-gray-100 dark:bg-neutral-800 text-gray-900 dark:text-[#111111] dark:text-white font-medium rounded-full hover:bg-gray-200 dark:hover:bg-neutral-700 transition-colors"
              >
                Continue Shopping
              </button>
            </div>
          ) : step === 1 ? (
            // Step 1: Cart Items
            <div className="space-y-6">
              {items.map((item) => (
                <div key={item.id} className="flex gap-4 p-4 bg-gray-50 dark:bg-neutral-900/50 rounded-2xl border border-gray-100 dark:border-neutral-800">
                  <div className="w-20 h-20 bg-white dark:bg-[#111] rounded-xl flex items-center justify-center p-2 flex-shrink-0 border border-gray-100 dark:border-neutral-800">
                    <img src={item.image} alt={item.name} className="max-w-full max-h-full object-contain" />
                  </div>
                  <div className="flex-1 flex flex-col justify-between">
                    <div>
                      <h3 className="font-semibold text-gray-900 dark:text-[#111111] dark:text-white text-sm line-clamp-2 leading-snug">{item.name}</h3>
                      <p className="text-[#E53935] font-bold text-sm mt-1">Rs. {item.price.toLocaleString()}</p>
                    </div>
                    <div className="flex items-center justify-between mt-3">
                      <div className="flex items-center bg-white dark:bg-[#111] rounded-lg border border-gray-200 dark:border-neutral-700">
                        <button onClick={() => updateQuantity(item.id, item.quantity - 1)} className="p-1.5 text-slate-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-[#111111] dark:text-white transition-colors">
                          <Minus className="w-3.5 h-3.5" />
                        </button>
                        <span className="w-8 text-center text-sm font-semibold text-gray-900 dark:text-[#111111] dark:text-white">{item.quantity}</span>
                        <button onClick={() => updateQuantity(item.id, item.quantity + 1)} className="p-1.5 text-slate-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-[#111111] dark:text-white transition-colors">
                          <Plus className="w-3.5 h-3.5" />
                        </button>
                      </div>
                      <button onClick={() => removeFromCart(item.id)} className="p-1.5 text-slate-500 dark:text-gray-400 hover:text-red-500 transition-colors">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            // Step 2: Shipping Form
            <div className="space-y-6 animate-in fade-in">
              <div>
                <h3 className="text-sm font-bold text-gray-900 dark:text-[#111111] dark:text-white mb-1">Shipping Address</h3>
                <p className="text-xs text-slate-400 dark:text-gray-500 dark:text-neutral-400 mb-4">Please provide your delivery details</p>
                
                <div className="space-y-4">
                  <div>
                    <label className="text-xs font-medium text-gray-700 dark:text-neutral-300 flex items-center gap-1.5 mb-1.5">
                      <Phone className="w-3.5 h-3.5" /> Full Name
                    </label>
                    <input 
                      type="text" 
                      placeholder="Enter your full name" 
                      value={formData.fullName}
                      onChange={(e) => setFormData({...formData, fullName: e.target.value})}
                      className="w-full bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]"
                    />
                  </div>
                  
                  <div>
                    <label className="text-xs font-medium text-gray-700 dark:text-neutral-300 flex items-center gap-1.5 mb-1.5">
                      <Mail className="w-3.5 h-3.5" /> Email Address
                    </label>
                    <input 
                      type="email" 
                      placeholder="Enter your email address" 
                      value={formData.email}
                      onChange={(e) => setFormData({...formData, email: e.target.value})}
                      className="w-full bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]"
                    />
                  </div>

                  <div>
                    <label className="text-xs font-medium text-gray-700 dark:text-neutral-300 flex items-center gap-1.5 mb-1.5">
                      <Phone className="w-3.5 h-3.5" /> Phone Number
                    </label>
                    <input 
                      type="tel" 
                      placeholder="Enter your phone number" 
                      value={formData.phone}
                      onChange={(e) => setFormData({...formData, phone: e.target.value})}
                      className="w-full bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]"
                    />
                  </div>
                  
                  <div>
                    <label className="text-xs font-medium text-gray-700 dark:text-neutral-300 flex items-center gap-1.5 mb-1.5">
                      <Truck className="w-3.5 h-3.5" /> Address
                    </label>
                    <input 
                      type="text" 
                      placeholder="Enter your complete address" 
                      value={formData.address}
                      onChange={(e) => setFormData({...formData, address: e.target.value})}
                      className="w-full bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#E53935] focus:ring-1 focus:ring-[#E53935]"
                    />
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        {items.length > 0 && (
          <div className="p-6 bg-white dark:bg-[#0a0a0a] border-t border-gray-200 dark:border-neutral-800">
            {step === 1 ? (
              <div className="space-y-4">
                <div className="flex items-center justify-between font-bold text-lg text-gray-900 dark:text-[#111111] dark:text-white">
                  <span>Subtotal</span>
                  <span>Rs. {totalPrice.toLocaleString()}</span>
                </div>
                <button 
                  onClick={handleCheckout}
                  className="w-full bg-[#E53935] hover:bg-red-700 text-[#111111] dark:text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-[0_8px_20px_rgba(229,57,53,0.3)] active:scale-[0.98]"
                >
                  Proceed to Checkout
                </button>
              </div>
            ) : (
              <div className="space-y-4">
                <div className="flex items-center justify-end font-bold text-lg text-gray-900 dark:text-[#111111] dark:text-white mb-2">
                  <span>Total Rs. {totalPrice.toLocaleString()}</span>
                </div>
                <button 
                  onClick={handleCheckout}
                  disabled={!isFormValid}
                  className="w-full bg-[#128C7E] hover:bg-[#075E54] disabled:bg-gray-300 disabled:dark:bg-neutral-800 disabled:text-slate-400 dark:text-gray-500 disabled:shadow-none text-[#111111] dark:text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-[0_8px_20px_rgba(18,140,126,0.3)] active:scale-[0.98] flex items-center justify-center gap-2"
                >
                  <Phone className="w-5 h-5" /> Order Via WhatsApp
                </button>
                <button 
                  onClick={() => setStep(1)}
                  className="w-full bg-transparent hover:bg-gray-100 dark:hover:bg-neutral-800 text-gray-600 dark:text-neutral-400 font-bold py-3 px-6 rounded-xl transition-colors"
                >
                  Back to Cart
                </button>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
    )}
    </>
  );
}
