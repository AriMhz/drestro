import StaffPortalLayout from "@/src/components/StaffPortalLayout";

export default function SupportLayout({ children }: { children: React.ReactNode }) {
  return <StaffPortalLayout portalName="support">{children}</StaffPortalLayout>;
}
