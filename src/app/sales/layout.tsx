import StaffPortalLayout from "@/src/components/StaffPortalLayout";

export default function SalesLayout({ children }: { children: React.ReactNode }) {
  return <StaffPortalLayout portalName="sales">{children}</StaffPortalLayout>;
}
