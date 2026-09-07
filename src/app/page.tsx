"use client";

import { useState, useEffect } from 'react';
import Link from 'next/link';
import ClientLogoMarquee from '@/src/components/ClientLogoMarquee';
import FaqSection from '@/src/components/FaqSection';
import { motion, AnimatePresence } from 'motion/react';
import { 
  CheckCircle2, 
  Store, 
  QrCode, 
  UtensilsCrossed, 
  TrendingUp, 
  Users,
  Smartphone,
  Server,
  ArrowRight,
  Monitor,
  Printer,
  FileCode2,
  Cpu,
  Plus,
  Trash2,
  Check,
  Wifi,
  Terminal,
  RotateCcw,
  Star,
  Tv as TvIcon
} from 'lucide-react';

const FADE_IN: any = {
  hidden: { opacity: 0, y: 30 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.6, ease: 'easeOut' } }
};

const STAGGER_CHILDREN = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.1 } }
};

interface CartItem {
  id: number;
  name: string;
  price: number;
  quantity: number;
}

interface KDSTicket {
  id: string;
  table: string;
  items: string[];
  status: 'NEW' | 'PREPARING' | 'READY';
  timestamp: string;
}

export default function Home() {
  // Hero Switcher Tabs
  const [activeTab, setActiveTab] = useState<'admin' | 'cashier' | 'waiter' | 'kds'>('admin');
  
  // Interactive POS Simulator State
  const [cart, setCart] = useState<CartItem[]>([]);
  const [tableNumber, setTableNumber] = useState<string>('T-04');
  const [kdsTickets, setKdsTickets] = useState<KDSTicket[]>([
    { id: '102', table: 'T-12', items: ['1x Ribeye Steak', '1x Lime Mint Soda'], status: 'PREPARING', timestamp: '12m ago' },
    { id: '103', table: 'T-02', items: ['2x Margherita Pizza'], status: 'READY', timestamp: '5m ago' }
  ]);
  const [lastTicketId, setLastTicketId] = useState<number>(103);
  const [receiptAnimation, setReceiptAnimation] = useState<boolean>(false);
  const [isKdsPulsing, setIsKdsPulsing] = useState<boolean>(false);
  const [testimonials, setTestimonials] = useState<any[]>([]);

  useEffect(() => {
    fetch('/api/testimonials')
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data) && data.length > 0) {
          setTestimonials(data);
        }
      })
      .catch(console.error);
  }, []);

  // Menu items list
  const MENU_ITEMS = [
    { id: 1, name: 'Margherita Pizza', price: 650, category: 'Mains' },
    { id: 2, name: 'Pasta Carbonara', price: 850, category: 'Mains' },
    { id: 3, name: 'MoMo (Chicken)', price: 350, category: 'Mains' },
    { id: 4, name: 'Himalayan Java Latte', price: 320, category: 'Drinks' },
    { id: 5, name: 'Lime Mint Soda', price: 220, category: 'Drinks' }
  ];

  // Add Item to Order
  const addToCart = (item: typeof MENU_ITEMS[0]) => {
    setCart((prevCart) => {
      const existing = prevCart.find((i) => i.id === item.id);
      if (existing) {
        return prevCart.map((i) => i.id === item.id ? { ...i, quantity: i.quantity + 1 } : i);
      }
      return [...prevCart, { ...item, quantity: 1 }];
    });
  };

  // Remove Item
  const removeFromCart = (id: number) => {
    setCart((prevCart) => prevCart.filter((i) => i.id !== id));
  };

  // Calculations
  const subtotal = cart.reduce((acc, item) => acc + item.price * item.quantity, 0);
  const serviceCharge = Math.round(subtotal * 0.1); // 10% Service Charge
  const vat = Math.round((subtotal + serviceCharge) * 0.13); // 13% VAT
  const total = subtotal + serviceCharge + vat;

  // Send Order to KDS
  const handleSendToKitchen = () => {
    if (cart.length === 0) return;
    const nextId = lastTicketId + 1;
    setLastTicketId(nextId);
    
    const newTicket: KDSTicket = {
      id: nextId.toString(),
      table: tableNumber,
      items: cart.map(item => `${item.quantity}x ${item.name}`),
      status: 'NEW',
      timestamp: 'Just now'
    };
    
    setKdsTickets(prev => [newTicket, ...prev]);
    setIsKdsPulsing(true);
    setTimeout(() => setIsKdsPulsing(false), 2000);
    
    // Animate thermal receipt print
    setReceiptAnimation(true);
    setTimeout(() => setReceiptAnimation(false), 1500);
    
    // Clear cart
    setCart([]);
  };

  // Toggle ticket status
  const handleToggleKdsStatus = (id: string) => {
    setKdsTickets(prev => prev.map(t => {
      if (t.id === id) {
        const nextStatus = t.status === 'NEW' ? 'PREPARING' : t.status === 'PREPARING' ? 'READY' : 'NEW';
        return { ...t, status: nextStatus };
      }
      return t;
    }));
  };

  // Reset demo
  const resetDemo = () => {
    setCart([]);
    setTableNumber('T-04');
    setKdsTickets([
      { id: '102', table: 'T-12', items: ['1x Ribeye Steak', '1x Lime Mint Soda'], status: 'PREPARING', timestamp: '12m ago' },
      { id: '103', table: 'T-02', items: ['2x Margherita Pizza'], status: 'READY', timestamp: '5m ago' }
    ]);
  };

  return (
    <div className="w-full bg-grid-pattern relative">
      
      {/* Background radial soft glows */}
      <div className="absolute top-[20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#E53935]/5 blur-[120px] pointer-events-none"></div>
      <div className="absolute top-[40%] right-[-10%] w-[600px] h-[600px] rounded-full bg-red-400/5 blur-[150px] pointer-events-none"></div>

      {/* 1. HERO SECTION */}
      <section className="relative bg-[#FFFFFF] dark:bg-[#0a0a0a] overflow-hidden pt-12 pb-24 border-b border-[#E2E2E7] dark:border-neutral-800">
        <div className="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] opacity-25"></div>
        <div className="max-w-[1024px] mx-auto px-10 relative z-10 pt-16">
          
          <div className="grid lg:grid-cols-1 gap-12 items-center text-center max-w-[850px] mx-auto mb-16">
            <motion.div 
              initial="hidden" 
              animate="visible" 
              variants={STAGGER_CHILDREN}
              className="flex flex-col items-center"
            >
              {/* Badge */}
              <motion.div 
                variants={FADE_IN} 
                className="inline-flex items-center space-x-2 bg-[rgba(229,57,53,0.06)] border border-[#E53935]/15 text-[#E53935] px-4 py-2 rounded-full text-xs font-bold mb-6 uppercase tracking-[0.06em]"
              >
                <span className="flex h-2.5 w-2.5 relative">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#E53935]"></span>
                </span>
                <span>Nepal's #1 Offline Local-Subnet Software</span>
              </motion.div>

              {/* Title */}
              <motion.h1 
                variants={FADE_IN} 
                className="text-5xl md:text-[64px] font-black tracking-[-0.03em] text-[#111111] dark:text-white leading-[1.05] mb-6"
              >
                The Smart, Unbreakable <br />
                <span className="bg-gradient-to-r from-[#E53935] to-red-600 bg-clip-text text-transparent">Way to Run Your Restaurant.</span>
              </motion.h1>

              {/* Sub-headline */}
              <motion.p 
                variants={FADE_IN} 
                className="text-lg md:text-xl text-[#555555] dark:text-neutral-400 leading-[1.6] mb-8 max-w-[680px]"
              >
                Zero internet dependencies. Directly launched on your local Windows hardware. Connecting Cashiers, Waiters, and Kitchen Displays concurrently on your local Wi-Fi router.
              </motion.p>

              {/* CTA Buttons */}
              <motion.div 
                variants={FADE_IN} 
                className="flex flex-col sm:flex-row gap-4 items-center justify-center w-full"
              >
                <a 
                  href="#sandbox" 
                  className="bg-[#E53935] text-white px-10 py-4 rounded-md font-bold text-base hover:opacity-90 hover:shadow-[0_8px_20px_rgba(229,57,53,0.35)] transition-all flex items-center justify-center space-x-2 w-full sm:w-auto cursor-pointer shadow-[0_4px_12px_rgba(229,57,53,0.2)]"
                >
                  <span>Interactive POS Demo</span>
                  <ArrowRight size={18} />
                </a>
                <Link 
                  href="/software" 
                  className="bg-[#111111] text-white hover:bg-neutral-800 px-10 py-4 rounded-md font-bold text-base transition-all flex items-center justify-center space-x-2 w-full sm:w-auto cursor-pointer shadow-[0_4px_12px_rgba(0,0,0,0.1)]"
                >
                  <span>Explore Software</span>
                </Link>
              </motion.div>
            </motion.div>
          </div>

          {/* 2. HERO INTERACTIVE SWITCHER */}
          <motion.div 
            initial={{ opacity: 0, y: 40 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            className="w-full"
          >
            {/* Custom Tabs */}
            <div className="flex flex-wrap justify-center gap-2 mb-4 max-w-[700px] mx-auto px-4">
              {[
                { id: 'admin', label: 'Admin Dashboard', shortcut: 'Desktop App Core', icon: <Cpu size={16} /> },
                { id: 'cashier', label: 'Cashier POS Billing', shortcut: 'Cashier Terminal', icon: <Monitor size={16} /> },
                { id: 'waiter', label: 'Waiter Take-Order', shortcut: 'Waiter Mobile Sync', icon: <Smartphone size={16} /> },
                { id: 'kds', label: 'Kitchen & Bar Display', shortcut: 'KDS Live Board', icon: <TvIcon size={16} /> }
              ].map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id as any)}
                  className={`flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-bold border transition-all cursor-pointer ${
                    activeTab === tab.id
                      ? 'bg-[#E53935] text-white border-[#E53935] shadow-[0_6px_15px_rgba(229,57,53,0.2)] scale-[1.02]'
                      : 'bg-white dark:bg-[#0a0a0a] text-[#555555] dark:text-neutral-400 border-[#E2E2E7] dark:border-neutral-800 hover:border-gray-400'
                  }`}
                >
                  {tab.icon}
                  <div className="text-left">
                    <div className="font-bold leading-tight">{tab.label}</div>
                    <div className={`text-[9px] font-mono leading-none mt-0.5 ${activeTab === tab.id ? 'text-red-100' : 'text-neutral-400'}`}>
                      {tab.shortcut}
                    </div>
                  </div>
                </button>
              ))}
            </div>

            {/* Dashboard Mockup Display */}
            <div className="bg-[#111111] rounded-[20px] h-[500px] w-full shadow-[0_45px_90px_rgba(0,0,0,0.2)] border-8 border-[#222222] overflow-hidden relative animate-glow">
              {/* Top address bar mockup */}
              <div className="h-12 bg-[#1a1a1a] border-b border-[#333333] flex items-center justify-between px-4 gap-3">
                <div className="flex items-center gap-2">
                  <div className="w-3 h-3 rounded-full bg-red-500/80"></div>
                  <div className="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                  <div className="w-3 h-3 rounded-full bg-green-500/80"></div>
                  <div className="text-[#666666] text-[11px] ml-4 font-mono select-none">
                    {activeTab === 'admin' && 'http://drestro.local/admin/dashboard'}
                    {activeTab === 'cashier' && 'http://drestro.local/pos/billing'}
                    {activeTab === 'waiter' && 'http://drestro.local/waiter/tables'}
                    {activeTab === 'kds' && 'http://drestro.local/kds/display'}
                  </div>
                </div>
                <div className="flex items-center gap-1.5 text-[#666666] text-[10px] font-semibold font-mono bg-[#111111] border border-[#333333] px-2 py-0.5 rounded">
                  <Wifi size={10} className="text-emerald-500 animate-pulse" />
                  <span>LOCAL-SUBNET CONNECTED</span>
                </div>
              </div>

              {/* Mockup Canvas */}
              <div className="relative h-[calc(100%-3rem)] bg-[#111111] p-4 md:p-6 text-neutral-300 font-sans overflow-y-auto overflow-x-hidden dark-scroll">
                
                {/* 2A. ADMIN DASHBOARD CONTENT */}
                {activeTab === 'admin' && (
                  <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="h-full flex flex-col">
                    <div className="flex justify-between items-center pb-4 border-b border-[#333333] mb-5">
                      <div>
                        <h2 className="text-white text-xl font-bold">Managerial Dashboard</h2>
                        <p className="text-[11px] text-neutral-500">Real-time restaurant operations overview</p>
                      </div>
                      <div className="flex gap-2">
                        <div className="bg-[#222222] border border-[#333333] px-3 py-1 rounded text-xs text-neutral-400 font-mono">Shift: 08:00 AM - 10:00 PM</div>
                      </div>
                    </div>
                    
                    {/* Stat Grid */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                      <div className="bg-[#1b1b1b] border border-[#2d2d2d] rounded-xl p-4">
                        <div className="text-[10px] text-neutral-500 uppercase tracking-wider font-semibold mb-1">Today's Sales</div>
                        <div className="text-white text-2xl font-black font-mono">Rs. 84,250</div>
                        <div className="text-[10px] text-emerald-500 mt-1 font-semibold">▲ +14% vs yesterday</div>
                      </div>
                      <div className="bg-[#1b1b1b] border border-[#2d2d2d] rounded-xl p-4">
                        <div className="text-[10px] text-neutral-500 uppercase tracking-wider font-semibold mb-1">Total Bills Issued</div>
                        <div className="text-white text-2xl font-black font-mono">68 Invoices</div>
                        <div className="text-[10px] text-neutral-400 mt-1">Average Rs. 1,238 / table</div>
                      </div>
                      <div className="bg-[#1b1b1b] border border-[#2d2d2d] rounded-xl p-4">
                        <div className="text-[10px] text-neutral-500 uppercase tracking-wider font-semibold mb-1">Active Dining Tables</div>
                        <div className="text-white text-2xl font-black font-mono">14 / 20 Tables</div>
                        <div className="text-[10px] text-red-500 mt-1 font-semibold">● Occupancy: 70%</div>
                      </div>
                      <div className="bg-[#1b1b1b] border border-[#2d2d2d] rounded-xl p-4">
                        <div className="text-[10px] text-neutral-500 uppercase tracking-wider font-semibold mb-1">Low Stock Recipes</div>
                        <div className="text-white text-2xl font-black font-mono text-amber-500">3 Alerts</div>
                        <div className="text-[10px] text-amber-500 mt-1">Check Ingredient Stocks</div>
                      </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 flex-1">
                      {/* Bar graph mockup */}
                      <div className="bg-[#161616] border border-[#2d2d2d] rounded-xl p-4 col-span-2 flex flex-col">
                        <div className="text-xs font-bold text-white mb-4">Hourly Sales Analytics (Rs.)</div>
                        <div className="flex-1 flex items-end gap-2.5 pb-2">
                          {[30, 45, 60, 50, 75, 95, 80, 85, 40].map((h, i) => (
                            <div key={i} className="flex-1 flex flex-col items-center">
                              <div className="w-full bg-[#E53935] rounded-t-sm" style={{ height: `${h}%` }}></div>
                              <span className="text-[9px] text-neutral-600 mt-1.5 font-mono">{11 + i}:00</span>
                            </div>
                          ))}
                        </div>
                      </div>
                      {/* Top items */}
                      <div className="bg-[#161616] border border-[#2d2d2d] rounded-xl p-4 flex flex-col">
                        <div className="text-xs font-bold text-white mb-3">Top Performing Items</div>
                        <div className="flex flex-col gap-2.5 text-xs">
                          <div className="flex justify-between items-center py-1.5 border-b border-[#2d2d2d]">
                            <span className="font-semibold text-white">1. MoMo (Chicken)</span>
                            <span className="text-neutral-500 font-mono">112 orders</span>
                          </div>
                          <div className="flex justify-between items-center py-1.5 border-b border-[#2d2d2d]">
                            <span className="font-semibold text-white">2. Pasta Carbonara</span>
                            <span className="text-neutral-500 font-mono">84 orders</span>
                          </div>
                          <div className="flex justify-between items-center py-1.5 border-b border-[#2d2d2d]">
                            <span className="font-semibold text-white">3. Himalayan Java Latte</span>
                            <span className="text-neutral-500 font-mono">76 orders</span>
                          </div>
                          <div className="flex justify-between items-center py-1.5 border-b border-[#2d2d2d]">
                            <span className="font-semibold text-white">4. Margherita Pizza</span>
                            <span className="text-neutral-500 font-mono">59 orders</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </motion.div>
                )}

                {/* 2B. CASHIER POS BILLING CONTENT */}
                {activeTab === 'cashier' && (
                  <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="h-full grid grid-cols-1 md:grid-cols-3 gap-4">
                    {/* Left: Table Grids & Order Type */}
                    <div className="col-span-2 bg-[#161616] border border-[#2d2d2d] rounded-xl p-4 flex flex-col">
                      <div className="flex justify-between items-center mb-4 pb-2 border-b border-[#2d2d2d]">
                        <h3 className="text-xs font-bold text-white">Active Dine-In Floor Plan</h3>
                        <div className="flex gap-2">
                          <span className="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 text-[9px] font-bold rounded">12 OCCUPIED</span>
                          <span className="px-2 py-0.5 bg-neutral-800 text-neutral-400 text-[9px] rounded">8 VACANT</span>
                        </div>
                      </div>

                      {/* Tables Layout Grid */}
                      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 flex-1">
                        {[
                          { num: 'T-01', bill: 'Rs. 2,450', status: 'occupied' },
                          { num: 'T-02', bill: 'Rs. 1,300', status: 'occupied' },
                          { num: 'T-03', bill: 'Rs. 4,200', status: 'occupied' },
                          { num: 'T-04', bill: 'Empty', status: 'vacant' },
                          { num: 'T-05', bill: 'Rs. 1,120', status: 'occupied' },
                          { num: 'T-06', bill: 'Empty', status: 'vacant' },
                          { num: 'T-07', bill: 'Rs. 5,800', status: 'occupied' },
                          { num: 'T-08', bill: 'Empty', status: 'vacant' },
                          { num: 'T-09', bill: 'Rs. 890', status: 'occupied' },
                          { num: 'T-10', bill: 'Empty', status: 'vacant' },
                          { num: 'T-11', bill: 'Rs. 3,100', status: 'occupied' },
                          { num: 'T-12', bill: 'Rs. 2,650', status: 'occupied' }
                        ].map((t) => (
                          <div
                            key={t.num}
                            className={`border rounded-lg p-3 flex flex-col justify-between cursor-pointer select-none transition-all ${
                              t.status === 'occupied'
                                ? 'bg-red-500/5 border-red-500/30 hover:bg-red-500/10'
                                : 'bg-[#222222] border-[#333333] hover:bg-[#2d2d2d] text-neutral-500'
                            }`}
                          >
                            <span className={`text-xs font-bold ${t.status === 'occupied' ? 'text-white' : 'text-neutral-500'}`}>{t.num}</span>
                            <span className={`text-[10px] font-mono mt-2 ${t.status === 'occupied' ? 'text-[#E53935] font-semibold' : 'text-neutral-600'}`}>{t.bill}</span>
                          </div>

                        ))}
                      </div>
                    </div>

                    {/* Right: Checkout & Details Panel */}
                    <div className="bg-[#1b1b1b] border border-[#2d2d2d] rounded-xl p-4 flex flex-col">
                      <div className="pb-3 border-b border-[#2d2d2d] mb-4">
                        <span className="text-[10px] text-neutral-500 font-mono">Invoice: #IN-2026-4089</span>
                        <h3 className="text-sm font-bold text-white mt-0.5">Selected: Table T-07</h3>
                      </div>
                      
                      {/* Ticket items */}
                      <div className="flex-1 flex flex-col gap-2.5 overflow-y-auto max-h-[160px] pr-1 mb-4 text-xs dark-scroll">
                        <div className="flex justify-between items-center py-1">
                          <div>
                            <span className="font-semibold text-white">2x MoMo (Chicken)</span>
                            <div className="text-[9px] text-neutral-500">Rs. 350 each</div>
                          </div>
                          <span className="font-mono text-white">Rs. 700</span>
                        </div>
                        <div className="flex justify-between items-center py-1">
                          <div>
                            <span className="font-semibold text-white">1x Ribeye Steak</span>
                            <div className="text-[9px] text-neutral-500">Rs. 2,100 each</div>
                          </div>
                          <span className="font-mono text-white">Rs. 2,100</span>
                        </div>
                        <div className="flex justify-between items-center py-1">
                          <div>
                            <span className="font-semibold text-white">3x Lime Mint Soda</span>
                            <div className="text-[9px] text-neutral-500">Rs. 220 each</div>
                          </div>
                          <span className="font-mono text-white">Rs. 660</span>
                        </div>
                      </div>

                      {/* Calculations */}
                      <div className="border-t border-dashed border-[#2d2d2d] pt-3 text-xs flex flex-col gap-1.5 font-mono mb-4 text-neutral-400">
                        <div className="flex justify-between">
                          <span>Subtotal:</span>
                          <span>Rs. 3,460</span>
                        </div>
                        <div className="flex justify-between text-[10px]">
                          <span>Service Charge (10%):</span>
                          <span>Rs. 346</span>
                        </div>
                        <div className="flex justify-between text-[10px]">
                          <span>VAT (13%):</span>
                          <span>Rs. 495</span>
                        </div>
                        <div className="flex justify-between text-sm font-bold text-white border-t border-[#2d2d2d] pt-2 mt-1">
                          <span>Total Bill:</span>
                          <span className="text-[#E53935]">Rs. 4,301</span>
                        </div>
                      </div>

                      {/* Action buttons */}
                      <div className="flex gap-2">
                        <button className="flex-1 bg-[#222222] border border-[#333333] text-neutral-400 py-2.5 rounded-lg text-[10px] font-bold uppercase tracking-wider hover:bg-neutral-800 cursor-pointer">
                          Split Bill
                        </button>
                        <button className="flex-1 bg-[#E53935] text-white py-2.5 rounded-lg text-[10px] font-bold uppercase tracking-wider hover:opacity-90 shadow-md cursor-pointer">
                          Settle & Print
                        </button>
                      </div>
                    </div>
                  </motion.div>
                )}

                {/* 2C. WAITER TAKE ORDER CONTENT */}
                {activeTab === 'waiter' && (
                  <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="h-full flex flex-col">
                    <div className="flex justify-between items-center pb-4 border-b border-[#333333] mb-4">
                      <div>
                        <h2 className="text-white text-base font-bold flex items-center gap-2">
                          <Smartphone size={16} className="text-[#E53935]" />
                          <span>Waiter Mobile Terminal</span>
                        </h2>
                        <p className="text-[10px] text-neutral-500 font-mono">Logged in: Waiter #3 (Aashish)</p>
                      </div>
                      <span className="px-3 py-1 bg-neutral-800 text-[#E53935] rounded-full text-[10px] font-black uppercase font-mono tracking-wider border border-red-500/10">
                        Table: T-04
                      </span>
                    </div>

                    {/* Waiter Interface Mockup */}
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 flex-1">
                      {/* Menu Grid */}
                      <div className="col-span-2 bg-[#161616] border border-[#2d2d2d] rounded-xl p-4 flex flex-col">
                        <div className="flex gap-2 mb-3">
                          <span className="px-2.5 py-1 bg-[#E53935] text-white text-[10px] font-bold rounded cursor-pointer">Mains</span>
                          <span className="px-2.5 py-1 bg-[#222222] text-neutral-400 text-[10px] font-bold rounded hover:bg-neutral-800 cursor-pointer">Drinks</span>
                          <span className="px-2.5 py-1 bg-[#222222] text-neutral-400 text-[10px] font-bold rounded hover:bg-neutral-800 cursor-pointer">Desserts</span>
                        </div>
                        <div className="grid grid-cols-2 gap-2 flex-1">
                          {[
                            { name: 'MoMo (Chicken)', p: 'Rs. 350', desc: 'Local spices, steamed' },
                            { name: 'Pasta Carbonara', p: 'Rs. 850', desc: 'Creamy cheese, bacon' },
                            { name: 'Margherita Pizza', p: 'Rs. 650', desc: 'Mozzarella, fresh basil' },
                            { name: 'Ribeye Steak', p: 'Rs. 2,100', desc: 'With mushroom glaze' }
                          ].map((item, i) => (
                            <div key={i} className="bg-[#222222] border border-[#333333] rounded-lg p-3 flex flex-col justify-between hover:border-red-500/40 cursor-pointer transition-all">
                              <div>
                                <h4 className="text-xs font-bold text-white">{item.name}</h4>
                                <p className="text-[9px] text-neutral-500 mt-0.5 leading-tight">{item.desc}</p>
                              </div>
                              <div className="flex justify-between items-center mt-3 pt-2 border-t border-[#333333]">
                                <span className="text-[10px] font-mono text-[#E53935] font-semibold">{item.p}</span>
                                <span className="bg-[#E53935] text-white p-1 rounded-md text-[8px] font-bold uppercase">Add +</span>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                      
                      {/* Active cart review panel */}
                      <div className="bg-[#1b1b1b] border border-[#2d2d2d] rounded-xl p-4 flex flex-col">
                        <h4 className="text-xs font-bold text-white pb-2 border-b border-[#2d2d2d] mb-3">Order Queue</h4>
                        <div className="flex-1 flex flex-col gap-2.5 text-xs text-neutral-400">
                          <div className="flex justify-between border-b border-dashed border-[#2d2d2d] pb-2">
                            <span>1x MoMo (Chicken)</span>
                            <span className="text-white font-mono">Rs. 350</span>
                          </div>
                          <div className="flex justify-between border-b border-dashed border-[#2d2d2d] pb-2">
                            <span>1x Margherita Pizza</span>
                            <span className="text-white font-mono">Rs. 650</span>
                          </div>
                          <div className="text-[10px] bg-red-500/5 text-[#E53935] border border-red-500/10 p-2.5 rounded mt-4">
                            <strong>Note:</strong> MoMo served medium spicy.
                          </div>
                        </div>
                        <button className="w-full bg-[#E53935] text-white py-3 rounded-lg text-[10px] font-extrabold uppercase tracking-widest mt-4 shadow-lg hover:opacity-90 cursor-pointer">
                          Send to Kitchen (KOT)
                        </button>
                      </div>
                    </div>
                  </motion.div>
                )}

                {/* 2D. KITCHEN DISPLAY SYSTEM KDS CONTENT */}
                {activeTab === 'kds' && (
                  <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="h-full flex flex-col">
                    <div className="flex justify-between items-center pb-4 border-b border-[#333333] mb-4">
                      <div>
                        <h2 className="text-white text-base font-bold flex items-center gap-2">
                          <UtensilsCrossed size={16} className="text-[#E53935]" />
                          <span>Kitchen Display System (KDS)</span>
                        </h2>
                        <p className="text-[10px] text-neutral-500">Live order preparation queue & progress board</p>
                      </div>
                      <div className="flex gap-2">
                        <span className="px-2 py-0.5 bg-red-500/10 text-[#E53935] text-[9px] font-bold rounded uppercase tracking-wider border border-red-500/20">3 Orders Waiting</span>
                      </div>
                    </div>

                    {/* KDS grid boards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 flex-1">
                      
                      {/* Ticket 1 */}
                      <div className="bg-[#1b1b1b] border-2 border-[#E53935]/60 rounded-xl flex flex-col overflow-hidden">
                        <div className="bg-[#E53935] text-white px-3 py-2 flex justify-between items-center">
                          <span className="text-xs font-black">TABLE: T-04</span>
                          <span className="text-[10px] font-mono bg-black/30 px-1.5 py-0.5 rounded font-black">NEW • 0m ago</span>
                        </div>
                        <div className="p-3 flex-1 flex flex-col gap-2 text-xs">
                          <div className="text-white font-bold py-1 border-b border-[#2d2d2d]">1x MoMo (Chicken)</div>
                          <div className="text-white font-bold py-1 border-b border-[#2d2d2d]">1x Margherita Pizza</div>
                          <div className="text-[9px] text-[#E53935] bg-[#E53935]/5 p-2 rounded mt-2 border border-red-500/10 font-medium">
                            *Note: MoMo served medium spicy
                          </div>
                        </div>
                        <div className="p-3 border-t border-[#2d2d2d]">
                          <button className="w-full bg-[#E53935] hover:bg-red-700 text-white font-black py-2 rounded text-[10px] uppercase tracking-wider cursor-pointer transition-colors">
                            Accept Cooking
                          </button>
                        </div>
                      </div>

                      {/* Ticket 2 */}
                      <div className="bg-[#1b1b1b] border border-amber-500/30 rounded-xl flex flex-col overflow-hidden">
                        <div className="bg-amber-600 text-white px-3 py-2 flex justify-between items-center">
                          <span className="text-xs font-black">TABLE: T-12</span>
                          <span className="text-[10px] font-mono bg-black/30 px-1.5 py-0.5 rounded">PREP • 12m ago</span>
                        </div>
                        <div className="p-3 flex-1 flex flex-col gap-2 text-xs">
                          <div className="text-neutral-300 font-bold py-1 border-b border-[#2d2d2d]">1x Ribeye Steak</div>
                          <div className="text-neutral-300 font-bold py-1 border-b border-[#2d2d2d]">1x Lime Mint Soda</div>
                        </div>
                        <div className="p-3 border-t border-[#2d2d2d]">
                          <button className="w-full bg-amber-600 hover:bg-amber-700 text-white font-black py-2 rounded text-[10px] uppercase tracking-wider cursor-pointer transition-colors">
                            Complete (Ready)
                          </button>
                        </div>
                      </div>

                      {/* Ticket 3 */}
                      <div className="bg-[#1b1b1b] border border-emerald-500/30 rounded-xl flex flex-col overflow-hidden opacity-75">
                        <div className="bg-emerald-600 text-white px-3 py-2 flex justify-between items-center">
                          <span className="text-xs font-black">TABLE: T-02</span>
                          <span className="text-[10px] font-mono bg-black/30 px-1.5 py-0.5 rounded">READY • 18m ago</span>
                        </div>
                        <div className="p-3 flex-1 flex flex-col gap-2 text-xs">
                          <div className="text-neutral-400 line-through py-1 border-b border-[#2d2d2d]">2x Margherita Pizza</div>
                        </div>
                        <div className="p-3 border-t border-[#2d2d2d]">
                          <div className="text-emerald-500 text-[10px] font-bold text-center py-2 flex items-center justify-center gap-1.5">
                            <CheckCircle2 size={12} />
                            <span>COLLECTED BY WAITER</span>
                          </div>
                        </div>
                      </div>

                    </div>
                  </motion.div>
                )}

              </div>
            </div>
          </motion.div>

        </div>
      </section>

      <ClientLogoMarquee />

      {/* 3. OFFLINE LOCAL SUBNET ARCHITECTURE */}
      <section className="py-24 bg-[#FAFAFA] dark:bg-[#0a0a0a] border-b border-[#E2E2E7] dark:border-neutral-800 relative">
        <div className="max-w-[1024px] mx-auto px-10">
          
          <div className="text-center max-w-[700px] mx-auto mb-16">
            <span className="text-[#E53935] text-xs font-bold uppercase tracking-widest border border-[#E53935]/20 bg-[#E53935]/5 px-3.5 py-1.5 rounded-full">
              Enterprise Local Networking
            </span>
            <h2 className="text-3xl lg:text-[42px] tracking-[-0.02em] font-extrabold text-[#111111] dark:text-white mt-5 mb-6 leading-tight">
              Concurrent Local Multi-Device Network
            </h2>
            <p className="text-[#555555] dark:text-neutral-400 text-lg leading-[1.6]">
              DRestro is optimized for **true local concurrency**. Using our proprietary offline sync engine and a secure local database core, multiple tablets, POS cashier screens, and kitchen display monitors run in complete synchronization without any cloud latency.
            </p>
          </div>

          <div className="grid lg:grid-cols-2 gap-12 items-center">
            
            {/* Visual SVGs connectivity map */}
            <div className="bg-[#111111] rounded-3xl p-8 border border-neutral-800 shadow-2xl relative overflow-hidden h-[400px] flex flex-col justify-center">
              
              <div className="absolute inset-0 bg-grid-dark opacity-10"></div>
              
              {/* Central Router */}
              <div className="flex justify-center items-center relative z-10 mb-8">
                <div className="w-16 h-16 rounded-2xl bg-[#E53935] flex flex-col items-center justify-center text-white border-2 border-white/20 shadow-[0_0_20px_rgba(229,57,53,0.4)]">
                  <Wifi size={24} className="animate-pulse" />
                  <span className="text-[8px] font-black tracking-wider uppercase font-mono mt-1">SUBNET</span>
                </div>
                {/* Router description label */}
                <div className="absolute top-[80px] text-center">
                  <span className="text-[10px] text-[#E53935] font-black font-mono tracking-widest bg-red-500/10 border border-red-500/25 px-2 py-0.5 rounded">
                    Wi-Fi Router (192.168.1.X)
                  </span>
                </div>
              </div>

              {/* Connected terminals */}
              <div className="grid grid-cols-3 gap-4 relative z-10 mt-8 pt-4">
                
                {/* Node 1 */}
                <div className="flex flex-col items-center bg-[#1a1a1a] border border-[#2d2d2d] rounded-xl p-3 shadow-md hover:border-[#E53935]/40 transition-colors">
                  <Server size={20} className="text-[#E53935]" />
                  <span className="text-[10px] font-bold text-white mt-1.5">Main POS PC</span>
                  <span className="text-[8px] font-mono text-neutral-500 mt-0.5">192.168.1.100</span>
                  <span className="text-[7px] font-mono text-emerald-500 mt-1 font-bold">HOST SERVER</span>
                </div>

                {/* Node 2 */}
                <div className="flex flex-col items-center bg-[#1a1a1a] border border-[#2d2d2d] rounded-xl p-3 shadow-md hover:border-[#E53935]/40 transition-colors">
                  <Smartphone size={20} className="text-[#E53935]" />
                  <span className="text-[10px] font-bold text-white mt-1.5">Waiter Tablets</span>
                  <span className="text-[8px] font-mono text-neutral-500 mt-0.5">192.168.1.120</span>
                  <span className="text-[7px] font-mono text-cyan-400 mt-1 font-bold">CLIENTS (CONCURRENT)</span>
                </div>

                {/* Node 3 */}
                <div className="flex flex-col items-center bg-[#1a1a1a] border border-[#2d2d2d] rounded-xl p-3 shadow-md hover:border-[#E53935]/40 transition-colors">
                  <Monitor size={20} className="text-[#E53935]" />
                  <span className="text-[10px] font-bold text-white mt-1.5">Kitchen KDS</span>
                  <span className="text-[8px] font-mono text-neutral-500 mt-0.5">192.168.1.150</span>
                  <span className="text-[7px] font-mono text-cyan-400 mt-1 font-bold">MONITORS (LIVE FEED)</span>
                </div>

              </div>

              {/* Pulsing signal cables */}
              <div className="absolute top-[210px] left-[50px] right-[50px] h-[1px] border-t border-dashed border-[#E53935]/40 pointer-events-none"></div>

            </div>

            {/* Technical Highlights */}
            <div className="flex flex-col gap-6">
              
              <div className="flex gap-4 items-start">
                <div className="w-10 h-10 rounded-lg bg-[#E53935]/10 text-[#E53935] flex items-center justify-center shrink-0 mt-1">
                  <Wifi size={18} />
                </div>
                <div>
                  <h4 className="text-lg font-bold text-[#111111] dark:text-white mb-1">True Cloudless Security</h4>
                  <p className="text-sm text-[#555555] dark:text-neutral-400 leading-[1.6]">
                    Your orders, inventory metrics, recipes, and transactions stay entirely within your restaurant's physical building. There's no risk of cloud server breaches, and billing functions continue seamlessly if outside internet lines cut out.
                  </p>
                </div>
              </div>

              <div className="flex gap-4 items-start">
                <div className="w-10 h-10 rounded-lg bg-[#E53935]/10 text-[#E53935] flex items-center justify-center shrink-0 mt-1">
                  <Cpu size={18} />
                </div>
                <div>
                  <h4 className="text-lg font-bold text-[#111111] dark:text-white mb-1">Proprietary Multi-Thread Core</h4>
                  <p className="text-sm text-[#555555] dark:text-neutral-400 leading-[1.6]">
                    Instead of standard single-threaded local software setups that lock up when multiple devices request actions simultaneously, DRestro utilizes a high-concurrency architecture to seamlessly handle dozens of cashier, waiter, and kitchen requests in the exact same fraction of a second.
                  </p>
                </div>
              </div>

              <div className="flex gap-4 items-start">
                <div className="w-10 h-10 rounded-lg bg-[#E53935]/10 text-[#E53935] flex items-center justify-center shrink-0 mt-1">
                  <Terminal size={18} />
                </div>
                <div>
                  <h4 className="text-lg font-bold text-[#111111] dark:text-white mb-1">Instant Local Network Sync</h4>
                  <p className="text-sm text-[#555555] dark:text-neutral-400 leading-[1.6]">
                    Our secure, pre-configured network configuration automates your local Wi-Fi router permissions. This allows handheld waiter tablets, kitchen display feeds, and secondary terminals to securely sync and communicate in real-time.
                  </p>
                </div>
              </div>

            </div>

          </div>

        </div>
      </section>

      {/* 4. INTERACTIVE POS DEMO SANDBOX */}
      <section id="sandbox" className="py-24 bg-white dark:bg-[#0a0a0a] border-b border-[#E2E2E7] dark:border-neutral-800 relative">
        <div className="max-w-[1024px] mx-auto px-10">
          
          <div className="text-center max-w-[700px] mx-auto mb-16">
            <span className="text-[#E53935] text-xs font-bold uppercase tracking-widest border border-[#E53935]/20 bg-[#E53935]/5 px-3.5 py-1.5 rounded-full">
              Live Sandbox Playground
            </span>
            <h2 className="text-3xl lg:text-[42px] tracking-[-0.02em] font-extrabold text-[#111111] dark:text-white mt-5 mb-6 leading-tight">
              Test DRestro's Local Workflows
            </h2>
            <p className="text-[#555555] dark:text-neutral-400 text-lg leading-[1.6]">
              Add items from our traditional Nepali restaurant menu card to simulate placing an order. Send it instantly to the Kitchen Display (KDS) board, and watch a digital thermal receipt generate in real-time.
            </p>
          </div>

          <div className="grid lg:grid-cols-12 gap-8 items-stretch">
            
            {/* Column 1 (Grid-Span 4): Menu Item selector */}
            <div className="lg:col-span-4 bg-[#FAFAFA] dark:bg-[#0a0a0a] border border-[#E2E2E7] dark:border-neutral-800 rounded-3xl p-5 flex flex-col justify-between">
              <div>
                <h3 className="text-sm font-extrabold text-[#111111] dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-[#E2E2E7] dark:border-neutral-800 flex justify-between items-center">
                  <span>Digital Menu Card</span>
                  <button onClick={resetDemo} className="text-xs text-neutral-400 hover:text-[#E53935] flex items-center gap-1 font-bold cursor-pointer">
                    <RotateCcw size={12} />
                    <span>Reset</span>
                  </button>
                </h3>
                
                {/* Menu items listing */}
                <div className="flex flex-col gap-3">
                  {MENU_ITEMS.map((item) => (
                    <div 
                      key={item.id}
                      onClick={() => addToCart(item)}
                      className="bg-white dark:bg-[#0a0a0a] border border-[#E2E2E7] dark:border-neutral-800 rounded-xl p-3.5 flex justify-between items-center hover:border-[#E53935]/50 hover:shadow-sm cursor-pointer transition-all active:scale-[0.98]"
                    >
                      <div>
                        <h4 className="text-xs font-bold text-[#111111] dark:text-white">{item.name}</h4>
                        <span className="text-[10px] font-bold text-neutral-400">{item.category}</span>
                      </div>
                      <div className="flex items-center gap-3">
                        <span className="text-xs font-mono text-[#E53935] font-black">Rs. {item.price}</span>
                        <div className="w-6 h-6 rounded-md bg-[#E53935]/10 text-[#E53935] flex items-center justify-center">
                          <Plus size={14} />
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Simple Table selector */}
              <div className="mt-8 pt-4 border-t border-[#E2E2E7] dark:border-neutral-800 text-xs">
                <span className="block font-bold text-[#111111] dark:text-white mb-2 uppercase tracking-wide">Assign Dining Table:</span>
                <div className="flex gap-2">
                  {['T-04', 'T-12', 'T-02', 'T-08'].map(t => (
                    <button
                      key={t}
                      onClick={() => setTableNumber(t)}
                      className={`flex-1 py-1.5 rounded-lg font-bold border transition-colors cursor-pointer ${
                        tableNumber === t 
                          ? 'bg-[#111111] text-white border-[#111111]' 
                          : 'bg-white dark:bg-[#0a0a0a] text-neutral-500 border-[#E2E2E7] dark:border-neutral-800 hover:border-neutral-400'
                      }`}
                    >
                      {t}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Column 2 (Grid-Span 4): Active Order & Cash Drawer Summary */}
            <div className="lg:col-span-4 bg-[#111111] text-neutral-300 rounded-3xl p-5 flex flex-col justify-between border border-neutral-800 shadow-xl">
              <div>
                <h3 className="text-xs font-black text-white uppercase tracking-widest mb-4 pb-2 border-b border-neutral-800 flex justify-between items-center">
                  <span>Active Bill: Table {tableNumber}</span>
                  <span className="px-2 py-0.5 bg-red-500/10 text-[#E53935] text-[9px] font-black rounded font-mono border border-red-500/20">LIVE DISPATCH</span>
                </h3>

                {cart.length === 0 ? (
                  <div className="flex flex-col items-center justify-center py-16 text-center text-neutral-600">
                    <UtensilsCrossed size={36} className="text-neutral-700 mb-3" />
                    <span className="text-xs font-bold">No items selected</span>
                    <p className="text-[10px] text-neutral-600 mt-1 max-w-[180px]">Click food cards in the Menu Panel to add to your order.</p>
                  </div>
                ) : (
                  <div className="flex flex-col gap-3 overflow-y-auto max-h-[220px] dark-scroll pr-1">
                    {cart.map((item) => (
                      <div key={item.id} className="flex justify-between items-center text-xs py-1 border-b border-dashed border-neutral-800 pb-2">
                        <div>
                          <div className="font-bold text-white">{item.quantity}x {item.name}</div>
                          <span className="text-[9px] text-neutral-500 font-mono">Rs. {item.price} each</span>
                        </div>
                        <div className="flex items-center gap-3">
                          <span className="font-mono text-white">Rs. {item.price * item.quantity}</span>
                          <button onClick={() => removeFromCart(item.id)} className="text-neutral-600 hover:text-[#E53935] cursor-pointer">
                            <Trash2 size={12} />
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Summary panel */}
              {cart.length > 0 && (
                <div className="mt-6 pt-4 border-t border-neutral-800 font-mono text-[11px] text-neutral-400">
                  <div className="flex justify-between mb-1">
                    <span>Subtotal:</span>
                    <span>Rs. {subtotal}</span>
                  </div>
                  <div className="flex justify-between mb-1 text-[10px]">
                    <span>Service Charge (10%):</span>
                    <span>Rs. {serviceCharge}</span>
                  </div>
                  <div className="flex justify-between mb-2 text-[10px]">
                    <span>VAT (13%):</span>
                    <span>Rs. {vat}</span>
                  </div>
                  <div className="flex justify-between text-xs font-black text-white border-t border-neutral-800 pt-2.5 mt-1.5">
                    <span>Total Amount:</span>
                    <span className="text-[#E53935] text-sm">Rs. {total}</span>
                  </div>
                  
                  <button 
                    onClick={handleSendToKitchen}
                    className="w-full bg-[#E53935] text-white py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest mt-4 shadow-lg hover:opacity-95 flex items-center justify-center gap-2 cursor-pointer transition-opacity"
                  >
                    <span>Send Order (KOT)</span>
                    <ArrowRight size={12} />
                  </button>
                </div>
              )}
            </div>

            {/* Column 3 (Grid-Span 4): Feeds (Kitchen Board + Thermal printer) */}
            <div className="lg:col-span-4 flex flex-col justify-between gap-6">
              
              {/* Upper Box: Real-time KDS Board feed */}
              <div className={`bg-[#FAFAFA] dark:bg-[#0a0a0a] border rounded-3xl p-5 flex-1 transition-all flex flex-col ${isKdsPulsing ? 'border-[#E53935] scale-[1.01] shadow-lg' : 'border-[#E2E2E7] dark:border-neutral-800'}`}>
                <h3 className="text-xs font-black text-[#111111] dark:text-white uppercase tracking-wider mb-3 pb-2 border-b border-[#E2E2E7] dark:border-neutral-800 flex justify-between items-center">
                  <span>Kitchen Feed (KDS)</span>
                  <span className="text-[9px] font-mono text-neutral-400">TABLE DISPATCH</span>
                </h3>
                
                <div className="flex flex-col gap-2.5 overflow-y-auto max-h-[140px] pr-1 flex-1 text-xs dark-scroll">
                  {kdsTickets.map((t) => (
                    <div 
                      key={t.id} 
                      onClick={() => handleToggleKdsStatus(t.id)}
                      className={`border rounded-xl p-3 flex flex-col justify-between cursor-pointer transition-all hover:bg-neutral-50 ${
                        t.status === 'NEW' ? 'border-[#E53935] bg-[#E53935]/5' : 'border-[#E2E2E7] dark:border-neutral-800 bg-white dark:bg-[#0a0a0a]'
                      }`}
                    >
                      <div className="flex justify-between items-center font-bold mb-1">
                        <span className={t.status === 'NEW' ? 'text-[#E53935]' : 'text-neutral-800'}>Table {t.table} (#KOT-{t.id})</span>
                        <span className={`text-[8px] px-1.5 py-0.5 rounded font-black tracking-wide ${
                          t.status === 'NEW' ? 'bg-[#E53935] text-white' : t.status === 'PREPARING' ? 'bg-amber-600 text-white' : 'bg-emerald-600 text-white'
                        }`}>
                          {t.status}
                        </span>
                      </div>
                      <div className="text-[10px] text-neutral-500 leading-tight">
                        {t.items.join(', ')}
                      </div>
                      <div className="text-[8px] font-mono text-neutral-400 text-right mt-1.5 font-bold">
                        Click to cycle status
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Lower Box: Beautiful high-fidelity monospace receipt */}
              <div className="bg-[#f0f0f2] border border-[#d2d2d6] rounded-3xl p-5 relative overflow-hidden h-[240px] flex flex-col">
                <div className="text-xs font-bold text-neutral-600 uppercase tracking-wider mb-2.5 flex justify-between items-center">
                  <span>Printer Output Simulator</span>
                  <div className="flex items-center gap-1 text-[9px] text-[#E53935] font-black">
                    <Printer size={10} />
                    <span>ESC/POS</span>
                  </div>
                </div>

                <div className="flex-1 relative overflow-hidden">
                  
                  {/* Sliding printing animation cover */}
                  <AnimatePresence>
                    {receiptAnimation && (
                      <motion.div 
                        initial={{ y: 0 }}
                        animate={{ y: '-100%' }}
                        exit={{ y: 0 }}
                        transition={{ duration: 1.2, ease: 'easeInOut' }}
                        className="absolute inset-0 bg-[#f0f0f2] z-20 flex flex-col items-center justify-center text-xs font-bold text-neutral-400 gap-2 border border-dashed border-[#E2E2E7] dark:border-neutral-800 rounded"
                      >
                        <Printer className="animate-bounce text-[#E53935]" />
                        <span>PRINTING RECEIPT...</span>
                      </motion.div>
                    )}
                  </AnimatePresence>

                  {/* Mono paper */}
                  <div className="receipt-paper h-full overflow-y-auto p-4 text-[10px] tracking-tight text-neutral-800 leading-tight">
                    <div className="text-center font-bold text-[12px] uppercase">*** DRESTRO ***</div>
                    <div className="text-center text-[8px] font-mono mt-0.5">Maitidevi, Kathmandu, Nepal</div>
                    <div className="text-center text-[8px] font-mono mb-2">Tel: +977-1-4400000</div>
                    
                    <div className="border-t border-b border-black border-dashed py-1.5 mb-2 font-mono flex flex-col gap-0.5 text-[8px]">
                      <div>Date: 2026-05-20 13:50</div>
                      <div>Cashier: Cashier #1</div>
                      <div>Table: {tableNumber} (Dine-in)</div>
                      <div>Bill: #D-{lastTicketId}</div>
                    </div>

                    <table className="w-full text-left font-mono text-[8px] mb-2">
                      <thead>
                        <tr className="border-b border-black border-dashed font-bold">
                          <th className="pb-1">Item Description</th>
                          <th className="text-right pb-1">Qty</th>
                          <th className="text-right pb-1">Amt</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr className="font-semibold">
                          <td className="pt-1">MoMo (Chicken)</td>
                          <td className="text-right pt-1">1</td>
                          <td className="text-right pt-1">350</td>
                        </tr>
                        <tr>
                          <td>Margherita Pizza</td>
                          <td className="text-right">1</td>
                          <td className="text-right">650</td>
                        </tr>
                      </tbody>
                    </table>

                    <div className="border-t border-black border-dashed pt-1.5 font-mono text-[8px] flex flex-col gap-0.5 text-right font-semibold">
                      <div>Subtotal: Rs. 1000</div>
                      <div>Serv. Charge (10%): Rs. 100</div>
                      <div>VAT (13%): Rs. 143</div>
                      <div className="font-bold text-[9px] border-t border-black border-dashed pt-1 mt-0.5">
                        Net Total: Rs. 1,243
                      </div>
                    </div>

                    <div className="text-center text-[8px] font-bold mt-4 uppercase">*** THANK YOU ***</div>
                  </div>
                </div>

              </div>

            </div>

          </div>

        </div>
      </section>

      {/* 5. NATIVE THERMAL PRINTER CONTROLS */}
      <section className="py-24 bg-[#FAFAFA] dark:bg-[#0a0a0a] border-b border-[#E2E2E7] dark:border-neutral-800">
        <div className="max-w-[1024px] mx-auto px-10">
          
          <div className="text-center max-w-[700px] mx-auto mb-16">
            <span className="text-[#E53935] text-xs font-bold uppercase tracking-widest border border-[#E53935]/20 bg-[#E53935]/5 px-3.5 py-1.5 rounded-full">
              Hardware Integrations
            </span>
            <h2 className="text-3xl lg:text-[42px] tracking-[-0.02em] font-extrabold text-[#111111] dark:text-white mt-5 mb-6 leading-tight">
              Thermal ESC/POS receipt printing
            </h2>
            <p className="text-[#555555] dark:text-neutral-400 text-lg leading-[1.6]">
              Direct connection with primary receipt printer brands (ZKTeco, Xprinter, Epson) is pre-integrated. Choose the method that matches your hardware setup.
            </p>
          </div>

          <div className="grid md:grid-cols-2 gap-8">
            
            {/* Card A: USB */}
            <div className="bg-white dark:bg-[#0a0a0a] border border-[#E2E2E7] dark:border-neutral-800 rounded-3xl p-8 hover:border-[#E53935]/40 hover:shadow-xl transition-all flex flex-col justify-between">
              <div>
                <div className="w-12 h-12 rounded-xl bg-[#E53935]/10 text-[#E53935] flex items-center justify-center mb-6">
                  <Printer size={20} />
                </div>
                <h3 className="text-xl font-bold text-[#111111] dark:text-white mb-2">Method 1: Windows USB sharing</h3>
                <p className="text-sm text-[#555555] dark:text-neutral-400 leading-[1.6] mb-6">
                  Perfect when the receipt printer is sitting directly next to the primary Cashier PC. Simply install the Windows thermal driver, enable printer sharing on Windows properties, and link the local SMB folder.
                </p>
                <div className="bg-[#111111] rounded-xl p-4 font-mono text-[10px] text-neutral-300 border border-neutral-800">
                  <div className="text-[#E53935] font-black uppercase mb-1.5 text-[8px] tracking-wider">Printer Path inside POS:</div>
                  <code>LocalPrinter_80mm</code>
                </div>
              </div>
              
              <div className="mt-8 pt-6 border-t border-[#E2E2E7] dark:border-neutral-800 flex justify-between items-center text-xs font-bold">
                <span className="text-neutral-400">Connection: SMB Windows Print</span>
                <span className="text-[#E53935]">ESC/POS 80mm Support &rarr;</span>
              </div>
            </div>

            {/* Card B: LAN */}
            <div className="bg-white dark:bg-[#0a0a0a] border border-[#E2E2E7] dark:border-neutral-800 rounded-3xl p-8 hover:border-[#E53935]/40 hover:shadow-xl transition-all flex flex-col justify-between">
              <div>
                <div className="w-12 h-12 rounded-xl bg-[#E53935]/10 text-[#E53935] flex items-center justify-center mb-6">
                  <Wifi size={20} />
                </div>
                <h3 className="text-xl font-bold text-[#111111] dark:text-white mb-2">Method 2: Network (LAN/Wi-Fi) printer</h3>
                <p className="text-sm text-[#555555] dark:text-neutral-400 leading-[1.6] mb-6">
                  Best when multiple waiter tablets or mobile phones need to issue orders directly to the Kitchen KOT printer or cashier printer wireless. Plug an Ethernet (LAN) cable from the printer directly into the Wi-Fi router.
                </p>
                <div className="bg-[#111111] rounded-xl p-4 font-mono text-[10px] text-neutral-300 border border-neutral-800">
                  <div className="text-[#E53935] font-black uppercase mb-1.5 text-[8px] tracking-wider">Printer IP inside POS:</div>
                  <code>192.168.1.X:9100</code>
                </div>
              </div>
              
              <div className="mt-8 pt-6 border-t border-[#E2E2E7] dark:border-neutral-800 flex justify-between items-center text-xs font-bold">
                <span className="text-neutral-400">Connection: TCP Socket Connection</span>
                <span className="text-[#E53935]">Wireless Multi-Device Support &rarr;</span>
              </div>
            </div>

          </div>

        </div>
      </section>

      {/* 6. TESTIMONIALS SECTION */}
      <section className="py-24 bg-white dark:bg-[#0a0a0a] border-b border-[#E2E2E7] dark:border-neutral-800 relative">
        {/* Decorative glows */}
        <div className="absolute top-[30%] left-[-10%] w-[350px] h-[350px] rounded-full bg-[#E53935]/5 blur-[100px] pointer-events-none"></div>

        <div className="max-w-[1024px] mx-auto px-10 relative z-10">
          
          <div className="text-center max-w-[700px] mx-auto mb-16">
            <span className="text-[#E53935] text-xs font-bold uppercase tracking-widest border border-[#E53935]/20 bg-[#E53935]/5 px-3.5 py-1.5 rounded-full">
              Success Stories
            </span>
            <h2 className="text-3xl lg:text-[42px] tracking-[-0.02em] font-extrabold text-[#111111] dark:text-white mt-5 mb-6 leading-tight">
              Trusted by leading <span className="bg-gradient-to-r from-[#E53935] to-red-600 bg-clip-text text-transparent">restaurants in Nepal</span>
            </h2>
            <p className="text-[#555555] dark:text-neutral-400 text-lg leading-[1.6]">
              See how our cloudless, unbreakable local-subnet system transforms daily kitchen & service operations.
            </p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {(testimonials.length > 0 ? testimonials : [
              {
                content: "DRestro completely changed how our cashier and waiters interact. With zero cloud dependency, we never have to worry about internet fluctuations. The local sync is faster than any cloud system we've ever tried!",
                authorName: "Pradip Adhikari",
                authorRole: "Store Manager",
                restaurant: "Himalayan Java (Baneshwor)",
                authorInitials: "HJ",
                bgColor: "bg-red-500",
                rating: 5
              },
              {
                content: "The concurrent multi-device sync is flawless. Our waiters take orders on tablets right at the customer tables, and they print instantly in the kitchen. Unbelievable reliability even on busy Friday nights!",
                authorName: "Sushant Shrestha",
                authorRole: "Operations Director",
                restaurant: "Trisara (Lazimpat)",
                authorInitials: "TS",
                bgColor: "bg-amber-500",
                rating: 5
              },
              {
                content: "Simple, unbreakable, and extremely fast. Direct ESC/POS printing connected via LAN works out of the box. Our staff adapted in less than a day. Highly recommended for any busy restaurant in Kathmandu!",
                authorName: "Maya Sherpa",
                authorRole: "Founder & Chef",
                restaurant: "Thakali Kitchen (Maitidevi)",
                authorInitials: "TK",
                bgColor: "bg-emerald-500",
                rating: 5
              }
            ]).map((t, idx) => (
              <div 
                key={idx}
                className="bg-[#FAFAFA] dark:bg-[#0a0a0a] border border-[#E2E2E7] dark:border-neutral-800 rounded-3xl p-8 hover:border-[#E53935]/30 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
              >
                <div>
                  <div className="flex gap-1 text-amber-400 mb-6">
                    {Array.from({ length: t.rating || 5 }).map((_, i) => <Star key={i} size={16} fill="currentColor" />)}
                  </div>
                  <p className="text-sm text-[#333333] dark:text-neutral-300 leading-relaxed italic mb-8 font-medium">
                    "{t.content}"
                  </p>
                </div>
                <div className="flex items-center gap-4 pt-6 border-t border-[#E2E2E7] dark:border-neutral-800">
                  <div className={`w-12 h-12 rounded-xl flex items-center justify-center text-white font-black text-lg ${t.bgColor || 'bg-[#E53935]'} shadow-inner`}>
                    {t.authorInitials}
                  </div>
                  <div>
                    <h4 className="font-bold text-[#111111] dark:text-white text-sm">{t.authorName}</h4>
                    <p className="text-xs text-[#E53935] font-bold uppercase tracking-wider">{t.authorRole}</p>
                    <p className="text-[10px] text-neutral-500 mt-0.5">{t.restaurant}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>

        </div>
      </section>

      {/* 7. FAQ SECTION */}
      <FaqSection />

      {/* 8. CTA BOTTOM BANNER */}
      <section className="py-24 bg-white dark:bg-[#0a0a0a] relative overflow-hidden">
        <div className="max-w-[1024px] mx-auto px-10 relative z-10 flex flex-col items-center text-center">
          <h2 className="text-3xl md:text-[48px] tracking-[-0.03em] font-extrabold text-[#111111] dark:text-white mb-6 leading-tight max-w-[800px]">
            Ready to upgrade your restaurant billing experience?
          </h2>
          <p className="text-[#555555] dark:text-neutral-400 text-lg mb-10 max-w-[600px] leading-[1.6] mx-auto">
            Install DRestro offline POS software in your establishment. Unbreakable uptime, concurrent ordering, and direct native thermal printing.
          </p>
          <div className="flex flex-col sm:flex-row justify-center gap-4 w-full sm:w-auto">

            <Link href="/demo" className="bg-[#E53935] text-white px-10 py-4 rounded-md font-bold text-base hover:opacity-90 transition shadow-[0_4px_12px_rgba(229,57,53,0.3)]">
              Book a Free Demo Setup
            </Link>
            <Link href="/contact" className="bg-transparent text-[#111111] dark:text-white border border-[#E2E2E7] dark:border-neutral-800 hover:border-neutral-400 px-10 py-4 rounded-md font-bold text-base transition">
              Talk to Nepalese Sales
            </Link>
          </div>
        </div>
      </section>

    </div>
  );
}
