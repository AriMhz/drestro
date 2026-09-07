"use client";

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { motion } from 'motion/react';
import { Mail, ArrowRight, Shield, ChevronLeft, CheckCircle2, Key } from 'lucide-react';

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  
  const [phoneMode, setPhoneMode] = useState(false);
  const [otp, setOtp] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [resetSuccess, setResetSuccess] = useState(false);
  const [phoneVal, setPhoneVal] = useState('');
  
  // Simple Math CAPTCHA
  const [num1, setNum1] = useState(0);
  const [num2, setNum2] = useState(0);
  const [captchaAnswer, setCaptchaAnswer] = useState('');

  useEffect(() => {
    // Generate new math problem on load
    setNum1(Math.floor(Math.random() * 10) + 1);
    setNum2(Math.floor(Math.random() * 10) + 1);
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    
    // Verify CAPTCHA
    if (parseInt(captchaAnswer) !== num1 + num2) {
      setError('Incorrect math answer. Please try again.');
      setCaptchaAnswer('');
      // Generate new problem
      setNum1(Math.floor(Math.random() * 10) + 1);
      setNum2(Math.floor(Math.random() * 10) + 1);
      return;
    }

    setIsLoading(true);

    try {
      const res = await fetch('/api/auth/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.error || 'Failed to send reset link');
      }

      if (data.type === 'phone') {
        setPhoneMode(true);
        setPhoneVal(data.phone);
      } else {
        setSuccess(true);
      }
    } catch (err: any) {
      setError(err.message || 'Something went wrong. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleResetSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    try {
      const res = await fetch('/api/auth/reset-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: otp, email: phoneVal, newPassword }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.error || 'Failed to reset password');
      }

      setResetSuccess(true);
    } catch (err: any) {
      setError(err.message || 'Something went wrong. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

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
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
          >
            <Link
              href="/login"
              className="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 dark:text-neutral-300 font-semibold mb-6 transition-colors w-fit"
            >
              <ChevronLeft size={16} />
              <span>Back to login</span>
            </Link>

            <div className="bg-white dark:bg-[#0a0a0a] rounded-2xl p-8 border border-gray-200/80 dark:border-neutral-800 shadow-[0_4px_20px_rgba(0,0,0,0.06)] dark:shadow-none">
              {resetSuccess ? (
                <div className="text-center py-6">
                  <div className="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-5">
                    <CheckCircle2 className="text-emerald-500 w-8 h-8" />
                  </div>
                  <h2 className="text-2xl font-bold text-[#111111] dark:text-white mb-2">Password Reset Successful</h2>
                  <p className="text-gray-500 text-[15px] leading-relaxed mb-8">
                    Your password has been reset successfully. You can now sign in using your new credentials.
                  </p>
                  <Link
                    href="/login"
                    className="w-full py-4 rounded-xl font-bold text-[15px] transition-all duration-300 shadow-sm cursor-pointer bg-gradient-to-r from-[#E53935] to-red-600 text-white hover:-translate-y-0.5 flex justify-center items-center"
                  >
                    Go to Sign In
                  </Link>
                </div>
              ) : phoneMode ? (
                <>
                  <div className="flex items-center gap-3 mb-8 pb-6 border-b border-gray-100 dark:border-neutral-800">
                    <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-[#E53935] to-red-600 flex items-center justify-center shadow-lg shadow-red-500/20 shrink-0">
                      <Key size={20} className="text-white" />
                    </div>
                    <div>
                      <h2 className="font-bold text-[#111111] dark:text-white text-xl">Reset Password</h2>
                      <p className="text-xs text-gray-400 mt-1 flex items-center gap-1.5 flex-wrap">
                        <span>OTP sent to {phoneVal}.</span>
                        <button type="button" onClick={() => setPhoneMode(false)} className="text-[#E53935] hover:underline font-bold cursor-pointer">Edit</button>
                      </p>
                    </div>
                  </div>

                  <form className="space-y-5" onSubmit={handleResetSubmit}>
                    {error && (
                      <div className="bg-red-50 text-[#E53935] text-sm p-3 rounded-lg border border-red-100 flex items-center justify-center font-medium">
                        {error}
                      </div>
                    )}

                    <div>
                      <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">OTP Code</label>
                      <input
                        type="text"
                        value={otp}
                        onChange={(e) => setOtp(e.target.value)}
                        placeholder="Enter 6-digit OTP"
                        className="w-full px-4 py-3.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 focus:bg-white dark:bg-[#111111] dark:focus:bg-[#151515] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px] tracking-widest font-mono text-center font-bold text-[#111111] dark:text-white"
                        required
                        maxLength={6}
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">New Password</label>
                      <input
                        type="password"
                        value={newPassword}
                        onChange={(e) => setNewPassword(e.target.value)}
                        placeholder="••••••••"
                        className="w-full px-4 py-3.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 focus:bg-white dark:bg-[#111111] dark:focus:bg-[#151515] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px] text-[#111111] dark:text-white"
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
                        'Reset Password'
                      )}
                    </button>
                  </form>
                </>
              ) : success ? (
                <div className="text-center py-6">
                  <div className="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-5">
                    <CheckCircle2 className="text-emerald-500 w-8 h-8" />
                  </div>
                  <h2 className="text-2xl font-bold text-[#111111] dark:text-white mb-2">Check your email</h2>
                  <p className="text-gray-500 text-[15px] leading-relaxed mb-8">
                    If an account exists for <span className="font-semibold text-gray-700 dark:text-neutral-300">{email}</span>, we have sent a secure password reset link to it.
                  </p>
                  <p className="text-xs text-gray-400">
                    Didn&apos;t receive it? Check your spam folder or <button onClick={() => setSuccess(false)} className="text-[#E53935] hover:underline font-semibold">try another email</button>.
                  </p>
                </div>
              ) : (
                <>
                  <div className="flex items-center gap-3 mb-8 pb-6 border-b border-gray-100 dark:border-neutral-800">
                    <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-[#E53935] to-red-600 flex items-center justify-center shadow-lg shadow-red-500/20 shrink-0">
                      <Mail size={20} className="text-white" />
                    </div>
                    <div>
                      <h2 className="font-bold text-[#111111] dark:text-white text-xl">Reset Password</h2>
                      <p className="text-xs text-gray-400 mt-1">We&apos;ll send you a link or OTP</p>
                    </div>
                  </div>

                  <form className="space-y-5" onSubmit={handleSubmit}>
                    {error && (
                      <div className="bg-red-50 text-[#E53935] text-sm p-3 rounded-lg border border-red-100 flex items-center justify-center font-medium">
                        {error}
                      </div>
                    )}
                    
                    <div>
                      <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">Email or Phone Number</label>
                      <input
                        type="text"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder="you@restaurant.com or 98xxxxxxxx"
                        className="w-full px-4 py-3.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 focus:bg-white dark:bg-[#111111] dark:focus:bg-[#151515] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px] text-[#111111] dark:text-white"
                        required
                      />
                    </div>

                    {/* CAPTCHA */}
                    <div className="bg-gray-50/80 dark:bg-[#111111] p-4 rounded-xl border border-gray-200/60 dark:border-neutral-800">
                      <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-2">Security Question</label>
                      <div className="flex items-center gap-3">
                        <div className="bg-white dark:bg-[#151515] px-4 py-2.5 rounded-lg border border-gray-200 dark:border-neutral-800 font-bold text-lg text-gray-600 dark:text-neutral-400 shrink-0 shadow-sm dark:shadow-none">
                          {num1} + {num2} = ?
                        </div>
                        <input
                          type="number"
                          value={captchaAnswer}
                          onChange={(e) => setCaptchaAnswer(e.target.value)}
                          placeholder="Answer"
                          className="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-neutral-800 bg-white dark:bg-[#151515] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px] text-[#111111] dark:text-white"
                          required
                        />
                      </div>
                    </div>

                    <button
                      type="submit"
                      disabled={isLoading}
                      className="w-full py-4 mt-2 rounded-xl font-bold text-[15px] transition-all duration-300 shadow-sm cursor-pointer bg-gradient-to-r from-[#E53935] to-red-600 text-white hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none flex justify-center items-center gap-2"
                    >
                      {isLoading ? (
                        <span className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                      ) : (
                        <>
                          <span>Send Verification</span>
                          <ArrowRight size={18} />
                        </>
                      )}
                    </button>
                  </form>

                  <div className="flex items-center justify-center gap-2 mt-6 pt-5 border-t border-gray-100 dark:border-neutral-800">
                    <Shield size={14} className="text-emerald-500" />
                    <span className="text-[11px] text-gray-400 font-medium">256-bit SSL encrypted • Your data is secure</span>
                  </div>
                </>
              )}
            </div>
          </motion.div>
        </div>
      </div>
    </div>
  );
}
