import React, { useState } from 'react';
import { Laptop, Smartphone, Zap, Brain, Check } from 'lucide-react';

interface ServiceOption {
  id: string;
  name: string;
  desc: string;
  basePrice: number;
  icon: React.ComponentType<{ className?: string }>;
}

const SERVICES: ServiceOption[] = [
  {
    id: 'web',
    name: 'Web Application & Portal',
    desc: 'ระบบเว็บองค์กร, แพลตฟอร์ม SaaS และ Dashboards',
    basePrice: 85000,
    icon: Laptop,
  },
  {
    id: 'mobile',
    name: 'Mobile App (iOS & Android)',
    desc: 'แอปพลิเคชันมือถือแบบ Cross-Platform ลื่นไหล',
    basePrice: 120000,
    icon: Smartphone,
  },
  {
    id: 'headless',
    name: 'Headless CMS & E-Commerce',
    desc: 'Astro + WordPress หรือ Custom Backend โหลดเร็วพิเศษ',
    basePrice: 65000,
    icon: Zap,
  },
  {
    id: 'ai',
    name: 'AI & Workflow Automation',
    desc: 'ระบบ AI Chatbot, OCR, Document Automation',
    basePrice: 95000,
    icon: Brain,
  },
];

interface AddonOption {
  id: string;
  name: string;
  price: number;
}

const ADDONS: AddonOption[] = [
  { id: 'auth', name: 'ระบบสมาชิก & Role-based Permissions', price: 18000 },
  { id: 'payment', name: 'เชื่อมต่อระบบชำระเงิน (PromptPay / บัตรเครดิต)', price: 15000 },
  { id: 'analytics', name: 'Advanced BI & Analytics Reporting', price: 20000 },
  { id: 'devops', name: 'CI/CD Cloud Deployment & 99.9% SLA', price: 25000 },
];

