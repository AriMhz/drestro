import re

with open('app/Livewire/Admin/StaffManager.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the email logic
new_email_logic = """
        $inviteUrl = url('/invite/' . $token);
        
        try {
            $restaurant = auth()->user()->restaurant;
            $restaurantName = $restaurant ? $restaurant->name : 'DRestro POS';
            $roleName = ucfirst(str_replace('_', ' ', $this->role));
            
            $html = "
            <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                    <h2 style='color: #1e293b;'>You're Invited!</h2>
                    <p style='color: #475569; font-size: 16px;'>You have been invited to join <strong>{$restaurantName}</strong> as a <strong>{$roleName}</strong>.</p>
                    <p style='color: #475569; font-size: 16px;'>Click the button below to accept your invitation and securely set up your account.</p>
                    
                    <a href='{$inviteUrl}' style='display: inline-block; background-color: #10b981; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; margin-top: 20px;'>Accept Invitation</a>
                    
                    <p style='margin-top: 30px; font-size: 12px; color: #94a3b8;'>If you didn't expect this invitation, you can safely ignore this email.</p>
                </div>
            </div>
            ";
            
            \Illuminate\Support\Facades\Mail::html($html, function ($message) use ($restaurantName) {
                $message->to($this->email)
                        ->subject("You're invited to join {$restaurantName}!");
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send invitation email: " . $e->getMessage());
        }
"""

# The existing try-catch block to replace
old_try_catch = r"try \{\s*\$restaurant = auth\(\)->user\(\)->restaurant;.*?\/\/ We still continue even if email fails, because the QR code still works!\n        \}"

content = re.sub(old_try_catch, new_email_logic.strip(), content, flags=re.DOTALL)

with open('app/Livewire/Admin/StaffManager.php', 'w', encoding='utf-8') as f:
    f.write(content)

import zipfile
import base64

# Zip it up
with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    zipf.write('app/Livewire/Admin/StaffManager.php', arcname='app/Livewire/Admin/StaffManager.php')

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_email_fix.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_email_fix.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_email_fix.zip');\n"
php_script += "    echo '<h1>Email Logic Fixed!</h1>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_email_fix.php', 'w') as f:
    f.write(php_script)
