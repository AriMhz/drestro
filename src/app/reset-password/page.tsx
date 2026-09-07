"use client";

import React, { useState, Suspense } from 'react';
import Link from 'next/link';
import { motion } from 'motion/react';
import { Lock, ArrowRight, Shield, CheckCircle2 } from 'lucide-react';
import { useSearchParams } from 'next/navigation';

function ResetPasswordForm() {
  const searchParams = useSearchParams();
  const token = searchParams.get('token');
  const email = searchParams.get('email');

  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  // If missing token or email from URL, show error
  if (!token || !email) {
    return (
      <div className="text-center py-6">
        <h2 className="text-2xl font-bold text-[#111111] dark:text-white mb-2">Invalid Link</h2>
        <p className="text-gray-500 text-[15px] leading-relaxed mb-6">
          This password reset link is invalid or has expired.
        </p>
        <Link href="/forgot-password" className="text-[#E53935] hover:underline font-semibold">
          Request a new link
        </Link>
      </div>
    );
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (password !== confirmPassword) {
      setError("Passwords do not match.");
      return;
    }

    if (password.length < 6) {
      setError("Password must be at least 6 characters.");
      return;
    }

    setIsLoading(true);

    try {
      const res = await fetch('/api/auth/reset-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, email, newPassword: password }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.error || 'Failed to reset password');
      }

      setSuccess(true);
    } catch (err: any) {
      setError(err.message || 'Something went wrong. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  if (success) {
    return (
      <div className="text-center py-6">
        <div className="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-5">
          <CheckCircle2 className="text-emerald-500 w-8 h-8" />
        </div>
        <h2 className="text-2xl font-bold text-[#111111] dark:text-white mb-2">Password Reset Successful!</h2>
        <p className="text-gray-500 text-[15px] leading-relaxed mb-8">
          Your password has been successfully updated. You can now use your new password to sign in.
        </p>
        <Link
          href="/login"
          className="inline-block px-8 py-3.5 bg-[#111111] text-white rounded-xl font-bold text-[15px] hover:bg-black hover:shadow-lg transition-all duration-300"
        >
          Go to Login
        </Link>
      </div>
    );
  }

  return (
    <>
      <div className="flex items-center gap-3 mb-8 pb-6 border-b border-gray-100 dark:border-neutral-800">
        <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-[#111111] to-gray-700 flex items-center justify-center shadow-lg shadow-black/20 shrink-0">
          <Lock size={20} className="text-white" />
        </div>
        <div>
          <h2 className="font-bold text-[#111111] dark:text-white text-xl">Create New Password</h2>
          <p className="text-xs text-gray-400 mt-1">For {email}</p>
        </div>
      </div>

      <form className="space-y-5" onSubmit={handleSubmit}>
        {error && (
          <div className="bg-red-50 text-[#E53935] text-sm p-3 rounded-lg border border-red-100 flex items-center justify-center font-medium">
            {error}
          </div>
        )}
        
        <div>
          <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">New Password</label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="••••••••"
            className="w-full px-4 py-3.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 focus:bg-white dark:bg-[#111111] dark:focus:bg-[#151515] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px] text-[#111111] dark:text-white"
            required
            minLength={6}
          />
        </div>

        <div>
          <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1.5">Confirm New Password</label>
          <input
            type="password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            placeholder="••••••••"
            className="w-full px-4 py-3.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 focus:bg-white dark:bg-[#111111] dark:focus:bg-[#151515] focus:ring-2 focus:ring-[#E53935]/20 focus:border-[#E53935] outline-none transition-all text-[15px] text-[#111111] dark:text-white"
            required
            minLength={6}
          />
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
              <span>Update Password</span>
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
  );
}

export default function ResetPasswordPage() {
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
            className="text-center mb-10"
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
            className="bg-white dark:bg-[#0a0a0a] rounded-2xl p-8 border border-gray-200/80 dark:border-neutral-800 shadow-[0_4px_20px_rgba(0,0,0,0.06)] dark:shadow-none"
          >
            <Suspense fallback={<div className="text-center p-10"><span className="w-8 h-8 border-4 border-[#E53935]/30 border-t-[#E53935] rounded-full animate-spin inline-block"></span></div>}>
              <ResetPasswordForm />
            </Suspense>
          </motion.div>
        </div>
      </div>
    </div>
  );
}
