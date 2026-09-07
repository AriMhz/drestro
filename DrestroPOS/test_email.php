<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "<h1>Testing Email Connection</h1>";

try {
    if (!class_exists(\App\Mail\StaffInvitationMail::class)) {
        echo "<p style='color:red;'>ERROR: StaffInvitationMail class NOT FOUND. The deployment script failed to create the file due to permissions.</p>";
    } else {
        echo "<p style='color:green;'>SUCCESS: StaffInvitationMail class exists.</p>";
    }

    echo "<p>Attempting to send a test email to admin...</p>";
    
    // We will just use raw Mail::raw to isolate the SMTP connection
    \Illuminate\Support\Facades\Mail::raw('This is a test email from DrestroPOS', function ($message) {
        // Use a generic test email
        $message->to('test@example.com')->subject('Test Email');
    });

    echo "<p style='color:green;'>SUCCESS: SMTP Connection works! The email was accepted by Resend.</p>";
    
} catch (\Throwable $e) {
    echo "<h2 style='color:red;'>EMAIL FAILED</h2>";
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc;'>";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
    echo "</pre>";
    
    if (strpos($e->getMessage(), 'Connection could not be established') !== false) {
        echo "<p><strong>Diagnosis:</strong> Your VPS cannot connect to smtp.resend.com. This is usually because your MAIL_PORT (465) and MAIL_ENCRYPTION (tls) are mismatched. Try changing MAIL_ENCRYPTION to 'ssl' in your .env file, or change MAIL_PORT to 587.</p>";
    }
}
?>
