"use client";

import { useState, useEffect } from "react";
import { Plus, Edit2, Trash2, Loader2, GripVertical } from "lucide-react";
import toast from "react-hot-toast";

export default function AdminFaqPage() {
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
    <div className="p-6 max-w-5xl mx-auto">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-[#111111] dark:text-white">FAQ Management</h1>
          <p className="text-slate-400 dark:text-gray-500 text-sm mt-1">Add, edit, or remove Frequently Asked Questions.</p>
        </div>
        <button 
          onClick={() => openModal()}
          className="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors shadow-sm"
        >
          <Plus size={18} />
          <span>Add New FAQ</span>
        </button>
      </div>

      <div className="bg-white dark:bg-[#0a0a0a] rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="p-12 flex justify-center items-center">
            <Loader2 className="animate-spin text-slate-500 dark:text-gray-400" size={32} />
          </div>
        ) : faqs.length === 0 ? (
          <div className="p-12 text-center text-slate-400 dark:text-gray-500">
            No FAQs found. Click "Add New FAQ" to create one.
          </div>
        ) : (
          <div className="divide-y divide-gray-100">
            {faqs.map((faq) => (
              <div key={faq.id} className="p-6 flex items-start gap-4 hover:bg-gray-50 transition-colors group">
                <div className="mt-1 cursor-move text-slate-600 dark:text-gray-300 group-hover:text-slate-400 dark:text-gray-500 transition-colors">
                  <GripVertical size={20} />
                </div>
                <div className="flex-grow">
                  <h3 className="text-base font-semibold text-gray-900 dark:text-[#111111] dark:text-white mb-1">{faq.question}</h3>
                  <p className="text-gray-600 dark:text-neutral-400 text-sm whitespace-pre-wrap">{faq.answer}</p>
                </div>
                <div className="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button 
                    onClick={() => openModal(faq)}
                    className="p-2 text-slate-500 dark:text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                  >
                    <Edit2 size={16} />
                  </button>
                  <button 
                    onClick={() => handleDelete(faq.id)}
                    className="p-2 text-slate-500 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
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
          <div className="bg-white dark:bg-[#0a0a0a] rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
            <div className="p-6 border-b border-gray-100">
              <h2 className="text-xl font-bold text-gray-900 dark:text-[#111111] dark:text-white">{editingFaq ? 'Edit FAQ' : 'Add New FAQ'}</h2>
            </div>
            
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1">Question</label>
                <input 
                  type="text" 
                  value={question}
                  onChange={(e) => setQuestion(e.target.value)}
                  className="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black/5"
                  placeholder="e.g. How does the POS work offline?"
                  required
                />
              </div>
              
              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1">Answer</label>
                <textarea 
                  value={answer}
                  onChange={(e) => setAnswer(e.target.value)}
                  rows={4}
                  className="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black/5 resize-none"
                  placeholder="Provide a detailed answer..."
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-1">Display Order</label>
                <input 
                  type="number" 
                  value={order}
                  onChange={(e) => setOrder(Number(e.target.value))}
                  className="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black/5"
                  required
                />
              </div>

              <div className="pt-4 flex items-center justify-end gap-3">
                <button 
                  type="button" 
                  onClick={closeModal}
                  className="px-4 py-2 text-gray-600 dark:text-neutral-400 hover:bg-gray-100 rounded-lg transition-colors font-medium"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  className="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors font-medium shadow-sm"
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
