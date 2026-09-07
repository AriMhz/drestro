"use client";

import { useState, useEffect } from "react";

export default function LoadingScreen() {
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Check if it's the first time in this session
    const hasVisited = sessionStorage.getItem("hasVisited");
    
    if (hasVisited) {
      setLoading(false);
    } else {
      // Show loading screen for 2 seconds
      const timer = setTimeout(() => {
        sessionStorage.setItem("hasVisited", "true");
        setLoading(false);
      }, 2000);
      
      return () => clearTimeout(timer);
    }
  }, []);

  if (!loading) return null;

  return (
    <div className="fixed inset-0 z-[9999] bg-slate-50 dark:bg-[#111111] flex items-center justify-center transition-opacity duration-500">
      <div className="relative flex flex-col items-center">
        {/* Pulsing glow behind logo */}
        <div className="absolute inset-0 bg-[#E53935] rounded-full blur-[60px] opacity-20 animate-pulse"></div>
        
        {/* Logo */}
        <img 
          src="/logos/logo.svg" 
          alt="DRestro Logo" 
          className="h-10 md:h-12 w-auto relative z-10 animate-bounce block dark:hidden"
        />
        <img 
          src="/logos/logo-light.svg" 
          alt="DRestro Logo" 
          className="h-10 md:h-12 w-auto relative z-10 animate-bounce hidden dark:block"
        />
        
        {/* Loading text */}
        <div className="mt-8 flex items-center gap-2 relative z-10">
          <div className="w-2 h-2 bg-[#E53935] rounded-full animate-bounce [animation-delay:-0.3s]"></div>
          <div className="w-2 h-2 bg-[#E53935] rounded-full animate-bounce [animation-delay:-0.15s]"></div>
          <div className="w-2 h-2 bg-[#E53935] rounded-full animate-bounce"></div>
        </div>
      </div>
    </div>
  );
}
