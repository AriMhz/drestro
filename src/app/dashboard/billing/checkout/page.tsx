"use client";

import React, { useState, useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { Check, ArrowLeft, ShieldCheck, HeadphonesIcon, XCircle, Star, Crown, Diamond, QrCode, X } from 'lucide-react';
import Link from 'next/link';

// Mock data based on the screenshot structure
const plansData = [
  {
    id: 'basic',
    name: 'Basic',
    icon: <Star size={16} className="text-gray-500" />,
    basePriceYearly: 12000,
    basePriceHalfYearly: 7000,
    features: [
      'Up to 5 Members Login',
      'Up to 20 Tables',
      'Up to 500 Dishes',
      'Free setup and onboarding assistance.',
      'Business hours support.'
    ]
  },
  {
    id: 'premium',
    name: 'Premium',
    icon: <Crown size={16} className="text-yellow-500" />,
    basePriceYearly: 24000,
    basePriceHalfYearly: 14000,
    features: [
      'Up to 24 Members Login',
      'Up to 50 Tables',
      'Up to 1000 Dishes',
      'Free setup and onboarding assistance.',
      '24/7 customer support.'
    ]
  },
  {
    id: 'platinum',
    name: 'Platinum',
    icon: <Diamond size={16} className="text-blue-500" />,
    basePriceYearly: 60000,
    basePriceHalfYearly: 35000,
    features: [
      'Unlimited Members Login',
      'Unlimited Tables',
      'Unlimited Dishes',
      'Free setup and onboarding assistance.',
      '24/7 priority customer support.',
      'Dedicated account manager.'
    ]
  }
];

const paymentMethods = [
  { id: 'fonepay', name: 'Fonepay', colors: 'text-red-600' },
  { id: 'esewa', name: 'eSewa', colors: 'text-green-600' },
  { id: 'nepalpay', name: 'NEPALPAY', colors: 'text-blue-600' },
  { id: 'khalti', name: 'Khalti', colors: 'text-purple-600' }
];

function CheckoutPageContent() {
  const router = useRouter();
  const searchParams = useSearchParams();

  const initialPlanId = searchParams.get('plan') || 'premium';
  const initialBilling = (searchParams.get('billing') as 'yearly' | 'half-yearly' | '2-yearly' | '3-yearly') || 'yearly';

  const [selectedPlanId, setSelectedPlanId] = useState(initialPlanId);
  const [billingCycle, setBillingCycle] = useState<'yearly' | 'half-yearly' | '2-yearly' | '3-yearly'>(initialBilling);
  const [selectedPayment, setSelectedPayment] = useState('nepalpay');
  const [isProcessing, setIsProcessing] = useState(false);
  const [showQRModal, setShowQRModal] = useState(false);
  const [settings, setSettings] = useState<any>({});
  const [screenshotFile, setScreenshotFile] = useState<File | null>(null);
  const [userEmail, setUserEmail] = useState("");
  const [restaurantName, setRestaurantName] = useState("");
  const [dbPlans, setDbPlans] = useState<any[]>([]);

  useEffect(() => {
    fetch('/api/plans')
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data) && data.length > 0) {
          setDbPlans(data);
        }
      })
      .catch(console.error);

    fetch('/api/settings/public')
      .then(res => res.json())
      .then(data => setSettings(data))
      .catch(console.error);

    fetch('/api/auth/session')
      .then(res => res.json())
      .then(data => {
        if (data?.user) {
          setUserEmail(data.user.email || "");
          setRestaurantName(data.user.name || "");
        }
      })
      .catch(console.error);
  }, []);

  const availablePlans = dbPlans.length > 0 ? dbPlans.map(p => {
    const isBasic = p.name.toLowerCase().includes('basic');
    const isPlatinum = p.name.toLowerCase().includes('platinum');
    return {
      id: p.id,
      name: p.name,
      icon: isBasic ? <Star size={16} className="text-gray-500" /> : isPlatinum ? <Diamond size={16} className="text-blue-500" /> : <Crown size={16} className="text-yellow-500" />,
      basePriceYearly: p.priceYearly,
      basePriceHalfYearly: p.priceHalfYearly || Math.round(p.priceYearly * 0.6),
      features: p.features && p.features.length > 0 
        ? p.features.map((f: any) => f.text)
        : [
            `Full ${p.name} access`,
            `Free setup and onboarding assistance.`,
            `Customer support.`
          ]
    };
  }) : plansData;

  const selectedPlan = availablePlans.find(p => 
    p.id.toLowerCase() === selectedPlanId.toLowerCase() || 
    p.name.toLowerCase() === selectedPlanId.toLowerCase()
  ) || availablePlans[0];

  // Price calculations
  let price = selectedPlan.basePriceYearly;
  if (billingCycle === 'half-yearly') {
    price = selectedPlan.basePriceHalfYearly;
  } else if (billingCycle === '2-yearly') {
    price = Math.round((selectedPlan.basePriceYearly * 2) * 0.90);
  } else if (billingCycle === '3-yearly') {
    price = Math.round((selectedPlan.basePriceYearly * 3) * 0.85);
  }

  // Backwards calculation to extract VAT from the target total price (as shown in screenshot)
  const subtotal = price / 1.13;
  const vat = price - subtotal;

  const handlePayNow = () => {
    setShowQRModal(true);
  };

  const simulateSuccess = async () => {
    if (!screenshotFile) {
      alert("Please upload your payment screenshot/receipt to simulate or submit payment.");
      return;
    }
    setIsProcessing(true);
    try {
      const body = new FormData();
      body.append("planId", selectedPlanId);
      body.append("billingCycle", billingCycle);
      body.append("amount", String(price));
      body.append("paymentMethod", selectedPayment);
      body.append("screenshot", screenshotFile);

      const response = await fetch("/api/billing/subscription-payment", {
        method: "POST",
        body
      });

      const data = await response.json();
      setIsProcessing(false);

      if (response.ok && data.success) {
        setShowQRModal(false);
        alert(`Payment screenshot receipt uploaded successfully!\nOur support team will verify your receipt and activate your ${selectedPlan.name} plan shortly.`);
        router.push('/dashboard/billing');
      } else {
        alert("Upload failed: " + (data.error || "Please contact support."));
      }
    } catch (err: any) {
      setIsProcessing(false);
      alert("Payment processing failed: " + err.message);
    }
  };

  const handleWhatsAppShare = () => {
    const cycleLabel = billingCycle === 'yearly' 
      ? '1 Year' 
      : billingCycle === 'half-yearly' 
      ? '6 Months' 
      : billingCycle === '2-yearly' 
      ? '2 Years' 
      : '3 Years';
    const message = `Hello DRestro Support, I have just made a payment for my subscription:

Plan: ${selectedPlan.name} (${cycleLabel})
Amount: Rs. ${price.toLocaleString()}
Payment Method: ${selectedPayment.toUpperCase()}
Restaurant/Owner: ${restaurantName || 'N/A'} (${userEmail || 'N/A'})

I am sending the screenshot receipt proof next. Please activate my account!`;

    const whatsappUrl = `https://wa.me/9779865029558?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, "_blank");
  };

  return (
    <div className="max-w-6xl mx-auto py-6">

      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <button
          onClick={() => router.push('/dashboard/billing/plans')}
          className="flex items-center gap-2 text-sm font-bold text-gray-600 dark:text-neutral-400 bg-white dark:bg-[#0a0a0a] border border-gray-200 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <ArrowLeft size={16} /> Back to Plans
        </button>
        <div className="flex-1 flex justify-center -ml-16 lg:ml-0">
          <img src="/logos/logo.svg" alt="DRestro" className="h-8 opacity-80 dark:hidden" />
          <img src="/logos/logo-light.svg" alt="DRestro" className="h-8 opacity-80 hidden dark:block" />
        </div>
        <div className="hidden lg:block w-[120px]"></div> {/* Spacer */}
      </div>

      <div className="flex flex-col lg:flex-row gap-8">

        {/* Left Column - Plan Selection */}
        <div className="w-full lg:w-[45%]">
          <div className="bg-white dark:bg-[#0a0a0a] rounded-2xl border border-gray-200 p-6 shadow-sm mb-6">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-6">Choose Plan</h2>

            {/* Billing Toggle */}
            <div className="bg-gray-50 dark:bg-neutral-900 rounded-xl p-1 grid grid-cols-2 sm:grid-cols-4 gap-1 relative mb-6 border border-gray-100 dark:border-neutral-800">
              <button
                type="button"
                onClick={() => setBillingCycle('half-yearly')}
                className={`py-2 text-xs font-bold rounded-lg transition-all ${billingCycle === 'half-yearly' ? 'bg-white dark:bg-[#0a0a0a] text-gray-900 dark:text-white shadow-sm border border-gray-200 dark:border-neutral-700' : 'text-gray-500 hover:text-gray-700 dark:text-neutral-400'}`}
              >
                6 Mo
              </button>
              <button
                type="button"
                onClick={() => setBillingCycle('yearly')}
                className={`py-2 text-xs font-bold rounded-lg transition-all ${billingCycle === 'yearly' ? 'bg-white dark:bg-[#0a0a0a] text-gray-900 dark:text-white shadow-sm border border-gray-200 dark:border-neutral-700' : 'text-gray-500 hover:text-gray-700 dark:text-neutral-400'}`}
              >
                1 Yr
              </button>
              <button
                type="button"
                onClick={() => setBillingCycle('2-yearly')}
                className={`py-2 text-xs font-bold rounded-lg transition-all relative ${billingCycle === '2-yearly' ? 'bg-white dark:bg-[#0a0a0a] text-gray-900 dark:text-white shadow-sm border border-gray-200 dark:border-neutral-700' : 'text-gray-500 hover:text-gray-700 dark:text-neutral-400'}`}
              >
                2 Yrs
                <span className="absolute -top-2.5 -right-1 bg-emerald-500 text-white text-[7px] font-black px-1 rounded-full z-10 scale-90">10% Off</span>
              </button>
              <button
                type="button"
                onClick={() => setBillingCycle('3-yearly')}
                className={`py-2 text-xs font-bold rounded-lg transition-all relative ${billingCycle === '3-yearly' ? 'bg-white dark:bg-[#0a0a0a] text-gray-900 dark:text-white shadow-sm border border-gray-200 dark:border-neutral-700' : 'text-gray-500 hover:text-gray-700 dark:text-neutral-400'}`}
              >
                3 Yrs
                <span className="absolute -top-2.5 -right-1 bg-emerald-500 text-white text-[7px] font-black px-1 rounded-full z-10 scale-90">15% Off</span>
              </button>
            </div>

            {/* Plan List */}
            <div className="space-y-3 mb-6">
              {availablePlans.map(plan => {
                const isSelected = selectedPlan.id === plan.id;
                
                let planPrice = plan.basePriceYearly;
                if (billingCycle === 'half-yearly') {
                  planPrice = plan.basePriceHalfYearly;
                } else if (billingCycle === '2-yearly') {
                  planPrice = Math.round((plan.basePriceYearly * 2) * 0.90);
                } else if (billingCycle === '3-yearly') {
                  planPrice = Math.round((plan.basePriceYearly * 3) * 0.85);
                }

                return (
                  <div
                    key={plan.id}
                    onClick={() => setSelectedPlanId(plan.id)}
                    className={`flex items-center justify-between p-4 rounded-xl cursor-pointer transition-all border-2 ${isSelected ? 'border-green-500 bg-green-500/10' : 'border-gray-100 bg-white dark:bg-[#0a0a0a] hover:border-gray-300 dark:border-neutral-800 dark:hover:border-neutral-700'}`}
                  >
                    <div className="flex items-center gap-3">
                      <div className={`w-8 h-8 rounded-full flex items-center justify-center ${isSelected ? 'bg-white dark:bg-[#0a0a0a] shadow-sm' : 'bg-gray-50'}`}>
                        {plan.icon}
                      </div>
                      <span className={`font-bold ${isSelected ? 'text-green-700' : 'text-gray-700 dark:text-neutral-300'}`}>{plan.name}</span>
                    </div>
                    <div className="flex items-center gap-3">
                      <div>
                        <span className={`font-bold ${isSelected ? 'text-green-600' : 'text-gray-900 dark:text-white'}`}>Rs {planPrice.toLocaleString()}</span>
                        <span className="text-gray-400 text-xs font-semibold">
                          /{billingCycle === 'yearly' 
                            ? '1 Yr' 
                            : billingCycle === 'half-yearly' 
                            ? '6 Mo' 
                            : billingCycle === '2-yearly' 
                            ? '2 Yrs' 
                            : '3 Yrs'}
                        </span>
                      </div>
                      {isSelected && <Check size={20} className="text-green-500" />}
                    </div>
                  </div>
                );
              })}
            </div>

            {/* Features Box */}
            <div className="bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-xl p-5">
              <h3 className="text-blue-600 dark:text-blue-400 font-bold text-sm mb-4 flex items-center gap-2">
                <ShieldCheck size={18} /> What's included on this plan:
              </h3>
              <ul className="space-y-2">
                {selectedPlan.features.map((feature, idx) => (
                  <li key={idx} className="flex items-start gap-2">
                    <div className="w-1.5 h-1.5 rounded-full bg-blue-500 mt-2 shrink-0"></div>
                    <span className="text-blue-900/80 dark:text-blue-200 text-sm font-medium">{feature}</span>
                  </li>
                ))}
              </ul>
            </div>
          </div>

          <div className="text-center text-gray-500 text-sm font-medium">
            <p>Contact us if you have any confusions regarding subscription</p>
            <p className="mt-1">+977-9865029558 | sales@drestro.com</p>
          </div>
        </div>

        {/* Right Column - Payment */}
        <div className="w-full lg:w-[55%]">
          <div className="bg-white dark:bg-[#0a0a0a] rounded-2xl border border-gray-200 p-8 shadow-sm">

            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Payment Method</h2>

            {/* Payment Methods Grid */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
              {paymentMethods.map(method => (
                <div
                  key={method.id}
                  onClick={() => setSelectedPayment(method.id)}
                  className={`h-16 rounded-xl border-2 flex items-center justify-center cursor-pointer transition-all ${selectedPayment === method.id ? 'border-green-500 bg-green-500/10' : 'border-gray-200 hover:border-gray-300 dark:border-neutral-800 dark:hover:border-neutral-700'}`}
                >
                  <span className={`font-black text-lg ${method.colors}`}>{method.name}</span>
                </div>
              ))}
            </div>

            <hr className="border-gray-100 dark:border-neutral-800 mb-8" />

            {/* Bill Summary */}
            <h2 className="text-lg font-bold text-gray-900 dark:text-white mb-4">Bill Summary</h2>

            <div className="space-y-3 mb-6">
              <div className="flex justify-between items-center text-gray-600 dark:text-neutral-400">
                <span className="font-medium text-sm">
                  Subtotal({selectedPlan.name} Plan {
                    billingCycle === 'yearly' 
                      ? '1 Year' 
                      : billingCycle === 'half-yearly' 
                      ? '6 Months' 
                      : billingCycle === '2-yearly' 
                      ? '2 Years' 
                      : '3 Years'
                  })
                </span>
                <span className="font-medium text-sm">Rs {subtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
              </div>
              <div className="flex justify-between items-center text-gray-600 dark:text-neutral-400">
                <span className="font-medium text-sm">VAT(13%)</span>
                <span className="font-medium text-sm">Rs {vat.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
              </div>
            </div>

            <div className="flex justify-between items-center py-4 border-t border-gray-100 dark:border-neutral-800 mb-6">
              <span className="text-lg font-bold text-gray-900 dark:text-white">Total Amount</span>
              <span className="text-xl font-black text-gray-900 dark:text-white">Rs {price.toLocaleString()}</span>
            </div>

            <button
              onClick={handlePayNow}
              disabled={isProcessing}
              className={`w-full bg-[#1da043] hover:bg-green-700 text-white font-bold py-4 rounded-xl text-lg shadow-md hover:shadow-lg transition-all flex justify-center items-center ${isProcessing ? 'opacity-70 cursor-not-allowed' : ''}`}
            >
              {isProcessing ? (
                <div className="w-6 h-6 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              ) : (
                `Pay Now Rs ${price.toLocaleString()}`
              )}
            </button>

            {/* Trust Badges */}
            <div className="flex flex-wrap justify-center items-center gap-4 mt-6 text-[12px] font-bold text-green-600/80">
              <div className="flex items-center gap-1.5">
                <ShieldCheck size={16} /> 100% Secure Payment
              </div>
              <div className="flex items-center gap-1.5 text-gray-500">
                <HeadphonesIcon size={16} /> 24/7 support
              </div>
              <div className="flex items-center gap-1.5 text-gray-500">
                <XCircle size={16} /> Cancel plan anytime
              </div>
            </div>

          </div>

          {/* Decorative jagged bottom edge to look like a receipt */}
          <div className="h-4 w-full" style={{ backgroundImage: 'radial-gradient(circle at 10px 0, transparent 10px, white 11px)', backgroundSize: '20px 20px', backgroundRepeat: 'repeat-x' }}></div>

        </div>

      </div>

      {/* QR Code Modal Overlay */}
      {showQRModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="bg-white dark:bg-[#0a0a0a] rounded-3xl p-8 max-w-sm w-full shadow-2xl relative flex flex-col items-center animate-in fade-in zoom-in duration-300">

            <button
              onClick={() => setShowQRModal(false)}
              className="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-900 dark:text-white bg-gray-50 hover:bg-gray-100 rounded-full transition-colors"
            >
              <X size={20} />
            </button>

            <h3 className="text-xl font-black text-gray-900 dark:text-white mb-2 mt-2">Scan to Pay</h3>
            <p className="text-gray-500 text-sm font-medium mb-6 text-center">
              Please scan the QR code using your {selectedPayment.toUpperCase()} app.
            </p>

            <div className="bg-gray-50 border-2 border-gray-200 p-6 rounded-2xl mb-6 relative w-[220px] h-[220px] flex items-center justify-center overflow-hidden bg-white">
              {settings[`payment_qr_${selectedPayment}`] ? (
                <img 
                  src={settings[`payment_qr_${selectedPayment}`]} 
                  alt={`${selectedPayment} QR Code`} 
                  className="w-full h-full object-contain"
                />
              ) : (
                <QrCode size={180} strokeWidth={1} className="text-gray-800 dark:text-neutral-200" />
              )}
            </div>

            <div className="bg-gray-50 dark:bg-neutral-800 rounded-xl px-6 py-4 w-full flex justify-between items-center mb-6">
              <span className="text-sm font-bold text-gray-500 dark:text-neutral-400">Amount</span>
              <span className="text-xl font-black text-[#111111] dark:text-white">Rs {price.toLocaleString()}</span>
            </div>

            {/* File Upload Box */}
            <div className="w-full mb-6 text-left">
              <label className="block text-xs font-bold text-gray-650 dark:text-neutral-400 mb-2 uppercase">Upload Payment Screenshot / Receipt *</label>
              <input
                type="file"
                accept="image/*"
                required
                onChange={(e) => {
                  if (e.target.files && e.target.files.length > 0) {
                    setScreenshotFile(e.target.files[0]);
                  }
                }}
                className="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-green-500/10 file:text-green-600 hover:file:bg-green-500/20 cursor-pointer border border-dashed border-gray-200 dark:border-neutral-800 p-2 rounded-xl"
              />
            </div>

            <button
              onClick={simulateSuccess}
              disabled={isProcessing}
              className={`w-full bg-[#1da043] hover:bg-green-700 text-white font-bold py-3.5 rounded-xl transition-all flex justify-center items-center mb-2 ${isProcessing ? 'opacity-70 cursor-not-allowed' : ''}`}
            >
              {isProcessing ? (
                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              ) : (
                "Activate Subscription Directly"
              )}
            </button>

            <button
              type="button"
              onClick={handleWhatsAppShare}
              className="w-full bg-[#25D366] hover:bg-[#20ba5a] text-white font-bold py-3.5 rounded-xl transition-all flex justify-center items-center gap-2 shadow-md shadow-green-500/10 mb-2"
            >
              <svg className="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.79-4.024l.32.19c1.52.9 3.42 1.37 5.36 1.38 5.454 0 9.892-4.437 9.896-9.897.002-2.646-1.002-5.132-2.825-6.958C17.92 2.865 15.434 1.86 12.8 1.86c-5.461 0-9.902 4.438-9.906 9.9.003 2.107.55 4.161 1.59 5.96l.21.36-1.05 3.84 3.93-1.03zM16.92 14.92c-.29-.15-1.74-.86-2.01-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.39-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.53.15-.17.2-.3.3-.5.1-.2.05-.38-.02-.53-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51-.17-.01-.37-.01-.57-.01-.2 0-.52.07-.8.37-.27.3-1.07 1.05-1.07 2.56s1.09 2.97 1.24 3.17c.15.2 2.15 3.28 5.21 4.6 1.21.52 2.16.83 2.91 1.07.75.24 1.43.21 1.97.13.6-.09 1.74-.71 1.99-1.4.25-.69.25-1.28.17-1.4-.08-.12-.28-.2-.58-.35z"/>
              </svg>
              <span>Share via WhatsApp</span>
            </button>

          </div>
        </div>
      )}

    </div>
  );
}

export default function CheckoutPage() {
  return (
    <React.Suspense fallback={<div className="flex justify-center p-8"><div className="w-8 h-8 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div></div>}>
      <CheckoutPageContent />
    </React.Suspense>
  );
}
