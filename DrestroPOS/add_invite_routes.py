import re

with open('routes/web.php', 'r', encoding='utf-8') as f:
    content = f.read()

invite_routes = r"""
// Staff Invitation Routes (Public)
Route::get('/invite/{token}', function($token) {
    $user = \App\Models\User::where('remember_token', $token)->first();
    if (!$user) {
        return "<h1>Invalid or expired invitation link.</h1>";
    }
    
    $restaurant = \App\Models\Restaurant::find($user->restaurant_id);
    return view('auth.invite', compact('user', 'token', 'restaurant'));
})->name('invite.accept');

Route::post('/invite/{token}', function(\Illuminate\Http\Request $request, $token) {
    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'password' => 'required|min:6|confirmed'
    ]);

    $user = \App\Models\User::where('remember_token', $token)->first();
    if (!$user) {
        return back()->withErrors(['token' => 'Invalid or expired invitation link.']);
    }

    $user->update([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'name' => $request->first_name . ' ' . $request->last_name,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        'remember_token' => null // Consume the token
    ]);

    \Illuminate\Support\Facades\Auth::login($user);
    
    // Redirect based on role
    if (in_array($user->role, ['admin', 'manager', 'super_admin'])) {
        return redirect('/admin');
    }
    
    $roleMap = [
        'waiter' => '/staff/waiter',
        'cashier' => '/staff/cashier',
        'receptionist' => '/staff/hotel-reception',
        'hotel' => '/staff/room-service',
        'kitchen' => '/staff/kitchen',
        'bar' => '/staff/bar'
    ];
    
    return redirect($roleMap[$user->role] ?? '/login');
});
"""

# Append to end of file if not already there
if "Staff Invitation Routes" not in content:
    with open('routes/web.php', 'a', encoding='utf-8') as f:
        f.write("\n" + invite_routes)
