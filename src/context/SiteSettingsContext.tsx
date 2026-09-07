"use client";
import React, { createContext, useContext, useState, useEffect } from "react";

const SiteSettingsContext = createContext({ whatsappNumber: "9779865029558" });

export const SiteSettingsProvider = ({ children, whatsappNumber: initialWhatsappNumber }: { children: React.ReactNode, whatsappNumber: string }) => {
  const [whatsappNumber, setWhatsappNumber] = useState(initialWhatsappNumber);

  useEffect(() => {
    fetch('/api/settings/public')
      .then(res => res.json())
      .then(data => {
        if (data.whatsapp_number) {
          setWhatsappNumber(String(data.whatsapp_number).replace(/[^0-9]/g, ''));
        }
      })
      .catch(err => console.error("Failed to fetch settings", err));
  }, []);

  return (
    <SiteSettingsContext.Provider value={{ whatsappNumber }}>
      {children}
    </SiteSettingsContext.Provider>
  );
};

export const useSiteSettings = () => useContext(SiteSettingsContext);
