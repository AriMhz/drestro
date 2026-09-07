import { NextResponse } from 'next/server';
import { prisma } from '@/src/lib/prisma';
import { Resend } from 'resend';

const resend = process.env.RESEND_API_KEY ? new Resend(process.env.RESEND_API_KEY) : null;

export async function POST(req: Request) {
  try {
    const { name, email, phone, subject, message } = await req.json();

    if (!name || !email || !subject || !message) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
    }

    // Generate sequential submissionCode
    const submissionCount = await prisma.contactSubmission.count();
    let submissionCode = `MSG-${100001 + submissionCount}`;
    while (true) {
      const existing = await prisma.contactSubmission.findFirst({ where: { submissionCode } });
      if (!existing) break;
      const num = parseInt(submissionCode.split('-')[1]) + 1;
      submissionCode = `MSG-${num}`;
    }

    const submission = await prisma.contactSubmission.create({
      data: {
        submissionCode,
        name,
        email,
        phone: phone || null,
        subject,
        message,
      },
    });

    // Send email notification to sales@drestro.com
    if (resend) {
      try {
        await resend.emails.send({
          from: 'DRestro Contact Form <info@drestro.com>',
          to: 'sales@drestro.com',
          subject: `New Contact Form Submission: ${subject}`,
          html: `
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
              <h2 style="color: #111; border-bottom: 2px solid #E53935; padding-bottom: 10px;">New Inquiry Received</h2>
              <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; width: 120px; color: #555;">Name:</td>
                  <td style="padding: 8px 0; color: #111;">${name}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Email:</td>
                  <td style="padding: 8px 0; color: #111;"><a href="mailto:${email}">${email}</a></td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Phone:</td>
                  <td style="padding: 8px 0; color: #111;">${phone || 'Not Provided'}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555;">Subject:</td>
                  <td style="padding: 8px 0; color: #111; font-weight: bold;">${subject}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 0; font-weight: bold; color: #555; vertical-align: top;">Message:</td>
                  <td style="padding: 8px 0; color: #111; white-space: pre-wrap; line-height: 1.5;">${message}</td>
                </tr>
              </table>
              <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;" />
              <p style="color: #999; font-size: 11px; text-align: center;">This message was submitted via the contact form on DRestro.com</p>
            </div>
          `,
        });
      } catch (mailError) {
        console.error("Failed to send contact notification email:", mailError);
      }
    } else {
      console.log("\n\n=======================================================");
      console.log("🚨 DEVELOPMENT MODE: EMAIL NOT SENT (RESEND_API_KEY MISSING) 🚨");
      console.log(`Inquiry Details:\nName: ${name}\nEmail: ${email}\nPhone: ${phone}\nSubject: ${subject}\nMessage: ${message}`);
      console.log("=======================================================\n\n");
    }

    return NextResponse.json({ success: true, id: submission.id });
  } catch (error) {
    console.error("Error creating contact submission:", error);
    return NextResponse.json({ error: "Failed to submit message" }, { status: 500 });
  }
}
