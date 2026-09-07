import os
import zipfile
import base64

# Create the Mailable PHP Class
mailable_code = """<?php

namespace App\Mail;

use Illuminate\\Bus\\Queueable;
use Illuminate\\Mail\\Mailable;
use Illuminate\\Queue\\SerializesModels;

class StaffInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inviteUrl;
    public $restaurantName;
    public $role;

    public function __construct($inviteUrl, $restaurantName, $role)
    {
        $this->inviteUrl = $inviteUrl;
        $this->restaurantName = $restaurantName;
        $this->role = $role;
    }

    public function build()
    {
        return $this->subject("You're invited to join {$this->restaurantName}!")
                    ->view('emails.staff-invitation');
    }
}
"""

os.makedirs('app/Mail', exist_ok=True)
with open('app/Mail/StaffInvitationMail.php', 'w', encoding='utf-8') as f:
    f.write(mailable_code)

# Create the Email Blade Template
email_blade = """<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn { display: inline-block; background-color: #10b981; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>You're Invited!</h2>
        <p>You have been invited to join <strong>{{ $restaurantName }}</strong> as a {{ ucfirst(str_replace('_', ' ', $role)) }}.</p>
        <p>Click the button below to accept your invitation and securely set up your account.</p>
        
        <a href="{{ $inviteUrl }}" class="btn">Accept Invitation</a>
        
        <p style="margin-top: 30px; font-size: 12px; color: #64748b;">If you didn't expect this invitation, you can safely ignore this email.</p>
    </div>
</body>
</html>
"""

os.makedirs('resources/views/emails', exist_ok=True)
with open('resources/views/emails/staff-invitation.blade.php', 'w', encoding='utf-8') as f:
    f.write(email_blade)

# Modify StaffManager.php to send the email
with open('app/Livewire/Admin/StaffManager.php', 'r', encoding='utf-8') as f:
    content = f.read()

email_logic = """
        $inviteUrl = url('/invite/' . $token);
        
        try {
            $restaurant = auth()->user()->restaurant;
            $restaurantName = $restaurant ? $restaurant->name : 'DRestro POS';
            
            \Illuminate\Support\Facades\Mail::to($this->email)->send(
                new \App\Mail\StaffInvitationMail($inviteUrl, $restaurantName, $this->role)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send invitation email: " . $e->getMessage());
            // We still continue even if email fails, because the QR code still works!
        }
"""

content = content.replace("$inviteUrl = url('/invite/' . $token);", email_logic)

with open('app/Livewire/Admin/StaffManager.php', 'w', encoding='utf-8') as f:
    f.write(content)

# Zip everything up
with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    zipf.write('app/Mail/StaffInvitationMail.php', arcname='app/Mail/StaffInvitationMail.php')
    zipf.write('resources/views/emails/staff-invitation.blade.php', arcname='resources/views/emails/staff-invitation.blade.php')
    zipf.write('app/Livewire/Admin/StaffManager.php', arcname='app/Livewire/Admin/StaffManager.php')

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_email_system.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_email_system.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_email_system.zip');\n"
php_script += "    echo '<h1>Email Logic Deployed!</h1>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_email_system.php', 'w') as f:
    f.write(php_script)
