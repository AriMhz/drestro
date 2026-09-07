import { prisma } from '@/src/lib/prisma';
import { Briefcase, MapPin, Clock, ArrowUpRight, Shield, Database, Zap, Sparkles, TrendingUp, HelpCircle } from 'lucide-react';

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Careers | Join the DRestro Team",
  description: "Explore career opportunities at DRestro and help us build the future of restaurant management systems.",
};

export default async function CareersPage() {
  const jobs = await prisma.careerPost.findMany({
    where: { isActive: true },
    orderBy: { createdAt: 'desc' }
  });

  let emailSetting = await prisma.siteSetting.findUnique({
    where: { key: 'careers_email' }
  });
  
  if (!emailSetting || !emailSetting.value) {
    emailSetting = await prisma.siteSetting.findUnique({
      where: { key: 'contact_email' }
    });
  }
  
  let contactEmail = "jobs@drestro.com";
  if (emailSetting?.value) {
    let val = emailSetting.value;
    try {
      const parsed = JSON.parse(val);
      if (typeof parsed === "string") {
        val = parsed;
      }
    } catch (e) {}
    val = val.trim();
    if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith("'") && val.endsWith("'"))) {
      val = val.slice(1, -1).trim();
    }
    if (val) {
      contactEmail = val;
    }
  }

  const values = [
    {
      icon: <Shield className="w-6 h-6 text-white" />,
      bgColor: "bg-red-500",
      title: "Safe and Secure",
      desc: "We value your trust, safeguarding your restaurant's data with utmost care."
    },
    {
      icon: <Database className="w-6 h-6 text-white" />,
      bgColor: "bg-red-500",
      title: "Stable & Scalable",
      desc: "Built to effortlessly adapt as your restaurant grows."
    },
    {
      icon: <Zap className="w-6 h-6 text-white" />,
      bgColor: "bg-red-500",
      title: "Fast Performance",
      desc: "Swift solutions designed to keep pace with the dynamic restaurant environment."
    },
    {
      icon: <Sparkles className="w-6 h-6 text-white" />,
      bgColor: "bg-red-500",
      title: "Useful & Creative Features",
      desc: "Practical, user-friendly features crafted from deep industry insights."
    },
    {
      icon: <TrendingUp className="w-6 h-6 text-white" />,
      bgColor: "bg-red-500",
      title: "Continuous Growth",
      desc: "Always learning, always improving to serve you better."
    },
    {
      icon: <HelpCircle className="w-6 h-6 text-white" />,
      bgColor: "bg-red-500",
      title: "Expert Support",
      desc: "Compassionate, reliable support, ready to assist whenever you need."
    }
  ];

  return (
    <div className="bg-white dark:bg-[#0a0a0a] text-slate-800 dark:text-white font-sans flex flex-col min-h-screen">
      {/* Hero Section */}
      <div className="relative py-24 bg-slate-50 dark:bg-[#111111] border-b border-slate-100 dark:border-neutral-900 text-center overflow-hidden">
        <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[600px] h-[200px] bg-[#E53935]/5 rounded-full blur-[100px] pointer-events-none"></div>
        <div className="max-w-4xl mx-auto px-6 relative z-10">
          <span className="text-[#E53935] text-[10px] font-black uppercase tracking-widest bg-red-500/10 border border-red-500/25 px-3 py-1 rounded-md">
            Work with us
          </span>
          <h1 className="text-4xl md:text-6xl font-black tracking-tight mt-6 mb-6 text-slate-900 dark:text-white">
            Current Openings!
          </h1>
          <p className="text-slate-500 dark:text-neutral-400 font-bold text-sm md:text-base max-w-xl mx-auto leading-relaxed">
            Feel free to email <a href={`mailto:${contactEmail}`} className="text-[#E53935] hover:underline font-extrabold">{contactEmail}</a> if you believe you would be a fantastic fit.
          </p>

          {/* All Filter Tab Chip */}
          <div className="mt-8 flex justify-center">
            <span className="inline-flex items-center gap-1.5 px-4 py-2 bg-[#E53935] text-white text-xs font-bold rounded-full shadow-lg shadow-red-500/20">
              All <span className="bg-white/25 text-white px-2 py-0.5 rounded-full text-[10px] ml-1">{jobs.length}</span>
            </span>
          </div>
        </div>
      </div>

      {/* Jobs List Section */}
      <div className="max-w-4xl mx-auto px-6 py-16 w-full">
        {jobs.length === 0 ? (
          <div className="bg-slate-50 dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-3xl p-12 text-center text-slate-500 dark:text-neutral-400">
            <Briefcase className="w-12 h-12 mx-auto mb-4 opacity-20 text-[#E53935]" />
            <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-2">No Openings Right Now</h3>
            <p className="text-sm text-slate-500 dark:text-neutral-500">We don&apos;t have any active positions listed at the moment, but we are always looking for great talent. Send your CV to the email above!</p>
          </div>
        ) : (
          <div className="space-y-6">
            {jobs.map((job) => (
              <div key={job.id} className="bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-2xl p-6 md:p-8 hover:border-slate-300 dark:hover:border-neutral-700 transition-all group flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-sm">
                <div className="space-y-3">
                  <div className="flex flex-wrap items-center gap-2.5">
                    <span className="text-[11px] font-bold text-[#E53935] uppercase tracking-wider bg-red-500/10 px-2.5 py-0.5 rounded">
                      {job.department}
                    </span>
                    <span className="inline-flex items-center gap-1 text-xs text-slate-600 dark:text-neutral-400 bg-slate-100 dark:bg-neutral-800 px-2.5 py-0.5 rounded-md font-semibold">
                      <Clock className="w-3.5 h-3.5 text-slate-500 dark:text-neutral-550" /> {job.type}
                    </span>
                    <span className="inline-flex items-center gap-1 text-xs text-slate-600 dark:text-neutral-400 bg-slate-100 dark:bg-neutral-800 px-2.5 py-0.5 rounded-md font-semibold">
                      <MapPin className="w-3.5 h-3.5 text-slate-500 dark:text-neutral-550" /> {job.location}
                    </span>
                  </div>
                  <h3 className="text-xl font-bold text-slate-900 dark:text-white group-hover:text-[#E53935] transition-colors">{job.title}</h3>
                  {job.experience && <p className="text-xs text-slate-500 dark:text-neutral-500 font-bold">Experience Required: {job.experience}</p>}
                  {job.salary && <p className="text-xs text-emerald-600 dark:text-emerald-400 font-bold">Salary: {job.salary}</p>}
                  
                  <div className="pt-2">
                    <h4 className="text-xs font-bold text-slate-500 dark:text-neutral-350 uppercase tracking-widest mb-1.5">Description</h4>
                    <p className="text-sm text-slate-655 dark:text-neutral-400 leading-relaxed max-w-2xl whitespace-pre-wrap font-medium">{job.description}</p>
                  </div>
                  {job.requirements && (
                    <div className="pt-2">
                      <h4 className="text-xs font-bold text-slate-500 dark:text-neutral-350 uppercase tracking-widest mb-1.5">Requirements</h4>
                      <p className="text-sm text-slate-655 dark:text-neutral-400 leading-relaxed max-w-2xl whitespace-pre-wrap font-medium">{job.requirements}</p>
                    </div>
                  )}
                </div>
                
                <a 
                  href={`mailto:${contactEmail}?subject=Application for ${encodeURIComponent(job.title)}`}
                  className="inline-flex items-center justify-center gap-1.5 px-5 py-3 bg-slate-800 hover:bg-[#E53935] dark:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-all duration-300 group-hover:translate-x-1 cursor-pointer w-full md:w-auto text-center shrink-0 border border-slate-700 dark:border-neutral-700/60 shadow-sm"
                >
                  <span>Apply Now</span>
                  <ArrowUpRight className="w-4 h-4" />
                </a>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Values Section */}
      <div className="bg-slate-50 dark:bg-[#111111] border-t border-slate-100 dark:border-neutral-900 py-24">
        <div className="max-w-6xl mx-auto px-6">
          <div className="text-center mb-16">
            <span className="text-xs font-bold text-[#E53935] uppercase tracking-widest border border-red-500/20 bg-red-500/5 px-3.5 py-1.5 rounded-md">
              Our Values
            </span>
            <h2 className="text-3xl md:text-4xl font-black tracking-tight mt-4 text-slate-900 dark:text-white">
              Why Join DRestro?
            </h2>
            <p className="text-slate-500 dark:text-neutral-500 text-sm md:text-base mt-2 font-semibold">
              We build tools with passion and dedication to serve the hospitality industry.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {values.map((v, i) => (
              <div key={i} className="bg-white dark:bg-[#0a0a0a] border border-slate-200 dark:border-neutral-800 rounded-3xl p-8 space-y-4 hover:border-slate-300 dark:hover:border-neutral-750 transition-colors shadow-sm">
                <div className={`w-12 h-12 rounded-2xl ${v.bgColor} flex items-center justify-center shadow-lg shrink-0`}>
                  {v.icon}
                </div>
                <div>
                  <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-2">{v.title}</h3>
                  <p className="text-sm text-slate-550 dark:text-neutral-400 leading-relaxed font-semibold">{v.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
