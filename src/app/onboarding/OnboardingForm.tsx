"use client";

import { MapPin, Phone, Store, ChevronDown, Mail, FileText, Lock } from "lucide-react";
import { useState, useRef, useEffect } from "react";
import { saveRestaurant } from "./actions";

const RESTAURANT_TYPES = ["FastFood", "Resort", "Hotel", "Bakery", "Cloud Kitchen", "Bar", "Cafe", "Restaurant"];

// Comprehensive list of major areas and cities in Nepal
const COMMON_LOCATIONS = [
  "Thamel, Kathmandu", "Balkhu, Kathmandu", "Kalanki, Kathmandu", 
  "Baneshwor, Kathmandu", "Koteshwor, Kathmandu", "Lazimpat, Kathmandu",
  "Baluwatar, Kathmandu", "Maharajgunj, Kathmandu", "Gongabu, Kathmandu",
  "Chabahil, Kathmandu", "Boudha, Kathmandu", "Kuleshwor, Kathmandu",
  "Samakhusi, Kathmandu", "Sinamangal, Kathmandu", "Maitidevi, Kathmandu",
  "Putalisadak, Kathmandu", "Naxal, Kathmandu", "Durbarmarg, Kathmandu",
  "Kirtipur, Kathmandu", "Tokha, Kathmandu", "Budhanilkantha, Kathmandu",
  "Thankot, Kathmandu", "Swayambhu, Kathmandu", "Sitapaila, Kathmandu",
  "Banasthali, Kathmandu", "Macha Pokhari, Kathmandu", "Dhapasi, Kathmandu",
  "Jhamsikhel, Lalitpur", "Patan, Lalitpur", "Kupondole, Lalitpur", 
  "Jawalakhel, Lalitpur", "Pulchowk, Lalitpur", "Bhaisepati, Lalitpur",
  "Sanepa, Lalitpur", "Satdobato, Lalitpur", "Imadol, Lalitpur",
  "Godawari, Lalitpur", "Lubhu, Lalitpur", "Nakhipot, Lalitpur",
  "Bhaktapur Durbar Square", "Suryabinayak, Bhaktapur", "Thimi, Bhaktapur",
  "Kamalbinayak, Bhaktapur", "Sallaghari, Bhaktapur",
  "Lakeside, Pokhara", "New Road, Pokhara", "Mahendrapul, Pokhara",
  "Bharatpur, Chitwan", "Narayangarh, Chitwan", "Sauraha, Chitwan",
  "Biratnagar, Morang", "Birgunj, Parsa", "Janakpur, Dhanusha",
  "Hetauda, Makwanpur", "Dharan, Sunsari", "Itahari, Sunsari",
  "Butwal, Rupandehi", "Bhairahawa, Rupandehi", "Nepalgunj, Banke",
  "Kohalpur, Banke", "Dhangadhi, Kailali", "Birendranagar, Surkhet",
  "Birtamod, Jhapa", "Damak, Jhapa", "Ghorahi, Dang", "Tulsipur, Dang",
  "Lahan, Siraha", "Rajbiraj, Saptari", "Baglung", "Tansen, Palpa",
  "Bhimdatta, Kanchanpur", "Tikapur, Kailali", "Dhulikhel, Kavre",
  "Banepa, Kavre", "Besisahar, Lamjung", "Gorkha Bazar", "Syangja",
  "Ilam", "Dhankuta", "Khandbari", "Okhaldhunga", "Diktel", "Bhojpur",
  "Myagdi", "Jomsom", "Jumla", "Dailekh", "Dadeldhura", "Baitadi", "Darchula"
].sort((a, b) => a.localeCompare(b));

