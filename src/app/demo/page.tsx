"use client";

import React, { useState } from 'react';
import { motion } from 'motion/react';
import { CheckCircle2, Calendar, Clock, Video } from 'lucide-react';

export default function Demo() {
  const [formStatus, setFormStatus] = useState<'idle' | 'submitting' | 'success'>('idle');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setFormStatus('submitting');
    setTimeout(() => {
      setFormStatus('success');
    }, 1500);
  };

  return (
    <div className="bg-gray-50 min-h-screen py-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div className="grid lg:grid-cols-2 gap-16 items-center">
          {/* Left Column - Info */}
          <div>
            <h1 className="text-4xl md:text-5xl font-bold text-[#111111] dark:text-white mb-6 leading-tight">See how DRestro can transform your business.</h1>
            <p className="text-xl text-gray-600 dark:text-neutral-400 mb-10">Book a free, personalized 30-minute demo with our product experts.</p>
            
            <div className="space-y-8">
              <div className="flex">
                <div className="shrink-0 mt-1 mr-4 w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                  <Video className="text-[#E53935]" size={24} />
                </div>
                <div>
                  <h3 className="text-xl font-bold text-[#111111] dark:text-white mb-2">Live 1-on-1 Tour</h3>
                  <p className="text-gray-600 dark:text-neutral-400">We'll walk you through the software on a live video call, tailored to your restaurant type.</p>
                </div>
              </div>
              
              <div className="flex">
                <div className="shrink-0 mt-1 mr-4 w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                  <Calendar className="text-[#E53935]" size={24} />
                </div>
                <div>
                  <h3 className="text-xl font-bold text-[#111111] dark:text-white mb-2">Discuss your needs</h3>
                  <p className="text-gray-600 dark:text-neutral-400">Got a specific workflow? Multiple outlets? We'll show you exactly how to set it up.</p>
                </div>
              </div>
              <div>
                <p className="font-bold text-[#111111] dark:text-white">Priya Sharma</p>
                <p className="text-sm text-gray-500">Product Specialist</p>
                <p className="text-sm italic text-gray-400 mt-1">"I look forward to showing you around!"</p>
              </div>
            </div>
          </div>

          {/* Right Column - Form */}
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-white dark:bg-[#0a0a0a] rounded-3xl p-8 md:p-10 shadow-xl border border-gray-100 relative overflow-hidden"
          >
            {formStatus === 'success' ? (
              <div className="absolute inset-0 bg-white dark:bg-[#0a0a0a] flex flex-col items-center justify-center p-8 text-center z-10 transition-all duration-500">
                <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
                  <CheckCircle2 className="w-10 h-10 text-green-500" />
                </div>
                <h3 className="text-3xl font-bold text-[#111111] dark:text-white mb-4">Demo Requested!</h3>
                <p className="text-gray-600 dark:text-neutral-400 text-lg mb-8">Thanks! We've received your request. Priya will reach out via WhatsApp/Phone shortly to confirm a time.</p>
                <button 
                  onClick={() => setFormStatus('idle')}
                  className="bg-gray-100 text-[#111111] dark:text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition"
                >
                  Book another
                </button>
              </div>
            ) : null}

            <h2 className="text-2xl font-bold text-[#111111] dark:text-white mb-6">Schedule your Demo</h2>
            
            <form onSubmit={handleSubmit} className="space-y-5">
              <div className="grid grid-cols-2 gap-5">
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">First Name *</label>
                  <input required type="text" className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#E53935] focus:border-transparent outline-none transition bg-gray-50 focus:bg-white dark:bg-[#0a0a0a]" placeholder="John" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Last Name *</label>
                  <input required type="text" className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#E53935] focus:border-transparent outline-none transition bg-gray-50 focus:bg-white dark:bg-[#0a0a0a]" placeholder="Doe" />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Phone Number *</label>
                <input required type="tel" className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#E53935] focus:border-transparent outline-none transition bg-gray-50 focus:bg-white dark:bg-[#0a0a0a]" placeholder="+977 Mobile Number" />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Restaurant Name *</label>
                <input required type="text" className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#E53935] focus:border-transparent outline-none transition bg-gray-50 focus:bg-white dark:bg-[#0a0a0a]" placeholder="The Tasty Bite" />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">City *</label>
                <input required type="text" className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#E53935] focus:border-transparent outline-none transition bg-gray-50 focus:bg-white dark:bg-[#0a0a0a]" placeholder="Kathmandu" />
              </div>

              <div className="pt-4">
                <button 
                  type="submit" 
                  disabled={formStatus === 'submitting'}
                  className="w-full bg-[#E53935] text-white py-4 rounded-xl font-bold text-lg hover:bg-red-700 transition shadow-sm disabled:opacity-70 flex items-center justify-center space-x-2"
                >
                  {formStatus === 'submitting' ? (
                     <div className="w-6 h-6 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                  ) : (
                    <>
                      <span>Book Free Demo</span>
                      <Calendar className="w-5 h-5 ml-2" />
                    </>
                  )}
                </button>
              </div>
              <p className="text-xs text-gray-400 text-center mt-4">By submitting, you agree to our Terms of Service & Privacy Policy.</p>
            </form>
          </motion.div>
        </div>
        
      </div>
    </div>
  );
}
