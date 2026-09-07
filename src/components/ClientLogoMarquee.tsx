"use client";

import { useState, useEffect } from "react";

interface ClientLogo {
  id: string;
  url: string;
  name: string | null;
  order: number;
}

export default function ClientLogoMarquee() {
  const [logos, setLogos] = useState<ClientLogo[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchLogos = async () => {
      try {
        const res = await fetch("/api/client-logos");
        if (res.ok) {
          const data = await res.json();
          setLogos(data);
        }
      } catch (error) {
        console.error("Failed to fetch logos:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchLogos();
  }, []);

  if (loading || logos.length === 0) return null;

  return (
    <section className="py-12 bg-white dark:bg-[#0a0a0a] border-y border-[#E2E2E7] dark:border-neutral-800 overflow-hidden">
      <div className="max-w-7xl mx-auto px-6 mb-8 text-center">
        <h3 className="text-[15px] font-bold text-gray-800 dark:text-neutral-200 dark:text-gray-200 tracking-wide uppercase">
          Trusted by 10,000+ restaurants of all sizes
        </h3>
      </div>
      
      <div className="relative flex w-full max-w-[100vw] overflow-hidden">
        {/* Left/Right Fade Gradients */}
        <div className="absolute left-0 top-0 bottom-0 w-24 md:w-48 bg-gradient-to-r from-white dark:from-[#0a0a0a] to-transparent z-10 pointer-events-none"></div>
        <div className="absolute right-0 top-0 bottom-0 w-24 md:w-48 bg-gradient-to-l from-white dark:from-[#0a0a0a] to-transparent z-10 pointer-events-none"></div>
        
        {/* Logo Track */}
        <div className="flex items-center gap-16 md:gap-24 min-w-max animate-marquee">
          {logos.map((logo, idx) => (
            <div key={`${logo.id}-${idx}`} className="flex-shrink-0 w-32 md:w-40 flex items-center justify-center opacity-70 hover:opacity-100 transition-opacity grayscale hover:grayscale-0 dark:invert">
              <img 
                src={logo.url} 
                alt={logo.name || "Client"} 
                className="max-h-16 w-auto object-contain"
                loading="lazy"
              />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
