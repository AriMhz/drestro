<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'arimhz2004@gmail.com';
$user = \App\Models\User::withoutGlobalScopes()->where('email', $email)->first();

if ($user) {
    // Check if they are trying to delete the only super admin
    if ($user->role === 'super_admin') {
        $superAdminCount = \App\Models\User::withoutGlobalScopes()->where('role', 'super_admin')->count();
        if ($superAdminCount <= 1) {
            die("<h2>❌ Error: Cannot delete the only Super Admin!</h2>");
        }
    }
    
    $user->delete();
    echo "<h2>✅ Success!</h2>";
    echo "<p>The staff account <strong>$email</strong> has been completely wiped from the database.</p>";
    echo "<p>You can now go to Staff & Roles and recreate them properly from scratch!</p>";
    echo "<p><a href='/admin/staff' style='display:inline-block;background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Go back to Staff Manager</a></p>";
} else {
    echo "<h2>ℹ️ Notice</h2>";
    echo "<p>The staff account <strong>$email</strong> was not found in the database. It may have already been deleted.</p>";
}
?>