export function OnboardingForm({ defaultEmail = "", defaultPhone = "" }: { defaultEmail?: string; defaultPhone?: string }) {
  const [phone, setPhone] = useState(defaultPhone);
  const [email, setEmail] = useState(defaultEmail);
  const [password, setPassword] = useState("");
  const [panNumber, setPanNumber] = useState("");
  const [address, setAddress] = useState("");
  const [isAddressFocused, setIsAddressFocused] = useState(false);
  const [selectedTypes, setSelectedTypes] = useState<string[]>(["Restaurant"]);
  
  const addressRef = useRef<HTMLDivElement>(null);

  // Close dropdown if clicked outside
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (addressRef.current && !addressRef.current.contains(event.target as Node)) {
        setIsAddressFocused(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value.replace(/[^0-9]/g, "").slice(0, 10);
    setPhone(value);
  };

  const toggleType = (type: string) => {
    setSelectedTypes(prev => 
      prev.includes(type) 
        ? prev.filter(t => t !== type) 
        : [...prev, type]
    );
  };

  const filteredLocations = COMMON_LOCATIONS.filter(loc => 
    loc.toLowerCase().includes(address.toLowerCase())
  );

  return (
    <form action={saveRestaurant} className="space-y-8">
      <input type="hidden" name="type" value={selectedTypes.join(", ")} />

      {/* Restaurant Name */}
      <div className="space-y-2">
        <label className="text-[15px] font-semibold text-gray-700 dark:text-neutral-300 flex gap-1">
          Restaurant Name <span className="text-red-500">*</span>
        </label>
        <div className="relative">
          <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
            <Store size={18} />
          </div>
          <input 
            type="text" 
            name="name" 
            required 
            placeholder="e.g. Ameci Cafe & Restaurant" 
            className="w-full bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl pl-11 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all shadow-sm"
          />
        </div>
      </div>

      {/* Restaurant Number */}
      <div className="space-y-2">
        <label className="text-[15px] font-semibold text-gray-700 dark:text-neutral-300 flex gap-1">
          Restaurant Number <span className="text-red-500">*</span>
        </label>
        <div className="flex gap-3">
          <div className="w-24 bg-gray-50 dark:bg-neutral-900 border border-gray-200 dark:border-neutral-800 rounded-xl flex items-center justify-center gap-2 shadow-sm text-gray-600 dark:text-neutral-400 font-medium select-none">
            🇳🇵 +977
          </div>
          <div className="relative flex-1">
            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
              <Phone size={18} />
            </div>
            <input 
              type="tel" 
              name="phone" 
              value={phone}
              onChange={handlePhoneChange}
              required 
              maxLength={10}
              pattern="^9[678]\d{8}$"
              title="Phone number must be exactly 10 digits and start with 98, 97, or 96"
              placeholder="98XXXXXXXX" 
              className="w-full bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl pl-11 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all shadow-sm invalid:[&:not(:placeholder-shown)]:border-red-500 invalid:[&:not(:placeholder-shown)]:ring-red-500/20"
            />
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Restaurant Email */}
        <div className="space-y-2">
          <label className="text-[15px] font-semibold text-gray-700 dark:text-neutral-300 flex justify-between items-center">
            <span>Branch Login Email <span className="text-red-500">*</span></span>
          </label>
          <div className="relative">
            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
              <Mail size={18} />
            </div>
            <input 
              type="email" 
              name="email" 
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required 
              placeholder="e.g. contact@restaurant.com" 
              className="w-full bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl pl-11 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all shadow-sm"
            />
          </div>
        </div>

        {/* Branch Password */}
        <div className="space-y-2">
          <label className="text-[15px] font-semibold text-gray-700 dark:text-neutral-300 flex justify-between items-center">
            <span>Branch Password <span className="text-gray-400 font-normal text-xs">(Optional)</span></span>
          </label>
          <div className="relative">
            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
              <Lock size={18} />
            </div>
            <input 
              type="password" 
              name="password" 
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Set a direct login password" 
              className="w-full bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl pl-11 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all shadow-sm"
            />
          </div>
        </div>
      </div>

      {/* PAN / VAT Number */}
      <div className="space-y-2">
        <label className="text-[15px] font-semibold text-gray-700 dark:text-neutral-300 flex gap-1">
          PAN / VAT Number <span className="text-gray-400 font-normal">(Optional)</span>
        </label>
        <div className="relative">
          <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
            <FileText size={18} />
          </div>
          <input 
            type="text" 
            name="panNumber" 
            value={panNumber}
            onChange={(e) => setPanNumber(e.target.value)}
            placeholder="e.g. 600000000" 
            className="w-full bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl pl-11 pr-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all shadow-sm"
          />
        </div>
      </div>

      {/* Type */}
      <div className="space-y-3">
        <label className="text-[15px] font-semibold text-gray-700 dark:text-neutral-300 flex gap-1">
          Type (Select multiple) <span className="text-red-500">*</span>
        </label>
        <div className="flex flex-wrap gap-3">
          {RESTAURANT_TYPES.map((type) => {
            const isSelected = selectedTypes.includes(type);
            return (
              <button 
                key={type}
                type="button"
                onClick={() => toggleType(type)}
                className={`px-5 py-2.5 rounded-xl font-medium text-sm border transition-all ${
                  isSelected 
                    ? "bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/30" 
                    : "bg-gray-50 dark:bg-neutral-900 text-gray-600 dark:text-neutral-400 border-transparent dark:border-neutral-800 hover:bg-gray-100 dark:hover:bg-neutral-800"
                }`}
              >
                {type}
              </button>
            );
          })}
        </div>
      </div>

      {/* Address with Custom Dropdown */}
      <div className="space-y-2" ref={addressRef}>
        <label className="text-[15px] font-semibold text-gray-700 dark:text-neutral-300 flex gap-1">
          Address <span className="text-red-500">*</span>
        </label>
        <div className="relative">
          <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
            <MapPin size={18} />
          </div>
          <input 
            type="text" 
            name="address" 
            value={address}
            onChange={(e) => setAddress(e.target.value)}
            onFocus={() => setIsAddressFocused(true)}
            required 
            autoComplete="off"
            placeholder="Search Location..." 
            className="w-full bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl pl-11 pr-10 py-3.5 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all shadow-sm"
          />
          <div className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <ChevronDown size={18} className={`transition-transform duration-200 ${isAddressFocused ? 'rotate-180' : ''}`} />
          </div>
          
          {/* Custom Dropdown Menu */}
          {isAddressFocused && filteredLocations.length > 0 && (
            <div className="absolute z-20 w-full mt-2 bg-white dark:bg-[#0a0a0a] border border-gray-200 dark:border-neutral-800 rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.4)] overflow-hidden max-h-60 overflow-y-auto">
              <ul className="py-2">
                {filteredLocations.map((loc) => (
                  <li key={loc}>
                    <button
                      type="button"
                      onMouseDown={(e) => {
                        e.preventDefault(); // Prevent input onblur from firing before click
                        setAddress(loc);
                        setIsAddressFocused(false);
                      }}
                      className="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-neutral-300 hover:bg-red-50 dark:hover:bg-red-950/20 hover:text-red-600 dark:hover:text-red-400 transition-colors flex items-center gap-2"
                    >
                      <MapPin size={14} className="text-gray-400" />
                      {loc}
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      </div>

      <div className="pt-6 flex gap-4">
        <button type="reset" onClick={() => { setPhone(""); setEmail(""); setPanNumber(""); setSelectedTypes(["Restaurant"]); setAddress(""); }} className="flex-1 py-4 px-6 rounded-xl font-bold text-gray-600 dark:text-neutral-400 bg-gray-50 dark:bg-neutral-900 hover:bg-gray-100 dark:hover:bg-neutral-800 border border-gray-200 dark:border-neutral-800 transition-all">
          Reset
        </button>
        <button type="submit" disabled={selectedTypes.length === 0} className="flex-[2] py-4 px-6 rounded-xl font-bold text-white bg-[#E96A74] hover:bg-[#E53935] shadow-[0_4px_14px_rgba(233,106,116,0.4)] hover:shadow-[0_6px_20px_rgba(229,57,53,0.4)] hover:-translate-y-0.5 transition-all disabled:opacity-50">
          Save Restaurant
        </button>
      </div>
    </form>
  );
}
