export async function sendSMS(to: string, message: string): Promise<boolean> {
  const token = process.env.AAKASH_SMS_AUTH_TOKEN;

  // Clean phone number (keep only digits)
  let cleanPhone = to.replace(/[^0-9]/g, "");

  // Aakash SMS expects standard 10-digit mobile number for Nepal (98xxxxxxxx)
  if (cleanPhone.length > 10) {
    if (cleanPhone.startsWith("977")) {
      cleanPhone = cleanPhone.substring(3);
    }
  }

  if (!token) {
    console.log("\n=======================================================");
    console.log("🚨 DEVELOPMENT MODE: AAKASH SMS TOKEN NOT CONFIGURED 🚨");
    console.log(`To: ${cleanPhone}`);
    console.log(`Message: ${message}`);
    console.log("=======================================================\n");
    return true; // Return true to mock success in dev
  }

  try {
    const params = new URLSearchParams();
    params.append("auth_token", token);
    params.append("to", cleanPhone);
    params.append("text", message);

    const response = await fetch("https://sms.aakashsms.com/sms/v3/send", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: params.toString(),
    });

    if (response.ok) {
      const data = await response.json();
      if (data && data.error === true) {
        console.error("Aakash SMS API response error:", data.message || "Unknown error");
        return false;
      }
      console.log(`Aakash SMS sent successfully to ${cleanPhone}.`);
      return true;
    } else {
      console.error(`Aakash SMS failed with HTTP status ${response.status}: ${await response.text()}`);
    }
  } catch (error) {
    console.error("Aakash SMS gateway exception:", error);
  }

  return false;
}
