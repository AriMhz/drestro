"use client";

import { Key } from "lucide-react";

export default function LicenseKey({ licenseKey }: { licenseKey: string }) {
  const handleCopy = () => {
    if (typeof window !== "undefined") {
      navigator.clipboard.writeText(licenseKey);
      alert(`Copied License Key: ${licenseKey}`);
    }
  };

  return (
    <div className="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-lg text-[11px]">
      <span className="text-slate-400 dark:text-neutral-500 font-semibold uppercase flex items-center gap-1">
        <Key size={10} /> License:
      </span>
      <code 
        onClick={handleCopy}
        className="font-mono text-[#E53935] hover:underline cursor-pointer font-bold select-all"
        title="Click to copy POS License Key"
      >
        {licenseKey}
      </code>
    </div>
  );
}
