import { NextResponse } from "next/server";
import { prisma } from "@/src/lib/prisma";

export async function GET() {
  try {
    const setting = await prisma.siteSetting.findUnique({
      where: { key: 'comparison_table' }
    });
    
    if (!setting) {
      return NextResponse.json([]);
    }
    
    return NextResponse.json(JSON.parse(setting.value));
  } catch (error) {
    return NextResponse.json({ error: "Failed to fetch" }, { status: 500 });
  }
}

export async function POST(req: Request) {
  try {
    const { data } = await req.json();
    
    if (!Array.isArray(data)) {
      return NextResponse.json({ error: "Data must be an array" }, { status: 400 });
    }

    await prisma.siteSetting.upsert({
      where: { key: 'comparison_table' },
      update: { value: JSON.stringify(data) },
      create: { key: 'comparison_table', value: JSON.stringify(data) }
    });

    return NextResponse.json({ success: true });
  } catch (error) {
    return NextResponse.json({ error: "Failed to save" }, { status: 500 });
  }
}
