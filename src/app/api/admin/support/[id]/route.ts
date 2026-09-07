import { NextResponse, NextRequest } from "next/server";
import { prisma } from "@/src/lib/prisma";
import { getSession } from "@/src/lib/auth";

export async function PUT(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const { status } = await req.json();

    if (!status) {
      return NextResponse.json(
        { error: "Status is required" },
        { status: 400 }
      );
    }

    const session = await getSession();
    const staffId = session?.staffId || null;

    const dataToUpdate: any = {
      status,
      lastUpdatedById: staffId
    };

    if (status === "RESOLVED" || status === "CLOSED") {
      dataToUpdate.resolvedById = staffId;
    }

    const ticket = await prisma.supportTicket.update({
      where: { id },
      data: dataToUpdate,
      include: {
        restaurant: true,
        assignedTo: true,
        resolvedBy: true,
        lastUpdatedBy: true,
      }
    });

    return NextResponse.json({ success: true, ticket });
  } catch (error) {
    console.error("Error updating ticket status:", error);
    return NextResponse.json(
      { error: "Failed to update ticket status" },
      { status: 500 }
    );
  }
}
