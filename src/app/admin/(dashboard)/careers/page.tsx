import { prisma } from "@/src/lib/prisma";
import { revalidatePath } from "next/cache";
import CareersAdminClient from "./CareersAdminClient";

export const dynamic = "force-dynamic";

async function saveJobAction(formData: FormData) {
  "use server";
  
  const id = formData.get("id") as string;
  const title = formData.get("title") as string;
  const department = formData.get("department") as string;
  const type = formData.get("type") as string;
  const location = formData.get("location") as string;
  const salary = formData.get("salary") as string;
  const experience = formData.get("experience") as string;
  const description = formData.get("description") as string;
  const requirements = formData.get("requirements") as string;
  const isActive = formData.get("isActive") === "on";

  if (!title || !description || !requirements) return;

  const data = {
    title,
    department: department || "Engineering",
    type: type || "Full-time",
    location: location || "Kathmandu, Nepal",
    salary: salary || null,
    experience: experience || null,
    description,
    requirements,
    isActive
  };

  if (id) {
    await prisma.careerPost.update({
      where: { id },
      data
    });
  } else {
    await prisma.careerPost.create({
      data
    });
  }

  revalidatePath("/admin/careers");
  revalidatePath("/careers");
}

async function deleteJobAction(formData: FormData) {
  "use server";
  const id = formData.get("id") as string;
  if (id) {
    await prisma.careerPost.delete({ where: { id } });
    revalidatePath("/admin/careers");
    revalidatePath("/careers");
  }
}

async function toggleJobStatusAction(id: string, active: boolean) {
  "use server";
  if (id) {
    await prisma.careerPost.update({
      where: { id },
      data: { isActive: active }
    });
    revalidatePath("/admin/careers");
    revalidatePath("/careers");
  }
}

export default async function AdminCareersPage() {
  const jobs = await prisma.careerPost.findMany({
    orderBy: { createdAt: "desc" }
  });

  return (
    <CareersAdminClient 
      jobs={jobs} 
      saveJobAction={saveJobAction} 
      deleteJobAction={deleteJobAction}
      toggleJobStatusAction={toggleJobStatusAction}
    />
  );
}
