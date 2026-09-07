"use client";

import Link from 'next/link';
import { motion } from 'motion/react';
import { Clock, User } from 'lucide-react';

const posts = [
  {
    id: 1,
    title: "10 Benefits of Cloud-Based Restaurant POS in 2024",
    excerpt: "Discover why upgrading to a cloud-based point of sale system can drastically improve your restaurant operations, reduce costs, and increase employee efficiency.",
    category: "Software",
    date: "Oct 12, 2024",
    author: "Prabesh Sharma",
    readTime: "5 min read",
    image: "https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 2,
    title: "How QR Menu Ordering Increases Average Ticket Size",
    excerpt: "Restaurants using dynamic QR code menus see an average of 15% increase in order value. Here's exactly why psychological upselling works better digitally.",
    category: "Growth",
    date: "Sep 28, 2024",
    author: "Aakriti Thapa",
    readTime: "4 min read",
    image: "https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 3,
    title: "Mastering Inventory Management to Reduce Food Waste",
    excerpt: "Food waste is one of the biggest killers of restaurant profit margins. Learn how strict inventory tracking and recipe management can save thousands.",
    category: "Management",
    date: "Sep 15, 2024",
    author: "Bibek Karki",
    readTime: "7 min read",
    image: "https://images.unsplash.com/photo-1583337130417-3346a1be7dee?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 4,
    title: "The Ultimate Guide to Thermal Printers for Kitchens",
    excerpt: "Not all printers are made equal. In the heat and grease of a commercial kitchen, you need something robust. We compare top models.",
    category: "Hardware",
    date: "Aug 30, 2024",
    author: "Team DRestro",
    readTime: "3 min read",
    image: "https://images.unsplash.com/photo-1620987278429-ab178d6eb547?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 5,
    title: "How to Build a Loyalty Program that Actually Works",
    excerpt: "Points don't matter if customers don't care. Design a restaurant loyalty system that brings diners back week after week.",
    category: "Marketing",
    date: "Aug 10, 2024",
    author: "Aakriti Thapa",
    readTime: "6 min read",
    image: "https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 6,
    title: "Managing Multiple Branches? Centralize Your Data",
    excerpt: "Scaling from 1 to 5 outlets is the hardest jump. Learn how central kitchen management and unified reporting makes it possible.",
    category: "Management",
    date: "Jul 22, 2024",
    author: "Prabesh Sharma",
    readTime: "8 min read",
    image: "https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&q=80&w=800"
  }
];

export default function Blog() {
  return (
    <div className="bg-gray-50 min-h-screen pt-12 pb-24">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div className="text-center max-w-3xl mx-auto mb-16">
          <h1 className="text-4xl md:text-5xl font-bold text-[#111111] dark:text-white mb-6">Resources & Insights</h1>
          <p className="text-xl text-gray-600 dark:text-neutral-400">Grow your restaurant with our guides, hardware reviews, and management tips.</p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          {posts.map((post, i) => (
            <motion.div 
              key={post.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: i * 0.1 }}
              className="bg-white dark:bg-[#0a0a0a] rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow border border-gray-100 flex flex-col group"
            >
              <div className="relative h-56 overflow-hidden">
                <img 
                  src={post.image} 
                  alt={post.title} 
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
                <div className="absolute top-4 left-4 bg-white dark:bg-[#0a0a0a]/90 backdrop-blur text-[#E53935] text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide shadow-sm">
                  {post.category}
                </div>
              </div>
              
              <div className="p-6 flex flex-col flex-grow">
                <h3 className="text-xl font-bold text-[#111111] dark:text-white mb-3 group-hover:text-[#E53935] transition-colors line-clamp-2">
                  <a href="#">{post.title}</a>
                </h3>
                <p className="text-gray-600 dark:text-neutral-400 mb-6 flex-grow line-clamp-3">
                  {post.excerpt}
                </p>
                
                <div className="flex items-center justify-between pt-4 border-t border-gray-100 text-sm text-gray-500">
                  <div className="flex items-center space-x-2">
                    <User size={14} />
                    <span>{post.author}</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Clock size={14} />
                    <span>{post.readTime}</span>
                  </div>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
        
         <div className="mt-16 text-center">
            <button className="bg-white dark:bg-[#0a0a0a] text-[#111111] dark:text-white border-2 border-gray-200 px-8 py-4 rounded-lg font-medium text-lg hover:border-gray-900 transition">
              Load More Articles
            </button>
        </div>

      </div>
    </div>
  );
}
