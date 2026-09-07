import type { Metadata } from "next";
import "../index.css";
import ClientLayoutWrapper from "../components/layout/ClientLayoutWrapper";
import LoadingScreen from "@/src/components/LoadingScreen";
import { ThemeProvider } from "@/src/components/ThemeProvider";
import { Toaster } from "react-hot-toast";
import { CartProvider } from "../context/CartContext";
import HardwareCheckout from "../components/HardwareCheckout";
import { SiteSettingsProvider } from "../context/SiteSettingsContext";
import { prisma } from "../lib/prisma";

export const metadata: Metadata = {
  title: "DrestroPOS - The Smart, Unbreakable Way to Run Your Restaurant",
  description: "Zero internet dependencies. Directly launched on your local Windows hardware.",
};

export default async function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  let whatsappNumber = "9779865029558";
  try {
    const setting = await prisma.siteSetting.findUnique({ where: { key: "whatsapp_number" } });
    if (setting) whatsappNumber = setting.value;
  } catch (e) {}

  return (
    <html lang="en" className="overflow-x-hidden" suppressHydrationWarning>
      <body className="flex flex-col min-h-screen bg-white dark:bg-[#0a0a0a] text-[#111111] dark:text-white font-sans overflow-x-hidden transition-colors duration-300">
        <ThemeProvider attribute="class" defaultTheme="system" enableSystem>
          <SiteSettingsProvider whatsappNumber={whatsappNumber}>
            <CartProvider>
              <LoadingScreen />
              {/* Layout Wrapper */}
              <ClientLayoutWrapper>
                {children}
              </ClientLayoutWrapper>
              <HardwareCheckout />
              <Toaster position="bottom-right" />
            </CartProvider>
          </SiteSettingsProvider>
        </ThemeProvider>
      </body>
    </html>
  );
}
