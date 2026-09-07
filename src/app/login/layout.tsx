import { auth } from "../../../auth";
import { redirect } from "next/navigation";

export default async function LoginLayout({ children }: { children: React.ReactNode }) {
  const session = await auth();
  
  // If the user is already logged in, send them to onboarding logic
  // (Onboarding will automatically forward them to portal.drestro.com if they already have a restaurant)
  if (session?.user?.id) {
    redirect("/onboarding");
  }

  return <>{children}</>;
}
