<!DOCTYPE html>
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
