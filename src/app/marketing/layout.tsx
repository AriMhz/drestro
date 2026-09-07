import StaffPortalLayout from "@/src/components/StaffPortalLayout";

export default function MarketingLayout({ children }: { children: React.ReactNode }) {
  return <StaffPortalLayout portalName="marketing">{children}</StaffPortalLayout>;
}
