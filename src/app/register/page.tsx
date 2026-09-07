"use client";

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { motion } from 'motion/react';
import { signIn } from 'next-auth/react';
import { Monitor, ArrowRight, Shield, Cloud, ChevronLeft, MessageCircle, Download, Key, Headphones, RefreshCw } from 'lucide-react';
import { useRouter } from 'next/navigation';
import { useSiteSettings } from '@/src/context/SiteSettingsContext';

const fetchClientIpv4 = async (): Promise<string> => {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 1200); // 1.2s timeout
  try {
    const response = await fetch("https://api4.ipify.org?format=json", {
      signal: controller.signal,
    });
    clearTimeout(timeoutId);
    if (response.ok) {
      const data = await response.json();
      if (data.ip) return data.ip;
    }
  } catch (e) {
    console.warn("ipify failed, trying icanhazip", e);
  }

  // Fallback 1: icanhazip
  const controller2 = new AbortController();
  const timeoutId2 = setTimeout(() => controller2.abort(), 1000);
  try {
    const response = await fetch("https://ipv4.icanhazip.com", {
      signal: controller2.signal,
    });
    clearTimeout(timeoutId2);
    if (response.ok) {
      const ip = (await response.text()).trim();
      if (ip) return ip;
    }
  } catch (e) {
    console.warn("icanhazip failed", e);
  }

  return "";
};

