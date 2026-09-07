"use client";

import { useState } from "react";
import { User } from "lucide-react";

interface ClientAvatarProps {
  src?: string | null;
  name?: string | null;
  className?: string;
  size?: number;
}

export default function ClientAvatar({ src, name, className = "w-full h-full object-cover", size = 16 }: ClientAvatarProps) {
  const [error, setError] = useState(false);

  if (!src || error) {
    if (name) {
      return (
        <div className={`flex items-center justify-center bg-gray-100 text-slate-400 dark:text-gray-500 font-bold ${className.replace('object-cover', '')}`}>
          {name.charAt(0).toUpperCase()}
        </div>
      );
    }
    return <User size={size} className="text-slate-500 dark:text-gray-400 m-auto" />;
  }

  return (
    <img 
      src={src} 
      alt="Profile" 
      className={className} 
      onError={() => setError(true)}
    />
  );
}
