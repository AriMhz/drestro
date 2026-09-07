"use client";

import React, { useState, useEffect, useRef } from "react";
import { useRouter, usePathname } from "next/navigation";
import Image from "next/image";
import { 
  Tag, HelpCircle, ImageIcon, MonitorSmartphone, PanelBottom,
  Plus, Edit, Trash2, Loader2, Save, Link as LinkIcon, FileText, 
  Phone, Server, GripVertical, Edit2, XCircle, QrCode, MessageSquare, Star
} from "lucide-react";
import toast from "react-hot-toast";

// Imports from other page components
import PricingAdminClient from "../pricing/PricingAdminClient";
import ClientLogosClient from "../client-logos/ClientLogosClient";
import HardwareForm from "../hardware/HardwareForm";

interface WebsiteClientProps {
  initialTab: string;
  editHardwareData: any;
  plans: any[];
  comparisonTable: any[];
  combos: any[];
  hardware: any[];
  saveComboAction: (formData: FormData) => Promise<void>;
  deleteComboAction: (formData: FormData) => Promise<void>;
  addHardwareAction: (formData: FormData) => Promise<{ success: boolean; error?: string }>;
  deleteHardwareAction: (formData: FormData) => Promise<void>;
  updateHardwareAction: (formData: FormData) => Promise<{ success: boolean; error?: string }>;
  deletePlanAction: (formData: FormData) => Promise<void>;
}