export default function RegisterPage() {
  const [selectedPortal, setSelectedPortal] = useState<'online' | 'offline' | null>(null);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [otp, setOtp] = useState('');
  const [otpSent, setOtpSent] = useState(false);
  const [isSendingOtp, setIsSendingOtp] = useState(false);
  const [otpTimer, setOtpTimer] = useState(0);
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const router = useRouter();
  const { whatsappNumber } = useSiteSettings();

  const handlePhoneChange = (val: string) => {
    // Keep only digits and '+' character initially
    let cleaned = val.replace(/[^0-9+]/g, '');
    
    // Auto-strip country codes if user types/pastes them
    if (cleaned.startsWith('+977')) {
      cleaned = cleaned.substring(4);
    } else if (cleaned.startsWith('977') && cleaned.length > 10) {
      cleaned = cleaned.substring(3);
    }
    
    // Strip any remaining non-digit characters
    cleaned = cleaned.replace(/[^0-9]/g, '');
    
    // Limit to exactly 10 digits
    if (cleaned.length > 10) {
      cleaned = cleaned.substring(0, 10);
    }
    
    setPhone(cleaned);
  };

  // OTP Timer countdown
  useEffect(() => {
    let interval: any;
    if (otpTimer > 0) {
      interval = setInterval(() => {
        setOtpTimer((prev) => prev - 1);
      }, 1000);
    }
    return () => clearInterval(interval);
  }, [otpTimer]);

  const handleSendOtp = async () => {
    if (!phone) {
      setError('Please enter a valid phone number first');
      return;
    }
    setIsSendingOtp(true);
    setError('');
    try {
      const res = await fetch('/api/auth/otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone, purpose: 'register' }),
      });
      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error || 'Failed to send OTP');
      }
      setOtpSent(true);
      setOtpTimer(60); // 60 seconds cooldown
    } catch (err: any) {
      setError(err.message || 'Failed to send OTP. Please try again.');
    } finally {
      setIsSendingOtp(false);
    }
  };

  const handleGoogleSignUp = async () => {
    setIsLoading(true);
    await signIn('google', { callbackUrl: '/api/auth/sso' });
  };

  const handleEmailSignUp = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!otpSent) {
      setError('Please request and verify your phone number OTP code');
      return;
    }
    setIsLoading(true);
    setError('');

    try {
      const clientIpv4 = await fetchClientIpv4();
      
      const searchParams = new URLSearchParams(window.location.search);
      const ref = searchParams.get('ref') || '';

      // 1. Create the user in the database
      const res = await fetch('/api/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, phone, password, otp, clientIpv4, ref }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.error || 'Registration failed');
      }

      // 2. Automatically log them in after successful registration
      const signInRes = await signIn('credentials', {
        email,
        password,
        clientIpv4,
        redirect: false,
      });

      if (signInRes?.error) {
        setError('Registered successfully, but auto-login failed.');
      } else {
        window.location.href = '/api/auth/sso';
      }
    } catch (err: any) {
      setError(err.message || 'Something went wrong. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const WHATSAPP_MSG = encodeURIComponent('Hi! I am interested in the DRestro Offline POS software. I would like to know more about licensing, pricing and installation.');

  return (
    <div className="min-h-screen bg-[#fafafa] dark:bg-[#0a0a0a] flex flex-col">
      {/* Subtle background pattern */}
      <div className="fixed inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:20px_20px] opacity-30 pointer-events-none"></div>
      <div className="fixed top-0 right-0 w-[600px] h-[600px] bg-[#E53935]/5 rounded-full blur-[150px] pointer-events-none"></div>
      <div className="fixed bottom-0 left-0 w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[150px] pointer-events-none"></div>

      <div className="flex-1 flex items-center justify-center px-4 py-16 relative z-10">
        <div className="w-full max-w-[520px]">
          
          {/* Logo */}
          <motion.div 
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-center mb-8"
          >
            <Link href="/">
              <img src="/logos/logo.svg" alt="DRestro" className="h-8 mx-auto mb-6 hover:scale-105 transition-transform dark:hidden" />
              <img src="/logos/logo-light.svg" alt="DRestro" className="h-8 mx-auto mb-6 hover:scale-105 transition-transform hidden dark:block" />
            </Link>
            <h1 className="text-3xl font-black text-[#111111] dark:text-white tracking-tight mb-2">
              Create an Account
            </h1>
            <p className="text-gray-500 text-[15px]">
              Join DRestro and manage your restaurant easily
            </p>
          </motion.div>

          {/* Portal Selector */}
          {!selectedPortal ? (
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.1 }}
              className="space-y-4"
            >
              <p className="text-center text-sm font-semibold text-gray-400 uppercase tracking-widest mb-6">Choose your platform</p>
              
              {/* Online POS Card */}
              <button
                onClick={() => setSelectedPortal('online')}
                className="w-full group bg-white dark:bg-[#0a0a0a] rounded-2xl p-6 border border-gray-200/80 hover:border-[#E53935]/40 hover:shadow-[0_8px_30px_rgba(229,57,53,0.1)] transition-all duration-300 text-left cursor-pointer"
              >
                <div className="flex items-start gap-5">
                  <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#E53935] to-red-600 flex items-center justify-center shadow-lg shadow-red-500/20 shrink-0">
                    <Cloud size={26} className="text-white" />
                  </div>
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-1">
                      <h3 className="text-lg font-bold text-[#111111] dark:text-white">Online POS Portal</h3>
                      <span className="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded-full border border-emerald-200">CLOUD</span>
                    </div>
                    <p className="text-sm text-gray-500 leading-relaxed">
                      Access your cloud-hosted restaurant management system from anywhere. Manage orders, billing & reports online.
                    </p>
                    <div className="flex items-center gap-1.5 mt-3 text-[#E53935] text-sm font-semibold">
                      <span>Create cloud account</span>
                      <ArrowRight size={16} className="group-hover:translate-x-1 transition-transform" />
                    </div>
                  </div>
                </div>
              </button>

              {/* Offline POS Card */}
              <button
                onClick={() => setSelectedPortal('offline')}
                className="w-full group bg-white dark:bg-[#0a0a0a] rounded-2xl p-6 border border-gray-200/80 hover:border-blue-500/40 hover:shadow-[0_8px_30px_rgba(59,130,246,0.1)] transition-all duration-300 text-left cursor-pointer"
              >
                <div className="flex items-start gap-5">
                  <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#111111] to-gray-700 flex items-center justify-center shadow-lg shadow-black/20 shrink-0">
                    <Monitor size={26} className="text-white" />
                  </div>
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-1">
                      <h3 className="text-lg font-bold text-[#111111] dark:text-white">Offline POS Software</h3>
                      <span className="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-full border border-blue-200">LOCAL</span>
                    </div>
                    <p className="text-sm text-gray-500 leading-relaxed">
                      Get the Windows desktop POS installed on your local hardware. Runs on your Wi-Fi — zero internet needed.
                    </p>
                    <div className="flex items-center gap-1.5 mt-3 text-[#111111] dark:text-white text-sm font-semibold">
                      <span>Get software via WhatsApp</span>
                      <ArrowRight size={16} className="group-hover:translate-x-1 transition-transform" />
                    </div>
                  </div>
                </div>
              </button>

              {/* Divider */}
              <div className="flex items-center gap-4 py-4">
                <div className="flex-1 h-px bg-gray-200"></div>
                <span className="text-xs text-gray-400 font-semibold">ALREADY HAVE AN ACCOUNT?</span>
                <div className="flex-1 h-px bg-gray-200"></div>
              </div>

              <Link
                href="/login"
                className="block w-full text-center py-4 rounded-xl border-2 border-dashed border-gray-200 text-gray-500 font-bold text-[15px] hover:border-[#E53935]/40 hover:text-[#E53935] hover:bg-red-50/30 transition-all duration-300"
              >
                Sign In to Dashboard →
              </Link>
            </motion.div>
          ) : selectedPortal === 'offline' ? (
            /* Offline POS — WhatsApp Contact Flow */
            <motion.div
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.3 }}
            >
              {/* Back button */}
              <button
                onClick={() => setSelectedPortal(null)}
                className="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 dark:text-neutral-300 font-semibold mb-6 transition-colors cursor-pointer"
              >
                <ChevronLeft size={16} />
                <span>Back to options</span>
              </button>

              <div className="bg-white dark:bg-[#0a0a0a] rounded-2xl p-8 border border-gray-200/80 shadow-[0_4px_20px_rgba(0,0,0,0.06)]">
                {/* Header */}
                <div className="flex items-center gap-3 mb-6 pb-6 border-b border-gray-100">
                  <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-[#111111] to-gray-700 flex items-center justify-center shadow-lg shadow-black/20 shrink-0">
                    <Monitor size={20} className="text-white" />
                  </div>
                  <div>
                    <h2 className="font-bold text-[#111111] dark:text-white text-lg">Offline POS Software</h2>
                    <p className="text-xs text-gray-400">Windows Desktop Application</p>
                  </div>
                </div>

                {/* What you get */}
                <h3 className="text-sm font-bold text-gray-700 dark:text-neutral-300 uppercase tracking-wider mb-4">What you&apos;ll get</h3>
                <div className="space-y-3 mb-8">
                  {[
                    { icon: <Download size={18} />, title: 'Software Installer', desc: 'Full DRestro POS .exe installer for Windows' },
                    { icon: <Key size={18} />, title: 'License Key', desc: 'Unique activation key for your restaurant' },
                    { icon: <Headphones size={18} />, title: 'Installation Support', desc: 'Our team will help set it up on your hardware' },
                    { icon: <RefreshCw size={18} />, title: 'Free Updates', desc: 'Lifetime software updates & bug fixes' }
                  ].map((item, i) => (
                    <div key={i} className="flex items-start gap-3.5 p-3 rounded-xl bg-gray-50/80 border border-gray-100">
                      <div className="w-9 h-9 rounded-lg bg-white dark:bg-[#0a0a0a] border border-gray-200 flex items-center justify-center text-[#E53935] shrink-0 mt-0.5">
                        {item.icon}
                      </div>
                      <div>
                        <p className="text-sm font-bold text-[#111111] dark:text-white">{item.title}</p>
                        <p className="text-xs text-gray-500">{item.desc}</p>
                      </div>
                    </div>
                  ))}
                </div>

                {/* WhatsApp CTA */}
                <a
                  href={`https://wa.me/${whatsappNumber}?text=${WHATSAPP_MSG}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full flex items-center justify-center gap-3 bg-[#25D366] text-white py-4 rounded-xl font-bold text-[15px] hover:bg-[#1fb855] hover:shadow-[0_8px_25px_rgba(37,211,102,0.35)] hover:-translate-y-0.5 transition-all duration-300 shadow-lg shadow-green-500/20 cursor-pointer"
                >
                  <MessageCircle size={22} />
                  <span>Chat on WhatsApp to Get Started</span>
                </a>

                <p className="text-center text-xs text-gray-400 mt-4">
                  Or call directly: <a href={`tel:+${whatsappNumber}`} className="text-[#111111] dark:text-white font-semibold hover:text-[#E53935] transition-colors">+{whatsappNumber}</a>
                </p>
              </div>

              {/* Help text */}
              <p className="text-center text-sm text-gray-400 mt-6">
                Want a live demo first? <Link href="/contact" className="text-[#E53935] font-semibold hover:underline">Book a Demo</Link>
              </p>
            </motion.div>
          ) : (
            /* Online POS — Register Form */
            <motion.div
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.3 }}
            >
              {/* Back button */}
              <button
                onClick={() => setSelectedPortal(null)}
                className="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 dark:text-neutral-300 font-semibold mb-6 transition-colors cursor-pointer"
              >
                <ChevronLeft size={16} />
                <span>Back to options</span>
              </button>

              <div className="bg-white dark:bg-[#0a0a0a] rounded-2xl p-8 border border-gray-200/80 shadow-[0_4px_20px_rgba(0,0,0,0.06)]">
                {/* Portal indicator */}
                <div className="flex items-center gap-3 mb-8 pb-6 border-b border-gray-100">
                  <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-[#E53935] to-red-600 flex items-center justify-center shadow-lg shadow-red-500/20 shrink-0">
                    <Cloud size={20} className="text-white" />
                  </div>
                  <div>
                    <h2 className="font-bold text-[#111111] dark:text-white text-lg">Create Portal Account</h2>
                    <p className="text-xs text-gray-400">Start your journey with Drestro</p>
                  </div>
                </div>

                {/* Google Sign In */}
                <button 
                  onClick={handleGoogleSignUp}
                  disabled={isLoading}
                  className="w-full flex items-center justify-center gap-3 bg-white dark:bg-[#0a0a0a] border border-gray-200 rounded-xl py-3.5 text-[15px] font-semibold text-gray-700 dark:text-neutral-300 hover:bg-gray-50 hover:border-gray-300 hover:shadow-sm transition-all duration-300 mb-5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg className="w-5 h-5" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                  </svg>
                  <span>Sign up with Google</span>
                </button>

                {/* Divider */}
                <div className="flex items-center gap-4 mb-5">
                  <div className="flex-1 h-px bg-gray-200"></div>
                  <span className="text-xs text-gray-400 font-medium">or register with email</span>
                  <div className="flex-1 h-px bg-gray-200"></div>
                </div>

                {/* Email/Password Form */}
                <form className="space-y-4" onSubmit={handleEmailSignUp}>
                  {error && (
                    <div className="bg-red-50 text-[#E53935] text-sm p-3 rounded-lg border border-red-100 flex items-center justify-center font-medium">
                      {error}
                    </div>
                  )}
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">Full Name</label>
                    <input
                      type="text"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      placeholder="John Doe"
                      className="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white dark:bg-[#0a0a0a] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px]"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">Email Address</label>
                    <input
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="you@restaurant.com"
                      className="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white dark:bg-[#0a0a0a] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px]"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">Phone Number</label>
                    <div className="flex gap-3">
                      <input
                        type="text"
                        value={phone}
                        onChange={(e) => handlePhoneChange(e.target.value)}
                        placeholder="98xxxxxxxx"
                        className="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white dark:bg-[#0a0a0a] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px]"
                        required
                        disabled={otpSent}
                      />
                      <button
                        type="button"
                        onClick={handleSendOtp}
                        disabled={isSendingOtp || otpTimer > 0}
                        className="px-4 py-3.5 bg-gray-100 hover:bg-gray-200 border border-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap min-w-[100px] flex items-center justify-center"
                      >
                        {isSendingOtp ? (
                          <span className="w-4 h-4 border-2 border-gray-500/30 border-t-gray-500 rounded-full animate-spin"></span>
                        ) : otpTimer > 0 ? (
                          `Resend in ${otpTimer}s`
                        ) : otpSent ? (
                          'Resend'
                        ) : (
                          'Send OTP'
                        )}
                      </button>
                    </div>
                  </div>

                  {otpSent && (
                    <motion.div
                      initial={{ opacity: 0, y: -10 }}
                      animate={{ opacity: 1, y: 0 }}
                    >
                      <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">OTP Code</label>
                      <input
                        type="text"
                        value={otp}
                        onChange={(e) => setOtp(e.target.value)}
                        placeholder="Enter 6-digit OTP"
                        className="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white dark:bg-[#0a0a0a] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px] tracking-widest font-mono text-center font-bold"
                        required
                        maxLength={6}
                      />
                    </motion.div>
                  )}
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">Password</label>
                    <input
                      type="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="••••••••"
                      className="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white dark:bg-[#0a0a0a] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px]"
                      required
                      minLength={6}
                    />
                  </div>

                  <button
                    type="submit"
                    disabled={isLoading}
                    className="w-full py-4 rounded-xl font-bold text-[15px] transition-all duration-300 shadow-sm cursor-pointer bg-gradient-to-r from-[#E53935] to-red-600 text-white hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none flex justify-center items-center gap-2"
                  >
                    {isLoading ? (
                      <span className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    ) : (
                      'Create Account'
                    )}
                  </button>
                </form>

                {/* Security badge */}
                <div className="flex items-center justify-center gap-2 mt-6 pt-5 border-t border-gray-100">
                  <Shield size={14} className="text-emerald-500" />
                  <span className="text-[11px] text-gray-400 font-medium">256-bit SSL encrypted • Your data is secure</span>
                </div>
              </div>

              {/* Help text */}
              <p className="text-center text-sm text-gray-400 mt-6">
                Already have an account? <Link href="/login" className="text-[#E53935] font-semibold hover:underline">Sign In</Link>
              </p>
            </motion.div>
          )}
        </div>
      </div>
    </div>
  );
}
