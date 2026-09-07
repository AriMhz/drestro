"use client";

import React, { useState } from "react";
import { Briefcase, Edit, Plus, Trash2, MapPin, Clock, DollarSign, ToggleLeft, ToggleRight, Check, X } from "lucide-react";
import toast from "react-hot-toast";

interface CareersAdminClientProps {
  jobs: any[];
  saveJobAction: (formData: FormData) => Promise<void>;
  deleteJobAction: (formData: FormData) => Promise<void>;
  toggleJobStatusAction: (id: string, active: boolean) => Promise<void>;
}

export default function CareersAdminClient({ jobs, saveJobAction, deleteJobAction, toggleJobStatusAction }: CareersAdminClientProps) {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingJob, setEditingJob] = useState<any>(null);

  const handleOpenAddModal = () => {
    setEditingJob(null);
    setIsModalOpen(true);
  };

  const handleOpenEditModal = (job: any) => {
    setEditingJob(job);
    setIsModalOpen(true);
  };

  const handleToggleActive = async (job: any) => {
    const toastId = toast.loading("Updating job status...");
    try {
      await toggleJobStatusAction(job.id, !job.isActive);
      toast.success(`Job status updated successfully!`, { id: toastId });
    } catch (e) {
      toast.error("Failed to update status", { id: toastId });
    }
  };

  const handleDelete = async (e: React.FormEvent, jobId: string) => {
    e.preventDefault();
    if (!confirm("Are you sure you want to delete this job posting?")) return;

    const toastId = toast.loading("Deleting job...");
    try {
      const formData = new FormData();
      formData.append("id", jobId);
      await deleteJobAction(formData);
      toast.success("Job deleted successfully!", { id: toastId });
    } catch (err) {
      toast.error("Failed to delete job", { id: toastId });
    }
  };

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const toastId = toast.loading("Saving job posting...");

    try {
      const formData = new FormData(e.currentTarget);
      await saveJobAction(formData);
      toast.success("Job posting saved successfully!", { id: toastId });
      setIsModalOpen(false);
      setEditingJob(null);
    } catch (err) {
      toast.error("Failed to save job posting", { id: toastId });
    }
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-3xl font-bold text-[#111111] dark:text-white tracking-tight flex items-center gap-3">
            <Briefcase className="text-[#E53935]" />
            Careers & Hirings
          </h1>
          <p className="text-slate-500 dark:text-gray-400 mt-2">Manage your restaurant POS career job openings shown on the public site.</p>
        </div>
        <button
          onClick={handleOpenAddModal}
          className="flex items-center gap-2 px-5 py-3 bg-[#E53935] hover:bg-red-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-500/10 active:scale-95 transition-all"
        >
          <Plus size={18} /> Add Job Opening
        </button>
      </div>

      {/* Table / List */}
      <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden shadow-sm">
        {jobs.length === 0 ? (
          <div className="p-12 text-center text-slate-500 dark:text-gray-400">
            <Briefcase className="w-12 h-12 mx-auto mb-4 opacity-15" />
            <h3 className="text-lg font-bold mb-1">No Career Postings Found</h3>
            <p className="text-sm">Get started by creating a new job opening above.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-slate-50 dark:bg-[#222222] border-b border-slate-200 dark:border-[#333333] text-xs font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">
                  <th className="px-6 py-4">Job Title</th>
                  <th className="px-6 py-4">Department</th>
                  <th className="px-6 py-4">Type / Location</th>
                  <th className="px-6 py-4">Salary</th>
                  <th className="px-6 py-4 text-center">Status</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-150 dark:divide-[#333333]">
                {jobs.map((job) => (
                  <tr key={job.id} className="hover:bg-slate-50/50 dark:hover:bg-[#222222]/30 transition-colors">
                    <td className="px-6 py-5">
                      <div className="font-bold text-slate-800 dark:text-white text-base">{job.title}</div>
                      {job.experience && <div className="text-xs text-slate-400 dark:text-gray-500 mt-1">Exp: {job.experience}</div>}
                    </td>
                    <td className="px-6 py-5 text-sm font-semibold text-slate-650 dark:text-gray-300">
                      {job.department}
                    </td>
                    <td className="px-6 py-5">
                      <div className="flex flex-col gap-1">
                        <span className="inline-flex items-center gap-1 text-xs text-slate-600 dark:text-gray-400 font-medium">
                          <Clock size={13} className="text-slate-400" /> {job.type}
                        </span>
                        <span className="inline-flex items-center gap-1 text-xs text-slate-600 dark:text-gray-400 font-medium">
                          <MapPin size={13} className="text-slate-400" /> {job.location}
                        </span>
                      </div>
                    </td>
                    <td className="px-6 py-5 text-sm font-bold text-emerald-600 dark:text-emerald-450">
                      {job.salary || "Negotiable"}
                    </td>
                    <td className="px-6 py-5 text-center">
                      <button
                        onClick={() => handleToggleActive(job)}
                        className="focus:outline-none transition-transform active:scale-95"
                        title={job.isActive ? "Deactivate job listing" : "Activate job listing"}
                      >
                        {job.isActive ? (
                          <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 text-xs font-bold rounded-full">
                            Active
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1.5 px-gray-500/10 bg-slate-100 dark:bg-neutral-800 text-slate-400 dark:text-gray-500 border border-slate-200 dark:border-neutral-700 text-xs font-bold rounded-full px-3 py-1">
                            Inactive
                          </span>
                        )}
                      </button>
                    </td>
                    <td className="px-6 py-5 text-right">
                      <div className="flex justify-end gap-2.5">
                        <button
                          onClick={() => handleOpenEditModal(job)}
                          className="text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 p-2 rounded-lg transition-colors"
                          title="Edit Job"
                        >
                          <Edit size={16} />
                        </button>
                        <form onSubmit={(e) => handleDelete(e, job.id)}>
                          <button
                            type="submit"
                            className="text-slate-400 hover:text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition-colors"
                            title="Delete Job"
                          >
                            <Trash2 size={16} />
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col animate-in scale-in duration-300">
            <div className="px-6 py-4 border-b border-slate-150 dark:border-[#333333] flex justify-between items-center bg-slate-50 dark:bg-[#222222]">
              <h3 className="font-bold text-lg text-slate-800 dark:text-white">
                {editingJob ? "Edit Job Posting" : "Add Job Posting"}
              </h3>
              <button
                onClick={() => setIsModalOpen(false)}
                className="text-slate-400 hover:text-slate-650 dark:hover:text-white p-1 rounded-lg transition-colors"
              >
                <X size={20} />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-6 space-y-5">
              {editingJob && <input type="hidden" name="id" value={editingJob.id} />}

              <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label className="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Job Title *</label>
                  <input
                    type="text"
                    name="title"
                    required
                    defaultValue={editingJob?.title || ""}
                    className="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/40 text-sm font-medium"
                    placeholder="e.g. Senior Frontend Developer"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Department *</label>
                  <input
                    type="text"
                    name="department"
                    required
                    defaultValue={editingJob?.department || "Engineering"}
                    className="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/40 text-sm font-medium"
                    placeholder="e.g. Engineering, Sales, Marketing"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label className="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Job Type *</label>
                  <select
                    name="type"
                    defaultValue={editingJob?.type || "Full-time"}
                    className="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/40 text-sm font-medium"
                  >
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Internship">Internship</option>
                    <option value="Contract">Contract</option>
                    <option value="Remote">Remote</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Job Location *</label>
                  <input
                    type="text"
                    name="location"
                    required
                    defaultValue={editingJob?.location || "Kathmandu, Nepal"}
                    className="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/40 text-sm font-medium"
                    placeholder="e.g. Kathmandu, Nepal or Remote"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label className="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Salary Range</label>
                  <input
                    type="text"
                    name="salary"
                    defaultValue={editingJob?.salary || ""}
                    className="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/40 text-sm font-medium"
                    placeholder="e.g. Rs. 80,000 - 1,20,000"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Experience Required</label>
                  <input
                    type="text"
                    name="experience"
                    defaultValue={editingJob?.experience || ""}
                    className="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/40 text-sm font-medium"
                    placeholder="e.g. 2+ Years, Entry Level"
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Job Description *</label>
                <textarea
                  name="description"
                  required
                  rows={4}
                  defaultValue={editingJob?.description || ""}
                  className="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/40 text-sm font-medium resize-y"
                  placeholder="Describe details of the role..."
                />
              </div>

              <div>
                <label className="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-1">Requirements *</label>
                <textarea
                  name="requirements"
                  required
                  rows={4}
                  defaultValue={editingJob?.requirements || ""}
                  className="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E53935]/40 text-sm font-medium resize-y"
                  placeholder="List skills and tools required..."
                />
              </div>

              <div className="flex items-center gap-3">
                <input
                  type="checkbox"
                  id="isActive"
                  name="isActive"
                  defaultChecked={editingJob ? editingJob.isActive : true}
                  className="w-4.5 h-4.5 text-[#E53935] border-slate-350 rounded focus:ring-[#E53935]"
                />
                <label htmlFor="isActive" className="text-sm font-bold text-slate-700 dark:text-gray-300 select-none">
                  Make this job posting active immediately
                </label>
              </div>

              <div className="pt-4 border-t border-slate-150 dark:border-[#333333] flex justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-5 py-2.5 border border-slate-200 dark:border-[#333333] hover:bg-slate-100 dark:hover:bg-[#222222] text-slate-650 dark:text-white text-sm font-bold rounded-xl transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-6 py-2.5 bg-[#E53935] hover:bg-[#c62828] text-white text-sm font-bold rounded-xl transition-colors shadow-sm"
                >
                  Save Posting
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
