"use client";

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { getSession, signOut } from 'next-auth/react';
import { Menu, X, ArrowRight, LogOut, User, Home, Monitor, Server, Tag } from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import ClientAvatar from '@/src/components/ClientAvatar';
import ThemeToggle from '@/src/components/ThemeToggle';

const NAV_LINKS = [
  { name: 'Software', path: '/software' },
  { name: 'Hardware', path: '/products' },
  { name: 'Pricing', path: '/pricing' },
  { name: 'Careers', path: '/careers' },
  { name: 'Support / FAQ', path: '/faq' },
  { name: 'Contact Us', path: '/contact' },
];

export default function Navbar() {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [session, setSession] = useState<any>(null);
  const pathname = usePathname();

  useEffect(() => {
    getSession().then((sess) => setSession(sess));
  }, []);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 20);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Hide Navbar on Admin, Login, and Register pages
  if (pathname.startsWith("/admin") || pathname.startsWith("/login") || pathname.startsWith("/register")) {
    return null;
  }

  useEffect(() => {
    if (isMobileMenuOpen) {
      document.body.style.overflow = 'hidden';
      document.documentElement.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
      document.documentElement.style.overflow = '';
    }
    return () => {
      document.body.style.overflow = '';
      document.documentElement.style.overflow = '';
    };
  }, [isMobileMenuOpen]);

  return (
    <>
      <nav
        className={`fixed top-0 w-full z-50 transition-all duration-300 border-b border-[#E2E2E7] dark:border-neutral-800 ${
        isScrolled ? 'bg-white dark:bg-[#0a0a0a]/90 backdrop-blur-md py-4 shadow-sm' : 'bg-white dark:bg-[#0a0a0a] h-[88px] flex flex-col justify-center'
      }`}
    >
      <div className="max-w-7xl mx-auto w-full px-6 lg:px-8 xl:px-10 flex justify-between items-center">
        <Link href="/" className="flex items-center group">
          <img src="/logos/logo.svg" alt="DRestro Logo" className="h-7 md:h-8 w-auto transition-transform hover:scale-105 duration-300 dark:hidden" />
          <img src="/logos/logo-light.svg" alt="DRestro Logo" className="h-7 md:h-8 w-auto transition-transform hover:scale-105 duration-300 hidden dark:block" />
        </Link>

        {/* Desktop Navigation */}
        <div className="hidden lg:flex items-center gap-6 xl:gap-10">
          {NAV_LINKS.map((link) => (
            <Link
              key={link.path}
              href={link.path}
              className={`text-[15px] font-semibold transition-colors hover:text-[#E53935] dark:hover:text-[#E53935] ${
                pathname === link.path ? 'text-[#E53935]' : 'text-[#444444] dark:text-neutral-300'
              }`}
            >
              {link.name}
            </Link>
          ))}
        </div>

        {/* Header Actions & Mobile Control */}
        <div className="flex items-center gap-2.5 sm:gap-3">
          {/* Desktop Actions */}
          <div className="hidden lg:flex items-center gap-3">
            <ThemeToggle />
            {session ? (
              <div className="flex items-center gap-1 bg-gray-50/80 dark:bg-neutral-800/80 pl-2 pr-1 py-1.5 rounded-full border border-gray-200/60 dark:border-neutral-700/60 shadow-sm hover:border-gray-300 dark:hover:border-neutral-600 transition-colors">
                <Link href="/api/auth/sso" className="flex items-center gap-2.5 group">
                  <div className="w-8 h-8 rounded-full overflow-hidden bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-700 flex items-center justify-center group-hover:border-[#E53935] transition-colors">
                    <ClientAvatar src={session.user?.image} name={session.user?.name} size={16} />
                  </div>
                  <span className="text-sm font-semibold text-gray-700 dark:text-neutral-300 pr-2 group-hover:text-[#E53935] transition-colors">{session.user?.name?.split(' ')[0] || 'Profile'}</span>
                </Link>
                <div className="w-[1px] h-4 bg-gray-200 mx-1"></div>
                <button 
                  onClick={() => signOut()}
                  className="p-1.5 text-slate-500 dark:text-gray-400 hover:text-[#E53935] transition-colors rounded-full hover:bg-red-50 dark:hover:bg-red-500/10"
                  title="Logout"
                >
                  <LogOut size={16} />
                </button>
              </div>
            ) : (
              <div className="flex items-center gap-2">
                <Link
                  href="/login"
                  className="px-4 py-2 rounded-xl font-bold text-[15px] cursor-pointer text-[#444444] dark:text-neutral-300 hover:text-[#E53935] hover:bg-gray-50 dark:hover:bg-neutral-800 transition-all duration-300"
                >
                  Login
                </Link>
                <Link
                  href="/register"
                  className="px-5 py-2.5 rounded-xl font-bold text-[15px] cursor-pointer bg-gray-50/80 dark:bg-neutral-800 hover:bg-gray-100 dark:hover:bg-neutral-700 text-gray-700 dark:text-neutral-300 dark:text-[#111111] dark:text-white hover:text-[#E53935] transition-all duration-300 border border-gray-200/60 dark:border-neutral-700"
                >
                  Sign Up
                </Link>
              </div>
            )}
            <Link
              href="/login"
              className="px-7 py-3 bg-[#E53935] text-white rounded-xl font-bold text-[15px] hover:bg-red-600 hover:shadow-[0_8px_25px_rgba(229,57,53,0.35)] hover:-translate-y-0.5 transition-all duration-300 shadow-[0_4px_12px_rgba(229,57,53,0.2)]"
            >
              Try for Free
            </Link>
          </div>

          {/* Mobile Menu Button */}
          <button
            className="lg:hidden p-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 dark:text-neutral-300 dark:bg-neutral-800 dark:hover:bg-neutral-700 transition-all duration-300 border border-gray-200/60 dark:border-neutral-700 shadow-sm"
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          >
            {isMobileMenuOpen ? <X size={22} className="text-[#E53935]" /> : <Menu size={22} />}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      <AnimatePresence>
        {isMobileMenuOpen && (
          <motion.div
            initial={{ opacity: 0, y: -20, scale: 0.98 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: -15, scale: 0.98 }}
            transition={{ duration: 0.25, ease: "easeOut" }}
            className="absolute top-[100%] left-0 w-full h-[calc(100dvh-75px)] bg-white dark:bg-[#0a0a0a]/95 backdrop-blur-3xl shadow-[0_40px_80px_rgba(0,0,0,0.12)] border-t border-gray-100/50 lg:hidden overflow-y-auto"
          >
            <div className="flex flex-col px-6 py-8 space-y-2 min-h-full pb-32">
              {NAV_LINKS.map((link, index) => (
                <motion.div
                  key={link.path}
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.05 * index, duration: 0.3 }}
                >
                  <Link
                    href={link.path}
                    onClick={() => setIsMobileMenuOpen(false)}
                    className={`group flex items-center justify-between text-[17px] p-4 rounded-2xl transition-all duration-300 ${
                      pathname === link.path 
                        ? 'bg-red-50 dark:bg-red-950/40 text-[#E53935] shadow-sm border border-red-100/50 dark:border-red-900/30 font-bold' 
                        : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-50 dark:hover:bg-neutral-800/60 hover:text-gray-900 dark:hover:text-white font-semibold border border-transparent'
                    }`}
                  >
                    <span>{link.name}</span>
                    <ArrowRight size={18} className={`transition-transform duration-300 ${pathname === link.path ? 'text-[#E53935] translate-x-1' : 'text-slate-600 dark:text-gray-300 group-hover:text-slate-400 dark:text-gray-500 group-hover:translate-x-1'}`} />
                  </Link>
                </motion.div>
              ))}
              
              <div className="pt-6 pb-4">
                <hr className="border-gray-100" />
              </div>
              
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.25, duration: 0.4 }}
                className="flex flex-col gap-3.5"
              >
                {session ? (
                  <div className="flex items-center justify-between bg-gray-50/80 dark:bg-neutral-800/80 pl-2 pr-4 py-2 rounded-2xl border border-gray-200/60 dark:border-neutral-700/60 shadow-sm w-full">
                    <Link href="/api/auth/sso" onClick={() => setIsMobileMenuOpen(false)} className="flex items-center gap-3 flex-1 min-w-0">
                      <div className="w-10 h-10 rounded-full overflow-hidden bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-700 flex items-center justify-center">
                        <ClientAvatar src={session.user?.image} name={session.user?.name} size={20} />
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="font-semibold text-gray-900 dark:text-[#111111] dark:text-white truncate">{session.user?.name || 'Profile'}</div>
                        <div className="text-xs text-slate-400 dark:text-gray-500 truncate">{session.user?.email}</div>
                      </div>
                    </Link>
                    <ThemeToggle />
                    <button 
                      onClick={() => { signOut(); setIsMobileMenuOpen(false); }}
                      className="ml-3 p-2.5 text-slate-400 dark:text-gray-500 dark:text-neutral-400 hover:text-[#E53935] transition-colors rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10"
                    >
                      <LogOut size={20} />
                    </button>
                  </div>
                ) : (
                  <div className="flex flex-col gap-3">
                    <div className="flex items-center gap-4">
                      <ThemeToggle />
                      <Link
                        href="/login"
                        onClick={() => setIsMobileMenuOpen(false)}
                        className="flex-1 text-center bg-white dark:bg-[#0a0a0a] border-2 border-gray-200 text-gray-700 dark:text-neutral-300 px-6 py-3.5 rounded-xl text-[15px] font-bold hover:border-gray-300 hover:text-[#E53935] transition-all duration-300"
                      >
                        Login
                      </Link>
                    </div>
                    <Link
                      href="/register"
                      onClick={() => setIsMobileMenuOpen(false)}
                      className="w-full text-center bg-slate-50 dark:bg-[#111111] text-[#111111] dark:text-white px-6 py-3.5 rounded-xl text-[15px] font-bold hover:bg-black hover:shadow-[0_8px_20px_rgba(0,0,0,0.15)] transition-all duration-300"
                    >
                      Sign Up
                    </Link>
                  </div>
                )}
                <Link
                  href="/login"
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="w-full text-center bg-[#E53935] text-white px-6 py-4 rounded-xl text-[15px] font-bold shadow-[0_8px_20px_rgba(229,57,53,0.25)] hover:bg-red-600 hover:shadow-[0_12px_25px_rgba(229,57,53,0.35)] hover:-translate-y-0.5 transition-all duration-300"
                >
                  Try for Free
                </Link>
              </motion.div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </nav>

      {/* Mobile Bottom Navigation Bar */}
      <div className="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-[#0a0a0a] border-t border-[#E2E2E7] dark:border-neutral-800 z-[60] pb-safe shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
        <div className="flex items-center justify-around h-16 px-2">
          <Link href="/" className={`flex flex-col items-center justify-center w-full h-full space-y-1 ${pathname === '/' ? 'text-[#E53935]' : 'text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white'}`}>
            <Home size={20} className={pathname === '/' ? 'fill-red-500/20 stroke-2' : 'stroke-[1.5]'} />
            <span className="text-[10px] font-semibold">Home</span>
          </Link>
          
          <Link href="/software" className={`flex flex-col items-center justify-center w-full h-full space-y-1 ${pathname === '/software' ? 'text-[#E53935]' : 'text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white'}`}>
            <Monitor size={20} className={pathname === '/software' ? 'fill-red-500/20 stroke-2' : 'stroke-[1.5]'} />
            <span className="text-[10px] font-semibold">Software</span>
          </Link>
          
          <Link href="/products" className={`flex flex-col items-center justify-center w-full h-full space-y-1 ${pathname === '/products' ? 'text-[#E53935]' : 'text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white'}`}>
            <Server size={20} className={pathname === '/products' ? 'fill-red-500/20 stroke-2' : 'stroke-[1.5]'} />
            <span className="text-[10px] font-semibold">Hardware</span>
          </Link>
          
          <Link href="/pricing" className={`flex flex-col items-center justify-center w-full h-full space-y-1 ${pathname === '/pricing' ? 'text-[#E53935]' : 'text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white'}`}>
            <Tag size={20} className={pathname === '/pricing' ? 'fill-red-500/20 stroke-2' : 'stroke-[1.5]'} />
            <span className="text-[10px] font-semibold">Pricing</span>
          </Link>

          <button 
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className={`flex flex-col items-center justify-center w-full h-full space-y-1 ${isMobileMenuOpen ? 'text-[#E53935]' : 'text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white'}`}
          >
            {isMobileMenuOpen ? <X size={20} className="stroke-2" /> : <Menu size={20} className="stroke-[1.5]" />}
            <span className="text-[10px] font-semibold">Menu</span>
          </button>
        </div>
      </div>
    </>
  );
}
