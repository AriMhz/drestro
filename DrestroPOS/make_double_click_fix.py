import re

with open('routes/web.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the GET and POST logic for /invite to handle double clicks
new_get = r"""Route::get('/invite/{token}', function($token) {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'manager', 'super_admin'])) return redirect('/admin');
        $roleMap = ['waiter' => '/staff/waiter', 'cashier' => '/staff/cashier', 'receptionist' => '/staff/hotel-reception', 'hotel' => '/staff/room-service', 'kitchen' => '/staff/kitchen', 'bar' => '/staff/bar'];
        return redirect($roleMap[$user->role] ?? '/login');
    }
    
    $user = \App\Models\User::where('remember_token', $token)->first();
    if (!$user) {
        return redirect('/login')->with('error', 'Invalid or expired invitation link. You may have already accepted it.');
    }
    
    $restaurant = \App\Models\Restaurant::find($user->restaurant_id);"""

# First, find the start of GET route and replace up to $restaurant
get_pattern = r"Route::get\('/invite/\{token\}', function\(\$token\) \{.*?\$restaurant = \\App\\Models\\Restaurant::find\(\$user->restaurant_id\);"
content = re.sub(get_pattern, new_get, content, flags=re.DOTALL)


new_post = r"""Route::post('/invite/{token}', function(\Illuminate\Http\Request $request, $token) {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'manager', 'super_admin'])) return redirect('/admin');
        $roleMap = ['waiter' => '/staff/waiter', 'cashier' => '/staff/cashier', 'receptionist' => '/staff/hotel-reception', 'hotel' => '/staff/room-service', 'kitchen' => '/staff/kitchen', 'bar' => '/staff/bar'];
        return redirect($roleMap[$user->role] ?? '/login');
    }

    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'password' => 'required|min:6|confirmed'
    ]);

    $user = \App\Models\User::where('remember_token', $token)->first();
    if (!$user) {
        return redirect('/login')->with('error', 'Invalid or expired invitation link. You may have already accepted it.');
    }"""

post_pattern = r"Route::post\('/invite/\{token\}', function\(\\Illuminate\\Http\\Request \$request, \$token\) \{.*?if \(!\$user\) \{\s*return back\(\)->withErrors\(\['token' => 'Invalid or expired invitation link\.'\]\);\s*\}"
content = re.sub(post_pattern, new_post, content, flags=re.DOTALL)

with open('routes/web.php', 'w', encoding='utf-8') as f:
    f.write(content)

import zipfile
import base64

with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    zipf.write('routes/web.php', arcname='routes/web.php')

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_double_click_fix.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_double_click_fix.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_double_click_fix.zip');\n"
php_script += "    if (file_exists(__DIR__ . '/../bootstrap/cache/routes-v7.php')) unlink(__DIR__ . '/../bootstrap/cache/routes-v7.php');\n"
php_script += "    echo '<h1>Fix Deployed!</h1>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_double_click_fix.php', 'w') as f:
    f.write(php_script)
