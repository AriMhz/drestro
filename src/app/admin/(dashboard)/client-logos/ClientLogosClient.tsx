"use client";

import { useState, useEffect, useRef } from "react";
import { Upload, X, Image as ImageIcon, Loader2, Trash2 } from "lucide-react";
import { motion, AnimatePresence } from "motion/react";

interface ClientLogo {
  id: string;
  url: string;
  name: string | null;
  order: number;
}

export default function ClientLogosClient() {
  const [logos, setLogos] = useState<ClientLogo[]>([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    fetchLogos();
  }, []);

  const fetchLogos = async () => {
    try {
      const res = await fetch("/api/admin/client-logos");
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

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setUploading(true);
    const formData = new FormData();
    formData.append("image", file);
    formData.append("name", file.name);

    try {
      const res = await fetch("/api/admin/client-logos", {
        method: "POST",
        body: formData,
      });

      if (res.ok) {
        const newLogo = await res.json();
        setLogos((prev) => [...prev, newLogo]);
      } else {
        alert("Failed to upload logo.");
      }
    } catch (error) {
      console.error("Upload error:", error);
      alert("Error uploading logo.");
    } finally {
      setUploading(false);
      if (fileInputRef.current) {
        fileInputRef.current.value = "";
      }
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this logo?")) return;

    try {
      const res = await fetch(`/api/admin/client-logos/${id}`, {
        method: "DELETE",
      });

      if (res.ok) {
        setLogos((prev) => prev.filter((l) => l.id !== id));
      } else {
        alert("Failed to delete logo.");
      }
    } catch (error) {
      console.error("Delete error:", error);
      alert("Error deleting logo.");
    }
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Client Logos</h1>
        <p className="text-slate-500 dark:text-gray-400 mt-1">
          Manage the trusted client logos that appear in the scrolling marquee on the homepage.
        </p>
      </div>

      <div className="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#333333] rounded-2xl p-6 shadow-sm">
        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Upload New Logo</h2>
        
        <div 
          className="border-2 border-dashed border-gray-300 dark:border-[#333333] rounded-xl p-10 flex flex-col items-center justify-center bg-gray-50 dark:bg-[#222222] hover:bg-gray-100 dark:hover:bg-[#333333] transition-colors cursor-pointer"
          onClick={() => fileInputRef.current?.click()}
        >
          {uploading ? (
            <div className="flex flex-col items-center">
              <Loader2 className="h-10 w-10 text-[#E53935] animate-spin mb-4" />
              <p className="text-sm font-medium text-gray-600 dark:text-gray-300">Compressing & Uploading...</p>
            </div>
          ) : (
            <>
              <div className="h-12 w-12 rounded-full bg-white dark:bg-[#111111] border border-gray-200 dark:border-[#333333] shadow-sm flex items-center justify-center mb-4 text-[#E53935]">
                <Upload size={24} />
              </div>
              <p className="text-sm font-medium text-gray-900 dark:text-white">Click or drag image to upload</p>
              <p className="text-xs text-slate-500 dark:text-gray-400 mt-1">PNG, JPG, SVG up to 2MB. Auto-converted to WebP.</p>
            </>
          )}
          <input 
            type="file" 
            ref={fileInputRef} 
            className="hidden" 
            accept="image/*"
            onChange={handleFileUpload}
            disabled={uploading}
          />
        </div>
      </div>

      <div className="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#333333] rounded-2xl p-6 shadow-sm">
        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-6">Current Logos</h2>
        
        {loading ? (
          <div className="flex justify-center items-center py-12">
            <Loader2 className="h-8 w-8 text-[#E53935] animate-spin" />
          </div>
        ) : logos.length === 0 ? (
          <div className="text-center py-12 border-2 border-dashed border-gray-200 dark:border-[#333333] rounded-xl">
            <ImageIcon className="mx-auto h-12 w-12 text-slate-400 dark:text-gray-500 mb-3" />
            <p className="text-slate-500 dark:text-gray-400">No logos uploaded yet.</p>
          </div>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <AnimatePresence>
              {logos.map((logo) => (
                <motion.div
                  key={logo.id}
                  initial={{ opacity: 0, scale: 0.9 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.9 }}
                  className="relative group border border-gray-200 dark:border-[#333333] rounded-xl p-4 bg-gray-50 dark:bg-[#222222] flex items-center justify-center h-32"
                >
                  <img 
                    src={logo.url} 
                    alt={logo.name || "Client Logo"} 
                    className="max-h-full max-w-full object-contain dark:invert"
                  />
                  <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center">
                    <button
                      onClick={() => handleDelete(logo.id)}
                      className="p-2 bg-white text-red-600 rounded-full hover:bg-red-50 hover:scale-110 transition-all shadow-lg"
                      title="Delete Logo"
                    >
                      <Trash2 size={18} />
                    </button>
                  </div>
                </motion.div>
              ))}
            </AnimatePresence>
          </div>
        )}
      </div>
    </div>
  );
}
