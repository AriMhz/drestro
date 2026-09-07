import nodemailer from "nodemailer";

export async function sendWelcomeEmail(to: string, userName: string): Promise<boolean> {
  const host = process.env.SMTP_HOST || "smtp.gmail.com";
  const port = parseInt(process.env.SMTP_PORT || "587");
  const user = process.env.SMTP_USER;
  const pass = process.env.SMTP_PASS;
  const from = process.env.SMTP_FROM || '"DRestro POS" <no-reply@drestro.com>';

  if (!user || !pass) {
    console.log("\n=======================================================");
    console.log("🚨 DEVELOPMENT MODE: SMTP CREDENTIALS NOT CONFIGURED 🚨");
    console.log(`Welcome Email To: ${to}`);
    console.log(`User Name: ${userName}`);
    console.log("=======================================================\n");
    return true;
  }

  try {
    const transporter = nodemailer.createTransport({
      host,
      port,
      secure: port === 465,
      auth: { user, pass },
    });

    const mailOptions = {
      from,
      to,
      subject: "Welcome to DRestro POS - Account Created Successfully! 🎉",
      html: `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 12px; background-color: #ffffff;">
          <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="color: #10b981; margin: 0;">Welcome to DRestro POS! 🍽️</h1>
            <p style="color: #666; font-size: 14px;">Smart Restaurant & Hotel Operations Platform</p>
          </div>
          
          <p style="font-size: 16px; color: #333;">Hi <strong>${userName || "Valued Restaurant Owner"}</strong>,</p>

          <p style="font-size: 14px; color: #555; line-height: 1.6;">
            Your account has been created successfully. Welcome aboard! You now have access to your restaurant workspace on DRestro.
          </p>

          <div style="background-color: #f9fafb; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; font-size: 14px; color: #333;">
              <strong>Web Login Portal:</strong> <a href="https://drestro.com/login" style="color: #10b981;">https://drestro.com/login</a><br/>
              <strong>POS Cloud Portal:</strong> <a href="https://portal.drestro.com" style="color: #10b981;">https://portal.drestro.com</a>
            </p>
          </div>

          <p style="font-size: 14px; color: #555; line-height: 1.6;">
            If you have any questions or need help setting up your menu, printers, or staff accounts, reach out to support at <a href="mailto:support@drestro.com" style="color: #10b981;">support@drestro.com</a>.
          </p>

          <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;" />
          <p style="font-size: 12px; color: #888; text-align: center;">
            © 2026 DRestro Technologies. All rights reserved.
          </p>
        </div>
      `,
    };

    await transporter.sendMail(mailOptions);
    console.log(`✅ Welcome Email sent successfully to ${to}`);
    return true;
  } catch (error) {
    console.error("Failed to send Welcome Email:", error);
    return false;
  }
}