export default function ProjectEstimator() {
  const [selectedService, setSelectedService] = useState<string>('headless');
  const [selectedAddons, setSelectedAddons] = useState<string[]>(['auth', 'devops']);
  const [scale, setScale] = useState<'mvp' | 'standard' | 'enterprise'>('standard');
  const [isSubmitted, setIsSubmitted] = useState<boolean>(false);

  const toggleAddon = (id: string) => {
    if (selectedAddons.includes(id)) {
      setSelectedAddons(selectedAddons.filter((a) => a !== id));
    } else {
      setSelectedAddons([...selectedAddons, id]);
    }
  };

  const currentService = SERVICES.find((s) => s.id === selectedService) || SERVICES[0];

  const scaleMultiplier = scale === 'mvp' ? 0.8 : scale === 'standard' ? 1.0 : 1.6;

  const addonsTotal = selectedAddons.reduce((sum, id) => {
    const item = ADDONS.find((a) => a.id === id);
    return sum + (item ? item.price : 0);
  }, 0);

  const totalEstimate = Math.round((currentService.basePrice + addonsTotal) * scaleMultiplier);
  const minEstimate = Math.round(totalEstimate * 0.9);
  const maxEstimate = Math.round(totalEstimate * 1.15);

  const estimatedWeeks = scale === 'mvp' ? '3 - 5 สัปดาห์' : scale === 'standard' ? '6 - 10 สัปดาห์' : '12 - 18 สัปดาห์';

  return (
    <div className="w-full glass-panel p-6 sm:p-8 lg:p-10 rounded-xl relative overflow-hidden transition-colors border-2 border-slate-200 dark:border-white/10 shadow-lg">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-200 dark:border-white/10">
        <div>
          <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#f0fafc] dark:bg-[#162d59]/10 text-sky-800 dark:text-[#93c5fd] border border-blue-300 dark:border-[#162d59]/20 mb-2">
            ✨ INTERACTIVE ESTIMATOR
          </span>
          <h3 className="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">คำนวณงบประมาณและเวลาโปรเจกต์เบื้องต้น</h3>
          <p className="text-sm text-slate-700 dark:text-slate-300 mt-1 font-normal">เลือกประเภทโปรเจกต์และฟังก์ชันเพื่อดูการประเมินราคาและระยะเวลาดำเนินการ</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {/* Left: Configuration Steps */}
        <div className="lg:col-span-7 space-y-6">
          {/* Step 1: Select Service */}
          <div>
            <label className="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-300 mb-3">
              1. เลือกประเภทโปรเจกต์หลัก
            </label>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              {SERVICES.map((srv) => (
                <button
                  key={srv.id}
                  type="button"
                  onClick={() => setSelectedService(srv.id)}
                  className={`text-left p-4 rounded-lg border-2 transition-all duration-200 cursor-pointer ${selectedService === srv.id
                      ? 'border-[#060c2e] bg-sky-50 dark:bg-[#162d59]/20 shadow-md ring-2 ring-[#162d59]/20'
                      : 'border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 hover:border-indigo-300 hover:bg-slate-50 dark:hover:bg-white/10 shadow-xs'
                    }`}
                >
                  <div className="flex items-center gap-2.5 mb-1.5">
                    <span className="p-1.5 rounded-lg bg-[#f0fafc] dark:bg-[#162d59]/20 text-[#0a192f] dark:text-[#93c5fd]">
                      <srv.icon className="w-4 h-4" />
                    </span>
                    <span className="font-extrabold text-sm text-slate-900 dark:text-white">{srv.name}</span>
                  </div>
                  <p className="text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-normal">{srv.desc}</p>
                </button>
              ))}
            </div>
          </div>

          {/* Step 2: Scale */}
          <div>
            <label className="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-300 mb-3">
              2. ขนาดของระบบ (Scope of Work)
            </label>
            <div className="grid grid-cols-3 gap-2.5">
              {[
                { id: 'mvp', label: 'MVP / Starter', desc: 'เน้นเปิดตัวไว' },
                { id: 'standard', label: 'Standard Business', desc: 'ฟังก์ชันครบพร้อมใช้' },
                { id: 'enterprise', label: 'Enterprise Grade', desc: 'ระบบขนาดใหญ่ รองรับโหลดสูง' },
              ].map((s) => (
                <button
                  key={s.id}
                  type="button"
                  onClick={() => setScale(s.id as any)}
                  className={`p-3 text-center rounded-lg border-2 transition-all text-xs cursor-pointer ${scale === s.id
                      ? 'border-[#060c2e] bg-sky-50 dark:bg-[#162d59]/20 text-sky-950 dark:text-white font-extrabold shadow-sm ring-2 ring-[#162d59]/20'
                      : 'border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-700 dark:text-slate-300 hover:border-blue-300 shadow-xs'
                    }`}
                >
                  <div className="font-bold text-slate-900 dark:text-white">{s.label}</div>
                  <div className="text-[11px] text-slate-600 dark:text-slate-400 mt-1 font-medium font-content">{s.desc}</div>
                </button>
              ))}
            </div>
          </div>

          {/* Step 3: Add-on Modules */}
          <div>
            <label className="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-300 mb-3">
              3. ฟังก์ชันเสริมที่ต้องการ
            </label>
            <div className="space-y-2.5">
              {ADDONS.map((addon) => {
                const isChecked = selectedAddons.includes(addon.id);
                return (
                  <label
                    key={addon.id}
                    onClick={() => toggleAddon(addon.id)}
                    className={`flex items-center justify-between p-3.5 rounded-lg border-2 text-xs sm:text-sm cursor-pointer transition-all ${isChecked
                        ? 'border-[#060c2e] bg-sky-50 dark:bg-[#162d59]/15 text-slate-950 dark:text-white shadow-xs'
                        : 'border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-800 dark:text-slate-200 hover:border-blue-300 hover:bg-slate-50 shadow-xs'
                      }`}
                  >
                    <div className="flex items-center gap-3">
                      <div
                        className={`w-4 h-4 rounded border-2 flex items-center justify-center transition-colors ${isChecked ? 'bg-[#060c2e] border-[#060c2e] text-white' : 'border-slate-400 dark:border-white/40 bg-white dark:bg-transparent'
                          }`}
                      >
                        {isChecked && <Check className="w-3 h-3 text-white stroke-[3]" />}
                      </div>
                      <span className="font-bold text-slate-900 dark:text-white">{addon.name}</span>
                    </div>
                    <span className="font-mono text-[#0a192f] dark:text-[#93c5fd] text-xs font-extrabold">
                      +฿{addon.price.toLocaleString()}
                    </span>
                  </label>
                );
              })}
            </div>
          </div>
        </div>

        {/* Right: Summary Card */}
        <div className="lg:col-span-5 flex flex-col">
          <div className="bg-slate-50 dark:bg-[#0b1120] border-2 border-slate-200 dark:border-[#162d59]/20 rounded-xl p-6 sm:p-7 flex flex-col justify-between h-full shadow-md relative">
            <div>
              <div className="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-white/10 mb-5">
                <span className="text-xs uppercase font-extrabold tracking-wider text-slate-700 dark:text-slate-300">สรุปการประเมินราคา</span>
                <span className="text-xs px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/30 font-bold">
                  Ready to Build
                </span>
              </div>

              <div className="space-y-3 mb-6 text-sm text-slate-700 dark:text-slate-300 font-content">
                <div className="flex justify-between">
                  <span className="text-slate-600 dark:text-slate-400 font-medium">บริการหลัก:</span>
                  <span className="font-bold text-slate-950 dark:text-white">{currentService.name}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-600 dark:text-slate-400 font-medium">ระดับสเกล:</span>
                  <span className="font-bold text-slate-950 dark:text-white uppercase">{scale}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-600 dark:text-slate-400 font-medium">โมดูลเสริม:</span>
                  <span className="font-bold text-slate-950 dark:text-white">{selectedAddons.length} รายการ</span>
                </div>
                <div className="flex justify-between pt-2 border-t border-slate-200 dark:border-white/10">
                  <span className="text-slate-600 dark:text-slate-400 font-medium">ระยะเวลาคาดการณ์:</span>
                  <span className="font-bold text-sky-800 dark:text-[#93c5fd] font-mono text-base">{estimatedWeeks}</span>
                </div>
              </div>

              <div className="bg-white dark:bg-sky-950/40 border-2 border-blue-200 dark:border-[#162d59]/30 rounded-lg p-5 mb-6 shadow-sm">
                <div className="text-xs uppercase tracking-wider text-sky-800 dark:text-[#38bdf8] font-extrabold mb-1">
                  ช่วงงบประมาณโดยประมาณ
                </div>
                <div className="text-2xl sm:text-3xl font-extrabold text-slate-950 dark:text-white font-mono tracking-tight">
                  ฿{minEstimate.toLocaleString()} - ฿{maxEstimate.toLocaleString()}
                </div>
                <p className="text-xs text-slate-600 dark:text-slate-400 mt-2 font-normal leading-relaxed">
                  *ราคานี้เป็นการประเมินเบื้องต้น ทีมงานจะส่งใบเสนอราคาละเอียดพร้อมขอบเขตงานอย่างเป็นทางการ
                </p>
              </div>
            </div>

            {isSubmitted ? (
              <div className="p-4 rounded-lg bg-emerald-100 border border-emerald-300 text-center">
                <div className="text-emerald-900 font-extrabold text-sm mb-1">ส่งคำขอสำเร็จเรียบร้อย!</div>
                <p className="text-xs text-emerald-800 font-medium">เจ้าหน้าที่ฝ่ายวิศวกรรมของ Offriend จะติดต่อกลับภายใน 24 ชม.</p>
              </div>
            ) : (
              <a
                href="/contact"
                className="w-full text-center py-3.5 px-4 rounded-lg font-extrabold text-sm text-white bg-gradient-to-r from-[#162d59] via-[#0a2d38] to-[#0a2d38] hover:from-[#0a192f] hover:to-[#162d59] shadow-md shadow-blue-600/25 hover:shadow-lg hover:shadow-blue-600/35 transition-all transform hover:-translate-y-0.5 cursor-pointer block"
              >
                นัดหมายปรึกษาโปรเจกต์นี้ฟรี →
              </a>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
