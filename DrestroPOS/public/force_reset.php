<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'arimhz2004@gmail.com';
$user = \App\Models\User::withoutGlobalScopes()->where('email', $email)->first();

if (!$user) {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>User $email not found!</p>";
    exit;
}

// Force the password to a known value
$newPassword = 'password123';
$user->password = $newPassword; // Relies on the 'hashed' cast to hash it exactly once
$user->save();

echo "<h2>✅ Password Reset!</h2>";
echo "<p>The password for <strong>$email</strong> has been forcefully reset to: <code>$newPassword</code></p>";
echo "<p>Please try logging in with this exact password!</p>";
echo "<p><a href='/login'>Go to Login</a></p>";
?>