export default function WebsiteClient({
  initialTab,
  editHardwareData,
  plans,
  comparisonTable,
  combos,
  hardware,
  saveComboAction,
  deleteComboAction,
  addHardwareAction,
  deleteHardwareAction,
  updateHardwareAction,
  deletePlanAction,
}: WebsiteClientProps) {
  const router = useRouter();
  const pathname = usePathname();
  const [activeTab, setActiveTab] = useState(initialTab);

  // Sync state if initialTab changes (e.g., when route updates query param)
  useEffect(() => {
    setActiveTab(initialTab);
  }, [initialTab]);

  const handleTabChange = (newTab: string) => {
    setActiveTab(newTab);
    router.push(`${pathname}?tab=${newTab}`);
  };

  const tabs = [
    { id: "pricing", label: "Pricing & Plans", icon: Tag },
    { id: "faq", label: "FAQs", icon: HelpCircle },
    { id: "testimonials", label: "Testimonials", icon: MessageSquare },
    { id: "logos", label: "Client Logos", icon: ImageIcon },
    { id: "hardware", label: "Hardware Store", icon: MonitorSmartphone },
    { id: "footer", label: "Footer & Policies", icon: PanelBottom },
  ];

  return (
    <div className="space-y-6 max-w-7xl mx-auto pb-20">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-[#111111] dark:text-white">Website Content</h1>
        <p className="text-sm text-slate-500 dark:text-gray-400 mt-1">Manage public website settings, logos, FAQs, products, and pricing plans.</p>
      </div>

      {/* Tabs list */}
      <div className="flex justify-start border-b border-slate-200 dark:border-[#333333] pb-4 overflow-x-auto hide-scrollbar">
        <div className="flex p-1 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-xl shadow-sm whitespace-nowrap">
          {tabs.map((tab) => {
            const Icon = tab.icon;
            const isActive = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => handleTabChange(tab.id)}
                className={`flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all ${
                  isActive
                    ? "bg-[#E53935] text-white shadow-lg"
                    : "text-slate-500 dark:text-gray-400 hover:text-[#111111] dark:text-white"
                }`}
              >
                <Icon size={16} />
                <span>{tab.label}</span>
              </button>
            );
          })}
        </div>
      </div>

      {/* Tab content view */}
      <div className="transition-all duration-300">
        {activeTab === "pricing" && (
          // @ts-ignore
          <PricingAdminClient
            plans={plans}
            comparisonTable={comparisonTable}
            combos={combos}
            saveComboAction={saveComboAction}
            deleteComboAction={deleteComboAction}
            deletePlanAction={deletePlanAction}
          />
        )}

        {activeTab === "faq" && (
          <FaqTabContent />
        )}

        {activeTab === "testimonials" && (
          <TestimonialTabContent />
        )}

        {activeTab === "logos" && (
          <ClientLogosClient />
        )}

        {activeTab === "hardware" && (
          <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            {/* Add/Edit Hardware Form */}
            <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] p-6 rounded-2xl">
              <h2 className="text-lg font-bold text-[#111111] dark:text-white mb-6 flex items-center gap-2">
                {editHardwareData ? (
                  <><Edit size={20} className="text-emerald-500" /> Edit Product</>
                ) : (
                  <><Plus size={20} className="text-emerald-500" /> Add New Product</>
                )}
              </h2>
              
              <HardwareForm 
                addHardwareAction={addHardwareAction} 
                updateHardwareAction={updateHardwareAction}
                initialData={editHardwareData} 
              />
            </div>

            {/* Hardware Table */}
            <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden shadow-sm">
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-500 dark:text-gray-400">
                  <thead className="bg-slate-100 dark:bg-[#222222] text-slate-600 dark:text-gray-300 text-xs uppercase font-semibold">
                    <tr>
                      <th className="px-6 py-4">Image</th>
                      <th className="px-6 py-4">Product Name</th>
                      <th className="px-6 py-4">Price</th>
                      <th className="px-6 py-4">Status</th>
                      <th className="px-6 py-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-[#333333]">
                    {hardware.length === 0 && (
                      <tr>
                        <td colSpan={5} className="px-6 py-8 text-center text-slate-400 dark:text-gray-500">No hardware products found. Add one above!</td>
                      </tr>
                    )}
                    {hardware.map((item) => (
                      <tr key={item.id} className="hover:bg-slate-100 dark:bg-[#222222]/50 transition-colors">
                        <td className="px-6 py-4">
                          {item.imageUrl ? (
                            <div className="relative w-16 h-12 rounded-lg overflow-hidden border border-slate-200 dark:border-[#333333] bg-slate-50 dark:bg-[#111111]">
                              <Image src={item.imageUrl} alt={item.name} fill className="object-cover" sizes="64px" />
                            </div>
                          ) : (
                            <div className="w-16 h-12 rounded-lg border border-slate-200 dark:border-[#333333] bg-slate-50 dark:bg-[#111111] flex items-center justify-center text-gray-600 dark:text-neutral-400 text-xs">
                              No Img
                            </div>
                          )}
                        </td>
                        <td className="px-6 py-4 font-medium text-[#111111] dark:text-white">{item.name}</td>
                        <td className="px-6 py-4">Rs. {item.price.toLocaleString()}</td>
                        <td className="px-6 py-4">
                          <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${
                            item.stockStatus === 'In Stock' 
                              ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' 
                              : 'bg-red-500/10 text-red-500 border border-red-500/20'
                          }`}>
                            {item.stockStatus}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-right">
                          <div className="flex items-center justify-end gap-3">
                            <a href={`/admin/website?tab=hardware&edit=${item.id}`} className="text-slate-400 dark:text-gray-500 hover:text-emerald-500 transition-colors" title="Edit Product">
                              <Edit size={18} />
                            </a>
                            <form action={async (formData) => {
                              if (confirm("Are you sure you want to delete this hardware product?")) {
                                await deleteHardwareAction(formData);
                                toast.success("Product deleted successfully!");
                              }
                            }}>
                              <input type="hidden" name="id" value={item.id} />
                              <button type="submit" className="text-slate-400 dark:text-gray-500 hover:text-red-500 transition-colors" title="Delete Product">
                                <Trash2 size={18} />
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {activeTab === "footer" && (
          <FooterTabContent />
        )}
      </div>
    </div>
  );
}

// Inline Helper: Testimonial Tab Content
function TestimonialTabContent() {
  const [testimonials, setTestimonials] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<any>(null);
  
  // Form State
  const [content, setContent] = useState("");
  const [authorName, setAuthorName] = useState("");
  const [authorRole, setAuthorRole] = useState("");
  const [authorInitials, setAuthorInitials] = useState("");
  const [bgColor, setBgColor] = useState("bg-red-500");
  const [rating, setRating] = useState(5);
  const [order, setOrder] = useState(0);

  const fetchTestimonials = async () => {
    try {
      const res = await fetch('/api/admin/testimonials');
      if (res.ok) {
        const data = await res.json();
        setTestimonials(data);
      }
    } catch (err) {
      toast.error("Failed to load Testimonials");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTestimonials();
  }, []);

  const openModal = (item: any = null) => {
    setEditingItem(item);
    setContent(item ? item.content : "");
    setAuthorName(item ? item.authorName : "");
    setAuthorRole(item ? item.authorRole : "");
    setAuthorInitials(item ? item.authorInitials : "");
    setBgColor(item ? item.bgColor : "bg-red-500");
    setRating(item ? item.rating : 5);
    setOrder(item ? item.order : testimonials.length);
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setEditingItem(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!content || !authorName) {
      toast.error("Please fill in required fields");
      return;
    }

    const toastId = toast.loading("Saving Testimonial...");
    try {
      const payload = { content, authorName, authorRole, authorInitials, bgColor, rating: Number(rating), order: Number(order) };
      
      let res;
      if (editingItem) {
        res = await fetch('/api/admin/testimonials', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: editingItem.id, ...payload })
        });
      } else {
        res = await fetch('/api/admin/testimonials', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
      }

      if (res.ok) {
        toast.success(editingItem ? "Testimonial Updated" : "Testimonial Created", { id: toastId });
        fetchTestimonials();
        closeModal();
      } else {
        toast.error("Failed to save Testimonial", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this Testimonial?")) return;
    
    const toastId = toast.loading("Deleting Testimonial...");
    try {
      const res = await fetch(`/api/admin/testimonials?id=${id}`, { method: 'DELETE' });
      if (res.ok) {
        toast.success("Testimonial Deleted", { id: toastId });
        fetchTestimonials();
      } else {
        toast.error("Failed to delete Testimonial", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-bold text-gray-900 dark:text-white">Testimonials Management</h2>
          <p className="text-slate-500 dark:text-gray-400 text-sm mt-1">Manage what your clients are saying about you.</p>
        </div>
        <button 
          onClick={() => openModal()}
          className="flex items-center gap-2 px-4 py-2 bg-black dark:bg-[#1A1A1A] hover:bg-gray-800 dark:hover:bg-[#222222] text-white border border-slate-200 dark:border-[#333333] rounded-lg transition-colors shadow-sm"
        >
          <Plus size={18} />
          <span>Add Testimonial</span>
        </button>
      </div>

      <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-slate-200 dark:border-[#333333] overflow-hidden">
        {loading ? (
          <div className="p-12 flex justify-center items-center">
            <Loader2 className="animate-spin text-slate-500 dark:text-gray-400" size={32} />
          </div>
        ) : testimonials.length === 0 ? (
          <div className="p-12 text-center text-slate-400 dark:text-gray-500">
            No testimonials found. Click "Add Testimonial" to create one.
          </div>
        ) : (
          <div className="divide-y divide-slate-100 dark:divide-[#333333]">
            {testimonials.map((item) => (
              <div key={item.id} className="p-6 flex items-start gap-4 hover:bg-slate-50 dark:hover:bg-[#222222]/30 transition-colors group">
                <div className="mt-1 cursor-move text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors">
                  <GripVertical size={20} />
                </div>
                <div className="flex-grow">
                  <h3 className="text-base font-semibold text-gray-900 dark:text-white mb-1">{item.authorName} <span className="text-xs text-neutral-500 font-normal ml-2">{item.authorRole}</span></h3>
                  <p className="text-gray-600 dark:text-gray-300 text-sm whitespace-pre-wrap leading-relaxed italic">"{item.content}"</p>
                </div>
                <div className="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button 
                    onClick={() => openModal(item)}
                    className="p-2 text-slate-400 dark:text-gray-500 hover:text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors"
                  >
                    <Edit2 size={16} />
                  </button>
                  <button 
                    onClick={() => handleDelete(item.id)}
                    className="p-2 text-slate-400 dark:text-gray-500 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors"
                  >
                    <Trash2 size={16} />
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {isModalOpen && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl h-auto max-h-[90vh] flex flex-col">
            <div className="p-6 border-b border-slate-100 dark:border-[#333333] shrink-0">
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">{editingItem ? 'Edit Testimonial' : 'Add Testimonial'}</h2>
            </div>
            
            <form onSubmit={handleSubmit} className="p-6 space-y-4 overflow-y-auto dark-scroll">
              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Author Name <span className="text-red-500">*</span></label>
                <input 
                  type="text" 
                  value={authorName}
                  onChange={(e) => setAuthorName(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Author Role/Restaurant</label>
                <input 
                  type="text" 
                  value={authorRole}
                  onChange={(e) => setAuthorRole(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
                  placeholder="e.g. Store Manager, Himalayan Java"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Initials (Avatar)</label>
                  <input 
                    type="text" 
                    value={authorInitials}
                    onChange={(e) => setAuthorInitials(e.target.value)}
                    className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
                    placeholder="e.g. HJ"
                    maxLength={2}
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Color Class</label>
                  <input 
                    type="text" 
                    value={bgColor}
                    onChange={(e) => setBgColor(e.target.value)}
                    className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
                    placeholder="e.g. bg-red-500"
                  />
                </div>
              </div>
              
              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Review Content <span className="text-red-500">*</span></label>
                <textarea 
                  value={content}
                  onChange={(e) => setContent(e.target.value)}
                  rows={4}
                  className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 resize-none"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Rating (1-5)</label>
                  <input 
                    type="number" 
                    value={rating}
                    onChange={(e) => setRating(Number(e.target.value))}
                    min={1} max={5}
                    className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Display Order</label>
                  <input 
                    type="number" 
                    value={order}
                    onChange={(e) => setOrder(Number(e.target.value))}
                    className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
                  />
                </div>
              </div>

              <div className="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-[#333333] shrink-0 mt-4">
                <button 
                  type="button" 
                  onClick={closeModal}
                  className="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-[#222222] rounded-lg transition-colors font-medium"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="px-5 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium shadow-sm"
                >
                  {editingItem ? 'Update Testimonial' : 'Save Testimonial'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

// Inline Helper: FAQ Tab Content (refactored from faq/page.tsx)
function FaqTabContent() {
  const [faqs, setFaqs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingFaq, setEditingFaq] = useState<any>(null);
  
  // Form State
  const [question, setQuestion] = useState("");
  const [answer, setAnswer] = useState("");
  const [order, setOrder] = useState(0);

  const fetchFaqs = async () => {
    try {
      const res = await fetch('/api/admin/faq');
      if (res.ok) {
        const data = await res.json();
        setFaqs(data);
      }
    } catch (err) {
      toast.error("Failed to load FAQs");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFaqs();
  }, []);

  const openModal = (faq: any = null) => {
    setEditingFaq(faq);
    setQuestion(faq ? faq.question : "");
    setAnswer(faq ? faq.answer : "");
    setOrder(faq ? faq.order : faqs.length);
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setEditingFaq(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!question || !answer) {
      toast.error("Please fill in all fields");
      return;
    }

    const toastId = toast.loading("Saving FAQ...");
    try {
      const payload = { question, answer, order: Number(order) };
      
      let res;
      if (editingFaq) {
        res = await fetch('/api/admin/faq', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: editingFaq.id, ...payload })
        });
      } else {
        res = await fetch('/api/admin/faq', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
      }

      if (res.ok) {
        toast.success(editingFaq ? "FAQ Updated" : "FAQ Created", { id: toastId });
        fetchFaqs();
        closeModal();
      } else {
        toast.error("Failed to save FAQ", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this FAQ?")) return;
    
    const toastId = toast.loading("Deleting FAQ...");
    try {
      const res = await fetch(`/api/admin/faq?id=${id}`, { method: 'DELETE' });
      if (res.ok) {
        toast.success("FAQ Deleted", { id: toastId });
        fetchFaqs();
      } else {
        toast.error("Failed to delete FAQ", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-bold text-gray-900 dark:text-white">FAQ Management</h2>
          <p className="text-slate-500 dark:text-gray-400 text-sm mt-1">Add, edit, or remove Frequently Asked Questions.</p>
        </div>
        <button 
          onClick={() => openModal()}
          className="flex items-center gap-2 px-4 py-2 bg-black dark:bg-[#1A1A1A] hover:bg-gray-800 dark:hover:bg-[#222222] text-white border border-slate-200 dark:border-[#333333] rounded-lg transition-colors shadow-sm"
        >
          <Plus size={18} />
          <span>Add New FAQ</span>
        </button>
      </div>

      <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-slate-200 dark:border-[#333333] overflow-hidden">
        {loading ? (
          <div className="p-12 flex justify-center items-center">
            <Loader2 className="animate-spin text-slate-500 dark:text-gray-400" size={32} />
          </div>
        ) : faqs.length === 0 ? (
          <div className="p-12 text-center text-slate-400 dark:text-gray-500">
            No FAQs found. Click "Add New FAQ" to create one.
          </div>
        ) : (
          <div className="divide-y divide-slate-100 dark:divide-[#333333]">
            {faqs.map((faq) => (
              <div key={faq.id} className="p-6 flex items-start gap-4 hover:bg-slate-50 dark:hover:bg-[#222222]/30 transition-colors group">
                <div className="mt-1 cursor-move text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors">
                  <GripVertical size={20} />
                </div>
                <div className="flex-grow">
                  <h3 className="text-base font-semibold text-gray-900 dark:text-white mb-1">{faq.question}</h3>
                  <p className="text-gray-600 dark:text-gray-300 text-sm whitespace-pre-wrap leading-relaxed">{faq.answer}</p>
                </div>
                <div className="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button 
                    onClick={() => openModal(faq)}
                    className="p-2 text-slate-400 dark:text-gray-500 hover:text-blue-500 hover:bg-blue-500/10 rounded-lg transition-colors"
                  >
                    <Edit2 size={16} />
                  </button>
                  <button 
                    onClick={() => handleDelete(faq.id)}
                    className="p-2 text-slate-400 dark:text-gray-500 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors"
                  >
                    <Trash2 size={16} />
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {isModalOpen && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
            <div className="p-6 border-b border-slate-100 dark:border-[#333333]">
              <h2 className="text-xl font-bold text-gray-900 dark:text-white">{editingFaq ? 'Edit FAQ' : 'Add New FAQ'}</h2>
            </div>
            
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Question</label>
                <input 
                  type="text" 
                  value={question}
                  onChange={(e) => setQuestion(e.target.value)}
                  className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
                  placeholder="e.g. How does the POS work offline?"
                  required
                />
              </div>
              
              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Answer</label>
                <textarea 
                  value={answer}
                  onChange={(e) => setAnswer(e.target.value)}
                  rows={4}
                  className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 resize-none"
                  placeholder="Provide a detailed answer..."
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Display Order</label>
                <input 
                  type="number" 
                  value={order}
                  onChange={(e) => setOrder(Number(e.target.value))}
                  className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-gray-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
                  required
                />
              </div>

              <div className="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-[#333333]">
                <button 
                  type="button" 
                  onClick={closeModal}
                  className="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-[#222222] rounded-lg transition-colors font-medium"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="px-5 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium shadow-sm"
                >
                  {editingFaq ? 'Update FAQ' : 'Save FAQ'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

// Inline Helper: Footer Tab Content (refactored from footer/page.tsx)
function FooterTabContent() {
  const [settings, setSettings] = useState({
    facebook_url: "",
    twitter_url: "",
    instagram_url: "",
    linkedin_url: "",
    whatsapp_number: "",
    contact_location: "",
    contact_phone: "",
    contact_email: "",
    careers_email: "",
    privacy_policy: "",
    terms_conditions: "",
    payment_qr_fonepay: "",
    payment_qr_esewa: "",
    payment_qr_nepalpay: "",
    payment_qr_khalti: "",
    contact_map_iframe: ""
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const fileInputRefs = useRef<{ [key: string]: HTMLInputElement | null }>({});

  const handleQrUpload = async (method: string, file: File) => {
    const toastId = toast.loading(`Uploading QR code...`);
    try {
      const formData = new FormData();
      formData.append('image', file);
      formData.append('method', method);

      const res = await fetch('/api/admin/settings/upload-qr', {
        method: 'POST',
        body: formData
      });

      const data = await res.json();
      if (res.ok) {
        setSettings(prev => ({ ...prev, [data.key]: data.url }));
        toast.success("QR Code uploaded successfully!", { id: toastId });
      } else {
        toast.error(data.error || "Failed to upload", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    }
  };

  useEffect(() => {
    fetch('/api/admin/settings')
      .then(res => res.json())
      .then(data => {
        if (!data.error) {
          setSettings(prev => ({ ...prev, ...data }));
        }
      })
      .catch(() => toast.error("Failed to load settings"))
      .finally(() => setLoading(false));
  }, []);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    const toastId = toast.loading("Saving settings...");

    try {
      const res = await fetch('/api/admin/settings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings)
      });

      if (res.ok) {
        toast.success("Settings saved successfully!", { id: toastId });
      } else {
        toast.error("Failed to save settings", { id: toastId });
      }
    } catch (err) {
      toast.error("An error occurred", { id: toastId });
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center py-16">
        <Loader2 className="animate-spin text-slate-500 dark:text-gray-400" size={32} />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-bold text-gray-900 dark:text-white">Footer & Policies</h2>
          <p className="text-slate-500 dark:text-gray-400 text-sm mt-1">Manage social links, contact info, and legal pages.</p>
        </div>
        <button 
          onClick={handleSave}
          disabled={saving}
          className="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors shadow-sm disabled:opacity-50 font-semibold text-sm"
        >
          {saving ? <Loader2 size={18} className="animate-spin" /> : <Save size={18} />}
          <span>Save Changes</span>
        </button>
      </div>

      <div className="space-y-8">
        {/* Contact Information */}
        <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-slate-200 dark:border-[#333333] overflow-hidden">
          <div className="bg-slate-50 dark:bg-[#222222] px-6 py-4 border-b border-slate-200 dark:border-[#333333] flex items-center gap-2">
            <Phone size={18} className="text-slate-400 dark:text-gray-500" />
            <h3 className="text-base font-semibold text-gray-800 dark:text-white">Contact Information</h3>
          </div>
          <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">WhatsApp Number</label>
              <input 
                type="text" name="whatsapp_number" value={settings.whatsapp_number} onChange={handleChange}
                placeholder="e.g. +977 9865029558"
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
              <p className="text-xs text-slate-500 dark:text-gray-500 mt-1">Used for the footer WhatsApp button.</p>
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Contact Phone</label>
              <input 
                type="text" name="contact_phone" value={settings.contact_phone} onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Contact Email</label>
              <input 
                type="email" name="contact_email" value={settings.contact_email} onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Careers Email</label>
              <input 
                type="email" name="careers_email" value={settings.careers_email} onChange={handleChange}
                placeholder="e.g. jobs@drestro.com"
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Location</label>
              <input 
                type="text" name="contact_location" value={settings.contact_location} onChange={handleChange}
                placeholder="e.g. Kathmandu, Nepal"
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Google Maps Embed Link / Iframe</label>
              <textarea 
                name="contact_map_iframe" value={settings.contact_map_iframe || ""} onChange={handleChange}
                placeholder='e.g. <iframe src="https://www.google.com/maps/embed?..." ...></iframe>'
                rows={3}
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 font-mono text-xs"
              />
              <p className="text-xs text-slate-500 dark:text-gray-500 mt-1">
                Paste the full Google Maps iframe embed code or map URL.
              </p>
            </div>
          </div>
        </div>

        {/* Social Links */}
        <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-slate-200 dark:border-[#333333] overflow-hidden">
          <div className="bg-slate-50 dark:bg-[#222222] px-6 py-4 border-b border-slate-200 dark:border-[#333333] flex items-center gap-2">
            <LinkIcon size={18} className="text-slate-400 dark:text-gray-500" />
            <h3 className="text-base font-semibold text-gray-800 dark:text-white">Social Links</h3>
          </div>
          <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Facebook URL</label>
              <input 
                type="url" name="facebook_url" value={settings.facebook_url} onChange={handleChange}
                placeholder="https://facebook.com/..."
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Instagram URL</label>
              <input 
                type="url" name="instagram_url" value={settings.instagram_url} onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Twitter (X) URL</label>
              <input 
                type="url" name="twitter_url" value={settings.twitter_url} onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">LinkedIn URL</label>
              <input 
                type="url" name="linkedin_url" value={settings.linkedin_url} onChange={handleChange}
                className="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50"
              />
            </div>
          </div>
        </div>

        {/* Payment QR Codes */}
        <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-slate-200 dark:border-[#333333] overflow-hidden">
          <div className="bg-slate-50 dark:bg-[#222222] px-6 py-4 border-b border-slate-200 dark:border-[#333333] flex items-center gap-2">
            <QrCode size={18} className="text-slate-400 dark:text-gray-500" />
            <h3 className="text-base font-semibold text-gray-800 dark:text-white">Payment QR Codes</h3>
          </div>
          <div className="p-6">
            <p className="text-sm text-slate-500 dark:text-gray-400 mb-6">Upload QR codes for each payment method. These will be displayed to customers during checkout.</p>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {[
                { id: 'fonepay', name: 'Fonepay', color: 'text-red-500' },
                { id: 'esewa', name: 'eSewa', color: 'text-green-500' },
                { id: 'nepalpay', name: 'NEPALPAY', color: 'text-blue-500' },
                { id: 'khalti', name: 'Khalti', color: 'text-purple-500' }
              ].map(method => (
                <div key={method.id} className="border border-slate-200 dark:border-[#333333] rounded-xl p-5 flex flex-col items-center justify-center text-center bg-slate-50 dark:bg-[#111111]">
                  <h4 className={`font-bold mb-4 ${method.color}`}>{method.name}</h4>
                  
                  {settings[`payment_qr_${method.id}` as keyof typeof settings] ? (
                    <div className="relative w-32 h-32 mb-4 rounded-xl overflow-hidden border border-slate-200 dark:border-[#333333] bg-white">
                      <Image src={settings[`payment_qr_${method.id}` as keyof typeof settings]} alt={`${method.name} QR`} fill className="object-contain" />
                    </div>
                  ) : (
                    <div className="w-32 h-32 mb-4 rounded-xl border-2 border-dashed border-slate-300 dark:border-[#333333] flex items-center justify-center bg-white dark:bg-[#1A1A1A]">
                      <QrCode size={32} className="text-slate-300 dark:text-slate-600" />
                    </div>
                  )}

                  <input 
                    type="file" 
                    accept="image/*" 
                    className="hidden" 
                    ref={el => { fileInputRefs.current[method.id] = el; }}
                    onChange={(e) => {
                      if (e.target.files && e.target.files[0]) {
                        handleQrUpload(method.id, e.target.files[0]);
                      }
                    }}
                  />
                  <div className="flex gap-2 flex-wrap justify-center mt-2">
                    <button 
                      type="button"
                      onClick={() => fileInputRefs.current[method.id]?.click()}
                      className="px-3 py-1.5 bg-white dark:bg-[#222222] border border-slate-200 dark:border-[#333333] text-xs font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-[#333333] transition-colors"
                    >
                      {settings[`payment_qr_${method.id}` as keyof typeof settings] ? 'Change' : 'Upload'}
                    </button>
                    {settings[`payment_qr_${method.id}` as keyof typeof settings] && (
                      <button 
                        type="button"
                        onClick={() => {
                          setSettings(prev => ({ ...prev, [`payment_qr_${method.id}`]: "" }));
                          toast.success(`${method.name} QR Code cleared. Click Save changes below to apply!`);
                        }}
                        className="px-3 py-1.5 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30 text-rose-600 dark:text-rose-400 text-xs font-semibold rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-colors"
                      >
                        Remove
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Policies */}
        <div className="bg-white dark:bg-[#1A1A1A] rounded-xl shadow-sm border border-slate-200 dark:border-[#333333] overflow-hidden">
          <div className="bg-slate-50 dark:bg-[#222222] px-6 py-4 border-b border-slate-200 dark:border-[#333333] flex items-center gap-2">
            <FileText size={18} className="text-slate-400 dark:text-gray-500" />
            <h3 className="text-base font-semibold text-gray-800 dark:text-white">Legal Policies</h3>
          </div>
          <div className="p-6 space-y-6">
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Privacy Policy</label>
              <textarea 
                name="privacy_policy" 
                value={settings.privacy_policy} 
                onChange={handleChange}
                rows={8}
                className="w-full px-4 py-3 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 font-mono text-sm leading-relaxed resize-y"
                placeholder="Enter privacy policy text (supports plain text and simple line breaks)..."
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Terms & Conditions</label>
              <textarea 
                name="terms_conditions" 
                value={settings.terms_conditions} 
                onChange={handleChange}
                rows={8}
                className="w-full px-4 py-3 bg-slate-50 dark:bg-[#111111] text-gray-900 dark:text-white border border-slate-200 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/50 font-mono text-sm leading-relaxed resize-y"
                placeholder="Enter terms and conditions text..."
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
