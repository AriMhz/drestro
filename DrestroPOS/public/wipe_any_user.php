<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    $user = \App\Models\User::withoutGlobalScopes()->where('email', $email)->first();

    if ($user) {
        // Prevent deleting the only super admin
        if ($user->role === 'super_admin') {
            $superAdminCount = \App\Models\User::withoutGlobalScopes()->where('role', 'super_admin')->count();
            if ($superAdminCount <= 1) {
                $message = "❌ Error: Cannot delete the only Super Admin in the entire system!";
                $messageType = "error";
            } else {
                $user->delete();
                $message = "✅ Success! Super Admin $email has been wiped.";
                $messageType = "success";
            }
        } else {
            $user->delete();
            $message = "✅ Success! Staff account $email has been completely wiped from the global database.";
            $messageType = "success";
        }
    } else {
        $message = "ℹ️ Notice: The email $email was not found in the database. It is already clear.";
        $messageType = "info";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wipe Stuck User</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h2 { margin-top: 0; color: #0f172a; }
        p { color: #64748b; font-size: 14px; margin-bottom: 24px; }
        input { width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 16px; outline: none; }
        input:focus { border-color: #ef4444; }
        button { background: #ef4444; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #dc2626; }
        .msg { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 500; }
        .msg.success { background: #dcfce7; color: #166534; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .msg.info { background: #e0f2fe; color: #075985; }
        .back-link { display: inline-block; margin-top: 16px; color: #3b82f6; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Wipe Stuck User</h2>
        <p>If a staff email says "already taken" but they are not in your staff list, enter it here to wipe them globally so you can re-invite them.</p>
        
        <?php if ($message): ?>
            <div class="msg <?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="staff@example.com" required>
            <button type="submit">Wipe User Completely</button>
        </form>
        
        <a href="/admin/staff" class="back-link">← Back to Staff Manager</a>
    </div>
</body>
</html>
